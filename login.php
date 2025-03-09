<?php
// login.php - Pagina di login con selezione tramite immagini
session_start();
require_once 'config.php';

// Se l'utente è già loggato, redirect alla dashboard
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
} elseif (isBarbiere()) {
    header("Location: barbiere/dashboard.php");
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
    <style>
        .user-type-selection {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .type-option {
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            opacity: 0.6;
        }

        .type-option.selected {
            opacity: 1;
            transform: scale(1.05);
        }

        .type-option img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-bottom: 0.5rem;
            border: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .type-option.selected img {
            border-color: var(--primary-color);
            box-shadow: 0 0 10px rgba(0, 102, 204, 0.3);
        }

        .type-option h3 {
            font-size: 1.2rem;
            margin: 0;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="auth-form">
        <h2>Accedi a BarberBook</h2>

        <?php if($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="user-type-selection">
            <div class="type-option" data-type="utente">
                <img src="img/user-icon.svg" alt="Cliente">
                <h3>Cliente</h3>
            </div>
            <div class="type-option" data-type="barbiere">
                <img src="img/barber-icon.svg" alt="Barbiere">
                <h3>Barbiere</h3>
            </div>
        </div>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="login-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="tipo" id="tipo-input" value="utente">

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
<script>
    // Script per gestire la selezione del tipo di utente
    document.addEventListener('DOMContentLoaded', function() {
        const typeOptions = document.querySelectorAll('.type-option');
        const tipoInput = document.getElementById('tipo-input');

        // Imposta utente come default
        document.querySelector('.type-option[data-type="utente"]').classList.add('selected');

        typeOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Rimuovi la classe selected da tutte le opzioni
                typeOptions.forEach(el => el.classList.remove('selected'));

                // Aggiungi la classe selected all'opzione selezionata
                this.classList.add('selected');

                // Aggiorna il valore dell'input hidden
                tipoInput.value = this.getAttribute('data-type');
            });
        });
    });
</script>
</body>
</html>
