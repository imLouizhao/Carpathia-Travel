<?php
require_once 'config.php';
require_once 'functions.php';

$cookiesAcceptat = false;

if (isset($_COOKIE['cookies_acceptat'])) {
    $cookiesAcceptat = ($_COOKIE['cookies_acceptat'] === 'true');
}

error_log("Cookies acceptat: " . ($cookiesAcceptat ? 'DA' : 'NU'));
error_log("Cookie valoare: " . ($_COOKIE['cookies_acceptat'] ?? 'NEDEFINIT'));
?>
<footer>
    <link rel="stylesheet" href="style.css">
    <div class="container">
        <div class="footer-content">
            <div class="footer-column">
                <h3>Link-uri utile</h3>
                <ul>
                    <li><a href="index.php">Acasă</a></li>
                    <li><a href="despre.php">Despre noi</a></li>
                    <li><a href="produse.php">Produse</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Informații</h3>
                <ul>
                    <li><a href="termeni_si_conditii.php">Termeni și condiții</a></li>
                    <li><a href="politica_de_confidentialitate.php">Politica de confidențialitate</a></li>
                    <li><a href="conditii_asigurare_calatorie.php">Condiții de asigurare de călătorie</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Contact</h3>
                <ul>
                    <li><i class="fas fa-map-marker-alt"></i> Strada Acvilei, nr 19, Ilfov, Comuna Chiajna, Sat Roșu</li>
                    <li><i class="fas fa-phone"></i> 0765 323 922</li>
                    <li><i class="fas fa-envelope"></i> carpathia.travel@gmail.com</li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="copyright">
                <p>&copy; 2026 CARPATHIA TRAVEL S.R.L. Toate drepturile rezervate.</p>
                <p class="copyright-desc">Folosirea conținutului acestui web site sau a unor părți din acesta fără înștiințarea și aprobarea CARPATHIA TRAVEL S.R.L.</p>
                <p class="copyright-desc">ca cesionar exclusiv al dreptului de autor, se sancționează conform legii.</p>
            </div>
            <div class="anpc-links">
                <a href="https://reclamatiisal.anpc.ro/" target="_blank" rel="nofollow noopener">
                    <img src="https://gomagcdn.ro/themes/fashion/gfx/sal.png" alt="SAL" title="SAL" width="250" height="50">
                </a>
                <a href="https://europa.eu/youreurope/business/dealing-with-customers/solving-disputes/alternative-dispute-resolution/index_ro.htm" target="_blank" rel="nofollow noopener">
                    <img src="https://gomagcdn.ro/themes/fashion/gfx/sol.png" alt="SOL" title="SOL" width="250" height="50">
                </a>
            </div>
        </div>
    </div>
</footer>

<script src="script.js"></script>

<?php if(!$cookiesAcceptat): ?>
<div id="cookies-banner" class="cookies-banner">
    <div class="cookies-content">
        <div class="cookies-text">
            <p>🏪 <strong>Acest site folosește cookies</strong> pentru funcționalități esențiale precum coșul de cumpărături și autentificarea. 
               <a href="politica_de_confidentialitate.php" target="_blank">Află mai multe în politica noastră de confidențialitate</a>
            </p>
        </div>
        <div class="cookies-buttons">
            <button onclick="acceptaCookies()" class="cookies-btn accept">Accept</button>
            <button onclick="respingeCookies()" class="cookies-btn reject">Respinge</button>
        </div>
    </div>
</div>
<?php endif; ?>

</body>
</html>
