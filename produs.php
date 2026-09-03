<?php
$idPachet = $_GET['id'] ?? 0;

if (!$idPachet) {
    header('Location: produse.php'); 
    exit;
}

require 'config.php';
require 'functions.php';

$pachet = getProdusById($idPachet); 

if (!$pachet) {
    header('Location: produse.php');
    exit;
}

$imagini = getImaginiProdus($idPachet);
$imaginePrincipala = !empty($imagini) ? $imagini[0] : 'imagini/default.jpg';

$pacheteSimilare = getProduseSimilare($idPachet, $pachet['tip_pachet']);

$cursEurRon = getCursValutarEurRon();

$pageTitle = esc($pachet['destinatie']) . ' - Carpathia Travel';
$pageDescription = 'Pachet turistic ' . esc($pachet['tip_pachet']) . ' către ' . esc($pachet['destinatie'])
    . ', ' . (int)$pachet['durata'] . ' zile, de la ' . esc((string)$pachet['pret']) . ' EUR / persoană.';

require 'header.php';
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="product-detail">
            <div class="product-gallery-large">
                <img id="mainImage"
                    src="<?php echo esc($imaginePrincipala); ?>"
                    alt="<?php echo esc($pachet['destinatie']); ?>"
                    class="main-image">

                <?php if (!empty($imagini)): ?>
                <div class="thumbnail-images">
                    <?php foreach ($imagini as $index => $img): ?>
                        <img src="<?php echo esc($img); ?>"
                            alt="<?php echo esc($pachet['destinatie']); ?> - Imagine <?php echo (int)$index + 1; ?>"
                            class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>"
                            onclick="changeMainImage('<?php echo esc($img); ?>', this)">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="product-info">
                <h1><?php echo esc($pachet['destinatie']); ?></h1>
                <div class="product-stock">
                    <?php
                    $locuriDisponibile = (int)$pachet['locuri_disponibile'];
                    if($locuriDisponibile > 1): ?>
                        <span class="available">✓ Locuri disponibile</span>
                    <?php elseif($locuriDisponibile == 1): ?>
                        <span class="low-stock">⚠ Ultimul loc disponibil!</span>
                    <?php else: ?>
                        <span class="out-of-stock">✗ Pachet epuizat</span>
                    <?php endif; ?>
                </div>

                <div class="product-price">
                    <?php echo esc((string)$pachet['pret']); ?> EUR
                    <?php if ($cursEurRon !== null): ?>
                        <span style="display:block; font-size:14px; font-weight:normal; color:#888;">
                            ≈ <?php echo number_format((float)$pachet['pret'] * $cursEurRon, 0, ',', '.'); ?> RON
                            <small>(curs orientativ EUR/RON actualizat automat)</small>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="product-description" style="white-space: pre-line;">
                    <?php echo esc($pachet['descriere']); ?>
                </div>

                <form method="POST" action="adauga_cos.php" class="add-to-cart-form">
                    <input type="hidden" name="id_produs" value="<?php echo (int)$pachet['id']; ?>">
                    <div class="quantity-selector">
                        <label for="quantity">Număr persoane:</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo (int)$locuriDisponibile; ?>" <?php echo $locuriDisponibile == 0 ? 'disabled' : ''; ?>>
                    </div>
                    <button type="submit" class="add-to-cart-large" <?php echo $locuriDisponibile == 0 ? 'disabled' : ''; ?>>
                        <?php echo $locuriDisponibile == 0 ? 'Pachet epuizat' : 'Rezervă acum'; ?>
                    </button>
                </form>
            </div>
        </div>

        <?php if($pacheteSimilare->num_rows > 0): ?>
        <div class="similar-products">
            <h2>Pachete similare</h2>
            <div class="products-grid">
                <?php while($pachetSimilar = $pacheteSimilare->fetch_assoc()):
                    $locuriSimilar = (int)$pachetSimilar['locuri_disponibile'];
                ?>
                    <div class="product-card">
                        <a href="produs.php?id=<?php echo (int)$pachetSimilar['id']; ?>" class="product-link">
                            <div class="product-img" style="background-image: url('<?php echo esc($pachetSimilar['imagine_principala'] ?? 'imagini/default.jpg'); ?>');"></div>
                        </a>
                        <div class="product-content">
                            <a href="produs.php?id=<?php echo (int)$pachetSimilar['id']; ?>" class="product-link">
                                <h3 class="product-title"><?php echo esc($pachetSimilar['destinatie']); ?></h3>
                            </a>

                            <div class="product-stock">
                                <?php if($locuriSimilar > 1): ?>
                                    <span class="available">✓ Locuri disponibile</span>
                                <?php elseif($locuriSimilar == 1): ?>
                                    <span class="low-stock">⚠ Ultimul loc!</span>
                                <?php else: ?>
                                    <span class="out-of-stock">✗ Pachet epuizat</span>
                                <?php endif; ?>
                            </div>

                            <div class="product-price-card"><?php echo esc((string)$pachetSimilar['pret']); ?> EUR</div>

                            <?php if($locuriSimilar > 0): ?>
                            <form method="POST" action="adauga_cos.php" class="add-to-cart-form">
                                <input type="hidden" name="id_produs" value="<?php echo (int)$pachetSimilar['id']; ?>">
                                <button type="submit" class="add-to-cart">
                                    Rezervă acum
                                </button>
                            </form>
                            <?php else: ?>
                            <button type="button" class="add-to-cart disabled" disabled>
                                Pachet epuizat
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
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
                    .then(r => r.json())
                    .then(data => {
                        if (data.message === 'login_required') {
                            if (confirm('Trebuie să fii autentificat pentru a rezerva. Mergi la login?')) {
                                window.location.href = 'login.php';
                            }
                            return;
                        }

                        if (data.success) {
                            alert('Pachetul a fost adăugat în coș!');
                            location.reload();
                            return;
                        }

                        alert('Eroare la adăugarea în coș: ' + (data.message || 'eroare necunoscută'));
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
