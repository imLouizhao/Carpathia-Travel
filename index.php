<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_produs'])) {
    header('Location: adauga_cos.php');
    exit;
}

require 'header.php';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bijuterii din Perle Naturale | Magazin Online</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/jpeg" href="alte_poze/logo_luna_pearl_borderless.jpeg">
    <style>
        .product-card {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .product-content {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            padding: 15px;
            position: relative;
        }
        
        .product-content-bottom {
            margin-top: auto; 
            padding-top: 10px;
            border-top: 1px solid #eee;
        }
        
        .product-card .add-to-cart {
            margin-top: 10px;
            width: 100%;
            margin-bottom: 0;
        }
        
        .product-title {
            margin-bottom: 8px;
            min-height: 48px;
        }
        
        .product-rating {
            margin-bottom: 8px;
        }
        
        .product-price {
            font-weight: bold;
            font-size: 1.2em;
            margin-bottom: 0;
        }
        
        .product-img {
            height: 250px;
            width: 100%;
            background-size: cover;
            background-position: center;
        }
        
        .product-top-content {
            margin-bottom: 15px;
        }
        
        .add-to-cart-form {
            width: 100%;
        }
    </style>
</head>
<body>
    <section class="hero">
        <div class="container">
            <h1>Călătorește prin lume cu Carpathia Travel</h1>
            <p>Descoperă colecția noastră exclusivă de pachete turistice prin toată lumea.</p>
            <a href="produse.php" class="btn">Descoperă colecția</a>
        </div>
    </section>

    <section class="categories">
        <div class="container">
            <h2 class="section-title">Destinațiile sezonului</h2>
            <div class="categories-grid">
                <div class="category-card">
                    <div class="category-img" style="background-image: url('alte_poze/turcia.jpg');"></div>
                    <div class="category-content">
                        <h3>Turcia</h3>
                        <p>Ospitalitate fără concurență, relaxare, servicii de lux, soare, hoteluri confortabile</p>
                    </div>
                </div>
                <div class="category-card">
                    <div class="category-img" style="background-image: url('alte_poze/egipt.jpg');"></div>
                    <div class="category-content">
                        <h3>Egipt</h3>
                        <p>Oaze de vacanță, mister, deșert, plajă, istorie antică, scufundări</p>
                    </div>
                </div>
                <div class="category-card">
                    <div class="category-img" style="background-image: url('alte_poze/grecia.jpg');"></div>
                    <div class="category-content">
                        <h3>Grecia</h3>
                        <p>Plaje nesfârșite, insule pitorești, mitologie, cultură, gastronomie aleasă</p>
                    </div>
                </div>
                <div class="category-card">
                    <div class="category-img" style="background-image: url('alte_poze/uae.jpg');"></div>
                    <div class="category-content">
                        <h3>Emiratele Arabe Unite</h3>
                        <p>Lux, modernitate, cultură, shopping, aventură, relaxare</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="about">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2 class="section-title">Despre noi</h2>
                    <p>La Carpathia Travel oferim experiențe turistice atent planificate, care pun în valoare frumusețea și diversitatea destinațiilor noastre.</p>
                    <p>Fiecare pachet de călătorie este gândit pentru confort, siguranță și experiențe autentice, astfel încât fiecare vacanță să fie memorabilă.</p>
                    <p>Punem la dispoziție o gamă variată de circuite și excursii, potrivite pentru toate tipurile de călători, de la aventuri în natură la city-break-uri sau vacanțe de relaxare.</p>

                    <a href="despre.php" class="btn">Află mai multe</a>
                </div>
                <div class="about-image" style="background-image: url('alte_poze/turism.jpg');"></div>
            </div>
        </div>
    </section>

    <script>
        document.querySelectorAll('.add-to-cart-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const productId = formData.get('id_produs');
                
                fetch('adauga_cos.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id_produs=' + productId
                })
                .then(response => response.text())
                .then(data => {
                    const trimmedData = data.trim();
                    
                    if (trimmedData === 'success') {
                        alert('Produsul a fost adăugat în coș!');
                        location.reload();
                    } else if (trimmedData === 'login_required') {
                        if (confirm('Trebuie să fii autentificat pentru a adăuga produse în coș! Dorești să te autentifici?')) {
                            window.location.href = 'login.php';
                        }
                    } else {
                        alert('Eroare la adăugarea în coș: ' + trimmedData);
                    }
                })
                .catch(error => {
                    console.error('Eroare:', error);
                    alert('Eroare la adăugarea în coș!');
                });
            });
        });
        
        document.querySelector('.newsletter-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            alert(`Vă mulțumim pentru abonare! Adresa ${email} a fost înregistrată.`);
            this.reset();
        });
    </script>
</body>
</html>
<?php require 'footer.php' ?>

<?php if(isset($conn)) $conn->close(); ?>
