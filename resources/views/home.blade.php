<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#070b16">
    <title>SecDetector — Pemeriksaan Keamanan Web</title>
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen overflow-x-hidden bg-[#070b16] font-sans text-slate-200 antialiased selection:bg-blue-500/30">
    <div class="pointer-events-none fixed inset-0 bg-[linear-gradient(rgba(148,163,184,.045)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,.045)_1px,transparent_1px)] bg-[size:52px_52px]"></div>
    <div class="pointer-events-none fixed inset-x-0 top-0 h-[540px] bg-[radial-gradient(ellipse_at_top,rgba(37,99,235,.20),transparent_65%)]"></div>

    <header class="relative z-10 border-b border-white/[.06]">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-5 py-5 sm:px-8" aria-label="Navigasi utama">
            <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="SecDetector beranda">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-600/20">
                    <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3 19 6v5c0 4.7-2.9 8-7 10-4.1-2-7-5.3-7-10V6l7-3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4"/></svg>
                </span>
                <span class="text-lg font-bold tracking-tight text-white sm:text-xl">SecDetector</span>
            </a>

            <div class="hidden items-center gap-8 text-sm text-slate-400 md:flex">
                <a href="#cakupan" class="transition hover:text-white">Cakupan</a>
                <a href="#alur" class="transition hover:text-white">Alur kerja</a>
            </div>

            <a href="{{ route('scan') }}" class="inline-flex items-center gap-2 rounded-xl border border-blue-400/30 bg-blue-500/10 px-4 py-2.5 text-sm font-semibold text-blue-200 transition hover:border-blue-300/60 hover:bg-blue-500/20 sm:px-5">
                Buka scanner
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69L10.22 5.03a.75.75 0 0 1 1.06-1.06l5.5 5.5a.75.75 0 0 1 0 1.06l-5.5 5.5a.75.75 0 1 1-1.06-1.06l4.22-4.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
            </a>
        </nav>
    </header>

    <main class="relative z-10">
        <section class="mx-auto grid max-w-7xl items-center gap-12 px-5 pb-20 pt-16 sm:px-8 sm:pt-24 lg:grid-cols-[1.02fr_.98fr] lg:gap-16 lg:pb-28 lg:pt-28">
            <div>
                <div class="mb-7 inline-flex items-center gap-2 rounded-full border border-blue-400/20 bg-blue-400/[.07] px-3.5 py-2 text-xs font-semibold uppercase tracking-[.16em] text-blue-300">
                    <span class="h-1.5 w-1.5 rounded-full bg-blue-400"></span>
                    Prototype analisis web lokal
                </div>
                <h1 class="max-w-3xl text-4xl font-bold leading-[1.08] tracking-tight text-white sm:text-5xl lg:text-6xl xl:text-[4.4rem]">
                    Kenali sinyal keamanan aplikasi web <span class="bg-gradient-to-r from-blue-400 via-indigo-400 to-violet-400 bg-clip-text text-transparent">lebih awal.</span>
                </h1>
                <p class="mt-7 max-w-2xl text-base leading-8 text-slate-400 sm:text-lg">
                    Jalankan pemeriksaan pasif pada aplikasi lab yang kamu miliki atau berizin. Lihat konfigurasi respons, struktur halaman, dan eksperimen kemiripan One-Shot dalam satu alur yang transparan.
                </p>
                <div class="mt-9 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <a href="{{ route('scan') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-3.5 font-semibold text-white shadow-lg shadow-blue-600/25 transition hover:bg-blue-500">
                        Mulai pemeriksaan
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69L10.22 5.03a.75.75 0 0 1 1.06-1.06l5.5 5.5a.75.75 0 0 1 0 1.06l-5.5 5.5a.75.75 0 1 1-1.06-1.06l4.22-4.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                    </a>
                    <a href="#alur" class="inline-flex items-center justify-center rounded-xl border border-white/10 px-6 py-3.5 font-semibold text-slate-300 transition hover:border-white/20 hover:bg-white/[.04] hover:text-white">Lihat alur kerja</a>
                </div>
                <p class="mt-5 flex items-start gap-2 text-xs leading-6 text-slate-500 sm:text-sm">
                    <svg class="mt-1 h-4 w-4 shrink-0 text-emerald-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 1.75a.75.75 0 0 1 .36.092l6 3.25a.75.75 0 0 1 .39.658v4.13c0 3.642-2.177 6.57-6.404 8.317a.95.95 0 0 1-.692 0C5.427 16.45 3.25 13.522 3.25 9.88v-4.13a.75.75 0 0 1 .39-.658l6-3.25A.75.75 0 0 1 10 1.75Zm3.28 6.72a.75.75 0 0 0-1.06-1.06l-2.97 2.97-1.47-1.47a.75.75 0 0 0-1.06 1.06l2 2a.75.75 0 0 0 1.06 0l3.5-3.5Z" clip-rule="evenodd"/></svg>
                    Scanner prototype dibatasi ke target lokal/lab privat dan meminta konfirmasi izin.
                </p>
            </div>

            <div class="relative">
                <div class="absolute -inset-5 rounded-[2rem] bg-blue-600/10 blur-3xl"></div>
                <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-[#0c1221]/95 shadow-2xl shadow-black/40">
                    <div class="flex items-center justify-between border-b border-white/[.07] bg-white/[.035] px-5 py-4 sm:px-6">
                        <div class="flex items-center gap-3">
                            <div class="flex gap-1.5" aria-hidden="true"><span class="h-2.5 w-2.5 rounded-full bg-rose-400/80"></span><span class="h-2.5 w-2.5 rounded-full bg-amber-300/80"></span><span class="h-2.5 w-2.5 rounded-full bg-emerald-400/80"></span></div>
                            <span class="font-mono text-xs text-slate-400">scan.preview</span>
                        </div>
                        <span class="rounded-md border border-amber-300/15 bg-amber-300/[.08] px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider text-amber-200">Mode pasif</span>
                    </div>
                    <div class="space-y-5 p-5 sm:p-7">
                        <div>
                            <p class="font-mono text-xs text-slate-500">TARGET</p>
                            <p class="mt-2 break-all font-mono text-sm text-blue-300">http://127.0.0.1:8000</p>
                        </div>
                        <div class="space-y-3 rounded-xl border border-white/[.06] bg-[#070b14] p-4 font-mono text-xs leading-6 sm:p-5 sm:text-[13px]">
                            <p class="text-slate-500"><span class="text-blue-400">01</span>  Validasi target dan izin</p>
                            <p class="text-slate-500"><span class="text-blue-400">02</span>  Satu permintaan GET tanpa payload</p>
                            <p class="text-slate-300"><span class="text-emerald-400">03</span>  HTTP 200 · respons diterima</p>
                            <p class="text-amber-200"><span class="text-amber-300">04</span>  Observasi header keamanan</p>
                            <p class="text-slate-500"><span class="text-slate-600">05</span>  AI perlu nilai query parameter</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-xl border border-white/[.07] bg-white/[.025] p-4">
                                <p class="text-xs text-slate-500">Permintaan</p><p class="mt-1.5 text-lg font-semibold text-white">1 GET</p>
                            </div>
                            <div class="rounded-xl border border-amber-300/15 bg-amber-300/[.04] p-4">
                                <p class="text-xs text-slate-500">AI</p><p class="mt-1.5 text-lg font-semibold text-amber-200">Eksperimen</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="cakupan" class="border-y border-white/[.06] bg-white/[.018]">
            <div class="mx-auto max-w-7xl px-5 py-16 sm:px-8 lg:py-20">
                <div class="max-w-2xl">
                    <p class="text-xs font-semibold uppercase tracking-[.18em] text-blue-300">Cakupan prototype</p>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight text-white sm:text-4xl">Fokus pada hasil yang bisa dijelaskan.</h2>
                    <p class="mt-4 leading-7 text-slate-400">Setiap observasi ditampilkan terpisah dari kandidat model agar hasil scan tidak memberi kesan lebih pasti daripada buktinya.</p>
                </div>
                <div class="mt-10 grid gap-4 md:grid-cols-3">
                    <article class="rounded-2xl border border-white/[.08] bg-[#0c1221]/80 p-6">
                        <span class="grid h-11 w-11 place-items-center rounded-xl bg-blue-500/10 text-blue-300"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h10M4 18h7"/></svg></span>
                        <h3 class="mt-5 font-semibold text-white">Konfigurasi respons</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-400">Memeriksa beberapa security header pada respons halaman dan menampilkan bukti observasinya.</p>
                    </article>
                    <article class="rounded-2xl border border-white/[.08] bg-[#0c1221]/80 p-6">
                        <span class="grid h-11 w-11 place-items-center rounded-xl bg-indigo-500/10 text-indigo-300"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="4" y="4" width="16" height="16" rx="2"/><path stroke-linecap="round" d="M8 9h8M8 13h5"/></svg></span>
                        <h3 class="mt-5 font-semibold text-white">Struktur halaman</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-400">Mencatat status HTTP, jenis konten, ukuran respons, form, input, serta field kata sandi yang terlihat.</p>
                    </article>
                    <article class="rounded-2xl border border-white/[.08] bg-[#0c1221]/80 p-6">
                        <span class="grid h-11 w-11 place-items-center rounded-xl bg-violet-500/10 text-violet-300"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="8" cy="8" r="3"/><circle cx="16" cy="16" r="3"/><path stroke-linecap="round" d="m10.2 10.2 3.6 3.6"/></svg></span>
                        <h3 class="mt-5 font-semibold text-white">Eksperimen One-Shot</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-400">Membandingkan nilai parameter URL dengan contoh dukungan. Skor adalah kemiripan, bukan probabilitas kerentanan.</p>
                    </article>
                </div>
                <p class="mt-5 text-xs leading-5 text-slate-500">Mode saat ini tidak menguji SQLi, XSS, IDOR, atau CSRF secara aktif. Model belum memiliki label IDOR/CSRF.</p>
            </div>
        </section>

        <section id="alur" class="mx-auto max-w-7xl px-5 py-16 sm:px-8 lg:py-20">
            <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
                <div class="max-w-2xl">
                    <p class="text-xs font-semibold uppercase tracking-[.18em] text-blue-300">Alur kerja</p>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight text-white sm:text-4xl">Dari target lab ke laporan yang terbaca.</h2>
                </div>
                <a href="{{ route('scan') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-blue-300 transition hover:text-blue-200">Buka halaman scan <span aria-hidden="true">→</span></a>
            </div>
            <div class="mt-9 grid gap-4 md:grid-cols-3">
                @foreach ([['01', 'Masukkan target', 'Gunakan URL lokal atau lab privat yang kamu miliki atau berizin untuk diuji.'], ['02', 'Pemeriksaan pasif', 'Sistem melakukan satu GET tanpa payload serangan dan memperlihatkan tahapan prosesnya.'], ['03', 'Tinjau observasi', 'Baca status respons, temuan konfigurasi, dan status AI sebelum mengambil kesimpulan.']] as [$number, $title, $description])
                    <article class="rounded-2xl border border-white/[.08] bg-gradient-to-b from-white/[.035] to-transparent p-6">
                        <p class="font-mono text-sm text-blue-300">{{ $number }}</p>
                        <h3 class="mt-5 text-lg font-semibold text-white">{{ $title }}</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-400">{{ $description }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-5 pb-20 sm:px-8 lg:pb-24">
            <div class="flex flex-col items-start justify-between gap-6 rounded-3xl border border-blue-400/15 bg-gradient-to-r from-blue-500/[.11] via-indigo-500/[.08] to-transparent p-7 sm:p-10 md:flex-row md:items-center">
                <div>
                    <h2 class="text-2xl font-bold text-white sm:text-3xl">Siap mencoba di aplikasi lab?</h2>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-slate-400">Buka ruang scan untuk memulai pemeriksaan pasif. Pastikan kamu memiliki izin atas targetnya.</p>
                </div>
                <a href="{{ route('scan') }}" class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white transition hover:bg-blue-500">Buka scanner <span aria-hidden="true">→</span></a>
            </div>
        </section>
    </main>

    <footer class="relative z-10 border-t border-white/[.06]">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 px-5 py-6 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <span class="font-semibold text-slate-400">SecDetector</span>
            <span>Prototype akademik · Gunakan hanya pada target yang diizinkan</span>
        </div>
    </footer>
</body>
</html>
