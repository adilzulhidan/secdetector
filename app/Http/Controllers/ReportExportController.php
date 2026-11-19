<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class ReportExportController extends Controller
{
    public function docx(Request $request): BinaryFileResponse|JsonResponse
    {
        if (strlen($request->getContent()) > 1_000_000) {
            return response()->json(['message' => 'Data laporan terlalu besar untuk diekspor.'], 413);
        }

        $validated = $request->validate([
            'created_at' => ['required', 'date'],
            'data' => ['required', 'array'],
            'data.url' => ['required', 'string', 'max:2048'],
            'data.classification' => ['nullable', 'string', 'max:100'],
            'data.summary' => ['nullable', 'string', 'max:2000'],
            'data.http' => ['nullable', 'array'],
            'data.http.status_code' => ['nullable', 'integer', 'between:100,599'],
            'data.http.bytes_inspected' => ['nullable', 'integer', 'min:0', 'max:524288'],
            'data.model' => ['nullable', 'array'],
            'data.model.status' => ['nullable', 'string', 'max:50'],
            'data.model.label' => ['nullable', 'string', 'max:100'],
            'data.model.score' => ['nullable', 'numeric', 'between:0,1'],
            'data.model.message' => ['nullable', 'string', 'max:2000'],
            'data.model.warning' => ['nullable', 'string', 'max:2000'],
            'data.page_structure' => ['nullable', 'array'],
            'data.page_structure.forms' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'data.page_structure.inputs' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'data.page_structure.password_fields' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'data.findings' => ['nullable', 'array', 'max:100'],
            'data.findings.*.category' => ['nullable', 'string', 'max:100'],
            'data.findings.*.severity' => ['nullable', 'string', 'max:50'],
            'data.findings.*.title' => ['nullable', 'string', 'max:300'],
            'data.findings.*.evidence' => ['nullable', 'string', 'max:2000'],
            'data.scope_note' => ['nullable', 'string', 'max:2000'],
        ]);

        if (!class_exists(ZipArchive::class)) {
            return response()->json(['message' => 'Ekspor DOCX memerlukan ekstensi PHP ZIP.'], 501);
        }

        $data = $validated['data'];
        $http = is_array($data['http'] ?? null) ? $data['http'] : [];
        $model = is_array($data['model'] ?? null) ? $data['model'] : [];
        $structure = is_array($data['page_structure'] ?? null) ? $data['page_structure'] : [];
        $findings = is_array($data['findings'] ?? null) ? $data['findings'] : [];

        $paragraph = fn (string $text, string $style = 'Normal'): string => $this->paragraphXml($text, $style);
        $blocks = [];
        $blocks[] = $paragraph('Hasil Pemeriksaan Keamanan Web', 'Title');
        $blocks[] = $paragraph('SecDetector | Laporan pemeriksaan pasif', 'Subtitle');
        $blocks[] = $paragraph('Dibuat: '.now()->format('d F Y H:i T'));
        $blocks[] = $paragraph('');

        $blocks[] = $paragraph('Identitas Pemeriksaan', 'Heading1');
        $blocks[] = $paragraph('Target: '.$data['url']);
        $blocks[] = $paragraph('Waktu pemeriksaan: '.$validated['created_at']);
        $blocks[] = $paragraph('Respons: '.($http['status_code'] ?? 'tidak tersedia').' | Byte diperiksa: '.($http['bytes_inspected'] ?? 'tidak tersedia'));
        $blocks[] = $paragraph('Klasifikasi sistem: '.($data['classification'] ?? 'tidak tersedia'));

        $blocks[] = $paragraph('Ringkasan', 'Heading1');
        $blocks[] = $paragraph((string) ($data['summary'] ?? 'Pemeriksaan pasif selesai.'));

        $blocks[] = $paragraph('Metode Pemeriksaan', 'Heading1');
        $blocks[] = $paragraph('1. Satu permintaan HTTP GET tanpa payload serangan; redirect tidak diikuti.');
        $blocks[] = $paragraph('2. Maksimal 512 KiB isi respons dibaca untuk observasi header dan struktur halaman.');
        $blocks[] = $paragraph('3. Header yang diamati: Content-Security-Policy, X-Content-Type-Options, dan HSTS pada HTTPS.');
        $blocks[] = $paragraph('4. Model Siamese membandingkan nilai query parameter URL dengan contoh support jika input tersedia.');

        $blocks[] = $paragraph('Observasi', 'Heading1');
        if ($findings === []) {
            $blocks[] = $paragraph('Tidak ada observasi konfigurasi pada pemeriksaan terbatas ini. Ini bukan bukti bahwa target aman.');
        } else {
            foreach (array_values($findings) as $index => $finding) {
                if (!is_array($finding)) {
                    continue;
                }
                $blocks[] = $paragraph('Observasi '.($index + 1).': '.($finding['title'] ?? 'Tanpa judul'), 'Heading2');
                $blocks[] = $paragraph('Kategori: '.($finding['category'] ?? 'Observasi').' | Tingkat: '.($finding['severity'] ?? 'informational'));
                $blocks[] = $paragraph('Bukti: '.($finding['evidence'] ?? 'Tidak ada bukti terlampir.'));
            }
        }

        $blocks[] = $paragraph('Analisis Siamese One-Shot', 'Heading1');
        $blocks[] = $paragraph('Status model: '.($model['status'] ?? 'tidak tersedia'));
        $blocks[] = $paragraph('Kelas kandidat: '.($model['label'] ?? 'tidak ada'));
        $blocks[] = $paragraph('Skor kemiripan: '.($model['score'] ?? 'tidak tersedia').' (bukan probabilitas kerentanan)');
        if (!empty($model['message'])) {
            $blocks[] = $paragraph('Keterangan: '.$model['message']);
        }
        if (!empty($model['warning'])) {
            $blocks[] = $paragraph('Peringatan model: '.$model['warning']);
        }

        $blocks[] = $paragraph('Struktur Halaman', 'Heading1');
        $blocks[] = $paragraph('Formulir: '.($structure['forms'] ?? 0).' | Input: '.($structure['inputs'] ?? 0).' | Field kata sandi: '.($structure['password_fields'] ?? 0));

        $blocks[] = $paragraph('Interpretasi dan Batasan', 'Heading1');
        $blocks[] = $paragraph('Observasi konfigurasi dan skor kemiripan bukan konfirmasi kerentanan. Hasil negatif juga bukan bukti bahwa target aman. Lakukan validasi manual pada sistem yang berizin.');
        $blocks[] = $paragraph((string) ($data['scope_note'] ?? 'Pemeriksaan terbatas pada satu halaman dan kondisi pasif.'));
        $blocks[] = $paragraph('Laporan ini merekam satu pemeriksaan pasif. Laporan bukan artikel jurnal, tidak menyertakan isi halaman mentah, dan tidak menggantikan evaluasi penelitian pada data uji.');

        $path = tempnam(sys_get_temp_dir(), 'secdetector-report-');
        if ($path === false) {
            return response()->json(['message' => 'File sementara untuk laporan tidak dapat dibuat.'], 500);
        }

        try {
            $zip = new ZipArchive();
            if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Tidak dapat membuat paket DOCX.');
            }

            $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
            $zip->addFromString('_rels/.rels', $this->packageRelationshipsXml());
            $zip->addFromString('word/document.xml', $this->documentXml(implode('', $blocks)));
            $zip->addFromString('word/styles.xml', $this->stylesXml());
            $zip->addFromString('word/_rels/document.xml.rels', $this->documentRelationshipsXml());
            $zip->close();

            $host = parse_url($data['url'], PHP_URL_HOST) ?: 'target';
            $safeHost = preg_replace('/[^A-Za-z0-9.-]/', '_', $host) ?: 'target';
            $filename = 'SecDetector-'.$safeHost.'-'.date('Y-m-d', strtotime($validated['created_at'])).'.docx';

            return response()->download($path, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ])->deleteFileAfterSend(true);
        } catch (\Throwable $exception) {
            @unlink($path);
            report($exception);

            return response()->json(['message' => 'DOCX gagal dibuat. Periksa konfigurasi ekstensi ZIP PHP.'], 500);
        }
    }

    private function paragraphXml(string $text, string $style = 'Normal'): string
    {
        $runs = [];
        foreach (explode("\n", $text) as $index => $line) {
            if ($index > 0) {
                $runs[] = '<w:r><w:br/></w:r>';
            }
            $escaped = htmlspecialchars($line, ENT_QUOTES | ENT_XML1, 'UTF-8');
            $runs[] = '<w:r><w:t xml:space="preserve">'.$escaped.'</w:t></w:r>';
        }

        return '<w:p><w:pPr><w:pStyle w:val="'.$style.'"/></w:pPr>'.implode('', $runs).'</w:p>';
    }

    private function documentXml(string $body): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<w:body>'.$body.'<w:sectPr><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="1134" w:right="1134" w:bottom="1134" w:left="1134" w:header="600" w:footer="600" w:gutter="0"/></w:sectPr></w:body></w:document>';
    }

    private function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
            .'<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>'
            .'</Types>';
    }

    private function packageRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
            .'</Relationships>';
    }

    private function documentRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    private function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            .'<w:docDefaults><w:rPrDefault><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial"/><w:sz w:val="20"/></w:rPr></w:rPrDefault><w:pPrDefault><w:pPr><w:spacing w:after="120" w:line="276" w:lineRule="auto"/></w:pPr></w:pPrDefault></w:docDefaults>'
            .'<w:style w:type="paragraph" w:default="1" w:styleId="Normal"><w:name w:val="Normal"/></w:style>'
            .'<w:style w:type="paragraph" w:styleId="Title"><w:name w:val="Title"/><w:basedOn w:val="Normal"/><w:pPr><w:spacing w:after="120"/></w:pPr><w:rPr><w:b/><w:sz w:val="36"/><w:color w:val="111827"/></w:rPr></w:style>'
            .'<w:style w:type="paragraph" w:styleId="Subtitle"><w:name w:val="Subtitle"/><w:basedOn w:val="Normal"/><w:rPr><w:sz w:val="22"/><w:color w:val="475569"/></w:rPr></w:style>'
            .'<w:style w:type="paragraph" w:styleId="Heading1"><w:name w:val="heading 1"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:pPr><w:keepNext/><w:spacing w:before="300" w:after="100"/></w:pPr><w:rPr><w:b/><w:sz w:val="26"/><w:color w:val="1D4ED8"/></w:rPr></w:style>'
            .'<w:style w:type="paragraph" w:styleId="Heading2"><w:name w:val="heading 2"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:pPr><w:keepNext/><w:spacing w:before="180" w:after="60"/></w:pPr><w:rPr><w:b/><w:sz w:val="22"/><w:color w:val="1F2937"/></w:rPr></w:style>'
            .'</w:styles>';
    }
}
