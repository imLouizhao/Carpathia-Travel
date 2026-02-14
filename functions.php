<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function esc($v): string {
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
}

function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

function currentUserId(): ?int {
    return isLoggedIn() ? (int)$_SESSION['user_id'] : null;
}

function currentUserRole(): string {
    return (string)($_SESSION['user_role'] ?? 'client');
}

function hasRole($roles): bool {
    $role = currentUserRole();
    if (is_string($roles)) {
        return $role === $roles;
    }
    if (is_array($roles)) {
        return in_array($role, $roles, true);
    }
    return false;
}

function requireLogin(string $redirect = 'login.php'): void {
    if (!isLoggedIn()) {
        header('Location: ' . $redirect);
        exit;
    }
}

function requireRole($roles, string $redirect = 'index.php'): void {
    requireLogin();
    if (!hasRole($roles)) {
        http_response_code(403);
        header('Location: ' . $redirect);
        exit;
    }
}

function refreshUserRoleFromDb(): void {
    if (!isLoggedIn()) return;
    if (!isset($GLOBALS['conn']) || !($GLOBALS['conn'] instanceof mysqli)) return;
    $conn = $GLOBALS['conn'];

    $uid = (int)$_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT rol FROM utilizatori WHERE id = ?");
    if (!$stmt) return;
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $_SESSION['user_role'] = $row['rol'] ?? 'client';
    }
    $stmt->close();
}

function csrfEnsureToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return (string)$_SESSION['csrf_token'];
}

function csrfValidate(?string $token): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) return false;
    return hash_equals((string)$_SESSION['csrf_token'], (string)$token);
}

function getProduse($filtruCategorie = '', $filtruPret = '', $sortare = '', $pagina = 1, $produsePerPagina = 8) {
    global $conn;

    $pagina = max(1, (int)$pagina);
    $produsePerPagina = max(1, (int)$produsePerPagina);
    $start = ($pagina - 1) * $produsePerPagina;

    $where = [];
    $params = [];
    $types = '';

    if (!empty($filtruCategorie) && $filtruCategorie !== 'Toate') {
        $where[] = "p.tip_pachet = ?";
        $params[] = $filtruCategorie;
        $types .= "s";
    }

    if (!empty($filtruPret)) {
        switch ($filtruPret) {
            case 'Sub 100 Lei':
                $where[] = "p.pret < 100";
                break;
            case '100-200 Lei':
                $where[] = "p.pret BETWEEN 100 AND 200";
                break;
            case 'Peste 200 Lei':
                $where[] = "p.pret > 200";
                break;
        }
    }

    $whereClause = '';
    if (!empty($where)) {
        $whereClause = "WHERE " . implode(" AND ", $where);
    }

    $orderBy = "ORDER BY p.id DESC";
    switch ($sortare) {
        case 'Sortează după preț: crescător':
            $orderBy = "ORDER BY p.pret ASC, p.id ASC";
            break;
        case 'Sortează după preț: descrescător':
            $orderBy = "ORDER BY p.pret DESC, p.id DESC";
            break;
        case 'Sortează după evaluare':
            $orderBy = "ORDER BY p.rating DESC, p.id DESC";
            break;
    }

    $sql = "
        SELECT p.*,
               (SELECT imagine
                FROM imagini_produse
                WHERE id_produs = p.id
                ORDER BY ordine ASC
                LIMIT 1) AS imagine_principala
        FROM produse p
        $whereClause
        $orderBy
        LIMIT ?, ?
    ";

    $params[] = $start;
    $params[] = $produsePerPagina;
    $types .= "ii";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Eroare prepare getProduse: " . $conn->error);
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result();
}

function getTotalProduse($filtruCategorie = '', $filtruPret = '') {
    global $conn;

    $where = [];
    $params = [];
    $types = '';

    if (!empty($filtruCategorie) && $filtruCategorie !== 'Toate') {
        $where[] = "tip_pachet = ?";
        $params[] = $filtruCategorie;
        $types .= "s";
    }

    if (!empty($filtruPret)) {
        switch ($filtruPret) {
            case 'Sub 100 Lei':
                $where[] = "pret < 100";
                break;
            case '100-200 Lei':
                $where[] = "pret BETWEEN 100 AND 200";
                break;
            case 'Peste 200 Lei':
                $where[] = "pret > 200";
                break;
        }
    }

    $whereClause = '';
    if (!empty($where)) {
        $whereClause = "WHERE " . implode(" AND ", $where);
    }

    $sql = "SELECT COUNT(*) AS total FROM produse $whereClause";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Eroare prepare getTotalProduse: " . $conn->error);
    }

    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return (int)($row['total'] ?? 0);
}

function getCartCount(): int {
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (isset($_SESSION['user_id'])) {
        global $conn;
        $stmt = $conn->prepare("SELECT COALESCE(SUM(cantitate),0) as total FROM cos_cumparaturi WHERE id_utilizator = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)($row['total'] ?? 0);
    }
    return 0;
}

function getImaginiProdus($idProdus): array {
    global $conn;

    $sql = "SELECT imagine FROM imagini_produse WHERE id_produs = ? ORDER BY ordine ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idProdus);
    $stmt->execute();
    $result = $stmt->get_result();

    $imagini = [];
    while ($row = $result->fetch_assoc()) {
        $imagini[] = $row['imagine'];
    }
    $stmt->close();
    return $imagini;
}

function getProdusById($id): ?array {
    global $conn;

    $sql = "
        SELECT p.*,
               (SELECT imagine
                FROM imagini_produse
                WHERE id_produs = p.id
                ORDER BY ordine ASC
                LIMIT 1) AS imagine_principala
        FROM produse p
        WHERE p.id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function getProduseSimilare($idProdus, $categorie, $limit = 4) {
    global $conn;

    $sql = "
        SELECT p.*,
               (SELECT imagine
                FROM imagini_produse
                WHERE id_produs = p.id
                ORDER BY ordine ASC
                LIMIT 1) AS imagine_principala
        FROM produse p
        WHERE p.tip_pachet = ? AND p.id != ?
        LIMIT ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $categorie, $idProdus, $limit);
    $stmt->execute();

    return $stmt->get_result();
}

function getImaginePrincipala($idProdus): string {
    global $conn;
    $sql = "SELECT imagine FROM imagini_produse WHERE id_produs = ? ORDER BY ordine ASC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idProdus);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt->close();
        return (string)$row['imagine'];
    }
    $stmt->close();
    return 'imagini/default.jpg';
}

?>
