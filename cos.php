<?php
session_start();

require 'config.php';
require 'functions.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

function esc($v): string {
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
}

$success_message = $_SESSION['success_message'] ?? null;
$error_message   = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Token de securitate invalid. Reîncearcă.";
        header('Location: cos.php');
        exit;
    }

    $user_id = (int)$_SESSION['user_id'];

    if (isset($_POST['sterge'])) {
        $id_cos = (int)$_POST['sterge'];

        $stmt = $conn->prepare("DELETE FROM cos_cumparaturi WHERE id = ? AND id_utilizator = ?");
        $stmt->bind_param("ii", $id_cos, $user_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Pachetul a fost șters din coș!";
        } else {
            $_SESSION['error_message'] = "Eroare la ștergere.";
        }
        $stmt->close();

        header('Location: cos.php');
        exit;
    }

    if (isset($_POST['update_qty']) && isset($_POST['id_cos']) && isset($_POST['cantitate'])) {
        header('Content-Type: application/json; charset=utf-8');

        $id_cos = (int)$_POST['id_cos'];
        $qty    = (int)$_POST['cantitate'];

        if ($id_cos <= 0 || $qty < 1) {
            echo json_encode(['success' => false, 'message' => 'Date invalide']);
            exit;
        }

        $check = $conn->prepare("
            SELECT p.locuri_disponibile AS stoc, p.pret
            FROM cos_cumparaturi c
            JOIN produse p ON p.id = c.id_produs
            WHERE c.id = ? AND c.id_utilizator = ?
        ");
        $check->bind_param("ii", $id_cos, $user_id);
        $check->execute();
        $res = $check->get_result();

        if (!$row = $res->fetch_assoc()) {
            $check->close();
            echo json_encode(['success' => false, 'message' => 'Produsul nu a fost găsit în coș']);
            exit;
        }

        $stoc = (int)$row['stoc'];
        $pret = (float)$row['pret'];

        if ($stoc < 1) {
            $check->close();
            echo json_encode(['success' => false, 'message' => 'Pachet indisponibil (0 locuri)']);
            exit;
        }

        if ($qty > $stoc) $qty = $stoc;

        $upd = $conn->prepare("UPDATE cos_cumparaturi SET cantitate = ? WHERE id = ? AND id_utilizator = ?");
        $upd->bind_param("iii", $qty, $id_cos, $user_id);

        if (!$upd->execute()) {
            $upd->close();
            $check->close();
            echo json_encode(['success' => false, 'message' => 'Eroare la actualizare']);
            exit;
        }
        $upd->close();
        $check->close();

        $totStmt = $conn->prepare("
            SELECT SUM(p.pret * c.cantitate) AS total
            FROM cos_cumparaturi c
            JOIN produse p ON p.id = c.id_produs
            WHERE c.id_utilizator = ?
        ");
        $totStmt->bind_param("i", $user_id);
        $totStmt->execute();
        $totRow = $totStmt->get_result()->fetch_assoc();
        $totStmt->close();

        $total = (float)($totRow['total'] ?? 0);
        $subtotal = $pret * $qty;

        echo json_encode([
            'success' => true,
            'cantitate' => $qty,
            'subtotal' => number_format($subtotal, 2, '.', ''),
            'total' => number_format($total, 2, '.', ''),
            'stoc' => $stoc
        ]);
        exit;
    }

    if (isset($_POST['finalizeaza_comanda'])) {
        $nume    = trim((string)($_POST['nume'] ?? ''));
        $email   = trim((string)($_POST['email'] ?? ''));
        $telefon = trim((string)($_POST['telefon'] ?? ''));
        $obs     = trim((string)($_POST['observatii'] ?? ''));

        if ($nume === '' || $email === '' || $telefon === '') {
            $_SESSION['error_message'] = "Completează numele, emailul și telefonul.";
            header('Location: cos.php');
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_message'] = "Email invalid.";
            header('Location: cos.php');
            exit;
        }

        $cartStmt = $conn->prepare("
            SELECT c.id AS id_cos, c.id_produs, c.cantitate,
                   p.pret, p.locuri_disponibile
            FROM cos_cumparaturi c
            JOIN produse p ON p.id = c.id_produs
            WHERE c.id_utilizator = ?
        ");
        $cartStmt->bind_param("i", $user_id);
        $cartStmt->execute();
        $cartRes = $cartStmt->get_result();

        $cartItems = [];
        $total = 0.0;

        while ($it = $cartRes->fetch_assoc()) {
            $qty  = (int)$it['cantitate'];
            $pret = (float)$it['pret'];
            $stoc = (int)$it['locuri_disponibile'];

            if ($stoc < 1) {
                $cartStmt->close();
                $_SESSION['error_message'] = "Un pachet din coș nu mai are locuri disponibile.";
                header('Location: cos.php');
                exit;
            }
            if ($qty > $stoc) $qty = $stoc;

            $it['cantitate'] = $qty;
            $total += $pret * $qty;
            $cartItems[] = $it;
        }
        $cartStmt->close();

        if ($total <= 0 || empty($cartItems)) {
            $_SESSION['error_message'] = "Coșul este gol.";
            header('Location: cos.php');
            exit;
        }

        $conn->begin_transaction();
        try {
            $status = 'plasata';

            $ins = $conn->prepare("
                INSERT INTO comenzi (id_utilizator, data_comanda, total, status)
                VALUES (?, NOW(), ?, ?)
            ");
            $ins->bind_param("ids", $user_id, $total, $status);

            if (!$ins->execute()) {
                throw new Exception("Eroare inserare comanda");
            }
            $id_comanda = (int)$conn->insert_id;
            $ins->close();

            $line = $conn->prepare("
                INSERT INTO comenzi_produse (id_comanda, id_produs, cantitate, pret_unitar)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($cartItems as $it) {
                $id_produs = (int)$it['id_produs'];
                $qty       = (int)$it['cantitate'];
                $pret      = (float)$it['pret'];

                $line->bind_param("iiid", $id_comanda, $id_produs, $qty, $pret);
                if (!$line->execute()) {
                    throw new Exception("Eroare inserare linie comanda");
                }
            }
            $line->close();

            $clr = $conn->prepare("DELETE FROM cos_cumparaturi WHERE id_utilizator = ?");
            $clr->bind_param("i", $user_id);
            if (!$clr->execute()) {
                throw new Exception("Eroare golire cos");
            }
            $clr->close();

            $conn->commit();
            $_SESSION['success_message'] = "Comanda a fost plasată cu succes! (ID comanda: #{$id_comanda})";
            header('Location: cos.php');
            exit;

        } catch (Throwable $e) {
            $conn->rollback();
            $_SESSION['error_message'] = "Eroare la plasarea comenzii: " . $e->getMessage();
            header('Location: cos.php');
            exit;
        }
    }

    $_SESSION['error_message'] = "Acțiune invalidă.";
    header('Location: cos.php');
    exit;
}

$cos_items = [];
$total = 0.0;

if (isset($_SESSION['user_id'])) {
    $user_id = (int)$_SESSION['user_id'];

    $stmt = $conn->prepare("
        SELECT
            c.id AS id_cos,
            c.id_produs,
            c.cantitate,
            p.plecare,
            p.destinatie,
            p.pret,
            p.durata,
            p.data_plecare,
            p.locuri_disponibile AS stoc,
            (SELECT ip.imagine
             FROM imagini_produse ip
             WHERE ip.id_produs = p.id
             ORDER BY ip.ordine ASC
             LIMIT 1) AS imagine_principala
        FROM cos_cumparaturi c
        JOIN produse p ON p.id = c.id_produs
        WHERE c.id_utilizator = ?
        ORDER BY c.id DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($item = $res->fetch_assoc()) {
        $pret = (float)$item['pret'];
        $qty  = (int)$item['cantitate'];
        $stoc = (int)$item['stoc'];
        if ($stoc > 0 && $qty > $stoc) $qty = $stoc;

        $item['cantitate'] = $qty;
        $total += $pret * $qty;
        $cos_items[] = $item;
    }
    $stmt->close();
}

require 'header.php';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coș - Carpathia Travel</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .cos-container { margin: 40px auto; padding: 20px; }
        .cos-title { text-align: center; margin-bottom: 30px; color: #333; }

        .alert { padding: 15px; text-align: center; margin: 20px auto; max-width: 1200px; border-radius: 5px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }

        .cos-item { display: flex; align-items: center; background: #fff; padding: 20px; margin-bottom: 15px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); gap: 15px; }
        .cos-item-img { width: 110px; height: 90px; background-size: cover; background-position: center; border-radius: 8px; flex: 0 0 auto; }
        .cos-item-details { flex: 1; }
        .cos-item-details h3 { margin: 0 0 5px 0; }
        .cos-item-details .meta { color: #666; font-size: 14px; }

        .qty-wrap { display: flex; align-items: center; gap: 10px; }
        .qty-controls { display: inline-flex; align-items: center; gap: 6px; }
        .qty-btn { width: 32px; height: 32px; border: 1px solid #ddd; background: #f9f9f9; border-radius: 6px; cursor: pointer; font-weight: 700; }
        .qty-btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .qty-input { width: 70px; padding: 8px; border: 1px solid #ddd; border-radius: 6px; text-align: center; }

        .line-subtotal { min-width: 120px; text-align: right; font-weight: 700; color: #8B7355; }

        .cos-total { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
        .cos-actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }

        .btn { display: inline-block; background: #8B7355; color: #fff; padding: 10px 18px; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; font-weight: 600; }
        .btn:hover { background: #6d5c46; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }

        .empty-cart { text-align: center; padding: 50px 20px; background: #fff; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); }
        .empty-cart h2 { margin-bottom: 10px; color: #8B7355; }

        .checkout-form { display: none; margin-top: 25px; background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); }
        .checkout-form.active { display: block; }
        .checkout-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .checkout-grid .full { grid-column: 1 / -1; }
        .checkout-group label { display: block; font-weight: 600; margin-bottom: 6px; }
        .checkout-group input, .checkout-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; }
        .checkout-group textarea { min-height: 90px; resize: vertical; }

        @media (max-width: 768px) {
            .cos-item { flex-direction: column; align-items: stretch; }
            .cos-item-img { width: 100%; height: 180px; }
            .checkout-grid { grid-template-columns: 1fr; }
            .line-subtotal { text-align: left; }
        }
    </style>
</head>
<body>
<div class="container cos-container">
    <h1 class="cos-title">Coșul tău</h1>

    <?php if ($success_message): ?>
        <div class="alert alert-success"><?= esc($success_message) ?></div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-error"><?= esc($error_message) ?></div>
    <?php endif; ?>

    <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="empty-cart">
            <h2>Trebuie să fii autentificat pentru a vedea coșul</h2>
            <p>Autentifică-te pentru a rezerva pachete turistice.</p>
            <a href="login.php" class="btn">Autentificare</a>
            <a href="register.php" class="btn">Înregistrare</a>
        </div>
    <?php else: ?>

        <?php if (empty($cos_items) || $total <= 0): ?>
            <div class="empty-cart">
                <h2>Coșul tău este gol</h2>
                <p>Adaugă un pachet turistic din listă.</p>
                <a href="produse.php" class="btn">Vezi pachete</a>
            </div>
        <?php else: ?>

            <?php foreach ($cos_items as $item): ?>
                <?php
                    $img  = $item['imagine_principala'] ?: 'imagini/default.jpg';
                    $stoc = (int)$item['stoc'];
                    $qty  = (int)$item['cantitate'];
                    $pret = (float)$item['pret'];
                    $subtotal = $pret * $qty;
                ?>
                <div class="cos-item"
                     data-idcos="<?= (int)$item['id_cos'] ?>"
                     data-price="<?= esc(number_format($pret, 2, '.', '')) ?>"
                     data-stock="<?= (int)$stoc ?>">
                    <div class="cos-item-img" style="background-image:url('<?= esc($img) ?>')"></div>

                    <div class="cos-item-details">
                        <h3><?= esc($item['plecare']) ?> → <?= esc($item['destinatie']) ?></h3>
                        <div class="meta">
                            Durată: <strong><?= esc($item['durata']) ?> zile</strong> ·
                            Plecare: <strong><?= esc($item['data_plecare']) ?></strong> ·
                            Preț: <strong><?= number_format($pret, 2) ?> EUR / persoană</strong>
                        </div>
                    </div>

                    <div class="qty-wrap">
                        <label>Persoane</label>
                        <div class="qty-controls">
                            <button class="qty-btn btn-minus" type="button">-</button>
                            <input class="qty-input" type="number" min="1"
                                   max="<?= $stoc > 0 ? (int)$stoc : 1 ?>"
                                   value="<?= (int)$qty ?>">
                            <button class="qty-btn btn-plus" type="button">+</button>
                        </div>
                    </div>

                    <div class="line-subtotal">
                        <span class="line-subtotal-value"><?= number_format($subtotal, 2) ?></span> EUR
                    </div>

                    <div>
                        <form method="POST" action="cos.php" style="margin:0;">
                            <input type="hidden" name="csrf_token" value="<?= esc($csrf_token) ?>">
                            <input type="hidden" name="sterge" value="<?= (int)$item['id_cos'] ?>">
                            <button type="submit" class="btn btn-danger"
                                    onclick="return confirm('Sigur vrei să ștergi acest pachet din coș?');">
                                Șterge
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="cos-total">
                <div>
                    <strong>Total:</strong> <span id="cartTotal"><?= number_format($total, 2) ?></span> EUR
                </div>

                <div class="cos-actions">
                    <a href="produse.php" class="btn">Continuă cumpărăturile</a>

                    <button type="button" id="toggleCheckoutBtn" class="btn btn-success">
                        Finalizează comanda
                    </button>
                </div>
            </div>

            <div class="checkout-form" id="checkoutForm">
                <h2 style="margin-top:0;">Detalii rezervare</h2>
                <p style="color:#666; margin-top:6px;">
                    Completează datele de contact. Te vom suna / trimite email pentru confirmare.
                </p>

                <form method="POST" action="cos.php" id="checkoutSubmitForm">
                    <input type="hidden" name="csrf_token" value="<?= esc($csrf_token) ?>">

                    <div class="checkout-grid">
                        <div class="checkout-group">
                            <label for="nume">Nume complet *</label>
                            <input type="text" id="nume" name="nume" required>
                        </div>

                        <div class="checkout-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required>
                        </div>

                        <div class="checkout-group full">
                            <label for="telefon">Telefon *</label>
                            <input type="tel" id="telefon" name="telefon" required placeholder="07xxxxxxxx">
                        </div>

                        <div class="checkout-group full">
                            <label for="observatii">Observații (opțional)</label>
                            <textarea id="observatii" name="observatii" placeholder="Preferințe, solicitări speciale etc."></textarea>
                        </div>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-top:15px;">
                        <div>
                            <strong>Total de plată:</strong> <span id="totalCheckout"><?= number_format($total, 2) ?></span> EUR
                        </div>

                        <div style="display:flex; gap:10px; flex-wrap:wrap;">
                            <button type="button" id="cancelCheckoutBtn" class="btn">
                                Înapoi
                            </button>

                            <button type="submit" name="finalizeaza_comanda" value="1" class="btn btn-success">
                                Plasează comanda
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <script>
                (function () {
                    const csrfToken = <?= json_encode($csrf_token) ?>;

                    const checkoutBox = document.getElementById('checkoutForm');
                    const toggleBtn = document.getElementById('toggleCheckoutBtn');
                    const cancelBtn = document.getElementById('cancelCheckoutBtn');

                    if (toggleBtn && checkoutBox) {
                        toggleBtn.addEventListener('click', () => {
                            checkoutBox.classList.add('active');
                            checkoutBox.scrollIntoView({behavior: 'smooth', block: 'start'});
                        });
                    }
                    if (cancelBtn && checkoutBox) {
                        cancelBtn.addEventListener('click', () => {
                            checkoutBox.classList.remove('active');
                        });
                    }

                    function setTotals(totalStr) {
                        const totalEl = document.getElementById('cartTotal');
                        const totalCheckoutEl = document.getElementById('totalCheckout');
                        if (totalEl) totalEl.textContent = totalStr;
                        if (totalCheckoutEl) totalCheckoutEl.textContent = totalStr;
                    }

                    async function updateQty(idCos, qty, rowEl) {
                        const fd = new FormData();
                        fd.append('csrf_token', csrfToken);
                        fd.append('update_qty', '1');
                        fd.append('id_cos', String(idCos));
                        fd.append('cantitate', String(qty));

                        const resp = await fetch('cos.php', {
                            method: 'POST',
                            body: fd
                        });

                        const data = await resp.json();
                        if (!data.success) {
                            alert(data.message || 'Eroare la actualizare cantitate');
                            return;
                        }

                        const newQty = parseInt(data.cantitate, 10);
                        const price = parseFloat(rowEl.getAttribute('data-price') || '0');
                        const lineSubtotal = price * newQty;

                        const input = rowEl.querySelector('.qty-input');
                        if (input) input.value = newQty;

                        const lineEl = rowEl.querySelector('.line-subtotal-value');
                        if (lineEl) lineEl.textContent = Number(lineSubtotal).toFixed(2);

                        setTotals(String(data.total));
                        refreshButtons(rowEl);
                    }

                    function refreshButtons(rowEl) {
                        const stock = parseInt(rowEl.getAttribute('data-stock') || '1', 10);
                        const input = rowEl.querySelector('.qty-input');
                        const minus = rowEl.querySelector('.btn-minus');
                        const plus = rowEl.querySelector('.btn-plus');

                        const val = parseInt(input.value || '1', 10);

                        if (minus) minus.disabled = val <= 1;
                        if (plus) plus.disabled = val >= stock;
                    }

                    document.querySelectorAll('.cos-item').forEach(rowEl => {
                        const idCos = parseInt(rowEl.getAttribute('data-idcos'), 10);
                        const stock = parseInt(rowEl.getAttribute('data-stock') || '1', 10);

                        const input = rowEl.querySelector('.qty-input');
                        const minus = rowEl.querySelector('.btn-minus');
                        const plus = rowEl.querySelector('.btn-plus');

                        refreshButtons(rowEl);

                        if (minus) {
                            minus.addEventListener('click', async () => {
                                const current = parseInt(input.value || '1', 10);
                                if (current > 1) {
                                    await updateQty(idCos, current - 1, rowEl);
                                }
                            });
                        }

                        if (plus) {
                            plus.addEventListener('click', async () => {
                                const current = parseInt(input.value || '1', 10);
                                if (current < stock) {
                                    await updateQty(idCos, current + 1, rowEl);
                                }
                            });
                        }

                        if (input) {
                            input.addEventListener('change', async () => {
                                let val = parseInt(input.value || '1', 10);
                                if (isNaN(val) || val < 1) val = 1;
                                if (val > stock) val = stock;
                                await updateQty(idCos, val, rowEl);
                            });
                        }
                    });
                })();
            </script>

        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>

<?php
require 'footer.php';
$conn->close();
