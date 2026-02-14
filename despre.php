<?php
require 'header.php';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Despre noi - Carpathia Travel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <section class="page-hero">
        <div class="container">
            <h1>Despre noi</h1>
            <p>Află mai multe despre agenția Carpathia Travel și serviciile oferite</p>
        </div>
    </section>

    <section class="about-section">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2 class="section-title">Cine suntem</h2>
                    <p>Carpathia Travel este o agenție de turism care oferă pachete turistice atent selecționate pentru cei care doresc să descopere destinații din România și din străinătate. Activitatea noastră se bazează pe organizarea de vacanțe accesibile, bine structurate și adaptate nevoilor fiecărui client.</p>
                    <p>Agenția a fost creată din dorința de a simplifica procesul de planificare a unei călătorii, punând la dispoziție informații clare despre destinații, durată, preț și disponibilitatea locurilor.</p>
                    <p>Prin platforma Carpathia Travel, utilizatorii pot consulta oferta de pachete turistice, pot verifica detaliile fiecărei vacanțe și pot efectua rezervări într-un mod rapid și sigur.</p>
                    <a href="produse.php" class="btn">Vezi pachetele turistice</a>
                </div>
                <div class="about-image" style="background-image: url('alte_poze/poza_despre_1.jpg');"></div>
            </div>
            
            <div class="about-content">
                <div class="about-image" style="background-image: url('alte_poze/poza_despre_2.jpg');"></div>
                <div class="about-text">
                    <h2 class="section-title">Misiunea noastră</h2>
                    <p>Misiunea Carpathia Travel este de a oferi clienților experiențe turistice bine organizate, transparente și ușor de accesat. Ne concentrăm pe calitatea serviciilor și pe furnizarea unor informații corecte și actualizate.</p>
                    <p>Ne dorim ca fiecare utilizator să poată alege pachetul potrivit în funcție de preferințe, buget și perioadă, beneficiind de suport pe tot parcursul procesului de rezervare.</p>
                    <p>Rezervările realizate prin platformă sunt analizate și confirmate de un agent, pentru a asigura corectitudinea datelor și disponibilitatea serviciilor incluse.</p>
                    <a href="contact.php" class="btn">Contactează-ne</a>
                </div>
            </div>
        </div>
    </section>

    <section class="values">
        <div class="container">
            <h2 class="section-title">Valorile noastre</h2>
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <h3>Transparență</h3>
                    <p>Oferim informații clare despre prețuri, durată, destinații și serviciile incluse în fiecare pachet turistic.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <h3>Profesionalism</h3>
                    <p>Gestionăm fiecare rezervare cu atenție și oferim suport clienților înainte și după efectuarea acesteia.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-globe-europe"></i>
                    </div>
                    <h3>Diversitate</h3>
                    <p>Punem la dispoziție pachete turistice variate, adaptate diferitelor tipuri de călători și destinații.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3>Încredere</h3>
                    <p>Ne bazăm pe colaborări sigure și pe respectarea angajamentelor față de clienții noștri.</p>
                </div>
            </div>
        </div>
    </section>
</body>
</html>

<?php require 'footer.php' ?>
