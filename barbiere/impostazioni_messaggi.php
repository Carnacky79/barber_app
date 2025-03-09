<?php
// barbiere/impostazioni_messaggi.php - Gestione messaggi predefiniti
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

// Ottieni le impostazioni attuali dei messaggi
$stmt = $conn->prepare("SELECT messaggi_predefiniti FROM barbieri WHERE id = ?");
$stmt->bind_param("i", $_SESSION['barbiere_id']);
$stmt->execute();
$result = $stmt->get_result();
$barbiere = $result->fetch_assoc();

// Converti la stringa JSON in array (o inizializza se non esiste)
$messaggi = json_decode($barbiere['messaggi_predefiniti'] ?? '{}', true) ?: [
    'conferma' => 'Ciao {nome}, il tuo appuntamento per {servizio} è confermato per il {data} alle {ora}. Ti aspettiamo!',
    'promemoria' => 'Ciao {nome}, ti ricordiamo che hai un appuntamento per {servizio} domani alle {ora}. Ti aspettiamo!',
    'modifica' => 'Ciao {nome}, il tuo appuntamento è stato modificato. Nuovo appuntamento: {servizio} il {data} alle {ora}.',
    'cancellazione' => 'Ciao {nome}, il tuo appuntamento del {data} alle {ora} è stato cancellato. Per maggiori informazioni contattaci.'
];

// Gestione salvataggio messaggi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['csrf_token']) && verifyCSRFToken($_POST['csrf_token'])) {
    $nuovi_messaggi = [
        'conferma' => sanitizeInput($_POST['msg_conferma']),
        'promemoria' => sanitizeInput($_POST['msg_promemoria']),
        'modifica' => sanitizeInput($_POST['msg_modifica']),
        'cancellazione' => sanitizeInput($_POST['msg_cancellazione'])
    ];

    // Converti l'array in stringa JSON
    $messaggi_json = json_encode($nuovi_messaggi);

    // Aggiorna il database
    $stmt = $conn->prepare("UPDATE barbieri SET messaggi_predefiniti = ? WHERE id = ?");
    $stmt->bind_param("si", $messaggi_json, $_SESSION['barbiere_id']);

    if ($stmt->execute()) {
        $success = "Messaggi predefiniti aggiornati con successo.";
        $messaggi = $nuovi_messaggi; // Aggiorna l'array per la visualizzazione
    } else {
        $error = "Errore durante l'aggiornamento dei messaggi.";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messaggi Predefiniti - BarberBook</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="admin-section">
        <h1>Messaggi Predefiniti</h1>

        <?php if($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="form-info mb-4">
            <p>Personalizza i messaggi che verranno inviati ai clienti. Puoi utilizzare i seguenti segnaposto:</p>
            <ul class="list-disc pl-6 mt-2">
                <li><code>{nome}</code> - Nome del cliente</li>
                <li><code>{servizio}</code> - Nome del servizio</li>
                <li><code>{data}</code> - Data dell'appuntamento</li>
                <li><code>{ora}</code> - Orario dell'appuntamento</li>
                <li><code>{operatore}</code> - Nome dell'operatore</li>
            </ul>
        </div>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

            <div class="form-section">
                <h2>Messaggi WhatsApp</h2>

                <div class="form-group">
                    <label for="msg_conferma">Messaggio di conferma:</label>
                    <textarea name="msg_conferma" id="msg_conferma" rows="3" class="w-full"><?php echo $messaggi['conferma']; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="msg_promemoria">Messaggio di promemoria:</label>
                    <textarea name="msg_promemoria" id="msg_promemoria" rows="3" class="w-full"><?php echo $messaggi['promemoria']; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="msg_modifica">Messaggio di modifica appuntamento:</label>
                    <textarea name="msg_modifica" id="msg_modifica" rows="3" class="w-full"><?php echo $messaggi['modifica']; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="msg_cancellazione">Messaggio di cancellazione:</label>
                    <textarea name="msg_cancellazione" id="msg_cancellazione" rows="3" class="w-full"><?php echo $messaggi['cancellazione']; ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Salva Messaggi</button>
                <a href="impostazioni.php" class="btn-secondary">Torna alle Impostazioni</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="../js/jquery.min.js"></script>
<script src="../js/script.js"></script>
</body>
</html>
