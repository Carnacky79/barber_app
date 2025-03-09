<?php
// register.php - Pagina di registrazione utente
session_start();
require_once 'config.php';

// Se l'utente è già loggato, redirect alla dashboard
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['csrf_token']) && verifyCSRFToken($_POST['csrf_token'])) {
        $nome = sanitizeInput($_POST['nome']);
        $cognome = sanitizeInput($_POST['cognome'] ?? '');
        $nickname = sanitizeInput($_POST['nickname']);
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $telefono = sanitizeInput($_POST['telefono']);
        $citta = sanitizeInput($_POST['citta']);

        // Validazione
        if ($password !== $confirm_password) {
            $error = "Le password non coincidono.";
        } else {
            $conn = connectDB();

            // Verifica se l'email è già in uso
            $stmt = $conn->prepare("SELECT id FROM utenti WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = "Email già registrata.";
            } else {
                // Gestione caricamento foto
                $foto = NULL;
                if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                    $upload = uploadImage($_FILES['foto'], 'uploads/users/');
                    if ($upload['success']) {
                        $foto = $upload['filename'];
                    } else {
                        $error = $upload['message'];
                    }
                }

                if (empty($error)) {
                    // Hash della password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Trova barbieri nella stessa città
                    $stmt = $conn->prepare("SELECT id FROM barbieri WHERE citta = ? LIMIT 1");
                    $stmt->bind_param("s", $citta);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $barbiere_default = NULL;

                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $barbiere_default = $row['id'];
                    }

                    // Inserimento nuovo utente
                    $stmt = $conn->prepare("INSERT INTO utenti (nome, cognome, nickname, password, email, telefono, citta, foto, barbiere_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssssssi", $nome, $cognome, $nickname, $hashed_password, $email, $telefono, $citta, $foto, $barbiere_default);

                    if ($stmt->execute()) {
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

// Ottenere la lista delle città dove ci sono barbieri
$conn = connectDB();
$sql = "SELECT DISTINCT citta FROM barbieri ORDER BY citta";
$result = $conn->query($sql);
$citta = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $citta[] = $row['citta'];
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione - BarberBook</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <div class="auth-form">
        <h2>Registrati a BarberBook</h2>

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
                    <label for="nome">Nome:</label>
                    <input type="text" name="nome" id="nome" required>
                </div>

                <div class="form-group">
                    <label for="cognome">Cognome (opzionale):</label>
                    <input type="text" name="cognome" id="cognome">
                </div>

                <div class="form-group">
                    <label for="nickname">Nickname:</label>
                    <input type="text" name="nickname" id="nickname" required>
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
                    <label for="confirm_password">Conferma Password:</label>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                </div>

                <div class="form-group">
                    <label for="telefono">Telefono:</label>
                    <input type="tel" name="telefono" id="telefono" required>
                </div>

                <div class="form-group">
                    <label for="citta">Città:</label>
                    <input type="text" name="citta" id="citta" required list="lista-citta">
                    <datalist id="lista-citta">
                        <?php foreach($citta as $c): ?>
                        <option value="<?php echo $c; ?>">
                            <?php endforeach; ?>
                    </datalist>
                </div>

                <div class="form-group">
                    <label for="foto">Foto (opzionale):</label>
                    <input type="file" name="foto" id="foto" accept="image/*">
                </div>

                <div class="form-group">
                    <button type="submit" class="btn-primary">Registrati</button>
                </div>

                <div class="form-links">
                    <a href="login.php">Hai già un account? Accedi</a>
                    <a href="register_barbiere.php">Sei un barbiere? Registra la tua attività</a>
                </div>
            </form>

        <?php endif; ?>
    </div>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>
