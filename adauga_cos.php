<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'config.php';
require 'functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodă invalidă']);
    exit;
}

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'login_required']);
    exit;
}

if (!hasRole(['client', 'admin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'forbidden']);
    exit;
}

$id_utilizator = (int)$_SESSION['user_id'];
$id_produs     = (int)($_POST['id_produs'] ?? 0);
$cantitate     = (int)($_POST['cantitate'] ?? 1);

if ($id_produs <= 0 || $cantitate < 1) {
    echo json_encode(['success' => false, 'message' => 'Date invalide']);
    exit;
}

$check = $conn->prepare("SELECT locuri_disponibile FROM produse WHERE id = ?");
$check->bind_param("i", $id_produs);
$check->execute();
$prodRes = $check->get_result();
$prod = $prodRes->fetch_assoc();
$check->close();

if (!$prod) {
    echo json_encode(['success' => false, 'message' => 'Produs inexistent']);
    exit;
}

$stoc = (int)$prod['locuri_disponibile'];
if ($stoc < 1) {
    echo json_encode(['success' => false, 'message' => 'Pachet indisponibil']);
    exit;
}

if ($cantitate > $stoc) $cantitate = $stoc;

$stmt = $conn->prepare("SELECT id, cantitate FROM cos_cumparaturi WHERE id_utilizator = ? AND id_produs = ?");
$stmt->bind_param("ii", $id_utilizator, $id_produs);
$stmt->execute();
$res = $stmt->get_result();

$ok = false;
if ($row = $res->fetch_assoc()) {
    $id_cos = (int)$row['id'];
    $curQty = (int)$row['cantitate'];
    $stmt->close();

    $nouaQty = $curQty + $cantitate;
    if ($nouaQty > $stoc) $nouaQty = $stoc;

    $upd = $conn->prepare("UPDATE cos_cumparaturi SET cantitate = ? WHERE id = ? AND id_utilizator = ?");
    $upd->bind_param("iii", $nouaQty, $id_cos, $id_utilizator);
    $ok = (bool)$upd->execute();
    $upd->close();
} else {
    $stmt->close();
    $ins = $conn->prepare("INSERT INTO cos_cumparaturi (id_utilizator, id_produs, cantitate) VALUES (?, ?, ?)");
    $ins->bind_param("iii", $id_utilizator, $id_produs, $cantitate);
    $ok = (bool)$ins->execute();
    $ins->close();
}

if (!$ok) {
    echo json_encode(['success' => false, 'message' => 'Eroare la adăugare']);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Pachet adăugat în coș!',
    'cart_count' => getCartCount()
]);
exit;
