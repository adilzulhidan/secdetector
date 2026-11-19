<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#070b16">
    <title>Scan Workspace — SecDetector</title>
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen overflow-x-hidden bg-[#070b16] font-sans text-slate-200 antialiased">
    <div class="pointer-events-none fixed inset-0 bg-[linear-gradient(rgba(148,163,184,.035)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,.035)_1px,transparent_1px)] bg-[size:52px_52px]"></div>

    <header class="relative z-10 border-b border-white/[.06] bg-[#070b16]/85 backdrop-blur-xl">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 sm:px-8" aria-label="Navigasi scan">
            <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="Kembali ke beranda">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-600/20">
                    <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3 19 6v5c0 4.7-2.9 8-7 10-4.1-2-7-5.3-7-10V6l7-3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4"/></svg>
                </span>
                <span class="text-base font-bold tracking-tight text-white sm:text-lg">SecDetector</span>
            </a>
            <div class="flex items-center gap-3 sm:gap-6">
                <a href="{{ route('home') }}" class="hidden text-sm text-slate-400 transition hover:text-white sm:inline">Beranda</a>
                <span class="hidden h-4 w-px bg-white/10 sm:block"></span>
                <span class="inline-flex items-center gap-2 text-sm font-medium text-white"><span class="h-1.5 w-1.5 rounded-full bg-blue-400"></span> Scan workspace</span>
            </div>
        </nav>
    </header>

    <main class="relative z-10 mx-auto max-w-7xl px-5 pb-16 pt-9 sm:px-8 sm:pt-12">
        <div class="mb-8 flex flex-col justify-between gap-5 lg:flex-row lg:items-end">
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-xs font-medium text-slate-500 transition hover:text-slate-300"><span aria-hidden="true">←</span> Beranda</a>
                <p class="mt-5 text-xs font-semibold uppercase tracking-[.18em] text-blue-300">Ruang pemeriksaan</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-white sm:text-4xl">Scan workspace</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-400 sm:text-base">Masukkan URL aplikasi lab yang kamu miliki atau berizin untuk diperiksa. Proses saat ini pasif dan tidak mengirim payload serangan.</p>
            </div>
            <div class="flex items-center gap-2 self-start rounded-xl border border-white/[.08] bg-white/[.025] px-3.5 py-2.5 text-xs text-slate-400 lg:self-auto">
                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                Target lokal / lab privat
            </div>
        </div>

        <section class="grid gap-5 xl:grid-cols-[minmax(0,1.55fr)_minmax(280px,.75fr)]" aria-label="Input target">
            <form id="scanForm" class="rounded-2xl border border-white/[.09] bg-[#0c1221]/90 p-5 shadow-xl shadow-black/20 sm:p-7">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="font-semibold text-white">Target aplikasi</h2>
                        <p class="mt-1 text-sm text-slate-500">Gunakan alamat lokal, misalnya http://127.0.0.1:3000</p>
                    </div>
                    <span class="rounded-lg border border-white/[.08] bg-white/[.03] px-2.5 py-1.5 font-mono text-[10px] uppercase tracking-wider text-slate-400">GET pasif</span>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <label for="targetUrl" class="sr-only">URL target</label>
                    <input type="url" id="targetUrl" name="url" required placeholder="http://127.0.0.1:3000" autocomplete="url" class="min-w-0 flex-1 rounded-xl border border-white/10 bg-[#070b14] px-4 py-3.5 font-mono text-sm text-slate-200 outline-none transition placeholder:text-slate-600 focus:border-blue-400/50 focus:ring-4 focus:ring-blue-500/10">
                    <button id="btnScan" type="submit" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-3.5 font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:opacity-60">
                        <svg id="scanIcon" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.75 2.5a6.25 6.25 0 1 0 3.91 11.126l3.857 3.857a.75.75 0 1 0 1.06-1.06l-3.856-3.858A6.25 6.25 0 0 0 8.75 2.5Zm-4.75 6.25a4.75 4.75 0 1 1 9.5 0 4.75 4.75 0 0 1-9.5 0Z" clip-rule="evenodd"/></svg>
                        <span id="buttonText">Mulai scan</span>
                    </button>
                </div>
                <label class="mt-5 flex cursor-pointer items-start gap-3 rounded-xl border border-white/[.06] bg-white/[.02] p-3.5 text-sm leading-6 text-slate-400 transition hover:border-white/10">
                    <input id="authorized" name="authorized" type="checkbox" class="mt-1 h-4 w-4 shrink-0 accent-blue-500">
                    <span>Saya pemilik target atau telah mendapat izin untuk menguji aplikasi ini.</span>
                </label>
                <p id="formNotice" class="mt-3 hidden text-sm text-amber-300" role="alert"></p>
                <p class="mt-4 flex items-start gap-2 text-xs leading-5 text-slate-500"><svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 1.75a.75.75 0 0 1 .36.092l6 3.25a.75.75 0 0 1 .39.658v4.13c0 3.642-2.177 6.57-6.404 8.317a.95.95 0 0 1-.692 0C5.427 16.45 3.25 13.522 3.25 9.88v-4.13a.75.75 0 0 1 .39-.658l6-3.25A.75.75 0 0 1 10 1.75Zm3.28 6.72a.75.75 0 0 0-1.06-1.06l-2.97 2.97-1.47-1.47a.75.75 0 0 0-1.06 1.06l2 2a.75.75 0 0 0 1.06 0l3.5-3.5Z" clip-rule="evenodd"/></svg>Hanya target lokal/lab privat. Scanner mengambil satu halaman dengan satu GET, tanpa injeksi payload. SQLi, XSS, IDOR, dan CSRF belum diuji secara aktif.</p>
            </form>

            <aside class="rounded-2xl border border-blue-400/15 bg-gradient-to-br from-blue-500/[.09] to-indigo-500/[.035] p-5 sm:p-6">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold text-white">Status layanan</h2>
                    <span id="apiStatus" class="rounded-lg border border-white/[.08] bg-black/20 px-2.5 py-1.5 font-mono text-[10px] uppercase tracking-wide text-slate-400">Memeriksa…</span>
                </div>
                <div class="mt-5 space-y-4">
                    <div class="flex items-start justify-between gap-4 border-b border-white/[.07] pb-3.5">
                        <div><p class="text-sm text-slate-300">Pemeriksaan web</p><p class="mt-1 text-xs text-slate-500">Satu GET · konfigurasi respons</p></div>
                        <span class="text-xs font-medium text-emerald-300">Aktif</span>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                        <div><p class="text-sm text-slate-300">Siamese One-Shot</p><p class="mt-1 text-xs text-slate-500">Membandingkan nilai query URL</p></div>
                        <span id="modelStatus" class="text-right text-xs font-medium text-amber-200">Memeriksa model…</span>
                    </div>
                </div>
                <p class="mt-5 rounded-xl border border-amber-300/10 bg-amber-300/[.04] p-3 text-xs leading-5 text-amber-100/75">Skor model adalah eksperimen kemiripan. Evaluasi XSS One-Shot saat ini masih memiliki FNR tinggi; jangan gunakan sebagai keputusan keamanan otomatis.</p>
            </aside>
        </section>

        <section class="mt-8" aria-labelledby="resultHeading">
            <div class="mb-4 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[.16em] text-slate-500">Hasil pemeriksaan</p>
                    <h2 id="resultHeading" class="mt-1 text-xl font-semibold text-white">Ringkasan scan</h2>
                </div>
                <div class="flex items-center gap-2 self-start rounded-lg border border-white/[.08] bg-white/[.025] px-3 py-2 text-xs text-slate-400 sm:self-auto">
                    <span id="scanStatusDot" class="h-1.5 w-1.5 rounded-full bg-slate-500"></span>
                    <span id="scanStatus">Belum ada scan</span>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <article class="rounded-2xl border border-white/[.08] bg-[#0c1221]/80 p-5">
                    <p class="text-xs font-medium uppercase tracking-[.12em] text-slate-500">Permintaan HTTP</p>
                    <p id="requestCount" class="mt-3 text-3xl font-semibold text-white">0</p>
                    <p class="mt-1 text-xs text-slate-500">Pada pemeriksaan terakhir</p>
                </article>
                <article class="rounded-2xl border border-indigo-400/15 bg-indigo-400/[.045] p-5">
                    <p class="text-xs font-medium uppercase tracking-[.12em] text-indigo-300">Observasi konfigurasi</p>
                    <p id="findingCount" class="mt-3 text-3xl font-semibold text-indigo-200">0</p>
                    <p class="mt-1 text-xs text-slate-500">Bukan kerentanan terkonfirmasi</p>
                </article>
                <article class="rounded-2xl border border-rose-400/15 bg-rose-400/[.045] p-5">
                    <p class="text-xs font-medium uppercase tracking-[.12em] text-rose-300">Kandidat pola AI</p>
                    <p id="candidateCount" class="mt-3 text-3xl font-semibold text-rose-200">—</p>
                    <p class="mt-1 text-xs text-slate-500">— berarti AI belum menilai input</p>
                </article>
            </div>

            <div class="mt-4 grid gap-4 xl:grid-cols-[minmax(0,.8fr)_minmax(0,1.2fr)]">
                <article class="overflow-hidden rounded-2xl border border-white/[.08] bg-[#0c1221]/80">
                    <div class="border-b border-white/[.06] px-5 py-4">
                        <h3 class="text-sm font-semibold text-white">Tahapan scan</h3>
                        <p class="mt-1 text-xs text-slate-500">Ringkasan alur pemeriksaan terakhir</p>
                    </div>
                    <ol id="stageList" class="space-y-0 px-5 py-2">
                        <li class="flex items-center gap-3 border-b border-white/[.05] py-3.5 text-sm text-slate-500"><span class="grid h-6 w-6 place-items-center rounded-full border border-white/10 text-[10px]">01</span>Target divalidasi</li>
                        <li class="flex items-center gap-3 border-b border-white/[.05] py-3.5 text-sm text-slate-500"><span class="grid h-6 w-6 place-items-center rounded-full border border-white/10 text-[10px]">02</span>Mengambil halaman secara pasif</li>
                        <li class="flex items-center gap-3 border-b border-white/[.05] py-3.5 text-sm text-slate-500"><span class="grid h-6 w-6 place-items-center rounded-full border border-white/10 text-[10px]">03</span>Memeriksa konfigurasi dan struktur</li>
                        <li class="flex items-center gap-3 py-3.5 text-sm text-slate-500"><span class="grid h-6 w-6 place-items-center rounded-full border border-white/10 text-[10px]">04</span>Menyusun laporan</li>
                    </ol>
                </article>

                <article class="overflow-hidden rounded-2xl border border-white/[.08] bg-[#0c1221]/80">
                    <div class="flex items-center justify-between border-b border-white/[.06] px-5 py-4">
                        <div><h3 class="text-sm font-semibold text-white">Aktivitas scan</h3><p class="mt-1 text-xs text-slate-500">Log hasil dari layanan pemindai</p></div>
                        <span class="font-mono text-[10px] uppercase tracking-wider text-slate-600">Live log</span>
                    </div>
                    <div class="max-h-[390px] min-h-[230px] overflow-auto bg-[#070b14] p-4 sm:p-5">
                        <ul id="logStream" class="space-y-3 font-mono text-xs leading-5 text-slate-400" aria-live="polite">
                            <li class="text-slate-600">Masukkan target lab dan mulai scan untuk melihat aktivitas.</li>
                        </ul>
                    </div>
                </article>
            </div>

            <div id="findingsPanel" class="mt-4 hidden rounded-2xl border border-white/[.08] bg-[#0c1221]/80">
                <div class="border-b border-white/[.06] px-5 py-4"><h3 class="text-sm font-semibold text-white">Detail observasi</h3><p class="mt-1 text-xs text-slate-500">Bukti yang diamati pada respons target</p></div>
                <ul id="findingsList" class="divide-y divide-white/[.05] px-5"></ul>
            </div>
        </section>
    </main>

    <footer class="relative z-10 border-t border-white/[.06]">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 px-5 py-5 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <a href="{{ route('home') }}" class="font-semibold text-slate-400 hover:text-white">← Kembali ke SecDetector</a>
            <span>Hasil prototype perlu diverifikasi manual.</span>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const apiUrl = 'http://127.0.0.1:8001';
            const form = document.getElementById('scanForm');
            const button = document.getElementById('btnScan');
            const buttonText = document.getElementById('buttonText');
            const logStream = document.getElementById('logStream');
            const formNotice = document.getElementById('formNotice');
            const scanStatus = document.getElementById('scanStatus');
            const scanStatusDot = document.getElementById('scanStatusDot');

            fetch(`${apiUrl}/api/health`)
                .then(response => response.ok ? response.json() : Promise.reject())
                .then(data => {
                    document.getElementById('apiStatus').textContent = 'API terhubung';
                    document.getElementById('apiStatus').className = 'rounded-lg border border-emerald-400/15 bg-emerald-400/[.06] px-2.5 py-1.5 font-mono text-[10px] uppercase tracking-wide text-emerald-200';
                    document.getElementById('modelStatus').textContent = data.model === 'ready' ? 'Eksperimen · siap' : 'Belum dilatih';
                })
                .catch(() => {
                    document.getElementById('apiStatus').textContent = 'API offline';
                    document.getElementById('modelStatus').textContent = 'Tidak tersedia';
                });

            form.addEventListener('submit', async event => {
                event.preventDefault();
                const url = document.getElementById('targetUrl').value.trim();
                const authorized = document.getElementById('authorized').checked;
                formNotice.classList.add('hidden');
                if (!url || !authorized) {
                    formNotice.textContent = !url ? 'Masukkan URL target terlebih dahulu.' : 'Konfirmasi izin untuk menguji target ini.';
                    formNotice.classList.remove('hidden');
                    return;
                }

                logStream.replaceChildren();
                document.getElementById('stageList').replaceChildren();
                document.getElementById('findingsList').replaceChildren();
                document.getElementById('findingsPanel').classList.add('hidden');
                document.getElementById('requestCount').textContent = '0';
                document.getElementById('findingCount').textContent = '0';
                document.getElementById('candidateCount').textContent = '—';
                button.disabled = true;
                buttonText.textContent = 'Memindai…';
                setScanStatus('Berjalan', 'bg-blue-400', 'text-blue-200');
                addLog('Target dan izin diterima. Menjalankan pemeriksaan pasif…', 'text-blue-300');

                try {
                    const response = await fetch(`${apiUrl}/api/scan`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ url, authorized })
                    });
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.detail || 'Permintaan scan gagal.');

                    renderStages(data.stages || []);
                    addLog(`HTTP ${data.http.status_code}; ${data.http.bytes_inspected} byte diperiksa.`, 'text-slate-300');
                    (data.findings || []).forEach(finding => addFinding(finding));
                    if (!data.findings.length) addLog('Tidak ada observasi konfigurasi pada pemeriksaan terbatas ini; hasil bukan jaminan aman.', 'text-slate-400');
                    addLog(data.model.message, 'text-amber-200');
                    addLog(data.scope_note, 'text-slate-500');
                    if (data.model.warning) addLog(data.model.warning, 'text-amber-200');

                    const modelStatus = document.getElementById('modelStatus');
                    modelStatus.textContent = data.model.status === 'ready' ? 'Eksperimen · siap' : (data.model.status === 'error' ? 'Gagal memuat' : (data.model.status === 'no_input' ? 'Perlu query parameter' : 'Belum dilatih'));
                    document.getElementById('candidateCount').textContent = data.model.status === 'ready' ? (data.model.label ? '1' : '0') : '—';
                    document.getElementById('requestCount').textContent = '1';
                    document.getElementById('findingCount').textContent = String(data.findings.length);
                    setScanStatus('Selesai', 'bg-emerald-400', 'text-emerald-200');
                } catch (error) {
                    addLog(error.message || 'API pemindai tidak dapat dihubungi.', 'text-rose-300');
                    setScanStatus('Gagal', 'bg-rose-400', 'text-rose-200');
                } finally {
                    button.disabled = false;
                    buttonText.textContent = 'Mulai scan';
                }
            });

            function addLog(message, colorClass) {
                const item = document.createElement('li');
                item.className = `flex items-start gap-3 ${colorClass}`;
                const time = document.createElement('span');
                time.className = 'shrink-0 text-slate-600';
                time.textContent = `[${new Date().toLocaleTimeString()}]`;
                const text = document.createElement('span');
                text.textContent = message;
                item.append(time, text);
                logStream.appendChild(item);
                item.scrollIntoView({ block: 'nearest' });
            }

            function addFinding(finding) {
                const panel = document.getElementById('findingsPanel');
                panel.classList.remove('hidden');
                const row = document.createElement('li');
                row.className = 'flex flex-col gap-2 py-4 sm:flex-row sm:items-start sm:justify-between sm:gap-6';
                const detail = document.createElement('div');
                const title = document.createElement('p');
                title.className = 'text-sm font-medium text-slate-200';
                title.textContent = finding.title;
                const evidence = document.createElement('p');
                evidence.className = 'mt-1 text-xs leading-5 text-slate-500';
                evidence.textContent = finding.evidence;
                detail.append(title, evidence);
                const badge = document.createElement('span');
                badge.className = 'w-fit shrink-0 rounded-md border border-amber-300/15 bg-amber-300/[.06] px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-amber-200';
                badge.textContent = `${finding.category} · ${finding.severity}`;
                row.append(detail, badge);
                document.getElementById('findingsList').appendChild(row);
            }

            function renderStages(stages) {
                const list = document.getElementById('stageList');
                stages.forEach((stage, index) => {
                    const item = document.createElement('li');
                    item.className = `flex items-center gap-3 border-b border-white/[.05] py-3.5 text-sm ${index === stages.length - 1 ? 'text-emerald-200' : 'text-slate-300'}`;
                    const number = document.createElement('span');
                    number.className = 'grid h-6 w-6 shrink-0 place-items-center rounded-full border border-emerald-400/20 bg-emerald-400/[.06] text-[10px] text-emerald-300';
                    number.textContent = String(index + 1).padStart(2, '0');
                    const label = document.createElement('span');
                    label.textContent = stage;
                    const check = document.createElement('span');
                    check.className = 'ml-auto text-emerald-400';
                    check.textContent = '✓';
                    item.append(number, label, check);
                    list.appendChild(item);
                });
            }

            function setScanStatus(label, dotClass, textClass) {
                scanStatus.textContent = label;
                scanStatus.className = textClass;
                scanStatusDot.className = `h-1.5 w-1.5 rounded-full ${dotClass}`;
            }
        });
    </script>
</body>
</html>
