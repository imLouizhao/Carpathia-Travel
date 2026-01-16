<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nume = trim($_POST['nume']);
    $email = trim($_POST['email']);
    $parola = $_POST['parola'];
    $confirma_parola = $_POST['confirma_parola'];

    if (empty($nume) || empty($email) || empty($parola) || empty($confirma_parola)) {
        $error = "Toate câmpurile sunt obligatorii!";
    } elseif ($parola !== $confirma_parola) {
        $error = "Parolele nu coincid!";
    } elseif (strlen($parola) < 6) {
        $error = "Parola trebuie să aibă minim 6 caractere!";
    } else {
        $stmt = $conn->prepare("SELECT id FROM utilizatori WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Există deja un cont cu acest email!";
        } else {
            $parolaHash = password_hash($parola, PASSWORD_BCRYPT);

            $stmt = $conn->prepare("INSERT INTO utilizatori (nume, email, parola) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nume, $email, $parolaHash);

            if ($stmt->execute()) {
                $_SESSION['user'] = $nume;
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['email'] = $email;
                header('Location: index.php');
                exit;
            } else {
                $error = "Eroare la crearea contului! " . $stmt->error; 
            }
        }
        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Înregistrare - Luna Pearl</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .password-strength {
            margin-top: 5px;
            font-size: 14px;
        }
        .strength-weak { color: #e74c3c; }
        .strength-medium { color: #f39c12; }
        .strength-strong { color: #27ae60; }
        .error { color: #e74c3c; background: #fde8e8; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .success { color: #27ae60; background: #e8f6ef; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <?php 
    require 'header.php'; 
    ?>
    <div class="container">
        <div class="auth-container">
            <h2 class="auth-title">Înregistrare</h2>
            
            <?php if($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" id="registerForm" onsubmit="return valideazaFormular()">
                <div class="form-group">
                    <label for="nume">Nume complet:</label>
                    <input type="text" id="nume" name="nume" required
                           oninvalid="this.setCustomValidity('Vă rugăm să introduceți numele complet')"
                           oninput="this.setCustomValidity('')"
                           title="Numele complet este obligatoriu"
                           placeholder="Introduceți numele complet">
                    <div id="nume-error" class="error-message" style="color:#e74c3c; font-size:14px; display:none;"></div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required
                           oninvalid="this.setCustomValidity('Introduceți o adresă de email validă')"
                           oninput="this.setCustomValidity('')"
                           title="Adresa de email este obligatorie"
                           placeholder="exemplu@domeniu.ro"
                           pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$">
                    <div id="email-error" class="error-message" style="color:#e74c3c; font-size:14px; display:none;"></div>
                </div>
                
                <div class="form-group">
                    <label for="parola">Parola (minim 6 caractere):</label>
                    <input type="password" id="parola" name="parola" required
                           minlength="6"
                           oninvalid="this.setCustomValidity('Parola trebuie să aibă minim 6 caractere')"
                           oninput="valideazaParola(); this.setCustomValidity('')"
                           title="Parola trebuie să conțină minim 6 caractere"
                           placeholder="Minim 6 caractere">
                    <div id="parola-strength" class="password-strength"></div>
                    <div id="parola-error" class="error-message" style="color:#e74c3c; font-size:14px; display:none;"></div>
                </div>
                
                <div class="form-group">
                    <label for="confirma_parola">Confirmă parola:</label>
                    <input type="password" id="confirma_parola" name="confirma_parola" required
                           oninvalid="this.setCustomValidity('Confirmați parola introdusă')"
                           oninput="valideazaConfirmareParola(); this.setCustomValidity('')"
                           title="Introduceți din nou parola pentru confirmare"
                           placeholder="Reintroduceți parola">
                    <div id="confirma-error" class="error-message" style="color:#e74c3c; font-size:14px; display:none;"></div>
                </div>
                
                <button type="submit" class="btn">Înregistrare</button>
            </form>
            
            <div class="auth-links">
                <p>Ai deja cont? <a href="login.php">Autentifică-te aici</a></p>
            </div>
        </div>
    </div>

    <script>
        function valideazaParola() {
            const parola = document.getElementById('parola').value;
            const strengthElement = document.getElementById('parola-strength');
            const errorElement = document.getElementById('parola-error');
            
            if (parola.length === 0) {
                strengthElement.textContent = '';
                errorElement.style.display = 'none';
                return;
            }
            
            if (parola.length < 6) {
                strengthElement.textContent = 'Parolă prea scurtă';
                strengthElement.className = 'password-strength strength-weak';
                errorElement.textContent = 'Parola trebuie să aibă minim 6 caractere';
                errorElement.style.display = 'block';
                return false;
            }
            
            let strength = 'slabă';
            let strengthClass = 'strength-weak';
            
            if (parola.length >= 8 && /[A-Z]/.test(parola) && /[0-9]/.test(parola)) {
                strength = 'puternică';
                strengthClass = 'strength-strong';
            } else if (parola.length >= 6) {
                strength = 'medie';
                strengthClass = 'strength-medium';
            }
            
            strengthElement.textContent = `Putere parolă: ${strength}`;
            strengthElement.className = `password-strength ${strengthClass}`;
            errorElement.style.display = 'none';
            
            valideazaConfirmareParola();
            
            return true;
        }
        
        function valideazaConfirmareParola() {
            const parola = document.getElementById('parola').value;
            const confirma = document.getElementById('confirma_parola').value;
            const errorElement = document.getElementById('confirma-error');
            
            if (confirma.length === 0) {
                errorElement.style.display = 'none';
                return;
            }
            
            if (parola !== confirma) {
                errorElement.textContent = 'Parolele nu coincid';
                errorElement.style.display = 'block';
                return false;
            }
            
            errorElement.style.display = 'none';
            return true;
        }
        
        function valideazaEmail() {
            const email = document.getElementById('email').value;
            const errorElement = document.getElementById('email-error');
            const emailPattern = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i;
            
            if (email.length === 0) {
                errorElement.style.display = 'none';
                return;
            }
            
            if (!emailPattern.test(email)) {
                errorElement.textContent = 'Introduceți o adresă de email validă';
                errorElement.style.display = 'block';
                return false;
            }
            
            errorElement.style.display = 'none';
            return true;
        }
        
        function valideazaNume() {
            const nume = document.getElementById('nume').value.trim();
            const errorElement = document.getElementById('nume-error');
            
            if (nume.length === 0) {
                errorElement.style.display = 'none';
                return;
            }
            
            if (nume.length < 2) {
                errorElement.textContent = 'Numele trebuie să aibă minim 2 caractere';
                errorElement.style.display = 'block';
                return false;
            }
            
            errorElement.style.display = 'none';
            return true;
        }
        
        function valideazaFormular() {
            let isValid = true;
            
            if (!valideazaNume()) isValid = false;
            if (!valideazaEmail()) isValid = false;
            if (!valideazaParola()) isValid = false;
            if (!valideazaConfirmareParola()) isValid = false;
            
            const inputs = document.querySelectorAll('input[required]');
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.style.borderColor = '#e74c3c';
                    isValid = false;
                } else {
                    input.style.borderColor = '#ccc';
                }
            });
            
            if (!isValid) {
                alert('Vă rugăm să completați corect toate câmpurile obligatorii!');
            }
            
            return isValid;
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('nume').addEventListener('blur', valideazaNume);
            document.getElementById('email').addEventListener('blur', valideazaEmail);
            document.getElementById('parola').addEventListener('input', valideazaParola);
            document.getElementById('confirma_parola').addEventListener('input', valideazaConfirmareParola);
            
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.style.borderColor = '#4a90e2';
                });
            });
        });
    </script>
</body>
</html>

<?php require 'footer.php' ?>