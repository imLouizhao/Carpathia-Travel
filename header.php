<?php
require 'config.php';
require 'functions.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']);
$searchTerm = $_GET['search'] ?? '';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Carpathia Travel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="alte_poze/carpathia_travel_logo.png">
</head>
<body>
<header>
    <div class="top-bar">
        <div class="top-bar-container">
            <div class="contact-info">
                <div class="contact-info-item">
                    <i class="fas fa-phone"></i>
                    <span>0765 323 922</span>
                </div>
                <span class="separator">|</span>
                <div class="contact-info-item">
                    <i class="fas fa-envelope"></i>
                    <span>carpathia.travel@gmail.com</span>
                </div>
            </div>
            <div class="auth-links">
                <?php if(isset($_SESSION['user'])): ?>
                    <span>Bun venit, <?php echo htmlspecialchars($_SESSION['user']); ?></span>
                    <span class="separator">|</span>
                    <a href="logout.php">Deconectare</a>
                <?php else: ?>
                    <a href="login.php"><i class="fas fa-sign-in-alt"></i> Autentificare</a>
                    <span class="separator">|</span>
                    <a href="register.php"><i class="fas fa-user-plus"></i> Înregistrare</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="container header-main">
        <a href="index.php" class="logo">
            <img src="alte_poze/carpathia_travel_logo.png" alt="Carpathia Travel" style="height: 150px;">
        </a>
        
        <nav style="font-size: 25px;">
            <ul>
                <li><a href="index.php">Acasă</a></li>
                <li><a href="produse.php">Produse</a></li>
                <li><a href="despre.php">Despre noi</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </nav>
        
        <div class="search-cart">
            <div class="search-box">
                <form method="GET" action="produse.php" id="searchForm">
                    <input type="text" name="search" placeholder="Caută produse..." 
                           value="<?php echo htmlspecialchars($searchTerm); ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <a href="cos.php" class="cart-icon">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-count">
                    <?php 
                    if(isset($_SESSION['user_id'])) {
                        echo getCartCount();
                    } else {
                        echo '0';
                    }
                    ?>
                </span>
            </a>
        </div>
    </div>
</header>
    
