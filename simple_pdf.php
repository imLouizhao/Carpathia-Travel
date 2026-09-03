<?php
function pdfRemoveDiacritics(string $s): string {
    $map = [
        'ă' => 'a', 'Ă' => 'A', 'â' => 'a', 'Â' => 'A',
        'î' => 'i', 'Î' => 'I',
        'ș' => 's', 'Ș' => 'S', 'ş' => 's', 'Ş' => 'S',
        'ț' => 't', 'Ț' => 'T', 'ţ' => 't', 'Ţ' => 'T',
    ];
    return strtr($s, $map);
}

function pdfSafeAscii(string $s): string {
    $s = pdfRemoveDiacritics($s);
    return (string)preg_replace('/[^\x20-\x7E]/', '', $s);
}

function pdfEscapeString(string $s): string {
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);
}

function pdfBuildTableLines(array $headerCols, array $rows): array {
    $headerCols = array_map('pdfSafeAscii', $headerCols);

    $cleanRows = [];
    foreach ($rows as $r) {
        $cleanRows[] = array_map(function ($v) {
            return pdfSafeAscii((string)$v);
        }, $r);
    }

    $widths = [];
    foreach ($headerCols as $i => $h) {
        $max = strlen($h);
        foreach ($cleanRows as $r) {
            $len = strlen((string)($r[$i] ?? ''));
            if ($len > $max) $max = $len;
        }
        $widths[$i] = min(max($max, 4), 26);
    }

    $formatLine = function (array $cols) use ($widths): string {
        $parts = [];
        foreach ($widths as $i => $w) {
            $c = substr((string)($cols[$i] ?? ''), 0, $w);
            $parts[] = str_pad($c, $w);
        }
        return implode(' | ', $parts);
    };

    $headerLine = $formatLine($headerCols);

    $dataLines = [];
    foreach ($cleanRows as $r) {
        $dataLines[] = $formatLine($r);
    }

    return [$headerLine, $dataLines];
}


function pdfFromPages(array $pages): string {
    $fontObjNum = 3;
    $nextObjNum = 4;

    $objects = [];
    $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";

    $pageObjNums = [];

    foreach ($pages as $lines) {
        $pageObjNum = $nextObjNum++;
        $contentObjNum = $nextObjNum++;
        $pageObjNums[] = $pageObjNum;

        $stream = "BT\n/F1 9 Tf\n12 TL\n40 800 Td\n";
        foreach ($lines as $line) {
            $stream .= "(" . pdfEscapeString($line) . ") Tj T*\n";
        }
        $stream .= "ET";

        $objects[$contentObjNum] = "<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "\nendstream";
        $objects[$pageObjNum] = "<< /Type /Page /Parent 2 0 R /Resources << /Font << /F1 {$fontObjNum} 0 R >> >> "
            . "/MediaBox [0 0 595 842] /Contents {$contentObjNum} 0 R >>";
    }

    $objects[2] = "<< /Type /Pages /Kids [" . implode(' ', array_map(function ($n) {
        return "{$n} 0 R";
    }, $pageObjNums)) . "] /Count " . count($pageObjNums) . " >>";

    $objects[$fontObjNum] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>";

    ksort($objects);

    $pdf = "%PDF-1.4\n";
    $offsets = [];
    foreach ($objects as $num => $body) {
        $offsets[$num] = strlen($pdf);
        $pdf .= "{$num} 0 obj\n{$body}\nendobj\n";
    }

    $xrefStart = strlen($pdf);
    $maxNum = max(array_keys($objects));

    $pdf .= "xref\n0 " . ($maxNum + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";
    for ($n = 1; $n <= $maxNum; $n++) {
        $pdf .= isset($offsets[$n])
            ? sprintf("%010d 00000 n \n", $offsets[$n])
            : "0000000000 00000 f \n";
    }

    $pdf .= "trailer\n<< /Size " . ($maxNum + 1) . " /Root 1 0 R >>\n";
    $pdf .= "startxref\n{$xrefStart}\n%%EOF";

    return $pdf;
}

function generateSimplePdf(string $title, array $headerCols, array $rows): string {
    [$headerLine, $dataLines] = pdfBuildTableLines($headerCols, $rows);

    $linesPerPage = 42;
    $chunks = array_chunk($dataLines, $linesPerPage);
    if (empty($chunks)) {
        $chunks = [[]];
    }

    $pages = [];
    foreach ($chunks as $idx => $chunkLines) {
        $pageLines = [];
        if ($idx === 0) {
            $pageLines[] = pdfSafeAscii($title);
            $pageLines[] = 'Generat: ' . date('d-m-Y H:i');
            $pageLines[] = '';
        }
        $pageLines[] = $headerLine;
        $pageLines[] = str_repeat('-', strlen($headerLine));
        foreach ($chunkLines as $l) {
            $pageLines[] = $l;
        }
        $pages[] = $pageLines;
    }

    return pdfFromPages($pages);
}
