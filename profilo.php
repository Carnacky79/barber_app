<?php
// profilo.php - Gestione profilo utente
session_start();
require_once 'config.php';

// Verifica che l'utente sia loggato
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$conn = connectDB();
$error = '';
$success = '';

// Ottieni i dati dell'utente
$stmt = $conn->prepare("SELECT * FROM utenti WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Gestione dell'aggiornamento del profilo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['csrf_token']) && verifyCSRFToken($_POST['csrf_token'])) {
    $nome = sanitizeInput($_POST['nome']);
    $cognome = sanitizeInput($_POST['cognome'] ?? '');
    $nickname = sanitizeInput($_POST['nickname']);
    $telefono = sanitizeInput($_POST['telefono']);
    $citta = sanitizeInput($_POST['citta']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validazione
    $errors = [];

    if (empty($nome)) {
        $errors[] = "Il nome è obbligatorio.";
    }

    if (empty($nickname)) {
        $errors[] = "Il nickname è obbligatorio.";
    }

    if (empty($telefono)) {
        $errors[] = "Il telefono è obbligatorio.";
    }

    if (empty($citta)) {
        $errors[] = "La città è obbligatoria.";
    }

    if (empty($email)) {
        $errors[] = "L'email è obbligatoria.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email non valida.";
    }

    // Verifica se l'email è già in uso da un altro utente
    if ($email !== $user['email']) {
        $stmt = $conn->prepare("SELECT id FROM utenti WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errors[] = "Email già in uso.";
        }
    }

    // Verifica password (solo se inserita)
    if (!empty($password)) {
        if (strlen($password) < 6) {
            $errors[] = "La password deve essere di almeno 6 caratteri.";
        }

        if ($password !== $confirm_password) {
            $errors[] = "Le password non coincidono.";
        }
    }

    // Gestione caricamento foto
    $foto = $user['foto'];
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $upload = uploadImage($_FILES['foto'], 'uploads/users/');
        if ($upload['success']) {
            $foto = $upload['filename'];
        } else {
            $errors[] = $upload['message'];
        }
    }

    if (empty($errors)) {
        // Prepara la query di aggiornamento
        if (!empty($password)) {
            // Aggiorna con nuova password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("
                UPDATE utenti 
                SET nome = ?, cognome = ?, nickname = ?, email = ?, 
                    telefono = ?, citta = ?, foto = ?, password = ?
                WHERE id = ?
            ");
            $stmt->bind_param("ssssssssi", $nome, $cognome, $nickname, $email, $telefono, $citta, $foto, $hashed_password, $_SESSION['user_id']);
        } else {
            // Aggiorna senza cambiare password
            $stmt = $conn->prepare("
                UPDATE utenti 
                SET nome = ?, cognome = ?, nickname = ?, email = ?, 
                    telefono = ?, citta = ?, foto = ?
                WHERE id = ?
            ");
            $stmt->bind_param("sssssssi", $nome, $cognome, $nickname, $email, $telefono, $citta, $foto, $_SESSION['user_id']);
        }

        if ($stmt->execute()) {
            // Aggiorna la sessione
            $_SESSION['user_name'] = $nickname;

            $success = "Profilo aggiornato con successo.";

            // Ricarica i dati dell'utente
            $stmt = $conn->prepare("SELECT * FROM utenti WHERE id = ?");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
        } else {
            $error = "Errore durante l'aggiornamento del profilo: " . $stmt->error;
        }
    } else {
        $error = implode("<br>", $errors);
    }
}

// Ottieni la lista delle città dove ci sono barbieri
$sql = "SELECT DISTINCT citta FROM barbieri ORDER BY citta";
$result = $conn->query($sql);
$citta_list = [];
while ($row = $result->fetch_assoc()) {
    $citta_list[] = $row['citta'];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilo - BarberBook</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="page-content">
        <h1>Il tuo Profilo</h1>

        <?php if($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="profile-form">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <div class="form-section">
                    <h2>Informazioni Personali</h2>

                    <div class="profile-photo">
                        <?php if($user['foto']): ?>
                            <img src="uploads/users/<?php echo $user['foto']; ?>" alt="<?php echo $user['nome']; ?>" class="user-photo">
                        <?php else: ?>
                            <div class="photo-placeholder">
                                <span><?php echo substr($user['nome'], 0, 1); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="foto">Cambia foto:</label>
                            <input type="file" name="foto" id="foto" accept="image/*">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nome">Nome:</label>
                        <input type="text" name="nome" id="nome" required value="<?php echo $user['nome']; ?>">
                    </div>

                    <div class="form-group">
                        <label for="cognome">Cognome (opzionale):</label>
                        <input type="text" name="cognome" id="cognome" value="<?php echo $user['cognome']; ?>">
                    </div>

                    <div class="form-group">
                        <label for="nickname">Nickname:</label>
                        <input type="text" name="nickname" id="nickname" required value="<?php echo $user['nickname']; ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" name="email" id="email" required value="<?php echo $user['email']; ?>">
                    </div>

                    <div class="form-group">
                        <label for="telefono">Telefono:</label>
                        <input type="tel" name="telefono" id="telefono" required value="<?php echo $user['telefono']; ?>">
                    </div>

                    <div class="form-group">
                        <label for="citta">Città:</label>
                        <input type="text" name="citta" id="citta" required value="<?php echo $user['citta']; ?>" list="lista-citta">
                        <datalist id="lista-citta">
                            <?php foreach($citta_list as $citta): ?>
                            <option value="<?php echo $citta; ?>">
                                <?php endforeach; ?>
                        </datalist>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Cambia Password</h2>
                    <p class="form-hint">Lascia vuoto per mantenere la password attuale.</p>

                    <div class="form-group">
                        <label for="password">Nuova Password:</label>
                        <input type="password" name="password" id="password">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Conferma Password:</label>
                        <input type="password" name="confirm_password" id="confirm_password">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Aggiorna Profilo</button>
                </div>
            </form>

            <div class="additional-actions">
                <h3>Altre Opzioni</h3>
                <ul>
                    <li><a href="dashboard.php">Torna alla dashboard</a></li>
                    <li><a href="cambia_barbiere.php">Cambia barbiere predefinito</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="js/jquery.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>
