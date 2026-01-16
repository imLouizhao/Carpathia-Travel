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
    $email = trim($_POST['email']);
    $parola = $_POST['parola'];

    if (empty($email) || empty($parola)) {
        $error = "Toate câmpurile sunt obligatorii!";
    } else {
        $stmt = $conn->prepare("SELECT id, nume, parola FROM utilizatori WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $nume, $parolaHash);
            $stmt->fetch();

            if (password_verify($parola, $parolaHash)) {
                $_SESSION['user'] = $nume;
                $_SESSION['user_id'] = $id;
                $_SESSION['email'] = $email;
                header('Location: index.php');
                exit;
            } else {
                $error = "Parolă incorectă!";
            }
        } else {
            $error = "Nu există cont cu acest email!";
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
    <title>Autentificare - Luna Pearl</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php 
    require 'header.php'; 
    ?>
    
    <div class="container">
        <div class="auth-container">
            <h2 class="auth-title">Autentificare</h2>
            
            <?php if($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required 
                        oninvalid="this.setCustomValidity('Introduceți o adresă de email validă')"
                        oninput="this.setCustomValidity('')"
                        title="Adresa de email este obligatorie">
                </div>
                
                <div class="form-group">
                    <label for="parola">Parola:</label>
                    <input type="password" id="parola" name="parola" required
                        oninvalid="this.setCustomValidity('Parola este obligatorie')"
                        oninput="this.setCustomValidity('')"
                        title="Introduceți parola contului">
                </div>
                
                <button type="submit" class="btn">Autentificare</button>
            </form>
            
            <div class="auth-links">
                <p>Nu ai cont? <a href="register.php">Înregistrează-te aici</a></p>
            </div>
        </div>
    </div>
    
    <?php require 'footer.php'; ?>
</body>
</html>