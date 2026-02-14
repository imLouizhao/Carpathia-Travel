<?php
session_start();

require 'config.php';
require 'functions.php';

requireRole(['agent', 'admin'], 'index.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin_panel.php');
    exit;
}

if (!csrfValidate($_POST['csrf_token'] ?? null)) {
    $_SESSION['error_message'] = "Token de securitate invalid.";
    header('Location: admin_panel.php');
    exit;
}

$action = (string)($_POST['action'] ?? '');


if ($action === 'update_order_status') {
    $id_comanda = (int)($_POST['id_comanda'] ?? 0);
    $status = (string)($_POST['status'] ?? '');

    $allowed = ['in_asteptare', 'confirmata', 'anulata'];
    if ($id_comanda <= 0 || !in_array($status, $allowed, true)) {
        $_SESSION['error_message'] = "Date invalide pentru actualizarea statusului.";
        header('Location: admin_panel.php?tab=orders');
        exit;
    }

    $stmt = $conn->prepare("UPDATE comenzi SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $id_comanda);
    $ok = $stmt->execute();
    $stmt->close();

    $_SESSION['success_message'] = $ok ? "Statusul comenzii #{$id_comanda} a fost actualizat." : "Eroare la actualizare.";
    header('Location: admin_panel.php?tab=orders');
    exit;
}

if (!hasRole('admin')) {
    $_SESSION['error_message'] = "Doar administratorul poate gestiona utilizatori/produse.";
    header('Location: admin_panel.php?tab=orders');
    exit;
}

if ($action === 'update_user_role') {
    $id_utilizator = (int)($_POST['id_utilizator'] ?? 0);
    $rol = (string)($_POST['rol'] ?? 'client');

    $allowed = ['client','agent','admin'];
    if ($id_utilizator <= 0 || !in_array($rol, $allowed, true)) {
        $_SESSION['error_message'] = "Date invalide pentru rol.";
        header('Location: admin_panel.php?tab=users');
        exit;
    }

    if ((int)$_SESSION['user_id'] === $id_utilizator && $rol !== 'admin') {
        $_SESSION['error_message'] = "Nu îți poți schimba propriul rol din admin în alt rol.";
        header('Location: admin_panel.php?tab=users');
        exit;
    }

    $stmt = $conn->prepare("UPDATE utilizatori SET rol = ? WHERE id = ?");
    $stmt->bind_param('si', $rol, $id_utilizator);
    $ok = $stmt->execute();
    $stmt->close();

    $_SESSION['success_message'] = $ok ? "Rolul utilizatorului #{$id_utilizator} a fost setat la '{$rol}'." : "Eroare la schimbarea rolului.";
    header('Location: admin_panel.php?tab=users');
    exit;
}

if ($action === 'create_product') {
    $tip_pachet = trim((string)($_POST['tip_pachet'] ?? ''));
    $plecare = trim((string)($_POST['plecare'] ?? ''));
    $destinatie = trim((string)($_POST['destinatie'] ?? ''));
    $descriere = trim((string)($_POST['descriere'] ?? ''));
    $pret = (float)($_POST['pret'] ?? 0);
    $durata = (int)($_POST['durata'] ?? 0);
    $locuri = (int)($_POST['locuri_disponibile'] ?? 0);
    $data_plecare = $_POST['data_plecare'] ?? null;
    $data_plecare = ($data_plecare === '') ? null : $data_plecare;

    if ($tip_pachet === '' || $plecare === '' || $destinatie === '' || $descriere === '' || $pret < 0 || $durata < 1 || $locuri < 0) {
        $_SESSION['error_message'] = "Completează corect toate câmpurile produsului.";
        header('Location: admin_panel.php?tab=products');
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO produse (tip_pachet, plecare, destinatie, descriere, pret, durata, locuri_disponibile, data_plecare)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssdiss', $tip_pachet, $plecare, $destinatie, $descriere, $pret, $durata, $locuri, $data_plecare);
    $ok = $stmt->execute();
    $newId = (int)$conn->insert_id;
    $stmt->close();

    $_SESSION['success_message'] = $ok ? "Produs adăugat (#{$newId})." : "Eroare la adăugare produs.";
    header('Location: admin_panel.php?tab=products');
    exit;
}

if ($action === 'delete_product') {
    $id_produs = (int)($_POST['id_produs'] ?? 0);
    if ($id_produs <= 0) {
        $_SESSION['error_message'] = "ID produs invalid.";
        header('Location: admin_panel.php?tab=products');
        exit;
    }

    $del = $conn->prepare("DELETE FROM produse WHERE id = ?");
    $del->bind_param('i', $id_produs);
    $ok = $del->execute();
    $del->close();

    $_SESSION['success_message'] = $ok ? "Produsul #{$id_produs} a fost șters." : "Eroare la ștergere.";
    header('Location: admin_panel.php?tab=products');
    exit;
}

$_SESSION['error_message'] = "Acțiune necunoscută.";
header('Location: admin_panel.php');
exit;
