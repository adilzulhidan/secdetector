<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#070b16">
    <title>Riwayat Scan — SecDetector</title>
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen overflow-x-hidden bg-[#070b16] font-sans text-slate-200 antialiased">
    <div class="pointer-events-none fixed inset-0 bg-[linear-gradient(rgba(148,163,184,.035)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,.035)_1px,transparent_1px)] bg-[size:52px_52px]"></div>

    <header class="relative z-10 border-b border-white/[.06] bg-[#070b16]/85 backdrop-blur-xl">
        <nav class="flex items-center justify-between px-5 py-4 mx-auto max-w-7xl sm:px-8" aria-label="Navigasi riwayat">
            <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="SecDetector beranda">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-600/20" aria-hidden="true">
                    <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3 19 6v5c0 4.7-2.9 8-7 10-4.1-2-7-5.3-7-10V6l7-3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4"/></svg>
                </span>
                <span class="text-base font-bold tracking-tight text-white sm:text-lg">SecDetector</span>
            </a>
            <div class="flex items-center gap-4 text-sm sm:gap-6">
                <a href="{{ route('home') }}" class="text-slate-400 transition hover:text-white">Beranda</a>
                <a href="{{ route('scan') }}" class="text-slate-400 transition hover:text-white">Mulai scan</a>
                <a href="{{ route('docs') }}" class="text-slate-400 transition hover:text-white">Dokumentasi</a>
                <span class="hidden font-medium text-white sm:inline">Riwayat scan</span>
            </div>
        </nav>
    </header>

    <main class="relative z-10 px-5 py-10 mx-auto max-w-5xl sm:px-8 sm:py-14">
        <div class="flex flex-col justify-between gap-5 mb-8 sm:flex-row sm:items-end">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[.18em] text-blue-300">Pemeriksaan tersimpan</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-white sm:text-4xl">Riwayat scan</h1>
                <p class="max-w-2xl mt-3 text-sm leading-6 text-slate-400">Hasil disimpan di browser ini saja dan dibatasi hingga 30 pemeriksaan terakhir.</p>
            </div>
            <div class="flex items-center gap-3">
                <span id="historyCount" class="text-sm text-slate-400">0 pemeriksaan</span>
                <button id="clearHistory" type="button" class="rounded-lg border border-rose-400/20 px-3 py-2 text-xs font-semibold text-rose-200 transition hover:bg-rose-400/10 disabled:cursor-not-allowed disabled:opacity-40" disabled>Hapus semua</button>
            </div>
        </div>

        <div id="storageNotice" class="mb-5 hidden rounded-xl border border-amber-300/20 bg-amber-300/[.06] p-4 text-sm text-amber-100" role="status"></div>
        <section id="historyList" class="space-y-4" aria-label="Daftar riwayat scan" aria-live="polite"></section>
        <section id="emptyState" class="hidden rounded-2xl border border-white/[.08] bg-[#0c1221]/80 px-6 py-14 text-center">
            <div class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-blue-500/10 text-xl text-blue-300" aria-hidden="true">⌕</div>
            <h2 class="mt-5 text-lg font-semibold text-white">Belum ada riwayat</h2>
            <p class="mt-2 text-sm text-slate-400">Hasil scan yang berhasil akan muncul di sini setelah pemeriksaan pertama.</p>
            <a href="{{ route('scan') }}" class="mt-6 inline-flex rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">Buka halaman scan</a>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const key = 'secdetector.scanHistory.v1';
            const list = document.getElementById('historyList');
            const emptyState = document.getElementById('emptyState');
            const count = document.getElementById('historyCount');
            const clearButton = document.getElementById('clearHistory');
            const notice = document.getElementById('storageNotice');

            function readHistory() {
                let parsed;
                try {
                    parsed = JSON.parse(localStorage.getItem(key) || '[]');
                } catch {
                    notice.textContent = 'Riwayat browser tidak dapat dibaca. Penyimpanan mungkin dinonaktifkan atau datanya rusak.';
                    notice.classList.remove('hidden');
                    return [];
                }

                const history = Array.isArray(parsed) ? parsed.filter(item => item && item.data && item.createdAt) : [];
                let migrated = false;
                history.forEach((record, index) => {
                    if (!record.id) {
                        record.id = `legacy-${new Date(record.createdAt).getTime() || index}-${Math.random().toString(36).slice(2, 8)}`;
                        migrated = true;
                    }
                });
                if (migrated) {
                    try { localStorage.setItem(key, JSON.stringify(history)); }
                    catch {
                        notice.textContent = 'Riwayat lama dapat dibaca, tetapi pembaruan formatnya tidak tersimpan di browser.';
                        notice.classList.remove('hidden');
                    }
                }
                return history;
            }

            function textElement(tag, className, value) {
                const element = document.createElement(tag);
                element.className = className;
                element.textContent = value == null ? '' : String(value);
                return element;
            }

            function render() {
                const history = readHistory();
                list.replaceChildren();
                count.textContent = `${history.length} pemeriksaan`;
                clearButton.disabled = history.length === 0;
                emptyState.classList.toggle('hidden', history.length > 0);

                history.forEach((record, index) => {
                    const result = record.data;
                    const model = result.model || {};
                    const findings = Array.isArray(result.findings) ? result.findings : [];
                    const article = document.createElement('article');
                    article.className = 'overflow-hidden rounded-2xl border border-white/[.08] bg-[#0c1221]/85';

                    const header = document.createElement('div');
                    header.className = 'flex flex-col justify-between gap-4 border-b border-white/[.06] p-5 sm:flex-row sm:items-start';
                    const heading = document.createElement('div');
                    heading.className = 'min-w-0';
                    heading.append(
                        textElement('p', 'break-all font-mono text-sm text-blue-200', result.url || 'URL tidak tersedia'),
                        textElement('p', 'mt-2 text-xs text-slate-500', formatDate(record.createdAt))
                    );
                    const actions = document.createElement('div');
                    actions.className = 'flex shrink-0 items-center gap-2';
                    const report = document.createElement('a');
                    report.href = `{{ route('scan.report') }}?id=${encodeURIComponent(record.id)}`;
                    report.target = '_blank';
                    report.rel = 'noopener';
                    report.className = 'rounded-lg border border-blue-400/20 px-3 py-2 text-xs font-semibold text-blue-200 transition hover:bg-blue-400/[.08]';
                    report.textContent = 'Laporan';
                    const remove = document.createElement('button');
                    remove.type = 'button';
                    remove.className = 'self-start rounded-lg border border-white/10 px-3 py-2 text-xs text-slate-400 transition hover:border-rose-400/30 hover:text-rose-200';
                    remove.textContent = 'Hapus';
                    remove.setAttribute('aria-label', `Hapus riwayat scan ${index + 1}`);
                    remove.addEventListener('click', () => {
                        const current = readHistory();
                        current.splice(index, 1);
                        saveHistory(current);
                    });
                    actions.append(report, remove);
                    header.append(heading, actions);

                    const metrics = document.createElement('div');
                    metrics.className = 'grid gap-3 p-5 sm:grid-cols-3';
                    metrics.append(
                        metric('HTTP', result.http ? `${result.http.status_code} · ${result.http.bytes_inspected} byte` : '—'),
                        metric('Observasi', String(findings.length)),
                        metric('AI', model.label ? `${model.label} · ${model.score ?? '—'}` : (model.status === 'ready' ? 'Tidak ada kandidat' : model.message || 'Belum dinilai'))
                    );

                    const details = document.createElement('details');
                    details.className = 'border-t border-white/[.06]';
                    const summary = textElement('summary', 'cursor-pointer px-5 py-4 text-sm font-medium text-slate-300 transition hover:text-white', 'Lihat detail pemeriksaan');
                    const detailBody = document.createElement('div');
                    detailBody.className = 'space-y-4 px-5 pb-5';
                    detailBody.append(textElement('p', 'text-sm leading-6 text-slate-400', result.summary || 'Pemeriksaan pasif selesai.'));
                    if (model.message) detailBody.append(textElement('p', 'rounded-xl border border-blue-400/10 bg-blue-400/[.04] p-3 text-sm leading-6 text-blue-100/80', model.message));
                    if (findings.length) {
                        const findingList = document.createElement('ul');
                        findingList.className = 'divide-y divide-white/[.06] rounded-xl border border-white/[.07] px-4';
                        findings.forEach(finding => {
                            const row = document.createElement('li');
                            row.className = 'py-3';
                            row.append(
                                textElement('p', 'text-sm font-medium text-slate-200', finding.title),
                                textElement('p', 'mt-1 text-xs leading-5 text-slate-500', finding.evidence),
                                textElement('p', 'mt-2 text-[10px] font-semibold uppercase tracking-wide text-amber-200', `${finding.category || 'Observasi'} · ${finding.severity || 'informational'}`)
                            );
                            findingList.append(row);
                        });
                        detailBody.append(findingList);
                    } else {
                        detailBody.append(textElement('p', 'text-sm text-slate-500', 'Tidak ada observasi konfigurasi pada pemeriksaan terbatas ini.'));
                    }
                    if (result.scope_note) detailBody.append(textElement('p', 'text-xs leading-5 text-slate-500', result.scope_note));
                    details.append(summary, detailBody);
                    article.append(header, metrics, details);
                    list.append(article);
                });
            }

            function metric(label, value) {
                const box = document.createElement('div');
                box.className = 'rounded-xl border border-white/[.06] bg-white/[.025] p-3';
                box.append(textElement('p', 'text-[10px] font-semibold uppercase tracking-wider text-slate-500', label));
                box.append(textElement('p', 'mt-2 break-words text-sm text-slate-200', value));
                return box;
            }

            function formatDate(value) {
                const date = new Date(value);
                return Number.isNaN(date.getTime()) ? 'Waktu tidak tersedia' : new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short' }).format(date);
            }

            function saveHistory(history) {
                try {
                    localStorage.setItem(key, JSON.stringify(history));
                    notice.classList.add('hidden');
                    render();
                } catch {
                    notice.textContent = 'Perubahan riwayat tidak tersimpan. Penyimpanan browser mungkin penuh atau dinonaktifkan.';
                    notice.classList.remove('hidden');
                }
            }

            clearButton.addEventListener('click', () => {
                if (readHistory().length && window.confirm('Hapus seluruh riwayat scan dari browser ini?')) saveHistory([]);
            });

            render();
        });
    </script>
</body>
</html>
