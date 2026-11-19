# Menjalankan prototype pemindai

## Layanan AI lokal

1. Gunakan environment project `.venv-ai` yang dibuat untuk Python 3.12. Jika membuat environment baru, pastikan TensorFlow dari `requirements.txt` terpasang:

   ```powershell
   py -3.12 -m venv .venv-ai
   .\.venv-ai\Scripts\python.exe -m pip install -r requirements.txt
   ```

2. Dari folder project, latih model Siamese dari `data/external/http_params_train.csv`:

   ```powershell
   .\.venv-ai\Scripts\python.exe data_prep.py
   ```

   Artefak dan laporan evaluasi tersimpan di `models/`. Jaringan memakai dua input teks, encoder karakter bersama (Bidirectional GRU), selisih absolut embedding, dan skor pasangan sigmoid. Dataset awal adalah nilai parameter HTTP berlabel; ia tidak mencakup IDOR/CSRF atau hasil eksploitasi aplikasi.

3. Jalankan API:

   ```powershell
   .\.venv-ai\Scripts\python.exe -m uvicorn app:app --host 127.0.0.1 --port 8001
   ```

## Dashboard

Jalankan Laravel dan Vite seperti biasa (`php artisan serve` dan `npm run dev`). Dashboard meminta URL serta konfirmasi izin. Prototype hanya mengizinkan target lokal/lab privat, mengambil satu halaman dengan GET tanpa payload, dan memeriksa beberapa header respons. Ia belum menjalankan uji SQLi, XSS, IDOR, atau CSRF.

Model membandingkan nilai parameter URL dengan contoh dukungan kelas. URL tanpa query parameter tidak mendapat penilaian AI. Skor pasangan bukan probabilitas kerentanan dan kandidat perlu diverifikasi manual. Tidak ada temuan bukan bukti sistem aman atau jaminan tidak ada miss.

## Evaluasi pelatihan saat ini

Laporan tersimpan di `models/evaluation.json`. Pada pilot ini, XSS ditahan dari pelatihan pasangan dan diuji dengan satu contoh dukungan, menggunakan split test independen. Recall XSS hanya sekitar 6.2% (FNR sekitar 93.8%); FPR sekitar 0.2%. Dengan lima contoh dukungan, recall pada konfigurasi ini 0%. Model masih eksperimen dan belum layak dipakai sebagai pemindai kerentanan. Nilai akurasi pasangan latih yang tinggi tidak mengubah kesimpulan ini.
