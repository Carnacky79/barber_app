<?php
// cancella_appuntamento.php - Cancellazione appuntamento da parte dell'utente
session_start();
require_once 'config.php';

// Verifica che l'utente sia loggato
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

// Verifica che sia stato fornito un ID valido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$appuntamento_id = intval($_GET['id']);
$conn = connectDB();
$error = '';
$success = '';

// Verifica che l'appuntamento appartenga all'utente e sia in stato valido
$stmt = $conn->prepare("
    SELECT a.*, b.nome as barbiere_nome, o.nome as operatore_nome, s.nome as servizio_nome
    FROM appuntamenti a
    JOIN barbieri b ON a.barbiere_id = b.id
    JOIN operatori o ON a.operatore_id = o.id
    JOIN servizi s ON a.servizio_id = s.id
    WHERE a.id = ? AND a.utente_id = ? AND a.stato IN ('in attesa', 'confermato')
");

$stmt->bind_param("ii", $appuntamento_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    // L'appuntamento non esiste o non appartiene all'utente
    $_SESSION['error'] = "Appuntamento non trovato o non può essere cancellato.";
    header("Location: dashboard.php");
    exit;
}

$appuntamento = $result->fetch_assoc();

// Verifica che l'appuntamento possa essere cancellato (almeno 24 ore prima)
$now = new DateTime();
$appDate = new DateTime($appuntamento['data_appuntamento'] . ' ' . $appuntamento['ora_inizio']);
$diff = $now->diff($appDate);
$canCancel = ($diff->days > 0 || ($diff->days == 0 && $diff->h >= 24));

if (!$canCancel) {
    $_SESSION['error'] = "Non è possibile cancellare l'appuntamento meno di 24 ore prima dell'orario previsto.";
    header("Location: dashboard.php");
    exit;
}

// Gestione della conferma di cancellazione
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['csrf_token']) && verifyCSRFToken($_POST['csrf_token'])) {
    // Cancella l'appuntamento
    $stmt = $conn->prepare("UPDATE appuntamenti SET stato = 'cancellato' WHERE id = ?");
    $stmt->bind_param("i", $appuntamento_id);

    if ($stmt->execute()) {
        // Invia email di notifica al barbiere
        $stmt = $conn->prepare("SELECT email FROM barbieri WHERE id = ?");
        $stmt->bind_param("i", $appuntamento['barbiere_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $barbiere_email = $result->fetch_assoc()['email'];

        $subject = 'Cancellazione appuntamento';
        $message = "
            <h2>Appuntamento cancellato</h2>
            <p>Un cliente ha cancellato il seguente appuntamento:</p>
            <p><strong>Dettagli:</strong></p>
            <ul>
                <li><strong>Cliente:</strong> ".$_SESSION['user_name']."</li>
                <li><strong>Servizio:</strong> {$appuntamento['servizio_nome']}</li>
                <li><strong>Operatore:</strong> {$appuntamento['operatore_nome']}</li>
                <li><strong>Data:</strong> ".date('d/m/Y', strtotime($appuntamento['data_appuntamento']))."</li>
                <li><strong>Orario:</strong> ".date('H:i', strtotime($appuntamento['ora_inizio']))." - ".date('H:i', strtotime($appuntamento['ora_fine']))."</li>
            </ul>
        ";

        sendEmail($barbiere_email, $subject, $message);

        $_SESSION['success'] = "Appuntamento cancellato con successo.";
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Errore durante la cancellazione dell'appuntamento.";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancella Appuntamento - BarberBook</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="page-content">
        <h1>Cancella Appuntamento</h1>

        <?php if($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="confirmation-box">
            <h2>Sei sicuro di voler cancellare questo appuntamento?</h2>

            <div class="appointment-details">
                <div class="detail-row">
                    <span class="detail-label">Barbiere:</span>
                    <span class="detail-value"><?php echo $appuntamento['barbiere_nome']; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Servizio:</span>
                    <span class="detail-value"><?php echo $appuntamento['servizio_nome']; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Operatore:</span>
                    <span class="detail-value"><?php echo $appuntamento['operatore_nome']; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Data:</span>
                    <span class="detail-value"><?php echo date('d/m/Y', strtotime($appuntamento['data_appuntamento'])); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Orario:</span>
                    <span class="detail-value">
                            <?php echo date('H:i', strtotime($appuntamento['ora_inizio'])); ?> -
                            <?php echo date('H:i', strtotime($appuntamento['ora_fine'])); ?>
                        </span>
                </div>
            </div>

            <div class="confirmation-actions">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo $appuntamento_id; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <button type="submit" class="btn-danger">Sì, cancella appuntamento</button>
                </form>
                <a href="dashboard.php" class="btn-secondary">No, torna alla dashboard</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="js/jquery.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>
