<?php
// barbiere/impostazioni.php - Gestione impostazioni barbiere
session_start();
require_once '../config.php';

// Verifica che il barbiere sia loggato
if (!isBarbiere()) {
    header("Location: ../login.php");
    exit;
}

$conn = connectDB();
$error = '';
$success = '';

// Ottieni i dati del barbiere
$stmt = $conn->prepare("SELECT * FROM barbieri WHERE id = ?");
$stmt->bind_param("i", $_SESSION['barbiere_id']);
$stmt->execute();
$barbiere = $stmt->get_result()->fetch_assoc();

// Gestione aggiornamento impostazioni
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['csrf_token']) && verifyCSRFToken($_POST['csrf_token'])) {
    $nome = sanitizeInput($_POST['nome']);
    $indirizzo = sanitizeInput($_POST['indirizzo']);
    $citta = sanitizeInput($_POST['citta']);
    $cap = sanitizeInput($_POST['cap']);
    $telefono = sanitizeInput($_POST['telefono']);
    $email = sanitizeInput($_POST['email']);
    $sito_web = sanitizeInput($_POST['sito_web'] ?? '');
    $approvazione_automatica = isset($_POST['approvazione_automatica']) ? 1 : 0;
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validazione
    $errors = [];

    if (empty($nome)) {
        $errors[] = "Il nome dell'attività è obbligatorio.";
    }

    if (empty($indirizzo)) {
        $errors[] = "L'indirizzo è obbligatorio.";
    }

    if (empty($citta)) {
        $errors[] = "La città è obbligatoria.";
    }

    if (empty($cap)) {
        $errors[] = "Il CAP è obbligatorio.";
    }

    if (empty($telefono)) {
        $errors[] = "Il telefono è obbligatorio.";
    }

    if (empty($email)) {
        $errors[] = "L'email è obbligatoria.";
    }

    // Verifica se l'email è già in uso da un altro barbiere
    $stmt = $conn->prepare("SELECT id FROM barbieri WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $_SESSION['barbiere_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $errors[] = "Email già in uso da un altro barbiere.";
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

    // Gestione caricamento logo
    $logo = $barbiere['logo'];
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $upload = uploadImage($_FILES['logo'], '../uploads/barbieri/');
        if ($upload['success']) {
            $logo = $upload['filename'];
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
                UPDATE barbieri 
                SET nome = ?, indirizzo = ?, citta = ?, cap = ?, telefono = ?, email = ?, 
                    sito_web = ?, logo = ?, approvazione_automatica = ?, password = ?
                WHERE id = ?
            ");
            $stmt->bind_param("ssssssssiis", $nome, $indirizzo, $citta, $cap, $telefono, $email, $sito_web, $logo, $approvazione_automatica, $hashed_password, $_SESSION['barbiere_id']);
        } else {
            // Aggiorna senza cambiare password
            $stmt = $conn->prepare("
                UPDATE barbieri 
                SET nome = ?, indirizzo = ?, citta = ?, cap = ?, telefono = ?, email = ?, 
                    sito_web = ?, logo = ?, approvazione_automatica = ?
                WHERE id = ?
            ");
            $stmt->bind_param("ssssssssis", $nome, $indirizzo, $citta, $cap, $telefono, $email, $sito_web, $logo, $approvazione_automatica, $_SESSION['barbiere_id']);
        }

        if ($stmt->execute()) {
            $success = "Impostazioni aggiornate con successo.";

            // Aggiorna i dati del barbiere nella sessione
            $_SESSION['barbiere_name'] = $nome;

            // Ricarica i dati del barbiere
            $stmt = $conn->prepare("SELECT * FROM barbieri WHERE id = ?");
            $stmt->bind_param("i", $_SESSION['barbiere_id']);
            $stmt->execute();
            $barbiere = $stmt->get_result()->fetch_assoc();
        } else {
            $error = "Errore durante l'aggiornamento delle impostazioni: " . $stmt->error;
        }
    } else {
        $error = implode("<br>", $errors);
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impostazioni - BarberBook</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="admin-section">
        <h1>Impostazioni</h1>

        <?php if($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="settings-form">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <div class="form-section">
                    <h2>Informazioni Attività</h2>

                    <div class="form-group">
                        <label for="nome">Nome Attività:</label>
                        <input type="text" name="nome" id="nome" required value="<?php echo $barbiere['nome']; ?>">
                    </div>

                    <div class="form-group">
                        <label for="indirizzo">Indirizzo:</label>
                        <input type="text" name="indirizzo" id="indirizzo" required value="<?php echo $barbiere['indirizzo']; ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="citta">Città:</label>
                            <input type="text" name="citta" id="citta" required value="<?php echo $barbiere['citta']; ?>">
                        </div>
                        <div class="form-group">
                            <label for="cap">CAP:</label>
                            <input type="text" name="cap" id="cap" required value="<?php echo $barbiere['cap']; ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="telefono">Telefono:</label>
                            <input type="tel" name="telefono" id="telefono" required value="<?php echo $barbiere['telefono']; ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" name="email" id="email" required value="<?php echo $barbiere['email']; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="sito_web">Sito Web (opzionale):</label>
                        <input type="url" name="sito_web" id="sito_web" value="<?php echo $barbiere['sito_web']; ?>">
                    </div>

                    <div class="form-group">
                        <label for="logo">Logo (opzionale):</label>
                        <?php if($barbiere['logo']): ?>
                            <div class="current-logo">
                                <img src="../uploads/barbieri/<?php echo $barbiere['logo']; ?>" alt="<?php echo $barbiere['nome']; ?>" width="150">
                                <p>Logo attuale</p>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="logo" id="logo" accept="image/*">
                    </div>
                </div>

                <div class="form-section">
                    <h2>Impostazioni Appuntamenti</h2>

                    <div class="form-group">
                        <label for="approvazione_automatica">
                            <input type="checkbox" name="approvazione_automatica" id="approvazione_automatica" <?php echo $barbiere['approvazione_automatica'] ? 'checked' : ''; ?>>
                            Approvazione automatica degli appuntamenti
                        </label>
                        <p class="form-hint">
                            Se attivato, gli appuntamenti prenotati dai clienti saranno automaticamente confermati.
                            Altrimenti, dovrai approvarli manualmente.
                        </p>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Modifica Password</h2>
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
                    <button type="submit" class="btn-primary">Salva Impostazioni</button>
                    <a href="dashboard.php" class="btn-secondary">Annulla</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="../js/jquery.min.js"></script>
<script src="../js/script.js"></script>
</body>
</html>
