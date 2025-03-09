<?php
// barbiere/nuovo_appuntamento.php - Aggiunta manuale di un appuntamento
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

// Gestione inserimento appuntamento
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['csrf_token']) && verifyCSRFToken($_POST['csrf_token'])) {
    $utente_id = intval($_POST['utente_id']);
    $operatore_id = intval($_POST['operatore_id']);
    $servizio_id = intval($_POST['servizio_id']);
    $data_appuntamento = $_POST['data_appuntamento'];
    $ora_inizio = $_POST['ora_inizio'];
    $ora_fine = $_POST['ora_fine'];
    $note = sanitizeInput($_POST['note'] ?? '');
    $stato = 'confermato'; // Gli appuntamenti creati manualmente sono già confermati

    // Validazione
    $errors = [];

    if ($utente_id <= 0) {
        $errors[] = "Seleziona un cliente.";
    }

    if ($operatore_id <= 0) {
        $errors[] = "Seleziona un operatore.";
    }

    if ($servizio_id <= 0) {
        $errors[] = "Seleziona un servizio.";
    }

    if (empty($data_appuntamento)) {
        $errors[] = "Seleziona una data.";
    }

    if (empty($ora_inizio) || empty($ora_fine)) {
        $errors[] = "Seleziona un orario di inizio e fine.";
    }

    // Verifica che lo slot orario sia disponibile
    if (empty($errors) && !isTimeSlotAvailable($_SESSION['barbiere_id'], $operatore_id, $data_appuntamento, $ora_inizio, $ora_fine)) {
        $errors[] = "Lo slot orario selezionato non è disponibile.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("
            INSERT INTO appuntamenti (utente_id, barbiere_id, operatore_id, servizio_id, data_appuntamento, ora_inizio, ora_fine, stato, note)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iiisssss", $utente_id, $_SESSION['barbiere_id'], $operatore_id, $servizio_id, $data_appuntamento, $ora_inizio, $ora_fine, $stato, $note);

        if ($stmt->execute()) {
            $success = "Appuntamento creato con successo.";
        } else {
            $error = "Errore durante la creazione dell'appuntamento: " . $stmt->error;
        }
    } else {
        $error = implode("<br>", $errors);
    }
}

// Ottieni utenti registrati
$stmt = $conn->prepare("
    SELECT u.* FROM utenti u
    WHERE u.barbiere_default = ?
    ORDER BY u.nome, u.cognome
");
$stmt->bind_param("i", $_SESSION['barbiere_id']);
$stmt->execute();
$result = $stmt->get_result();
$utenti = [];
while ($row = $result->fetch_assoc()) {
    $utenti[] = $row;
}

// Ottieni servizi
$stmt = $conn->prepare("SELECT * FROM servizi WHERE barbiere_id = ? ORDER BY nome");
$stmt->bind_param("i", $_SESSION['barbiere_id']);
$stmt->execute();
$result = $stmt->get_result();
$servizi = [];
while ($row = $result->fetch_assoc()) {
    $servizi[] = $row;
}

// Ottieni operatori
$stmt = $conn->prepare("SELECT * FROM operatori WHERE barbiere_id = ? AND attivo = 1 ORDER BY nome");
$stmt->bind_param("i", $_SESSION['barbiere_id']);
$stmt->execute();
$result = $stmt->get_result();
$operatori = [];
while ($row = $result->fetch_assoc()) {
    $operatori[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuovo Appuntamento - BarberBook</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="admin-section">
        <h1>Nuovo Appuntamento</h1>

        <?php if($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="appointment-form">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <div class="form-group">
                    <label for="utente_id">Cliente:</label>
                    <select name="utente_id" id="utente_id" required>
                        <option value="">Seleziona cliente</option>
                        <?php foreach($utenti as $utente): ?>
                            <option value="<?php echo $utente['id']; ?>">
                                <?php echo $utente['nome'] . ' ' . $utente['cognome']; ?> (<?php echo $utente['telefono']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="servizio_id">Servizio:</label>
                    <select name="servizio_id" id="servizio_id" required>
                        <option value="">Seleziona servizio</option>
                        <?php foreach($servizi as $servizio): ?>
                            <option value="<?php echo $servizio['id']; ?>" data-durata="<?php echo $servizio['durata_minuti']; ?>">
                                <?php echo $servizio['nome']; ?> (€<?php echo number_format($servizio['prezzo'], 2); ?>, <?php echo $servizio['durata_minuti']; ?> min)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="operatore_id">Operatore:</label>
                    <select name="operatore_id" id="operatore_id" required>
                        <option value="">Seleziona operatore</option>
                        <?php foreach($operatori as $operatore): ?>
                            <option value="<?php echo $operatore['id']; ?>">
                                <?php echo $operatore['nome']; ?> (<?php echo $operatore['qualifica']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="data_appuntamento">Data:</label>
                    <input type="date" name="data_appuntamento" id="data_appuntamento" required min="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label for="ora_inizio">Ora inizio:</label>
                    <input type="time" name="ora_inizio" id="ora_inizio" required>
                </div>

                <div class="form-group">
                    <label for="ora_fine">Ora fine:</label>
                    <input type="time" name="ora_fine" id="ora_fine" required>
                </div>

                <div class="form-group">
                    <label for="note">Note (opzionale):</label>
                    <textarea name="note" id="note" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn-primary">Crea Appuntamento</button>
                    <a href="appuntamenti.php" class="btn-secondary">Annulla</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="../js/jquery.min.js"></script>
<script src="../js/script.js"></script>
<script>
    $(document).ready(function() {
        // Imposta automaticamente l'ora di fine in base alla durata del servizio
        $('#servizio_id, #ora_inizio').change(function() {
            const servizioOption = $('#servizio_id option:selected');
            const oraInizio = $('#ora_inizio').val();

            if (servizioOption.val() && oraInizio) {
                const durata = parseInt(servizioOption.data('durata'));

                // Calcola l'ora di fine
                const [hours, minutes] = oraInizio.split(':').map(Number);
                let totalMinutes = hours * 60 + minutes + durata;

                const endHours = Math.floor(totalMinutes / 60).toString().padStart(2, '0');
                const endMinutes = (totalMinutes % 60).toString().padStart(2, '0');

                $('#ora_fine').val(`${endHours}:${endMinutes}`);
            }
        });
    });
</script>
</body>
</html>
