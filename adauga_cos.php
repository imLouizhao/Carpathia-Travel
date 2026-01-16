<?php
ob_start();
header('Content-Type: text/plain; charset=utf-8');
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo 'error';
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo 'login_required';
    exit;
}

$id_utilizator = (int)$_SESSION['user_id'];
$id_produs = (int)($_POST['id_produs'] ?? 0);
$cantitate = (int)($_POST['cantitate'] ?? 1);

if ($id_produs <= 0 || $cantitate < 1) {
    echo 'error';
    exit;
}

$check = $conn->prepare("SELECT id FROM produse WHERE id = ?");
$check->bind_param("i", $id_produs);
$check->execute();

if ($check->get_result()->num_rows === 0) {
    $check->close();
    echo 'error';
    exit;
}
$check->close();

$stmt = $conn->prepare("
    SELECT id 
    FROM cos_cumparaturi 
    WHERE id_utilizator = ? AND id_produs = ?
");
$stmt->bind_param("ii", $id_utilizator, $id_produs);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    $id_cos = (int)$row['id'];
    $stmt->close();

    $upd = $conn->prepare("
        UPDATE cos_cumparaturi 
        SET cantitate = cantitate + ? 
        WHERE id = ? AND id_utilizator = ?
    ");
    $upd->bind_param("iii", $cantitate, $id_cos, $id_utilizator);
    $ok = $upd->execute();
    $upd->close();
} else {
    $stmt->close();

    $ins = $conn->prepare("
        INSERT INTO cos_cumparaturi (id_utilizator, id_produs, cantitate)
        VALUES (?, ?, ?)
    ");
    $ins->bind_param("iii", $id_utilizator, $id_produs, $cantitate);
    $ok = $ins->execute();
    $ins->close();
}

echo $ok ? 'success' : 'error';
ob_end_flush();
$conn->close();
exit;
