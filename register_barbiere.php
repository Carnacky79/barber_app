<?php
// register_barbiere.php - Pagina di registrazione per i barbieri
session_start();
require_once 'config.php';

// Se l'utente è già loggato come barbiere, redirect alla dashboard
if (isBarbiere()) {
    header("Location: barbiere/dashboard.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['csrf_token']) && verifyCSRFToken($_POST['csrf_token'])) {
        $nome = sanitizeInput($_POST['nome']);
        $indirizzo = sanitizeInput($_POST['indirizzo']);
        $citta = sanitizeInput($_POST['citta']);
        $cap = sanitizeInput($_POST['cap']);
        $telefono = sanitizeInput($_POST['telefono']);
        $email = sanitizeInput($_POST['email']);
        $sito_web = sanitizeInput($_POST['sito_web'] ?? '');
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validazione
        if ($password !== $confirm_password) {
            $error = "Le password non coincidono.";
        } else {
            $conn = connectDB();

            // Verifica se l'email è già in uso
            $stmt = $conn->prepare("SELECT id FROM barbieri WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = "Email già registrata.";
            } else {
                // Gestione caricamento logo
                $logo = NULL;
                if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
                    $upload = uploadImage($_FILES['logo'], 'uploads/barbieri/');
                    if ($upload['success']) {
                        $logo = $upload['filename'];
                    } else {
                        $error = $upload['message'];
                    }
                }

                if (empty($error)) {
                    // Hash della password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Inserimento nuovo barbiere
                    $stmt = $conn->prepare("INSERT INTO barbieri (nome, indirizzo, citta, cap, telefono, email, sito_web, logo, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssssssss", $nome, $indirizzo, $citta, $cap, $telefono, $email, $sito_web, $logo, $hashed_password);

                    if ($stmt->execute()) {
                        $barbiere_id = $conn->insert_id;

                        // Crea orari di apertura predefiniti (lun-sab, 9-19)
                        $giorni = ['lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato'];
                        $stmt = $conn->prepare("INSERT INTO orari_apertura (barbiere_id, giorno, aperto, ora_apertura, ora_chiusura) VALUES (?, ?, ?, ?, ?)");

                        foreach ($giorni as $giorno) {
                            $aperto = true;
                            $ora_apertura = '09:00:00';
                            $ora_chiusura = '19:00:00';
                            $stmt->bind_param("isiss", $barbiere_id, $giorno, $aperto, $ora_apertura, $ora_chiusura);
                            $stmt->execute();
                        }

                        // Domenica chiuso
                        $giorno = 'domenica';
                        $aperto = false;
                        $ora_apertura = NULL;
                        $ora_chiusura = NULL;
                        $stmt->bind_param("isiss", $barbiere_id, $giorno, $aperto, $ora_apertura, $ora_chiusura);
                        $stmt->execute();

                        $success = "Registrazione completata con successo! Ora puoi accedere.";
                    } else {
                        $error = "Errore durante la registrazione: " . $stmt->error;
                    }
                }
            }

            $stmt->close();
            $conn->close();
        }
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
    <title>Registrazione Barbiere - BarberBook</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <div class="auth-form">
        <h2>Registra il tuo Barbershop</h2>

        <?php if($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
            <div class="form-links">
                <a href="login.php" class="btn-primary">Vai al login</a>
            </div>
        <?php else: ?>

            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <div class="form-group">
                    <label for="nome">Nome dell'attività:</label>
                    <input type="text" name="nome" id="nome" required>
                </div>

                <div class="form-group">
                    <label for="indirizzo">Indirizzo:</label>
                    <input type="text" name="indirizzo" id="indirizzo" required>
                </div>

                <div class="form-group">
                    <label for="citta">Città:</label>
                    <input type="text" name="citta" id="citta" required>
                </div>

                <div class="form-group">
                    <label for="cap">CAP:</label>
                    <input type="text" name="cap" id="cap" required>
                </div>

                <div class="form-group">
                    <label for="telefono">Telefono:</label>
                    <input type="tel" name="telefono" id="telefono" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" required>
                </div>

                <div class="form-group">
                    <label for="sito_web">Sito Web (opzionale):</label>
                    <input type="url" name="sito_web" id="sito_web">
                </div>

                <div class="form-group">
                    <label for="logo">Logo (opzionale):</label>
                    <input type="file" name="logo" id="logo" accept="image/*">
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Conferma Password:</label>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn-primary">Registra la tua attività</button>
                </div>

                <div class="form-links">
                    <a href="login.php">Hai già un account? Accedi</a>
                </div>
            </form>

        <?php endif; ?>
    </div>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>
