<?php
// login.php - Pagina di login
session_start();
require_once 'config.php';

// Se l'utente è già loggato, redirect alla dashboard
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['csrf_token']) && verifyCSRFToken($_POST['csrf_token'])) {
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        $tipo = sanitizeInput($_POST['tipo']); // 'utente' o 'barbiere'

        $conn = connectDB();

        if ($tipo === 'utente') {
            $stmt = $conn->prepare("SELECT id, nome, nickname, password, barbiere_default FROM utenti WHERE email = ?");
        } else {
            $stmt = $conn->prepare("SELECT id, nome, email, password FROM barbieri WHERE email = ?");
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                if ($tipo === 'utente') {
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['user_name'] = $row['nickname'];
                    $_SESSION['barbiere_default'] = $row['barbiere_default'];
                    header("Location: dashboard.php");
                } else {
                    $_SESSION['barbiere_id'] = $row['id'];
                    $_SESSION['barbiere_name'] = $row['nome'];
                    header("Location: barbiere/dashboard.php");
                }
                exit;
            } else {
                $error = "Password non valida.";
            }
        } else {
            $error = "Nessun account trovato con questa email.";
        }

        $stmt->close();
        $conn->close();
    } else {
        $error = "Token CSRF non valido.";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BarberBook</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <div class="auth-form">
        <h2>Accedi a BarberBook</h2>

        <?php if($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

            <div class="form-group">
                <label for="tipo">Tipo di account:</label>
                <select name="tipo" id="tipo" required>
                    <option value="utente">Cliente</option>
                    <option value="barbiere">Barbiere</option>
                </select>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>

            <div class="form-group">
                <button type="submit" class="btn-primary">Accedi</button>
            </div>

            <div class="form-links">
                <a href="register.php">Non hai un account? Registrati</a>
                <a href="reset_password.php">Password dimenticata?</a>
            </div>
        </form>
    </div>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>
