"""Shared model architecture for training and inference."""

from tensorflow.keras import Model, layers


def build_model(vocab_size: int, max_seq_length: int = 256, compile_model: bool = True):
    sequence = layers.Input((max_seq_length,), dtype="int32", name="parameter_text")
    encoded = layers.Embedding(vocab_size, 48, mask_zero=True)(sequence)
    encoded = layers.Bidirectional(layers.GRU(32))(encoded)
    encoded = layers.Dense(64, activation="relu")(encoded)
    encoder = Model(sequence, layers.UnitNormalization()(encoded), name="shared_text_encoder")

    left = layers.Input((max_seq_length,), dtype="int32", name="left_parameter")
    right = layers.Input((max_seq_length,), dtype="int32", name="right_parameter")
    left_embedding, right_embedding = encoder(left), encoder(right)
    maximum = layers.Maximum()([left_embedding, right_embedding])
    minimum = layers.Minimum()([left_embedding, right_embedding])
    difference = layers.Subtract(name="absolute_embedding_difference")([maximum, minimum])
    compared = layers.Dense(128, activation="relu")(difference)
    similarity = layers.Dense(1, activation="sigmoid", name="similarity_score")(compared)
    model = Model([left, right], similarity, name="siamese_http_parameter_model")
    if compile_model:
        model.compile(optimizer="adam", loss="binary_crossentropy", metrics=["accuracy"])
    return model
