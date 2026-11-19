<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#070b16">
    <meta name="description" content="Dokumentasi prototype SecDetector: quickstart, alur pemeriksaan, hasil, model Siamese One-Shot, dan batasan sistem.">
    <title>Dokumentasi — SecDetector</title>
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen overflow-x-hidden bg-[#070b16] font-sans text-slate-200 antialiased">
    <div class="pointer-events-none fixed inset-0 bg-[linear-gradient(rgba(148,163,184,.025)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,.025)_1px,transparent_1px)] bg-[size:52px_52px]"></div>

    <header class="sticky top-0 z-30 border-b border-white/[.07] bg-[#070b16]/90 backdrop-blur-xl">
        <nav class="flex items-center justify-between px-5 py-3.5 mx-auto max-w-[1600px] sm:px-8" aria-label="Navigasi dokumentasi">
            <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="SecDetector beranda">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-600/20" aria-hidden="true">
                    <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3 19 6v5c0 4.7-2.9 8-7 10-4.1-2-7-5.3-7-10V6l7-3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4"/></svg>
                </span>
                <span class="font-bold tracking-tight text-white">SecDetector <span class="font-normal text-slate-500">Docs</span></span>
            </a>
            <div class="flex items-center gap-3 text-sm sm:gap-6">
                <a href="{{ route('home') }}" class="hidden text-slate-400 transition hover:text-white sm:inline">Beranda</a>
                <a href="{{ route('scan.history') }}" class="hidden text-slate-400 transition hover:text-white sm:inline">Riwayat</a>
                <a href="{{ route('scan') }}" class="rounded-lg bg-blue-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-blue-500 sm:text-sm">Buka scanner</a>
            </div>
        </nav>
    </header>

    <div class="relative z-10 mx-auto grid max-w-[1600px] gap-8 px-5 py-8 sm:px-8 lg:grid-cols-[230px_minmax(0,1fr)] xl:grid-cols-[250px_minmax(0,1fr)_190px] xl:gap-12">
        <details class="rounded-xl border border-white/[.08] bg-[#0c1221]/90 p-4 lg:hidden">
            <summary class="cursor-pointer text-sm font-semibold text-white">Daftar dokumentasi</summary>
            <nav class="mt-4 grid gap-2 text-sm" aria-label="Daftar dokumentasi mobile">
                <a href="#overview" class="doc-link rounded-lg px-3 py-2 text-slate-300 hover:bg-white/[.05]" data-doc-link>Gambaran umum</a>
                <a href="#quickstart" class="doc-link rounded-lg px-3 py-2 text-slate-300 hover:bg-white/[.05]" data-doc-link>Quickstart</a>
                <a href="#workflow" class="doc-link rounded-lg px-3 py-2 text-slate-300 hover:bg-white/[.05]" data-doc-link>Alur pemeriksaan</a>
                <a href="#scope" class="doc-link rounded-lg px-3 py-2 text-slate-300 hover:bg-white/[.05]" data-doc-link>Input & cakupan</a>
                <a href="#results" class="doc-link rounded-lg px-3 py-2 text-slate-300 hover:bg-white/[.05]" data-doc-link>Memahami hasil</a>
                <a href="#model" class="doc-link rounded-lg px-3 py-2 text-slate-300 hover:bg-white/[.05]" data-doc-link>Model One-Shot</a>
                <a href="#history" class="doc-link rounded-lg px-3 py-2 text-slate-300 hover:bg-white/[.05]" data-doc-link>Riwayat scan</a>
                <a href="#troubleshooting" class="doc-link rounded-lg px-3 py-2 text-slate-300 hover:bg-white/[.05]" data-doc-link>Pemecahan masalah</a>
            </nav>
        </details>

        <aside class="hidden lg:block">
            <div class="sticky top-24 max-h-[calc(100vh-7rem)] overflow-y-auto pr-2">
                <label for="docsSearch" class="sr-only">Cari dokumentasi</label>
                <input id="docsSearch" type="search" placeholder="Cari dokumentasi…" class="w-full rounded-xl border border-white/[.09] bg-[#0c1221] px-3.5 py-3 text-sm text-slate-200 outline-none placeholder:text-slate-600 focus:border-blue-400/40 focus:ring-4 focus:ring-blue-500/10">
                <p id="searchEmpty" class="mt-3 hidden px-2 text-xs text-slate-500">Tidak ada topik yang cocok.</p>
                <nav class="mt-7 space-y-7" aria-label="Navigasi topik dokumentasi">
                    <div class="doc-group">
                        <p class="mb-2 px-2 text-[10px] font-bold uppercase tracking-[.18em] text-slate-600">Mulai di sini</p>
                        <a href="#overview" class="doc-link block rounded-lg border-l-2 border-blue-400 bg-blue-400/[.07] px-3 py-2 text-sm font-medium text-blue-200" data-doc-link>Gambaran umum</a>
                        <a href="#quickstart" class="doc-link mt-1 block rounded-lg border-l-2 border-transparent px-3 py-2 text-sm text-slate-400 transition hover:bg-white/[.04] hover:text-white" data-doc-link>Quickstart</a>
                    </div>
                    <div class="doc-group">
                        <p class="mb-2 px-2 text-[10px] font-bold uppercase tracking-[.18em] text-slate-600">Cara kerja</p>
                        <a href="#workflow" class="doc-link block rounded-lg border-l-2 border-transparent px-3 py-2 text-sm text-slate-400 transition hover:bg-white/[.04] hover:text-white" data-doc-link>Alur pemeriksaan</a>
                        <a href="#scope" class="doc-link mt-1 block rounded-lg border-l-2 border-transparent px-3 py-2 text-sm text-slate-400 transition hover:bg-white/[.04] hover:text-white" data-doc-link>Input & cakupan</a>
                        <a href="#results" class="doc-link mt-1 block rounded-lg border-l-2 border-transparent px-3 py-2 text-sm text-slate-400 transition hover:bg-white/[.04] hover:text-white" data-doc-link>Memahami hasil</a>
                    </div>
                    <div class="doc-group">
                        <p class="mb-2 px-2 text-[10px] font-bold uppercase tracking-[.18em] text-slate-600">Sistem</p>
                        <a href="#model" class="doc-link block rounded-lg border-l-2 border-transparent px-3 py-2 text-sm text-slate-400 transition hover:bg-white/[.04] hover:text-white" data-doc-link>Model One-Shot</a>
                        <a href="#history" class="doc-link mt-1 block rounded-lg border-l-2 border-transparent px-3 py-2 text-sm text-slate-400 transition hover:bg-white/[.04] hover:text-white" data-doc-link>Riwayat scan</a>
                    </div>
                    <div class="doc-group">
                        <p class="mb-2 px-2 text-[10px] font-bold uppercase tracking-[.18em] text-slate-600">Bantuan</p>
                        <a href="#troubleshooting" class="doc-link block rounded-lg border-l-2 border-transparent px-3 py-2 text-sm text-slate-400 transition hover:bg-white/[.04] hover:text-white" data-doc-link>Pemecahan masalah</a>
                    </div>
                </nav>
            </div>
        </aside>

        <main class="min-w-0">
            <article class="mx-auto max-w-3xl">
                <div class="mb-5 flex items-center gap-2 text-xs text-slate-500"><span>Dokumentasi</span><span aria-hidden="true">/</span><span class="text-blue-300">Mulai di sini</span></div>
                <section id="overview" class="scroll-mt-28">
                    <p class="text-xs font-bold uppercase tracking-[.2em] text-blue-300">Panduan prototype</p>
                    <h1 class="mt-3 text-4xl font-bold tracking-tight text-white sm:text-5xl">Dokumentasi SecDetector</h1>
                    <p class="mt-5 text-lg leading-8 text-slate-300">SecDetector adalah prototype pemeriksaan keamanan web lokal. Sistem mengambil satu halaman secara pasif, mengamati beberapa konfigurasi respons, dan menyediakan eksperimen kemiripan teks menggunakan model Siamese One-Shot.</p>
                    <div class="mt-7 rounded-2xl border border-amber-300/15 bg-amber-300/[.045] p-5">
                        <p class="text-sm font-semibold text-amber-100">Status kemampuan saat ini</p>
                        <p class="mt-2 text-sm leading-6 text-amber-100/75">Prototype ini belum melakukan pengujian aktif SQLi, XSS, IDOR, atau CSRF. Model menganalisis nilai parameter URL, bukan seluruh halaman atau bukti eksploitasi. Hasilnya adalah observasi dan kandidat untuk ditinjau manual.</p>
                    </div>
                </section>

                <section id="quickstart" class="mt-14 scroll-mt-28 border-t border-white/[.07] pt-10">
                    <p class="text-xs font-bold uppercase tracking-[.18em] text-slate-500">01 · Persiapan</p>
                    <h2 class="mt-2 text-2xl font-bold text-white">Quickstart</h2>
                    <p class="mt-3 leading-7 text-slate-400">Jalankan aplikasi web dan API pemindai di terminal terpisah. Perintah berikut menggunakan PowerShell pada Windows dengan Laragon dan Python 3.12.</p>
                    <h3 class="mt-7 text-base font-semibold text-white">1. Jalankan dashboard</h3>
                    <pre class="mt-3 overflow-x-auto rounded-xl border border-white/[.08] bg-[#080c16] p-4 text-sm leading-6 text-blue-100"><code>php artisan serve
npm run dev</code></pre>
                    <h3 class="mt-6 text-base font-semibold text-white">2. Siapkan dan latih model</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-400">Lewati pelatihan jika artefak model yang disertakan sudah tersedia dan ingin mencoba prototipe apa adanya.</p>
                    <pre class="mt-3 overflow-x-auto rounded-xl border border-white/[.08] bg-[#080c16] p-4 text-sm leading-6 text-blue-100"><code>py -3.12 -m venv .venv-ai
.\.venv-ai\Scripts\python.exe -m pip install -r requirements.txt
.\.venv-ai\Scripts\python.exe data_prep.py</code></pre>
                    <h3 class="mt-6 text-base font-semibold text-white">3. Jalankan API pemindai</h3>
                    <pre class="mt-3 overflow-x-auto rounded-xl border border-white/[.08] bg-[#080c16] p-4 text-sm leading-6 text-blue-100"><code>.\.venv-ai\Scripts\python.exe -m uvicorn app:app --host 127.0.0.1 --port 8001</code></pre>
                    <p class="mt-4 text-sm leading-6 text-slate-500">Dashboard tersedia melalui Laravel, sedangkan API lokal berjalan di <code class="rounded bg-white/[.06] px-1.5 py-0.5 font-mono text-blue-200">127.0.0.1:8001</code>. Browser harus dapat mengakses keduanya.</p>
                </section>

                <section id="workflow" class="mt-14 scroll-mt-28 border-t border-white/[.07] pt-10">
                    <p class="text-xs font-bold uppercase tracking-[.18em] text-slate-500">02 · Alur sistem</p>
                    <h2 class="mt-2 text-2xl font-bold text-white">Alur pemeriksaan</h2>
                    <ol class="mt-6 space-y-4">
                        <li class="flex gap-4 rounded-xl border border-white/[.07] bg-white/[.02] p-4"><span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-blue-500/10 text-sm font-bold text-blue-200">1</span><div><h3 class="font-semibold text-white">Validasi target dan izin</h3><p class="mt-1 text-sm leading-6 text-slate-400">Pengguna memasukkan URL dan menyatakan memiliki izin. API membatasi target ke localhost atau alamat yang terpetakan ke jaringan lokal/private.</p></div></li>
                        <li class="flex gap-4 rounded-xl border border-white/[.07] bg-white/[.02] p-4"><span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-blue-500/10 text-sm font-bold text-blue-200">2</span><div><h3 class="font-semibold text-white">Ambil satu halaman</h3><p class="mt-1 text-sm leading-6 text-slate-400">Pemindai mengirim satu permintaan GET tanpa payload serangan, tidak mengikuti redirect, dan membaca paling banyak 512 KiB isi respons.</p></div></li>
                        <li class="flex gap-4 rounded-xl border border-white/[.07] bg-white/[.02] p-4"><span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-blue-500/10 text-sm font-bold text-blue-200">3</span><div><h3 class="font-semibold text-white">Periksa konfigurasi dan struktur</h3><p class="mt-1 text-sm leading-6 text-slate-400">Sistem mengamati header keamanan tertentu dan menghitung formulir serta field kata sandi pada HTML.</p></div></li>
                        <li class="flex gap-4 rounded-xl border border-white/[.07] bg-white/[.02] p-4"><span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-blue-500/10 text-sm font-bold text-blue-200">4</span><div><h3 class="font-semibold text-white">Analisis parameter URL</h3><p class="mt-1 text-sm leading-6 text-slate-400">Jika URL memiliki nilai query, API membandingkannya dengan contoh support yang tersimpan. URL tanpa query tidak memperoleh skor model.</p></div></li>
                        <li class="flex gap-4 rounded-xl border border-white/[.07] bg-white/[.02] p-4"><span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-blue-500/10 text-sm font-bold text-blue-200">5</span><div><h3 class="font-semibold text-white">Tampilkan dan simpan hasil</h3><p class="mt-1 text-sm leading-6 text-slate-400">Dashboard menyajikan observasi, tahapan, dan ringkasan model. Hasil sukses disimpan pada penyimpanan browser lokal hingga 30 pemeriksaan.</p></div></li>
                    </ol>
                </section>

                <section id="scope" class="mt-14 scroll-mt-28 border-t border-white/[.07] pt-10">
                    <p class="text-xs font-bold uppercase tracking-[.18em] text-slate-500">03 · Sebelum memindai</p>
                    <h2 class="mt-2 text-2xl font-bold text-white">Input dan cakupan</h2>
                    <div class="mt-5 overflow-hidden rounded-xl border border-white/[.08]">
                        <div class="grid grid-cols-[130px_1fr] border-b border-white/[.07] bg-white/[.025] px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500"><span>Bagian</span><span>Perilaku prototype</span></div>
                        <div class="grid grid-cols-[130px_1fr] border-b border-white/[.06] px-4 py-4 text-sm"><span class="font-medium text-slate-200">Target</span><span class="leading-6 text-slate-400">Gunakan URL HTTP/HTTPS milik sendiri atau lab lokal. Public website ditolak; pastikan semua alamat DNS target mengarah ke loopback/private/link-local.</span></div>
                        <div class="grid grid-cols-[130px_1fr] border-b border-white/[.06] px-4 py-4 text-sm"><span class="font-medium text-slate-200">Permintaan</span><span class="leading-6 text-slate-400">Satu GET tanpa payload uji. Redirect tidak diikuti. Batas baca respons adalah 512 KiB.</span></div>
                        <div class="grid grid-cols-[130px_1fr] border-b border-white/[.06] px-4 py-4 text-sm"><span class="font-medium text-slate-200">Observasi</span><span class="leading-6 text-slate-400">Header Content-Security-Policy, X-Content-Type-Options, dan HSTS pada HTTPS; juga struktur form serta field kata sandi melalui HTTP.</span></div>
                        <div class="grid grid-cols-[130px_1fr] px-4 py-4 text-sm"><span class="font-medium text-slate-200">Tidak diuji</span><span class="leading-6 text-slate-400">Eksploitasi SQLi/XSS, kontrol akses IDOR, CSRF, autentikasi, konfigurasi server secara menyeluruh, dan bukti zero-day.</span></div>
                    </div>
                    <p class="mt-4 text-sm leading-6 text-slate-500">Centang konfirmasi izin sebelum scan. Konfirmasi tersebut adalah pengingat tanggung jawab pengguna; pembatasan teknis API tetap berlaku.</p>
                </section>

                <section id="results" class="mt-14 scroll-mt-28 border-t border-white/[.07] pt-10">
                    <p class="text-xs font-bold uppercase tracking-[.18em] text-slate-500">04 · Laporan</p>
                    <h2 class="mt-2 text-2xl font-bold text-white">Memahami hasil</h2>
                    <ul class="mt-5 space-y-3 text-sm leading-6 text-slate-400">
                        <li class="rounded-xl border border-white/[.07] bg-white/[.02] p-4"><strong class="text-white">Observasi konfigurasi</strong> adalah kondisi yang terlihat, misalnya header tidak ada. Ini bukan bukti bahwa aplikasi rentan atau dapat dieksploitasi.</li>
                        <li class="rounded-xl border border-white/[.07] bg-white/[.02] p-4"><strong class="text-white">Kandidat pola AI</strong> berarti nilai parameter paling mirip dengan contoh kelas support melewati ambang model. Ini bukan probabilitas kerentanan.</li>
                        <li class="rounded-xl border border-white/[.07] bg-white/[.02] p-4"><strong class="text-white">AI belum menilai</strong> biasanya berarti URL tidak memiliki query parameter untuk dibandingkan, atau model/API belum tersedia.</li>
                        <li class="rounded-xl border border-white/[.07] bg-white/[.02] p-4"><strong class="text-white">Tidak ada observasi</strong> tidak membuktikan bahwa target aman. Pemeriksaan hanya mencakup satu halaman dan beberapa kondisi pasif.</li>
                    </ul>
                    <div class="mt-5 rounded-xl border border-blue-300/15 bg-blue-300/[.04] p-4">
                        <p class="text-sm font-semibold text-blue-100">Ekspor untuk dokumentasi penelitian</p>
                        <p class="mt-2 text-sm leading-6 text-blue-100/70">Setelah scan berhasil, buka laporan untuk mengunduh DOCX yang dapat diedit, mencetak atau menyimpan PDF, dan mengunduh JSON untuk analisis lanjutan. Laporan mencatat metode, waktu, target, observasi, hasil kemiripan, dan batasan. Gunakan sebagai artefak hasil eksperimen/lampiran; artikel jurnal tetap memerlukan metode penelitian, evaluasi pada data uji, analisis, serta pembahasan tersendiri.</p>
                    </div>
                </section>

                <section id="model" class="mt-14 scroll-mt-28 border-t border-white/[.07] pt-10">
                    <p class="text-xs font-bold uppercase tracking-[.18em] text-slate-500">05 · Analisis eksperimental</p>
                    <h2 class="mt-2 text-2xl font-bold text-white">Model Siamese One-Shot</h2>
                    <p class="mt-4 leading-7 text-slate-400">Model membandingkan teks nilai parameter URL dengan contoh support berlabel. Encoder karakter bersama membentuk embedding, lalu skor kemiripan pasangan digunakan untuk memilih kelas yang paling mirip.</p>
                    <div class="mt-5 rounded-xl border border-indigo-300/15 bg-indigo-300/[.04] p-5">
                        <h3 class="font-semibold text-indigo-100">Batas evaluasi yang diketahui</h3>
                        <p class="mt-2 text-sm leading-6 text-indigo-100/70">Dataset berisi nilai parameter HTTP, bukan hasil pengujian kerentanan website. Evaluasi pilot kelas XSS menunjukkan recall One-Shot yang rendah dan false negative tinggi. Karena itu model ditampilkan sebagai eksperimen dan tidak boleh menjadi satu-satunya dasar keputusan.</p>
                    </div>
                    <p class="mt-4 text-sm leading-6 text-slate-500">Untuk angka dan metode split evaluasi terbaru, lihat <code class="rounded bg-white/[.06] px-1.5 py-0.5 font-mono text-blue-200">models/evaluation.json</code> dan <a class="text-blue-300 underline decoration-blue-300/30 underline-offset-4 hover:text-blue-200" href="{{ route('home') }}#cakupan">cakupan prototype</a>.</p>
                </section>

                <section id="history" class="mt-14 scroll-mt-28 border-t border-white/[.07] pt-10">
                    <p class="text-xs font-bold uppercase tracking-[.18em] text-slate-500">06 · Data hasil</p>
                    <h2 class="mt-2 text-2xl font-bold text-white">Riwayat scan</h2>
                    <p class="mt-4 leading-7 text-slate-400">Hasil yang berhasil disimpan di browser yang sama menggunakan localStorage, maksimal 30 item. Riwayat tidak tersimpan ke database server dan tidak otomatis tersedia di browser atau perangkat lain. Menghapus data situs/browser juga dapat menghapus riwayat.</p>
                    <a href="{{ route('scan.history') }}" class="mt-5 inline-flex text-sm font-semibold text-blue-300 transition hover:text-blue-200">Buka riwayat scan <span class="ml-2" aria-hidden="true">→</span></a>
                </section>

                <section id="troubleshooting" class="mt-14 scroll-mt-28 border-t border-white/[.07] pt-10">
                    <p class="text-xs font-bold uppercase tracking-[.18em] text-slate-500">07 · Bantuan</p>
                    <h2 class="mt-2 text-2xl font-bold text-white">Pemecahan masalah</h2>
                    <div class="mt-5 space-y-4">
                        <details class="group rounded-xl border border-white/[.08] bg-white/[.02] p-4"><summary class="cursor-pointer font-semibold text-slate-200">Status API offline</summary><p class="mt-3 text-sm leading-6 text-slate-400">Pastikan layanan uvicorn berjalan di port 8001 dan dashboard dibuka melalui localhost port 8000. Jika dashboard memakai origin berbeda, sesuaikan <code class="rounded bg-white/[.06] px-1 font-mono text-blue-200">SCANNER_ALLOWED_ORIGINS</code> sebelum menjalankan API.</p></details>
                        <details class="group rounded-xl border border-white/[.08] bg-white/[.02] p-4"><summary class="cursor-pointer font-semibold text-slate-200">Model belum dilatih atau gagal dimuat</summary><p class="mt-3 text-sm leading-6 text-slate-400">Periksa paket pada <code class="rounded bg-white/[.06] px-1 font-mono text-blue-200">requirements.txt</code> dan keberadaan file model di folder <code class="rounded bg-white/[.06] px-1 font-mono text-blue-200">models/</code>. Jalankan ulang pelatihan dengan perintah pada Quickstart bila diperlukan.</p></details>
                        <details class="group rounded-xl border border-white/[.08] bg-white/[.02] p-4"><summary class="cursor-pointer font-semibold text-slate-200">Ekspor DOCX tidak tersedia</summary><p class="mt-3 text-sm leading-6 text-slate-400">Pastikan ekstensi ZIP untuk PHP aktif. Alternatifnya, gunakan tombol Cetak / Simpan PDF pada halaman laporan.</p></details>
                        <details class="group rounded-xl border border-white/[.08] bg-white/[.02] p-4"><summary class="cursor-pointer font-semibold text-slate-200">AI tidak mendapat input</summary><p class="mt-3 text-sm leading-6 text-slate-400">Tambahkan query parameter pada URL lab, misalnya <code class="rounded bg-white/[.06] px-1 font-mono text-blue-200">http://127.0.0.1:8000/search?q=contoh</code>. Nilai query itulah yang dibandingkan oleh model.</p></details>
                        <details class="group rounded-xl border border-white/[.08] bg-white/[.02] p-4"><summary class="cursor-pointer font-semibold text-slate-200">Target ditolak atau tidak dapat diakses</summary><p class="mt-3 text-sm leading-6 text-slate-400">Gunakan target lokal/lab yang DNS-nya hanya mengarah ke alamat loopback atau private. Pastikan server target sedang berjalan dan URL menggunakan skema HTTP atau HTTPS.</p></details>
                    </div>
                </section>

                <div class="mt-14 flex flex-col justify-between gap-4 border-t border-white/[.07] pt-6 text-sm sm:flex-row sm:items-center">
                    <span class="text-slate-500">Dokumentasi untuk prototype SecDetector</span>
                    <a href="{{ route('scan') }}" class="font-semibold text-blue-300 hover:text-blue-200">Lanjut ke scanner →</a>
                </div>
            </article>
        </main>

        <aside class="hidden xl:block">
            <div class="sticky top-24">
                <p class="mb-4 text-[10px] font-bold uppercase tracking-[.18em] text-slate-500">Di halaman ini</p>
                <nav class="space-y-1 border-l border-white/[.1] text-xs" aria-label="Daftar bagian halaman">
                    <a href="#overview" class="block border-l border-blue-400 -ml-px py-2 pl-3 text-blue-200">Gambaran umum</a>
                    <a href="#quickstart" class="block py-2 pl-3 text-slate-500 transition hover:text-white">Quickstart</a>
                    <a href="#workflow" class="block py-2 pl-3 text-slate-500 transition hover:text-white">Alur pemeriksaan</a>
                    <a href="#scope" class="block py-2 pl-3 text-slate-500 transition hover:text-white">Input & cakupan</a>
                    <a href="#results" class="block py-2 pl-3 text-slate-500 transition hover:text-white">Memahami hasil</a>
                    <a href="#model" class="block py-2 pl-3 text-slate-500 transition hover:text-white">Model One-Shot</a>
                    <a href="#history" class="block py-2 pl-3 text-slate-500 transition hover:text-white">Riwayat scan</a>
                    <a href="#troubleshooting" class="block py-2 pl-3 text-slate-500 transition hover:text-white">Pemecahan masalah</a>
                </nav>
            </div>
        </aside>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const search = document.getElementById('docsSearch');
            if (!search) return;
            const links = [...document.querySelectorAll('[data-doc-link]')];
            const groups = [...document.querySelectorAll('.doc-group')];
            const empty = document.getElementById('searchEmpty');

            search.addEventListener('input', () => {
                const query = search.value.trim().toLocaleLowerCase('id');
                let visibleCount = 0;
                links.forEach(link => {
                    const matches = link.textContent.toLocaleLowerCase('id').includes(query);
                    link.classList.toggle('hidden', !matches);
                    if (matches) visibleCount++;
                });
                groups.forEach(group => group.classList.toggle('hidden', !group.querySelector('[data-doc-link]:not(.hidden)')));
                empty.classList.toggle('hidden', visibleCount > 0);
            });

            document.querySelectorAll('a[href^="#"]').forEach(link => {
                link.addEventListener('click', () => {
                    const menu = link.closest('details');
                    if (menu) menu.open = false;
                });
            });
        });
    </script>
</body>
</html>
