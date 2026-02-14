<?php
require 'header.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "Toate câmpurile marcate cu * sunt obligatorii!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresa de email nu este validă!";
    } else {
        $to = "carpathiatravel@gmail.com";
        
        $email_subject = "Mesaj site Carpathia Travel: " . $subject;
        
        $email_body = "Nume: $name\n";
        $email_body .= "Email: $email\n";
        $email_body .= "Telefon: $phone\n";
        $email_body .= "Subiect: $subject\n\n";
        $email_body .= "Mesaj:\n$message\n\n";
        $email_body .= "Data: " . date('d-m-Y H:i:s');
        
        $headers = "From: $email\r\n";
        $headers .= "Reply-To: $email\r\n";
        
        if (mail($to, $email_subject, $email_body, $headers)) {
            $success = "Mesajul a fost trimis! Verifică și spam/junk folder.";
        } else {
            $error = "Eroare la trimitere. Încearcă din nou.";
        }
    }
}
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
        .error-message {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 5px;
            display: none;
        }
        .field-error {
            border-color: #e74c3c !important;
        }
        .field-success {
            border-color: #27ae60 !important;
        }
        .form-success {
            color: #27ae60;
            background: #e8f6ef;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
        .form-error {
            color: #e74c3c;
            background: #fde8e8;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
        .character-count {
            font-size: 12px;
            color: #666;
            text-align: right;
            margin-top: 5px;
        }
        .character-count.warning {
            color: #f39c12;
        }
        .character-count.error {
            color: #e74c3c;
        }
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
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-text">
                            <h3>Adresă</h3>
                            <p>Strada Acvilei, nr. 19<br>Județ Ilfov, comuna Chiajna, sat Roșu<br>România</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-text">
                            <h3>Telefon</h3>
                            <p>0765 323 922<br>Luni - Duminică: 08:00 - 18:00</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-text">
                            <h3>Email</h3>
                            <p>carpathia.travel@gmail.com</p>
                        </div>
                    </div>
                </div>

                <div class="message-form">
                    <h3>Trimiteți un mesaj</h3>
                    <form id="contactForm" method="POST" onsubmit="return valideazaContactForm()">
                        <div class="message-field">
                            <label for="name">Nume complet *</label>
                            <input type="text" id="name" name="name" required
                                   oninvalid="this.setCustomValidity('Vă rugăm să introduceți numele complet')"
                                   oninput="valideazaNume(); this.setCustomValidity('')"
                                   title="Numele complet este obligatoriu"
                                   placeholder="Introduceți numele complet">
                            <div id="name-error" class="error-message"></div>
                        </div>
                        
                        <div class="message-field">
                            <label for="email">Adresă email *</label>
                            <input type="email" id="email" name="email" required
                                   oninvalid="this.setCustomValidity('Introduceți o adresă de email validă')"
                                   oninput="valideazaEmail(); this.setCustomValidity('')"
                                   title="Adresa de email este obligatorie"
                                   placeholder="exemplu@domeniu.ro"
                                   pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$">
                            <div id="email-error" class="error-message"></div>
                        </div>
                        
                        <div class="message-field">
                            <label for="phone">Telefon</label>
                            <input type="tel" id="phone" name="phone"
                                   oninput="valideazaTelefon()"
                                   title="Introduceți un număr de telefon valid (opțional)"
                                   placeholder="07xx xxx xxx">
                            <div id="phone-error" class="error-message"></div>
                        </div>
                        
                        <div class="message-field">
                            <label for="subject">Subiect *</label>
                            <input type="text" id="subject" name="subject" required
                                   oninvalid="this.setCustomValidity('Introduceți subiectul mesajului')"
                                   oninput="valideazaSubiect(); this.setCustomValidity('')"
                                   title="Subiectul mesajului este obligatoriu"
                                   placeholder="Despre ce doriți să vorbiți?">
                            <div id="subject-error" class="error-message"></div>
                        </div>
                        
                        <div class="message-field">
                            <label for="message">Mesaj *</label>
                            <textarea id="message" name="message" required
                                      oninvalid="this.setCustomValidity('Introduceți mesajul dvs.')"
                                      oninput="valideazaMesaj(); this.setCustomValidity('')"
                                      title="Mesajul este obligatoriu"
                                      placeholder="Scrieți mesajul dvs. aici..."
                                      rows="6"
                                      maxlength="1000"></textarea>
                            <div id="message-count" class="character-count">0/1000 caractere</div>
                            <div id="message-error" class="error-message"></div>
                        </div>
                        
                        <input type="hidden" name="contact_submit" value="1">
                        <button type="submit" class="btn">Trimite mesaj</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script>
        function valideazaNume() {
            const nume = document.getElementById('name').value.trim();
            const errorElement = document.getElementById('name-error');
            const inputElement = document.getElementById('name');
            
            if (nume.length === 0) {
                errorElement.textContent = 'Numele este obligatoriu';
                errorElement.style.display = 'block';
                inputElement.classList.add('field-error');
                inputElement.classList.remove('field-success');
                return false;
            }
            
            if (nume.length < 2) {
                errorElement.textContent = 'Numele trebuie să aibă minim 2 caractere';
                errorElement.style.display = 'block';
                inputElement.classList.add('field-error');
                inputElement.classList.remove('field-success');
                return false;
            }
            
            errorElement.style.display = 'none';
            inputElement.classList.remove('field-error');
            inputElement.classList.add('field-success');
            return true;
        }
        
        function valideazaEmail() {
            const email = document.getElementById('email').value.trim();
            const errorElement = document.getElementById('email-error');
            const inputElement = document.getElementById('email');
            const emailPattern = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i;
            
            if (email.length === 0) {
                errorElement.textContent = 'Email-ul este obligatoriu';
                errorElement.style.display = 'block';
                inputElement.classList.add('field-error');
                inputElement.classList.remove('field-success');
                return false;
            }
            
            if (!emailPattern.test(email)) {
                errorElement.textContent = 'Introduceți o adresă de email validă';
                errorElement.style.display = 'block';
                inputElement.classList.add('field-error');
                inputElement.classList.remove('field-success');
                return false;
            }
            
            errorElement.style.display = 'none';
            inputElement.classList.remove('field-error');
            inputElement.classList.add('field-success');
            return true;
        }
        
        function valideazaTelefon() {
            const telefon = document.getElementById('phone').value.trim();
            const errorElement = document.getElementById('phone-error');
            const inputElement = document.getElementById('phone');
            
            if (telefon.length === 0) {
                errorElement.style.display = 'none';
                inputElement.classList.remove('field-error');
                inputElement.classList.remove('field-success');
                return true;
            }
            
            const telefonPattern = /^07[0-9]{8}$/;
            const telefonFaraSpatii = telefon.replace(/\s/g, '');
            
            if (!telefonPattern.test(telefonFaraSpatii)) {
                errorElement.textContent = 'Introduceți un număr de telefon românesc valid (07xx xxx xxx)';
                errorElement.style.display = 'block';
                inputElement.classList.add('field-error');
                inputElement.classList.remove('field-success');
                return false;
            }
            
            errorElement.style.display = 'none';
            inputElement.classList.remove('field-error');
            inputElement.classList.add('field-success');
            return true;
        }
        
        function valideazaSubiect() {
            const subiect = document.getElementById('subject').value.trim();
            const errorElement = document.getElementById('subject-error');
            const inputElement = document.getElementById('subject');
            
            if (subiect.length === 0) {
                errorElement.textContent = 'Subiectul este obligatoriu';
                errorElement.style.display = 'block';
                inputElement.classList.add('field-error');
                inputElement.classList.remove('field-success');
                return false;
            }
            
            errorElement.style.display = 'none';
            inputElement.classList.remove('field-error');
            inputElement.classList.add('field-success');
            return true;
        }
        
        function valideazaMesaj() {
            const mesaj = document.getElementById('message').value.trim();
            const countElement = document.getElementById('message-count');
            const textareaElement = document.getElementById('message');
            
            const count = mesaj.length;
            countElement.textContent = `${count}/1000 caractere`;
            
            if (count > 900) {
                countElement.classList.add('warning');
                countElement.classList.remove('error');
            } else if (count === 1000) {
                countElement.classList.add('error');
                countElement.classList.remove('warning');
            } else {
                countElement.classList.remove('warning', 'error');
            }
            
            if (mesaj.length === 0) {
                errorElement.textContent = 'Mesajul este obligatoriu';
                errorElement.style.display = 'block';
                textareaElement.classList.add('field-error');
                textareaElement.classList.remove('field-success');
                return false;
            }
            
            if (mesaj.length < 10) {
                errorElement.textContent = 'Mesajul trebuie să aibă minim 10 caractere';
                errorElement.style.display = 'block';
                textareaElement.classList.add('field-error');
                textareaElement.classList.remove('field-success');
                return false;
            }
            
            errorElement.style.display = 'none';
            textareaElement.classList.remove('field-error');
            textareaElement.classList.add('field-success');
            return true;
        }
        
        function valideazaContactForm() {
            let isValid = true;
            
            if (!valideazaNume()) isValid = false;
            if (!valideazaEmail()) isValid = false;
            if (!valideazaTelefon()) isValid = false;
            if (!valideazaSubiect()) isValid = false;
            if (!valideazaMesaj()) isValid = false;
            
            if (isValid) {
                alert('Mesajul dvs. a fost trimis cu succes! Vă vom contacta în cel mai scurt timp posibil.');
                return true;
            } else {
                alert('Vă rugăm să completați corect toate câmpurile obligatorii!');
                return false;
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('name').addEventListener('blur', valideazaNume);
            document.getElementById('email').addEventListener('blur', valideazaEmail);
            document.getElementById('phone').addEventListener('blur', valideazaTelefon);
            document.getElementById('subject').addEventListener('blur', valideazaSubiect);
            document.getElementById('message').addEventListener('input', valideazaMesaj);
            
            const inputs = document.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.style.borderColor = '#4a90e2';
                });
                
                input.addEventListener('blur', function() {
                    this.style.borderColor = '';
                });
            });
            
            valideazaMesaj();
        });
        
        document.getElementById('phone').addEventListener('keydown', function(e) {
            if ([46, 8, 9, 27, 13, 110, 190].indexOf(e.keyCode) !== -1 ||
                (e.keyCode === 65 && e.ctrlKey === true) ||
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true) ||
                (e.keyCode >= 35 && e.keyCode <= 39)) {
                return;
            }
            
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>

<?php require 'footer.php' ?>