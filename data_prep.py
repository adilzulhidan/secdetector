"""Train and evaluate the prototype Siamese model on labeled HTTP parameters.

The input corpus is parameter text, not full website responses. Results are
therefore about request-value similarity, not proof that a target is vulnerable.
"""

from __future__ import annotations

import csv
import json
import random
from collections import Counter, defaultdict
from pathlib import Path

import numpy as np
import tensorflow as tf
from siamese_model import build_model
from tensorflow.keras.preprocessing.text import Tokenizer
from tensorflow.keras.utils import pad_sequences

SEED = 17
MAX_SEQ_LENGTH = 256
VOCAB_SIZE = 4000
PAIRS_PER_CLASS = 400
EPOCHS = 8
DATA_DIR = Path(__file__).parent / "data" / "external"
ARTIFACT_DIR = Path(__file__).parent / "models"
TRAIN_FILE = DATA_DIR / "http_params_train.csv"
TEST_FILE = DATA_DIR / "http_params_test.csv"
HELD_OUT_CLASS = "xss"


def read_rows(path: Path):
    if not path.is_file():
        raise FileNotFoundError(f"Dataset tidak ditemukan: {path}")
    rows = []
    with path.open("r", encoding="utf-8-sig", newline="") as stream:
        for row in csv.DictReader(stream):
            text = (row.get("payload") or "").strip()
            label = (row.get("attack_type") or "").strip().lower()
            if text and label:
                rows.append((text, label))
    if not rows:
        raise ValueError(f"Dataset kosong atau kolom payload/attack_type tidak ada: {path}")
    return rows


def make_balanced_pairs(sequences: np.ndarray, labels: list[str], pairs_per_class: int):
    rng = random.Random(SEED)
    by_label = defaultdict(list)
    for index, label in enumerate(labels):
        by_label[label].append(index)
    classes = sorted(by_label)
    left, right, targets = [], [], []
    for label in classes:
        if len(by_label[label]) < 2:
            continue
        for _ in range(pairs_per_class):
            anchor = rng.choice(by_label[label])
            positive = rng.choice([i for i in by_label[label] if i != anchor])
            other_label = rng.choice([candidate for candidate in classes if candidate != label])
            negative = rng.choice(by_label[other_label])
            left.extend((sequences[anchor], sequences[anchor]))
            right.extend((sequences[positive], sequences[negative]))
            targets.extend((1.0, 0.0))
    order = list(range(len(targets)))
    rng.shuffle(order)
    left_array = np.asarray(left, dtype="int32")[order]
    right_array = np.asarray(right, dtype="int32")[order]
    target_array = np.asarray(targets, dtype="float32")[order]
    return [left_array, right_array], target_array


def encode(tokenizer, texts):
    sequences = tokenizer.texts_to_sequences([text.lower() for text in texts])
    return pad_sequences(sequences, maxlen=MAX_SEQ_LENGTH, padding="post", truncating="post")


