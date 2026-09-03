<?php
require_once 'config.php';
require_once 'functions.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['user_role'] ?? 'client';
$userRoleLabel = [
    'client' => 'Client',
    'agent' => 'Agent',
    'admin' => 'Admin'
][$userRole] ?? 'Client';

$searchTerm = $_GET['search'] ?? '';

/* SEO: fiecare pagină poate seta $pageTitle / $pageDescription înainte de
   require 'header.php'; dacă nu setează, se folosesc valori implicite. */
$pageTitle       = $pageTitle ?? 'Carpathia Travel - Pachete turistice și vacanțe';
$pageDescription = $pageDescription ?? 'Carpathia Travel - agenție de turism online. Descoperă pachete turistice, city break-uri, circuite și vacanțe la prețuri accesibile.';
$canonicalUrl    = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://')
                    . ($_SERVER['HTTP_HOST'] ?? 'carpathiatravel.free.nf')
                    . strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$noIndex         = $noIndex ?? false;
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo esc($pageTitle); ?></title>
    <meta name="description" content="<?php echo esc($pageDescription); ?>">
    <meta name="keywords" content="agentie de turism, pachete turistice, vacante, city break, circuite turistice, litoral, munte, Carpathia Travel">
    <meta name="robots" content="<?php echo $noIndex ? 'noindex, nofollow' : 'index, follow'; ?>">
    <link rel="canonical" href="<?php echo esc($canonicalUrl); ?>">

    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo esc($pageTitle); ?>">
    <meta property="og:description" content="<?php echo esc($pageDescription); ?>">
    <meta property="og:url" content="<?php echo esc($canonicalUrl); ?>">
    <meta property="og:image" content="<?php echo esc((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? '') . '/alte_poze/carpathia_travel_logo.png'); ?>">
    <meta property="og:locale" content="ro_RO">
    <meta name="twitter:card" content="summary_large_image">

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
                    <span>Bun venit, <?php echo htmlspecialchars($_SESSION['user']); ?> (<?php echo esc($userRoleLabel); ?>)</span>
                    <span class="separator">|</span>
                    <?php if(($userRole ?? 'client') !== 'client'): ?>
                        <a href="admin_panel.php"><i class="fas fa-user-shield"></i> Panou</a>
                        <span class="separator">|</span>
                    <?php endif; ?>
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
                           value="<?php echo esc($searchTerm); ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <a href="cos.php" class="cart-icon">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-count">
                    <?php echo isset($_SESSION['user_id']) ? getCartCount() : '0'; ?>
                </span>
            </a>
        </div>
    </div>
</header>
