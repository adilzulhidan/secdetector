"""Local, passive web assessment API for the SecDetector prototype.

Only targets on the local machine / explicitly local lab hostnames are allowed.
The scanner performs one bounded GET request and never injects attack payloads.
"""

from __future__ import annotations

import ipaddress
import json
import os
import socket
from functools import lru_cache
from pathlib import Path
from html.parser import HTMLParser
from urllib.parse import urlparse

import requests
from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field
from urllib.parse import parse_qsl

app = FastAPI(title="SecDetector Local Scanner", version="0.2.0")
app.add_middleware(
    CORSMiddleware,
    allow_origins=os.getenv("SCANNER_ALLOWED_ORIGINS", "http://127.0.0.1:8000,http://localhost:8000").split(","),
    allow_credentials=False,
    allow_methods=["POST", "GET"],
    allow_headers=["Content-Type"],
)
MODEL_DIR = Path(__file__).parent / "models"


def _one_shot_match(request_text: str):
    """Compare a sanitized request description with one labeled support sample per class."""
    model_path = MODEL_DIR / "siamese_pair_model.weights.h5"
    metadata_path = MODEL_DIR / "metadata.json"
    tokenizer_path = MODEL_DIR / "tokenizer.json"
    if not all(path.is_file() for path in (model_path, metadata_path, tokenizer_path)):
        return {"name": "Siamese One-Shot", "status": "not_trained", "score": None, "label": None, "message": "Model belum tersedia; tidak ada skor kemiripan yang dibuat-buat."}
    try:
        metadata, tokenizer, siamese = _load_model_artifacts()
        import numpy as np
        from tensorflow.keras.utils import pad_sequences
        support = metadata["support_examples"]
        examples = [support[label] for label in support]
        sequences = tokenizer.texts_to_sequences([request_text.lower(), *[text.lower() for text in examples]])
        padded = pad_sequences(sequences, maxlen=metadata["max_seq_length"], padding="post", truncating="post")
        query_batch = np.repeat(padded[:1], len(examples), axis=0)
        similarities = siamese.predict([query_batch, padded[1:]], verbose=0).reshape(-1)
        best = int(np.argmax(similarities))
        score = float(similarities[best])
        label = list(support)[best]
        threshold = float(metadata.get("threshold", 0.80))
        candidate = score >= threshold and label not in {"norm", "normal", "benign"}
        return {"name": "Siamese One-Shot", "status": "ready", "score": round(score, 4), "label": label if candidate else None,
                "message": f"Pola paling mirip: {label} ({score:.2f}). Kandidat perlu verifikasi manual." if candidate else "Tidak ada pola support yang melewati ambang; ini bukan bukti target aman.",
                "warning": metadata.get("corpus_warning", "")}
    except Exception as exc:
        return {"name": "Siamese One-Shot", "status": "error", "score": None, "label": None, "message": f"Model gagal dimuat ({exc.__class__.__name__}); hasil AI tidak tersedia."}


@lru_cache(maxsize=1)
def _load_model_artifacts():
    from tensorflow.keras.preprocessing.text import tokenizer_from_json
    from siamese_model import build_model

    metadata = json.loads((MODEL_DIR / "metadata.json").read_text(encoding="utf-8"))
    tokenizer = tokenizer_from_json((MODEL_DIR / "tokenizer.json").read_text(encoding="utf-8"))
    siamese = build_model(metadata["vocabulary_size"], metadata["max_seq_length"], compile_model=False)
    siamese.load_weights(MODEL_DIR / metadata["weights_file"])
    return metadata, tokenizer, siamese


class ScanRequest(BaseModel):
    url: str = Field(min_length=8, max_length=2048)
    authorized: bool


class PageStructure(HTMLParser):
    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.forms = 0
        self.password_fields = 0
        self.inputs = 0

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        if tag.lower() == "form":
            self.forms += 1
        if tag.lower() == "input":
            self.inputs += 1
            if attrs.get("type", "").lower() == "password":
                self.password_fields += 1


