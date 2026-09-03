<?php
session_start();

require 'config.php';
require 'functions.php';
require 'simple_pdf.php';

requireRole(['agent', 'admin'], 'index.php');

$entity = $_GET['entity'] ?? '';
$format = $_GET['format'] ?? '';

if (!in_array($entity, ['orders', 'products'], true) || !in_array($format, ['csv', 'doc', 'pdf'], true)) {
    http_response_code(400);
    die('Parametri de export invalizi.');
}

if ($entity === 'products' && !hasRole('admin')) {
    http_response_code(403);
    die('Acces interzis.');
}

if ($entity === 'orders') {
    $headerCols = ['ID', 'Client', 'Email', 'Data comanda', 'Total (lei)', 'Status'];
    $res = $conn->query(
        "SELECT c.id, u.nume, u.email, c.data_comanda, c.total, c.status
         FROM comenzi c
         JOIN utilizatori u ON u.id = c.id_utilizator
         ORDER BY c.data_comanda DESC, c.id DESC"
    );

    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = [
            (int)$r['id'],
            $r['nume'],
            $r['email'],
            $r['data_comanda'],
            number_format((float)$r['total'], 2, '.', ''),
            $r['status'],
        ];
    }

    $title = 'Export comenzi - Carpathia Travel';
    $filenameBase = 'comenzi_' . date('Y-m-d');
} else {
    $headerCols = ['ID', 'Tip pachet', 'Plecare', 'Destinatie', 'Pret (EUR)', 'Durata (zile)', 'Locuri', 'Data plecare'];
    $res = $conn->query(
        "SELECT id, tip_pachet, plecare, destinatie, pret, durata, locuri_disponibile, data_plecare
         FROM produse
         ORDER BY id DESC"
    );

    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = [
            (int)$r['id'],
            $r['tip_pachet'],
            $r['plecare'],
            $r['destinatie'],
            number_format((float)$r['pret'], 2, '.', ''),
            (int)$r['durata'],
            (int)$r['locuri_disponibile'],
            $r['data_plecare'],
        ];
    }

    $title = 'Export produse - Carpathia Travel';
    $filenameBase = 'produse_' . date('Y-m-d');
}

switch ($format) {
    case 'csv':
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filenameBase . '.csv"');
        echo "\xEF\xBB\xBF"; // BOM UTF-8, pentru diacritice corecte în Excel
        $out = fopen('php://output', 'w');
        fputcsv($out, $headerCols, ';');
        foreach ($rows as $r) {
            fputcsv($out, $r, ';');
        }
        fclose($out);
        exit;

    case 'doc':
        header('Content-Type: application/msword; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filenameBase . '.doc"');
        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" ';
        echo 'xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head><meta charset="UTF-8"><title>' . esc($title) . '</title></head><body>';
        echo '<h2>' . esc($title) . '</h2>';
        echo '<p>Generat la: ' . date('d-m-Y H:i') . '</p>';
        echo '<table border="1" cellspacing="0" cellpadding="4" style="border-collapse:collapse;">';
        echo '<tr>';
        foreach ($headerCols as $h) {
            echo '<th style="background:#8B7355;color:#fff;">' . esc($h) . '</th>';
        }
        echo '</tr>';
        foreach ($rows as $r) {
            echo '<tr>';
            foreach ($r as $c) {
                echo '<td>' . esc((string)$c) . '</td>';
            }
            echo '</tr>';
        }
        echo '</table></body></html>';
        exit;

    case 'pdf':
        $pdfContent = generateSimplePdf($title, $headerCols, $rows);
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filenameBase . '.pdf"');
        header('Content-Length: ' . strlen($pdfContent));
        echo $pdfContent;
        exit;
}
