<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'config.php';
require 'functions.php';

$csrf_token = csrfEnsureToken();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $subject = trim((string)($_POST['subject'] ?? ''));
    $message = trim((string)($_POST['message'] ?? ''));
    $captchaRaspuns = $_POST['captcha_raspuns'] ?? null;
    $honeypot = $_POST['website'] ?? null;

    if (!csrfValidate($_POST['csrf_token'] ?? null)) {
        $error = "Token de securitate invalid. Reîncarcă pagina și încearcă din nou.";
    } elseif (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "Toate câmpurile marcate cu * sunt obligatorii!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresa de email nu este validă!";
    } elseif (!captchaVerify($captchaRaspuns, $honeypot)) {
        $error = "Răspunsul la întrebarea de verificare este incorect. Încearcă din nou.";
    } else {
        if (trimiteEmailContact($name, $email, $phone, $subject, $message)) {
            $success = "Mesajul a fost trimis! Verifică și spam/junk folder.";
        } else {
            $error = "Eroare la trimiterea mesajului. Te rugăm să încerci din nou mai târziu.";
        }
    }
}

$captcha = captchaGenerate();

$pageTitle = 'Contact - Carpathia Travel';
$pageDescription = 'Contactează echipa Carpathia Travel pentru întrebări despre pachetele turistice sau rezervările tale.';

require 'header.php';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - Carpathia Travel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .error-message { color: #e74c3c; font-size: 14px; margin-top: 5px; display: none; }
        .field-error { border-color: #e74c3c !important; }
        .field-success { border-color: #27ae60 !important; }
        .form-success { color: #27ae60; background: #e8f6ef; padding: 15px; border-radius: 4px; margin-bottom: 20px; text-align: center; }
        .form-error { color: #e74c3c; background: #fde8e8; padding: 15px; border-radius: 4px; margin-bottom: 20px; text-align: center; }
        .character-count { font-size: 12px; color: #666; text-align: right; margin-top: 5px; }
        .character-count.warning { color: #f39c12; }
        .character-count.error { color: #e74c3c; }
    </style>
</head>
<body>
    <section class="page-hero">
        <div class="container">
            <h1>Contact</h1>
            <p>Suntem aici pentru a vă ajuta și a răspunde la orice întrebări aveți</p>
        </div>
    </section>

    <section class="contact-section">
        <div class="container">
            <h2 class="section-title">Luați legătura cu noi</h2>
            
            <?php if($success): ?>
                <div class="form-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="form-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="contact-content">
                <div class="contact-details">
                    <h3>Informații de contact</h3>
                    <p>Nu ezitați să ne contactați folosind informațiile de mai jos sau formularul de contact. Echipa noastră vă va răspunde în cel mai scurt timp posibil.</p>
                    
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div class="contact-text">
                            <h3>Adresă</h3>
                            <p>Strada Acvilei, nr. 19<br>Județ Ilfov, comuna Chiajna, sat Roșu<br>România</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fas fa-phone"></i></div>
                        <div class="contact-text">
                            <h3>Telefon</h3>
                            <p>0765 323 922<br>Luni - Duminică: 08:00 - 18:00</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                        <div class="contact-text">
                            <h3>Email</h3>
                            <p>carpathia.travel@gmail.com</p>
                        </div>
                    </div>
                </div>

                <div class="message-form">
                    <h3>Trimiteți un mesaj</h3>
                    <form id="contactForm" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo esc($csrf_token); ?>">

                        <div style="position:absolute; left:-9999px; top:-9999px;" aria-hidden="true">
                            <label for="website">Nu completați acest câmp</label>
                            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="message-field">
                            <label for="name">Nume complet *</label>
                            <input type="text" id="name" name="name" required placeholder="Introduceți numele complet">
                            <div id="name-error" class="error-message"></div>
                        </div>
                        
                        <div class="message-field">
                            <label for="email">Adresă email *</label>
                            <input type="email" id="email" name="email" required placeholder="exemplu@domeniu.ro">
                            <div id="email-error" class="error-message"></div>
                        </div>
                        
                        <div class="message-field">
                            <label for="phone">Telefon</label>
                            <input type="tel" id="phone" name="phone" placeholder="07xx xxx xxx">
                            <div id="phone-error" class="error-message"></div>
                        </div>
                        
                        <div class="message-field">
                            <label for="subject">Subiect *</label>
                            <input type="text" id="subject" name="subject" required placeholder="Despre ce doriți să vorbiți?">
                            <div id="subject-error" class="error-message"></div>
                        </div>
                        
                        <div class="message-field">
                            <label for="message">Mesaj *</label>
                            <textarea id="message" name="message" required placeholder="Scrieți mesajul dvs. aici..." rows="6" maxlength="1000"></textarea>
                            <div id="message-count" class="character-count">0/1000 caractere</div>
                            <div id="message-error" class="error-message"></div>
                        </div>
                        
                        <div class="message-field">
                            <label for="captcha_raspuns">Verificare anti-spam: <?php echo esc($captcha['question']); ?> *</label>
                            <input type="text" id="captcha_raspuns" name="captcha_raspuns" required inputmode="numeric" autocomplete="off" placeholder="Scrie rezultatul">
                        </div>

                        <input type="hidden" name="contact_submit" value="1">
                        <button type="submit" class="btn">Trimite mesaj</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script>
        function valideazaMesaj() {
            const mesaj = document.getElementById('message').value.trim();
            const countElement = document.getElementById('message-count');
            const count = mesaj.length;
            countElement.textContent = `${count}/1000 caractere`;
            
            if (count > 900) {
                countElement.classList.add('warning');
            } else {
                countElement.classList.remove('warning');
            }
        }
        document.getElementById('message').addEventListener('input', valideazaMesaj);
    </script>
</body>
</html>
<?php require 'footer.php'; ?>
