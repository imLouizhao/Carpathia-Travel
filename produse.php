<?php
session_start();
require 'config.php';
require 'functions.php';

$filtruTip = $_GET['categorie'] ?? '';
$filtruPret = $_GET['pret'] ?? '';
$sortare = $_GET['sortare'] ?? '';        
$pagina = (int)($_GET['pagina'] ?? 1);

$produse = getProduse($filtruTip, $filtruPret, $sortare, $pagina, 8);
$totalProduse = getTotalProduse($filtruTip, $filtruPret);
$totalPagini = (int)ceil($totalProduse / 8);

require 'header.php';
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Pachete turistice – Carpathia Travel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <div class="filters">
        <form method="GET">
            <h3>Tip pachet</h3>
            <div class="filter-options">
                <a href="produse.php" class="filter-btn <?= empty($filtruTip) ? 'active' : '' ?>">Toate</a>

                <?php
                $keep = [];
                if (!empty($filtruPret)) $keep['pret'] = $filtruPret;
                if (!empty($sortare)) $keep['sortare'] = $sortare;
                ?>

                <a href="?<?= htmlspecialchars(http_build_query(array_merge($keep, ['categorie'=>'city break']))) ?>"
                   class="filter-btn <?= $filtruTip=='city break'?'active':'' ?>">City Break</a>

                <a href="?<?= htmlspecialchars(http_build_query(array_merge($keep, ['categorie'=>'circuit']))) ?>"
                   class="filter-btn <?= $filtruTip=='circuit'?'active':'' ?>">Circuit</a>

                <a href="?<?= htmlspecialchars(http_build_query(array_merge($keep, ['categorie'=>'munte']))) ?>"
                   class="filter-btn <?= $filtruTip=='munte'?'active':'' ?>">Munte</a>

                <a href="?<?= htmlspecialchars(http_build_query(array_merge($keep, ['categorie'=>'litoral']))) ?>"
                   class="filter-btn <?= $filtruTip=='litoral'?'active':'' ?>">Litoral</a>
            </div>
        </form>
    </div>
</div>

<div class="container">
    <h1 class="section-title">Pachete turistice</h1>

    <div class="products-grid">
        <?php while($produs = $produse->fetch_assoc()):
            $imagine = $produs['imagine_principala'] ?? 'img/no-image.jpg';

            $locuri = (int)($produs['locuri_disponibile'] ?? 0);

            if ($locuri > 5) {
                $status = "Locuri disponibile";
                $class = "available";
            } elseif ($locuri > 0) {
                $status = "Ultimele locuri";
                $class = "low-stock";
            } else {
                $status = "Indisponibil";
                $class = "out-of-stock";
            }
        ?>

        <div class="product-card">
            <a href="produs.php?id=<?= (int)$produs['id'] ?>">
                <img class="product-card-img" src="<?= htmlspecialchars($imagine) ?>" alt="<?= htmlspecialchars($produs['destinatie'] ?? '') ?>">
            </a>

            <div class="product-content">
                <h3><?= htmlspecialchars($produs['plecare'] ?? '') ?> → <?= htmlspecialchars($produs['destinatie'] ?? '') ?></h3>

                <p class="product-type">
                    Tip pachet: <strong><?= htmlspecialchars(ucfirst($produs['tip_pachet'] ?? '')) ?></strong>
                </p>

                <p class="product-duration">
                    Durata: <?= (int)($produs['durata'] ?? 0) ?> zile
                </p>

                <p class="product-date">
                    Data plecare: <?= htmlspecialchars($produs['data_plecare'] ?? '') ?>
                </p>

                <div class="product-price">
                    <?= htmlspecialchars($produs['pret'] ?? '') ?> EUR / persoană
                </div>

                <div class="product-availability <?= $class ?>">
                    <?= $status ?>
                </div>

                <?php if ($locuri > 0): ?>
                    <form method="POST" action="adauga_cos.php" class="add-to-cart-form">
                        <input type="hidden" name="id_produs" value="<?= (int)$produs['id'] ?>">
                        <input type="hidden" name="cantitate" value="1">
                        <button type="submit" class="add-to-cart">Rezervă</button>
                    </form>
                <?php else: ?>
                    <button class="add-to-cart disabled" disabled>Indisponibil</button>
                <?php endif; ?>
            </div>
        </div>

        <?php endwhile; ?>
    </div>

    <?php if ($totalPagini > 1): ?>
    <div class="pagination">
        <?php
            $params = $_GET;
            for ($i = 1; $i <= $totalPagini; $i++):
                $params['pagina'] = $i;
                $url = '?' . http_build_query($params);
        ?>
            <a href="<?= htmlspecialchars($url) ?>" class="<?= ($i === (int)$pagina) ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        document.querySelectorAll('.add-to-cart-form').forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const fd = new FormData(this);
                const id = fd.get('id_produs');
                const qty = fd.get('cantitate') || fd.get('quantity') || 1;

                fetch('adauga_cos.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id_produs=' + encodeURIComponent(id) +
                        '&cantitate=' + encodeURIComponent(qty)
                })
                .then(r => r.text())
                .then(txt => {
                    const resp = txt.trim();

                    if (resp === 'login_required') {
                        if (confirm('Trebuie să fii autentificat pentru a rezerva. Mergi la login?')) {
                            window.location.href = 'login.php';
                        }
                        return;
                    }

                    if (resp === 'success') {
                        alert('Pachetul a fost adăugat în coș!');
                        location.reload();
                        return;
                    }

                    alert('Eroare la adăugarea în coș!');
                })
                .catch(() => {
                    alert('Eroare de rețea!');
                });
            });
        });

    });
</script>



</body>
</html>

<?php require 'footer.php'; ?>
