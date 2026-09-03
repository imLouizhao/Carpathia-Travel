<?php
require 'config.php';

header('Content-Type: application/xml; charset=UTF-8');

$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://')
    . ($_SERVER['HTTP_HOST'] ?? 'carpathiatravel.free.nf');

$staticPages = [
    ['loc' => '/index.php', 'priority' => '1.0'],
    ['loc' => '/produse.php', 'priority' => '0.9'],
    ['loc' => '/despre.php', 'priority' => '0.6'],
    ['loc' => '/contact.php', 'priority' => '0.6'],
    ['loc' => '/termeni_si_conditii.php', 'priority' => '0.3'],
    ['loc' => '/politica_de_confidentialitate.php', 'priority' => '0.3'],
    ['loc' => '/conditii_asigurare_calatorie.php', 'priority' => '0.3'],
];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

foreach ($staticPages as $p) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($baseUrl . $p['loc'], ENT_XML1) . "</loc>\n";
    echo "    <priority>" . $p['priority'] . "</priority>\n";
    echo "  </url>\n";
}

$res = $conn->query("SELECT id FROM produse ORDER BY id DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $id = (int)$row['id'];
        echo "  <url>\n";
        echo "    <loc>" . htmlspecialchars($baseUrl . '/produs.php?id=' . $id, ENT_XML1) . "</loc>\n";
        echo "    <priority>0.7</priority>\n";
        echo "  </url>\n";
    }
}

echo '</urlset>';

$conn->close();