def train_model(rows, epochs=EPOCHS, validation=True):
    if validation:
        rng = random.Random(SEED)
        grouped = defaultdict(list)
        for row in rows:
            grouped[row[1]].append(row)
        training_rows, validation_rows = [], []
        for label, items in grouped.items():
            rng.shuffle(items)
            validation_count = max(1, int(len(items) * 0.15)) if len(items) >= 4 else 0
            validation_rows.extend(items[:validation_count])
            training_rows.extend(items[validation_count:])
    else:
        training_rows, validation_rows = rows, []

    tokenizer = Tokenizer(num_words=VOCAB_SIZE, char_level=True, lower=True, oov_token="?")
    tokenizer.fit_on_texts([text for text, _ in training_rows])
    training_sequences = encode(tokenizer, [text for text, _ in training_rows])
    pair_inputs, pair_targets = make_balanced_pairs(training_sequences, [label for _, label in training_rows], PAIRS_PER_CLASS)
    vocabulary_size = min(VOCAB_SIZE, max(tokenizer.word_index.values(), default=0) + 1)
    model = build_model(vocabulary_size, MAX_SEQ_LENGTH)
    callbacks = [tf.keras.callbacks.EarlyStopping(monitor="val_loss", patience=2, restore_best_weights=True)]
    fit_options = {}
    if validation_rows:
        validation_sequences = encode(tokenizer, [text for text, _ in validation_rows])
        val_inputs, val_targets = make_balanced_pairs(validation_sequences, [label for _, label in validation_rows], max(100, PAIRS_PER_CLASS // 4))
        fit_options["validation_data"] = (val_inputs, val_targets)
    else:
        callbacks = []
    history = model.fit(pair_inputs, pair_targets, batch_size=64, epochs=epochs, shuffle=True, verbose=2, callbacks=callbacks, **fit_options)
    return model, tokenizer, history


def evaluate_held_out_class(train_rows, test_rows):
    known_rows = [(text, label) for text, label in train_rows if label != HELD_OUT_CLASS]
    train_by_label = defaultdict(list)
    for text, label in train_rows:
        train_by_label[label].append(text)
    if not train_by_label.get(HELD_OUT_CLASS):
        raise ValueError(f"Tidak ada contoh dukungan untuk kelas yang ditahan: {HELD_OUT_CLASS}")

    model, tokenizer, fold_history = train_model(known_rows)
    validation_losses = fold_history.history.get("val_loss", [])
    selected_epochs = max(1, int(np.argmin(validation_losses)) + 1) if validation_losses else EPOCHS
    queries = [(text, label) for text, label in test_rows if label in {"norm", HELD_OUT_CLASS}]
    query_sequences = encode(tokenizer, [text for text, _ in queries])
    threshold = 0.5

    def score_support_count(support_count):
        rng = random.Random(SEED + support_count)
        support_labels = sorted(train_by_label)
        support_texts = {
            label: rng.sample(train_by_label[label], min(support_count, len(train_by_label[label])))
            for label in support_labels
        }
        support_sequences = {label: encode(tokenizer, texts) for label, texts in support_texts.items()}
        predictions = []
        for start in range(0, len(queries), 512):
            batch = query_sequences[start:start + 512]
            class_scores = []
            for label in support_labels:
                supports = support_sequences[label]
                query_pairs = np.repeat(batch, len(supports), axis=0)
                support_pairs = np.tile(supports, (len(batch), 1))
                scores = model.predict([query_pairs, support_pairs], batch_size=256, verbose=0).reshape(len(batch), len(supports))
                class_scores.append(scores.mean(axis=1))
            scores_by_class = np.stack(class_scores, axis=1)
            best = scores_by_class.argmax(axis=1)
            for index, class_index in enumerate(best):
                predicted = support_labels[class_index]
                if predicted == HELD_OUT_CLASS and scores_by_class[index, class_index] < threshold:
                    predicted = "norm"
                predictions.append(predicted)

        actual_positive = np.asarray([label == HELD_OUT_CLASS for _, label in queries])
        predicted_positive = np.asarray([label == HELD_OUT_CLASS for label in predictions])
        tp = int(np.sum(actual_positive & predicted_positive))
        fn = int(np.sum(actual_positive & ~predicted_positive))
        fp = int(np.sum(~actual_positive & predicted_positive))
        tn = int(np.sum(~actual_positive & ~predicted_positive))
        return {
            "support_examples_per_class": support_count,
            "confusion_matrix_binary": {"TP": tp, "FN": fn, "FP": fp, "TN": tn},
            "precision": tp / (tp + fp) if tp + fp else 0.0,
            "recall_tpr": tp / (tp + fn) if tp + fn else 0.0,
            "fnr": fn / (tp + fn) if tp + fn else 0.0,
            "fpr": fp / (fp + tn) if fp + tn else 0.0,
            "accuracy": (tp + tn) / len(queries) if queries else 0.0,
        }

    support_results = {str(count): score_support_count(count) for count in (1, 5)}
    return {
        "held_out_class": HELD_OUT_CLASS,
        "query_count": len(queries),
        "selected_epochs_from_validation": selected_epochs,
        "best_validation_loss": min(validation_losses) if validation_losses else None,
        "threshold": threshold,
        "results_by_support_count": support_results,
        "note": "XSS was excluded from pair training. One-shot and five-shot support results use the independent supplied test split. This measures parameter-text matching, not target vulnerability confirmation.",
    }


def main():
    random.seed(SEED)
    np.random.seed(SEED)
    tf.keras.utils.set_random_seed(SEED)
    train_rows = read_rows(TRAIN_FILE)
    test_rows = read_rows(TEST_FILE)
    train_counts = Counter(label for _, label in train_rows)
    test_counts = Counter(label for _, label in test_rows)
    evaluation = evaluate_held_out_class(train_rows, test_rows)

    final_epochs = evaluation["selected_epochs_from_validation"]
    final_model, tokenizer, final_history = train_model(train_rows, epochs=final_epochs, validation=False)
    support_rng = random.Random(SEED)
    support_examples = {}
    for label in sorted(train_counts):
        support_examples[label] = support_rng.choice([text for text, row_label in train_rows if row_label == label])

    ARTIFACT_DIR.mkdir(parents=True, exist_ok=True)
    final_model.save_weights(ARTIFACT_DIR / "siamese_pair_model.weights.h5")
    (ARTIFACT_DIR / "tokenizer.json").write_text(tokenizer.to_json(), encoding="utf-8")
    metadata = {
        "model": "character-level Siamese text-pair model",
        "weights_file": "siamese_pair_model.weights.h5",
        "weights_file": "siamese_pair_model.weights.h5",
        "comparison": "shared Bidirectional GRU encoder + absolute embedding difference + learned sigmoid pair score",
        "labels": sorted(train_counts),
        "support_examples": support_examples,
        "max_seq_length": MAX_SEQ_LENGTH,
        "vocabulary_size": min(VOCAB_SIZE, max(tokenizer.word_index.values(), default=0) + 1),
        "training_examples": len(train_rows),
        "training_label_counts": dict(train_counts),
        "test_examples": len(test_rows),
        "test_label_counts": dict(test_counts),
        "threshold": 0.5,
        "corpus_warning": "HTTP parameter values from a public corpus; not full traffic or vulnerability outcomes. IDOR/CSRF are absent. Score is not a probability of vulnerability.",
    }
    (ARTIFACT_DIR / "metadata.json").write_text(json.dumps(metadata, indent=2), encoding="utf-8")
    report = {
        "dataset": "Morzeux/HttpParamsDataset payload_train.csv + payload_test.csv",
        "seed": SEED,
        "pair_training_examples": PAIRS_PER_CLASS * len(train_counts) * 2,
        "epochs_completed": len(final_history.history["loss"]),
        "held_out_one_shot_evaluation": evaluation,
        "warning": "This pilot must not be interpreted as confirmed web-vulnerability detection or zero-day assurance.",
    }
    (ARTIFACT_DIR / "evaluation.json").write_text(json.dumps(report, indent=2), encoding="utf-8")
    print(json.dumps(report, indent=2))
    print(f"Artifacts written to: {ARTIFACT_DIR}")


if __name__ == "__main__":
    main()