def _validate_local_target(raw_url: str) -> str:
    parsed = urlparse(raw_url.strip())
    if parsed.scheme not in {"http", "https"} or not parsed.hostname or parsed.username or parsed.password:
        raise HTTPException(422, "Masukkan URL HTTP/HTTPS yang valid tanpa nama pengguna atau kata sandi.")
    host = parsed.hostname.rstrip(".").lower()
    local_names = {"localhost", "127.0.0.1", "::1"}
    if host not in local_names and not host.endswith((".localhost", ".test")):
        try:
            addresses = {ipaddress.ip_address(item[4][0]) for item in socket.getaddrinfo(host, None)}
        except (OSError, ValueError):
            raise HTTPException(422, "Target tidak dapat dipastikan sebagai lingkungan lokal. Gunakan localhost atau domain lab lokal.")
        if not addresses or any(not address.is_private and not address.is_loopback and not address.is_link_local for address in addresses):
            raise HTTPException(403, "Untuk prototype ini, pemindaian hanya diizinkan ke target lokal/lab privat.")
    return parsed.geturl()


@app.get("/api/health")
async def health():
    model = "ready" if all((MODEL_DIR / name).is_file() for name in ("siamese_pair_model.weights.h5", "tokenizer.json", "metadata.json")) else "not_trained"
    return {"status": "ready", "model": model, "scope": "local_lab_only"}


@app.post("/api/scan")
async def process_scan(request: ScanRequest):
    if not request.authorized:
        raise HTTPException(400, "Konfirmasi bahwa Anda berwenang menguji target ini.")
    target = _validate_local_target(request.url)
    parsed_target = urlparse(target)
    parameter_values = [value for _, value in parse_qsl(parsed_target.query, keep_blank_values=False) if value.strip()]
    if parameter_values:
        parameter_results = [_one_shot_match(value) for value in parameter_values]
        available_results = [item for item in parameter_results if item.get("status") in {"ready", "error"}]
        model_result = max(available_results, key=lambda item: item.get("score") or 0) if available_results else parameter_results[0]
        model_result = {**model_result, "evaluated_parameter_count": len(parameter_results), "input_type": "query_parameter_values"}
    else:
        model_result = {"name": "Siamese One-Shot", "status": "no_input", "score": None, "label": None,
                        "message": "Tidak ada nilai parameter URL untuk dibandingkan; model dilatih pada nilai parameter HTTP, bukan header keamanan halaman."}
    stages = ["Target divalidasi", "Mengambil halaman secara pasif", "Memeriksa konfigurasi dan struktur", "Menyusun laporan"]
    try:
        response = requests.get(
            target,
            timeout=(3, 8),
            allow_redirects=False,
            headers={"User-Agent": "SecDetector-Prototype/0.2 (authorized local assessment)"},
            stream=True,
        )
        body = response.raw.read(512 * 1024, decode_content=True).decode(response.encoding or "utf-8", errors="replace")
        response.close()
    except requests.RequestException as exc:
        raise HTTPException(502, f"Target lokal tidak dapat diakses: {exc.__class__.__name__}.")

    headers = {key.lower(): value for key, value in response.headers.items()}
    parser = PageStructure()
    parser.feed(body)
    findings = []
    for header, label in [
        ("content-security-policy", "Content-Security-Policy"),
        ("x-content-type-options", "X-Content-Type-Options"),
        ("strict-transport-security", "Strict-Transport-Security"),
    ]:
        if header not in headers and (header != "strict-transport-security" or target.startswith("https://")):
            findings.append({"category": "Konfigurasi", "severity": "informational", "title": f"Header {label} tidak ditemukan", "evidence": "Header respons tidak memuat header tersebut.", "status": "observed"})
    if target.startswith("http://") and parser.password_fields:
        findings.append({"category": "Konfigurasi", "severity": "medium", "title": "Form kata sandi dikirim melalui HTTP", "evidence": f"Halaman memuat {parser.password_fields} field kata sandi dan URL menggunakan HTTP.", "status": "observed"})

    if model_result.get("label"):
        classification = "candidate_pattern"
    elif findings:
        classification = "observations_found"
    elif model_result.get("status") == "no_input":
        classification = "ai_not_evaluated"
    elif model_result.get("status") != "ready":
        classification = "ai_unavailable"
    else:
        classification = "no_issue_observed"

    return {
        "url": target,
        "status": "completed",
        "classification": classification,
        "summary": "Pemeriksaan pasif selesai. Tidak ditemukannya observasi bukan bukti bahwa target aman.",
        "model": model_result,
        "http": {"status_code": response.status_code, "content_type": headers.get("content-type", "tidak diketahui"), "bytes_inspected": len(body.encode("utf-8"))},
        "page_structure": {"forms": parser.forms, "inputs": parser.inputs, "password_fields": parser.password_fields},
        "stages": stages,
        "findings": findings,
        "scope_note": "Hanya satu permintaan GET tanpa payload uji. SQLi, XSS, IDOR, dan CSRF belum diuji oleh mode pasif ini.",
    }
