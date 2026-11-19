<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#070b16">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laporan Scan — SecDetector</title>
    @vite('resources/css/app.css')
    <style>
        @page { size: A4; margin: 16mm; }
        @media print {
            html, body { background: #fff !important; color: #111827 !important; }
            body { min-height: auto !important; overflow: visible !important; font-size: 10pt; }
            .report-toolbar, .report-grid-bg, #reportError { display: none !important; }
            .report-paper { max-width: none !important; margin: 0 !important; padding: 0 !important; border: 0 !important; background: #fff !important; box-shadow: none !important; }
            .report-paper * { color: #111827 !important; border-color: #d1d5db !important; }
            .report-paper .report-muted { color: #4b5563 !important; }
            .report-paper .report-callout { background: #f3f4f6 !important; }
            .report-section { break-inside: avoid; }
            a { color: #111827 !important; text-decoration: none !important; }
        }
    </style>
</head>
<body class="min-h-screen bg-[#070b16] font-sans text-slate-200 antialiased">
    <div class="report-grid-bg pointer-events-none fixed inset-0 bg-[linear-gradient(rgba(148,163,184,.025)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,.025)_1px,transparent_1px)] bg-[size:52px_52px]"></div>
    <header class="report-toolbar relative z-10 border-b border-white/[.07] bg-[#070b16]/90 backdrop-blur-xl">
        <nav class="flex items-center justify-between gap-4 px-5 py-3.5 mx-auto max-w-5xl sm:px-8" aria-label="Navigasi laporan">
            <a href="{{ route('scan.history') }}" class="text-sm text-slate-400 transition hover:text-white">← Kembali ke riwayat</a>
            <div class="flex gap-2">
                <button id="downloadJson" type="button" class="rounded-lg border border-white/10 px-3 py-2 text-xs font-semibold text-slate-300 transition hover:bg-white/[.05]">Unduh JSON</button>
                <button id="downloadDocx" type="button" class="rounded-lg border border-blue-400/20 px-3 py-2 text-xs font-semibold text-blue-200 transition hover:bg-blue-400/[.08]">Unduh DOCX</button>
                <button id="printReport" type="button" class="rounded-lg bg-blue-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-blue-500">Cetak / Simpan PDF</button>
            </div>
        </nav>
    </header>

    <main class="relative z-10 px-5 py-8 mx-auto max-w-5xl sm:px-8 sm:py-10">
        <div id="exportNotice" class="mb-5 hidden rounded-xl border border-amber-300/20 bg-amber-300/[.06] p-4 text-sm text-amber-100" role="status"></div>
        <div id="reportError" class="hidden rounded-2xl border border-amber-300/20 bg-amber-300/[.06] p-6">
            <h1 class="text-lg font-semibold text-amber-100">Data laporan tidak ditemukan</h1>
            <p class="mt-2 text-sm leading-6 text-amber-100/75">Laporan hanya dapat dibuka pada browser dan profil yang menyimpan riwayat scan. Riwayat mungkin sudah dihapus atau browser memakai penyimpanan lokal yang berbeda.</p>
            <a href="{{ route('scan.history') }}" class="mt-4 inline-flex text-sm font-semibold text-blue-300 hover:text-blue-200">Kembali ke riwayat scan →</a>
        </div>

        <article id="reportPaper" class="report-paper mx-auto hidden max-w-4xl rounded-2xl border border-white/[.08] bg-[#0c1221] p-6 shadow-2xl shadow-black/20 sm:p-10">
            <header class="report-section border-b border-white/[.1] pb-6">
                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[.18em] text-blue-300">SecDetector · laporan pemeriksaan</p>
                        <h1 class="mt-3 text-3xl font-bold tracking-tight text-white">Hasil pemeriksaan keamanan web</h1>
                        <p id="generatedAt" class="report-muted mt-2 text-sm text-slate-400"></p>
                    </div>
                    <span class="rounded-full border border-amber-300/20 bg-amber-300/[.06] px-3 py-1.5 text-[10px] font-bold uppercase tracking-wide text-amber-200">Pemeriksaan pasif</span>
                </div>
            </header>

            <section class="report-section mt-7">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-300">Identitas pemeriksaan</h2>
                <dl class="mt-3 grid gap-x-8 gap-y-4 rounded-xl border border-white/[.08] bg-white/[.02] p-4 sm:grid-cols-2">
                    <div><dt class="report-muted text-[10px] font-semibold uppercase tracking-wider text-slate-500">Target</dt><dd id="targetUrl" class="mt-1 break-all font-mono text-sm text-slate-100"></dd></div>
                    <div><dt class="report-muted text-[10px] font-semibold uppercase tracking-wider text-slate-500">Waktu pemeriksaan</dt><dd id="scanTime" class="mt-1 text-sm text-slate-100"></dd></div>
                    <div><dt class="report-muted text-[10px] font-semibold uppercase tracking-wider text-slate-500">Respons HTTP</dt><dd id="httpResult" class="mt-1 text-sm text-slate-100"></dd></div>
                    <div><dt class="report-muted text-[10px] font-semibold uppercase tracking-wider text-slate-500">Klasifikasi hasil sistem</dt><dd id="classification" class="mt-1 font-mono text-sm text-slate-100"></dd></div>
                </dl>
            </section>

            <section class="report-section mt-7">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-300">Ringkasan</h2>
                <p id="summary" class="report-muted mt-3 text-sm leading-7 text-slate-300"></p>
                <div class="report-callout mt-4 rounded-xl border border-amber-300/15 bg-amber-300/[.04] p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-amber-200">Interpretasi</p>
                    <p class="report-muted mt-2 text-sm leading-6 text-amber-100/75">Observasi konfigurasi dan skor kemiripan bukan konfirmasi kerentanan. Hasil negatif juga bukan bukti bahwa target aman. Lakukan validasi manual pada sistem yang berizin.</p>
                </div>
            </section>

            <section class="report-section mt-7">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-300">Metode pemeriksaan</h2>
                <ul class="report-muted mt-3 list-disc space-y-2 pl-5 text-sm leading-6 text-slate-400">
                    <li>Satu permintaan HTTP GET tanpa payload serangan dan tanpa mengikuti redirect.</li>
                    <li>Maksimal 512 KiB isi respons dibaca untuk observasi header dan struktur halaman.</li>
                    <li>Pemeriksaan header: Content-Security-Policy, X-Content-Type-Options, dan HSTS pada HTTPS.</li>
                    <li>Model Siamese membandingkan nilai query parameter URL dengan contoh support jika input tersedia.</li>
                </ul>
            </section>

            <section class="report-section mt-7">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-slate-300">Observasi</h2>
                    <span id="findingCount" class="report-muted text-xs text-slate-500"></span>
                </div>
                <div class="mt-3 overflow-hidden rounded-xl border border-white/[.08]">
                    <table class="w-full border-collapse text-left text-xs">
                        <thead class="bg-white/[.04] text-[10px] uppercase tracking-wide text-slate-400"><tr><th class="px-3 py-3">#</th><th class="px-3 py-3">Kategori / tingkat</th><th class="px-3 py-3">Observasi dan bukti</th></tr></thead>
                        <tbody id="findingRows" class="divide-y divide-white/[.07]"></tbody>
                    </table>
                </div>
            </section>

            <section class="report-section mt-7">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-300">Analisis Siamese One-Shot</h2>
                <dl class="mt-3 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-xl border border-white/[.08] bg-white/[.02] p-3"><dt class="report-muted text-[10px] uppercase tracking-wider text-slate-500">Status</dt><dd id="modelStatus" class="mt-2 text-sm text-slate-100"></dd></div>
                    <div class="rounded-xl border border-white/[.08] bg-white/[.02] p-3"><dt class="report-muted text-[10px] uppercase tracking-wider text-slate-500">Kelas kandidat</dt><dd id="modelLabel" class="mt-2 text-sm text-slate-100"></dd></div>
                    <div class="rounded-xl border border-white/[.08] bg-white/[.02] p-3"><dt class="report-muted text-[10px] uppercase tracking-wider text-slate-500">Skor kemiripan</dt><dd id="modelScore" class="mt-2 text-sm text-slate-100"></dd></div>
                </dl>
                <p id="modelMessage" class="report-muted mt-3 text-sm leading-6 text-slate-400"></p>
                <p id="modelWarning" class="report-muted mt-2 hidden text-xs leading-5 text-amber-200/80"></p>
            </section>

            <section class="report-section mt-7">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-300">Struktur halaman yang diamati</h2>
                <dl class="mt-3 grid grid-cols-3 gap-3">
                    <div class="rounded-xl border border-white/[.08] bg-white/[.02] p-3"><dt class="report-muted text-[10px] uppercase tracking-wider text-slate-500">Formulir</dt><dd id="formCount" class="mt-2 text-lg font-semibold text-white"></dd></div>
                    <div class="rounded-xl border border-white/[.08] bg-white/[.02] p-3"><dt class="report-muted text-[10px] uppercase tracking-wider text-slate-500">Input</dt><dd id="inputCount" class="mt-2 text-lg font-semibold text-white"></dd></div>
                    <div class="rounded-xl border border-white/[.08] bg-white/[.02] p-3"><dt class="report-muted text-[10px] uppercase tracking-wider text-slate-500">Field kata sandi</dt><dd id="passwordCount" class="mt-2 text-lg font-semibold text-white"></dd></div>
                </dl>
            </section>

            <footer class="report-section mt-8 border-t border-white/[.1] pt-5">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Batasan dan cakupan</p>
                <p id="scopeNote" class="report-muted mt-2 text-xs leading-6 text-slate-500"></p>
                <p class="report-muted mt-3 text-[10px] leading-5 text-slate-600">Laporan dibuat dari data yang tersimpan di browser lokal. Laporan ini merekam satu pemeriksaan pasif, tidak menyertakan isi halaman mentah, dan tidak menggantikan evaluasi keamanan profesional atau validasi penelitian.</p>
            </footer>
        </article>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const historyKey = 'secdetector.scanHistory.v1';
            const recordId = new URLSearchParams(window.location.search).get('id');
            const errorPanel = document.getElementById('reportError');
            const paper = document.getElementById('reportPaper');
            let reportRecord = null;

            try {
                const history = JSON.parse(localStorage.getItem(historyKey) || '[]');
                reportRecord = Array.isArray(history) ? history.find(item => item && item.id === recordId) : null;
            } catch {
                reportRecord = null;
            }

            if (!reportRecord || !reportRecord.data) {
                errorPanel.classList.remove('hidden');
                document.getElementById('printReport').disabled = true;
                document.getElementById('downloadJson').disabled = true;
                document.getElementById('downloadDocx').disabled = true;
                return;
            }

            const data = reportRecord.data;
            const http = data.http || {};
            const model = data.model || {};
            const structure = data.page_structure || {};
            const findings = Array.isArray(data.findings) ? data.findings : [];
            const dateText = formatDate(reportRecord.createdAt);

            setText('generatedAt', `Dibuat ${formatDate(new Date().toISOString())}`);
            setText('targetUrl', data.url || 'Tidak tersedia');
            setText('scanTime', dateText);
            setText('httpResult', http.status_code == null ? 'Tidak tersedia' : `HTTP ${http.status_code} · ${http.bytes_inspected ?? 0} byte dibaca`);
            setText('classification', data.classification || 'Tidak tersedia');
            setText('summary', data.summary || 'Pemeriksaan pasif selesai.');
            setText('findingCount', `${findings.length} observasi`);
            setText('modelStatus', model.status || 'Tidak tersedia');
            setText('modelLabel', model.label || 'Tidak ada kandidat');
            setText('modelScore', model.score == null ? 'Tidak tersedia' : `${model.score} (kemiripan, bukan probabilitas)`);
            setText('modelMessage', model.message || 'Tidak ada keterangan model.');
            setText('formCount', structure.forms ?? 0);
            setText('inputCount', structure.inputs ?? 0);
            setText('passwordCount', structure.password_fields ?? 0);
            setText('scopeNote', data.scope_note || 'Pemeriksaan terbatas; interpretasikan hasil dengan hati-hati.');
            if (model.warning) {
                setText('modelWarning', model.warning);
                document.getElementById('modelWarning').classList.remove('hidden');
            }

            const rows = document.getElementById('findingRows');
            if (findings.length) {
                findings.forEach((finding, index) => {
                    const row = document.createElement('tr');
                    row.className = 'align-top';
                    row.append(
                        cell(String(index + 1), 'w-8 px-3 py-3 text-slate-500'),
                        cell(`${finding.category || 'Observasi'} · ${finding.severity || 'informational'}`, 'w-36 px-3 py-3 font-medium text-slate-300'),
                        cell(`${finding.title || 'Temuan'}\n${finding.evidence || 'Tidak ada bukti terlampir.'}`, 'whitespace-pre-line px-3 py-3 leading-5 text-slate-400')
                    );
                    rows.append(row);
                });
            } else {
                const row = document.createElement('tr');
                const empty = cell('Tidak ada observasi konfigurasi pada pemeriksaan terbatas ini.', 'px-3 py-4 text-slate-500');
                empty.colSpan = 3;
                row.append(empty);
                rows.append(row);
            }

            paper.classList.remove('hidden');
            document.getElementById('printReport').addEventListener('click', () => window.print());
            document.getElementById('downloadJson').addEventListener('click', downloadJson);
            document.getElementById('downloadDocx').addEventListener('click', downloadDocx);

            function setText(id, value) {
                document.getElementById(id).textContent = value == null ? '' : String(value);
            }

            function cell(value, className) {
                const element = document.createElement('td');
                element.className = className;
                element.textContent = value;
                return element;
            }

            function formatDate(value) {
                const date = new Date(value);
                return Number.isNaN(date.getTime()) ? 'Waktu tidak tersedia' : new Intl.DateTimeFormat('id-ID', { dateStyle: 'long', timeStyle: 'short' }).format(date);
            }

            function downloadJson() {
                const artifact = {
                    report_schema_version: '1.0',
                    application: 'SecDetector',
                    generated_at: new Date().toISOString(),
                    scanned_at: reportRecord.createdAt,
                    method: {
                        mode: 'passive_single_page',
                        request: 'one HTTP GET; no attack payload; redirects disabled',
                        body_limit_bytes: 524288,
                        ai_input: 'URL query parameter values only'
                    },
                    result: data,
                    interpretation: 'Observations and similarity scores are not confirmed vulnerabilities or proof that the target is safe.'
                };
                const blob = new Blob([JSON.stringify(artifact, null, 2)], { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                const host = safeHost(data.url);
                link.href = url;
                link.download = `SecDetector-${host}-${new Date(reportRecord.createdAt).toISOString().slice(0, 10)}.json`;
                document.body.append(link);
                link.click();
                link.remove();
                window.setTimeout(() => URL.revokeObjectURL(url), 1000);
            }

            async function downloadDocx() {
                const button = document.getElementById('downloadDocx');
                const notice = document.getElementById('exportNotice');
                button.disabled = true;
                button.textContent = 'Menyiapkan…';
                notice.classList.add('hidden');
                try {
                    const response = await fetch("{{ route('scan.report.docx') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/vnd.openxmlformats-officedocument.wordprocessingml.document, application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ created_at: reportRecord.createdAt, data })
                    });
                    if (!response.ok) {
                        const error = await response.json().catch(() => ({}));
                        throw new Error(error.message || error.detail || `Ekspor gagal (${response.status}).`);
                    }
                    const blob = await response.blob();
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = `SecDetector-${safeHost(data.url)}-${new Date(reportRecord.createdAt).toISOString().slice(0, 10)}.docx`;
                    document.body.append(link);
                    link.click();
                    link.remove();
                    window.setTimeout(() => URL.revokeObjectURL(url), 1000);
                } catch (error) {
                    notice.textContent = error.message || 'DOCX gagal dibuat. Periksa apakah layanan Laravel aktif.';
                    notice.classList.remove('hidden');
                } finally {
                    button.disabled = false;
                    button.textContent = 'Unduh DOCX';
                }
            }

            function safeHost(rawUrl) {
                try { return new URL(rawUrl).hostname.replace(/[^a-zA-Z0-9.-]/g, '_'); }
                catch { return 'scan-report'; }
            }
        });
    </script>
</body>
</html>
