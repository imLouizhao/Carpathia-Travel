<?php
require 'header.php';

$idPachet = $_GET['id'] ?? 0;

if (!$idPachet) {
    header('Location: produse.php'); 
    exit;
}

$pachet = getProdusById($idPachet); 

if (!$pachet) {
    header('Location: produse.php');
    exit;
}

$imagini = getImaginiProdus($idPachet);
$imaginePrincipala = !empty($imagini) ? $imagini[0] : 'imagini/default.jpg';


$pacheteSimilare = getProduseSimilare($idPachet, $pachet['tip_pachet']);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pachet['destinatie']); ?> - Carpathia Travel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="product-detail">
            <div class="product-gallery-large">
                <img id="mainImage"
                    src="<?php echo $imaginePrincipala; ?>"
                    alt="<?php echo htmlspecialchars($pachet['destinatie']); ?>"
                    class="main-image">

                <?php if (!empty($imagini)): ?>
                <div class="thumbnail-images">
                    <?php foreach ($imagini as $index => $img): ?>
                        <img src="<?php echo $img; ?>"
                            alt="<?php echo htmlspecialchars($pachet['destinatie']); ?> - Imagine <?php echo $index + 1; ?>"
                            class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>"
                            onclick="changeMainImage('<?php echo $img; ?>', this)">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="product-info">
                <h1><?php echo htmlspecialchars($pachet['destinatie']); ?></h1>
                <div class="product-stock">
                    <?php
                    $locuriDisponibile = $pachet['locuri_disponibile'];
                    if($locuriDisponibile > 1): ?>
                        <span class="available">✓ Locuri disponibile</span>
                    <?php elseif($locuriDisponibile == 1): ?>
                        <span class="low-stock">⚠ Ultimul loc disponibil!</span>
                    <?php else: ?>
                        <span class="out-of-stock">✗ Pachet epuizat</span>
                    <?php endif; ?>
                </div>

                <div class="product-price"><?php echo $pachet['pret']; ?> EUR </div>

                <div class="product-description" style="white-space: pre-line;">
                    <?php echo htmlspecialchars($pachet['descriere']); ?>
                </div>

                <form method="POST" action="adauga_cos.php" class="add-to-cart-form">
                    <input type="hidden" name="id_produs" value="<?php echo $pachet['id']; ?>">
                    <div class="quantity-selector">
                        <label for="quantity">Număr persoane:</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $locuriDisponibile; ?>" <?php echo $locuriDisponibile == 0 ? 'disabled' : ''; ?>>
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
                    $locuriSimilar = $pachetSimilar['locuri_disponibile'];
                ?>
                    <div class="product-card">
                        <a href="produs.php?id=<?php echo $pachetSimilar['id']; ?>" class="product-link">
                            <div class="product-img" style="background-image: url('<?php echo $pachetSimilar['imagine_principala']; ?>');"></div>
                        </a>
                        <div class="product-content">
                            <a href="produs.php?id=<?php echo $pachetSimilar['id']; ?>" class="product-link">
                                <h3 class="product-title"><?php echo htmlspecialchars($pachetSimilar['destinatie']); ?></h3>
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

                            <div class="product-price-card"><?php echo $pachetSimilar['pret']; ?> EUR</div>

                            <?php if($locuriSimilar > 0): ?>
                            <form method="POST" action="adauga_cos.php" class="add-to-cart-form">
                                <input type="hidden" name="id_produs" value="<?php echo $pachetSimilar['id']; ?>">
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
