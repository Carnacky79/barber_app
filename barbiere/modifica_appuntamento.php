<?php
// barbiere/modifica_appuntamento.php - Modifica appuntamento
session_start();
require_once '../config.php';

// Verifica che il barbiere sia loggato
if (!isBarbiere()) {
    header("Location: ../login.php");
    exit;
}

// Verifica che sia stato fornito un ID valido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: appuntamenti.php");
    exit;
}

$appuntamento_id = intval($_GET['id']);
$error = '';
$success = '';

$conn = connectDB();

// Ottieni i dati dell'appuntamento
$stmt = $conn->prepare("
    SELECT a.*, u.id as utente_id, u.nome as utente_nome, u.telefono as utente_telefono, 
           u.email as utente_email, o.id as operatore_id, o.nome as operatore_nome, 
           s.id as servizio_id, s.nome as servizio_nome, s.durata_minuti
    FROM appuntamenti a
    JOIN utenti u ON a.utente_id = u.id
    JOIN operatori o ON a.operatore_id = o.id
    JOIN servizi s ON a.servizio_id = s.id
    WHERE a.id = ? AND a.barbiere_id = ? AND a.stato IN ('in attesa', 'confermato')
");

$stmt->bind_param("ii", $appuntamento_id, $_SESSION['barbiere_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    // L'appuntamento non esiste, non appartiene al barbiere o non può essere modificato
    header("Location: appuntamenti.php");
    exit;
}

$appuntamento = $result->fetch_assoc();

// Gestione form di modifica
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['csrf_token']) && verifyCSRFToken($_POST['csrf_token'])) {
    $operatore_id = intval($_POST['operatore_id']);
    $servizio_id = intval($_POST['servizio_id']);
    $data_appuntamento = $_POST['data_appuntamento'];
    $ora_inizio = $_POST['ora_inizio'];
    $ora_fine = $_POST['ora_fine'];
    $note = sanitizeInput($_POST['note'] ?? '');
    $stato = $_POST['stato'];

    // Validazione
    $errors = [];

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

    // Verifica che lo slot orario sia disponibile (escludendo l'appuntamento corrente)
    if (empty($errors)) {
        $stmt = $conn->prepare("
            SELECT id FROM appuntamenti 
            WHERE barbiere_id = ? AND operatore_id = ? AND data_appuntamento = ? 
            AND stato IN ('in attesa', 'confermato')
            AND id != ?
            AND ((ora_inizio < ? AND ora_fine > ?) OR (ora_inizio < ? AND ora_fine > ?) OR (ora_inizio >= ? AND ora_fine <= ?))
        ");
        $stmt->bind_param("iissssssss", $_SESSION['barbiere_id'], $operatore_id, $data_appuntamento, $appuntamento_id, $ora_fine, $ora_inizio, $ora_fine, $ora_inizio, $ora_inizio, $ora_fine);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errors[] = "Lo slot orario selezionato non è disponibile. C'è già un altro appuntamento programmato.";
        }
    }

    if (empty($errors)) {
        // Aggiorna l'appuntamento
        $stmt = $conn->prepare("
            UPDATE appuntamenti 
            SET operatore_id = ?, servizio_id = ?, data_appuntamento = ?, 
                ora_inizio = ?, ora_fine = ?, note = ?, stato = ?
            WHERE id = ?
        ");
        $stmt->bind_param("iisssssi", $operatore_id, $servizio_id, $data_appuntamento, $ora_inizio, $ora_fine, $note, $stato, $appuntamento_id);

        if ($stmt->execute()) {
            // Notifica il cliente via email
            $stmt = $conn->prepare("
                SELECT u.email, u.nome as utente_nome, b.nome as barbiere_nome, 
                       o.nome as operatore_nome, s.nome as servizio_nome
                FROM appuntamenti a
                JOIN utenti u ON a.utente_id = u.id
                JOIN barbieri b ON a.barbiere_id = b.id
                JOIN operatori o ON a.operatore_id = o.id
                JOIN servizi s ON a.servizio_id = s.id
                WHERE a.id = ?
            ");
            $stmt->bind_param("i", $appuntamento_id);
            $stmt->execute();
            $info = $stmt->get_result()->fetch_assoc();

            $subject = 'Modifica appuntamento';
            $message = "
                <h2>Appuntamento modificato</h2>
                <p>Gentile {$info['utente_nome']},</p>
                <p>Il tuo appuntamento è stato modificato.</p>
                <p><strong>Nuovi dettagli:</strong></p>
                <ul>
                    <li><strong>Barbiere:</strong> {$info['barbiere_nome']}</li>
                    <li><strong>Servizio:</strong> {$info['servizio_nome']}</li>
                    <li><strong>Operatore:</strong> {$info['operatore_nome']}</li>
                    <li><strong>Data:</strong> ".date('d/m/Y', strtotime($data_appuntamento))."</li>
                    <li><strong>Orario:</strong> ".date('H:i', strtotime($ora_inizio))." - ".date('H:i', strtotime($ora_fine))."</li>
                    <li><strong>Stato:</strong> ".($stato === 'in attesa' ? 'In attesa di conferma' : 'Confermato')."</li>
                </ul>
                <p>Puoi modificare o cancellare l'appuntamento fino a 24 ore prima dell'orario previsto.</p>
                <p>Grazie per aver scelto BarberBook!</p>
            ";

            sendEmail($info['email'], $subject, $message);

            $success = "Appuntamento aggiornato con successo.";

            // Ricarica i dati dell'appuntamento
            $stmt = $conn->prepare("
                SELECT a.*, u.id as utente_id, u.nome as utente_nome, u.telefono as utente_telefono, 
                       u.email as utente_email, o.id as operatore_id, o.nome as operatore_nome, 
                       s.id as servizio_id, s.nome as servizio_nome, s.durata_minuti
                FROM appuntamenti a
                JOIN utenti u ON a.utente_id = u.id
                JOIN operatori o ON a.operatore_id = o.id
                JOIN servizi s ON a.servizio_id = s.id
                WHERE a.id = ?
            ");
            $stmt->bind_param("i", $appuntamento_id);
            $stmt->execute();
            $appuntamento = $stmt->get_result()->fetch_assoc();
        } else {
            $error = "Errore durante l'aggiornamento dell'appuntamento: " . $stmt->error;
        }
    } else {
        $error = implode("<br>", $errors);
    }
}

// Ottieni operatori
$stmt = $conn->prepare("
    SELECT o.* FROM operatori o
    JOIN operatori_servizi os ON o.id = os.operatore_id
    WHERE o.barbiere_id = ? AND o.attivo = 1 AND os.servizio_id = ?
    ORDER BY o.nome
");
$stmt->bind_param("ii", $_SESSION['barbiere_id'], $appuntamento['servizio_id']);
$stmt->execute();
$result = $stmt->get_result();
$operatori = [];
while ($row = $result->fetch_assoc()) {
    $operatori[] = $row;
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

$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Appuntamento - BarberBook</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="admin-section">
        <h1>Modifica Appuntamento</h1>

        <?php if($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="appointment-form">
            <div class="customer-info">
                <h2>Informazioni Cliente</h2>
                <div class="info-group">
                    <span class="info-label">Nome:</span>
                    <span class="info-value"><?php echo $appuntamento['utente_nome']; ?></span>
                </div>
                <div class="info-group">
                    <span class="info-label">Telefono:</span>
                    <span class="info-value"><?php echo $appuntamento['utente_telefono']; ?></span>
                </div>
                <div class="info-group">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?php echo $appuntamento['utente_email']; ?></span>
                </div>
            </div>

            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo $appuntamento_id; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <div class="form-group">
                    <label for="servizio_id">Servizio:</label>
                    <select name="servizio_id" id="servizio_id" required>
                        <option value="">Seleziona servizio</option>
                        <?php foreach($servizi as $servizio): ?>
                            <option value="<?php echo $servizio['id']; ?>" data-durata="<?php echo $servizio['durata_minuti']; ?>" <?php echo $servizio['id'] == $appuntamento['servizio_id'] ? 'selected' : ''; ?>>
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
                            <option value="<?php echo $operatore['id']; ?>" <?php echo $operatore['id'] == $appuntamento['operatore_id'] ? 'selected' : ''; ?>>
                                <?php echo $operatore['nome']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="data_appuntamento">Data:</label>
                    <input type="date" name="data_appuntamento" id="data_appuntamento" required value="<?php echo $appuntamento['data_appuntamento']; ?>">
                </div>

                <div class="form-group">
                    <label for="ora_inizio">Ora inizio:</label>
                    <input type="time" name="ora_inizio" id="ora_inizio" required value="<?php echo substr($appuntamento['ora_inizio'], 0, 5); ?>">
                </div>

                <div class="form-group">
                    <label for="ora_fine">Ora fine:</label>
                    <input type="time" name="ora_fine" id="ora_fine" required value="<?php echo substr($appuntamento['ora_fine'], 0, 5); ?>">
                </div>

                <div class="form-group">
                    <label for="stato">Stato:</label>
                    <select name="stato" id="stato" required>
                        <option value="in attesa" <?php echo $appuntamento['stato'] === 'in attesa' ? 'selected' : ''; ?>>In attesa di conferma</option>
                        <option value="confermato" <?php echo $appuntamento['stato'] === 'confermato' ? 'selected' : ''; ?>>Confermato</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="note">Note (opzionale):</label>
                    <textarea name="note" id="note" rows="3"><?php echo $appuntamento['note']; ?></textarea>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn-primary">Aggiorna Appuntamento</button>
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
        // Quando cambia il servizio, aggiorna l'elenco degli operatori
        $('#servizio_id').change(function() {
            const servizioId = $(this).val();
            const durata = $(this).find(':selected').data('durata');

            // Aggiorna la durata dell'appuntamento
            const oraInizio = $('#ora_inizio').val();
            if (oraInizio && durata) {
                const [hours, minutes] = oraInizio.split(':').map(Number);
                let totalMinutes = hours * 60 + minutes + durata;

                const endHours = Math.floor(totalMinutes / 60).toString().padStart(2, '0');
                const endMinutes = (totalMinutes % 60).toString().padStart(2, '0');

                $('#ora_fine').val(`${endHours}:${endMinutes}`);
            }

            // Carica gli operatori per questo servizio
            if (servizioId) {
                $.ajax({
                    url: 'get_operatori.php',
                    type: 'POST',
                    data: { servizio_id: servizioId },
                    dataType: 'json',
                    success: function(operatori) {
                        const operatoreSelect = $('#operatore_id');
                        operatoreSelect.empty();
                        operatoreSelect.append('<option value="">Seleziona operatore</option>');

                        $.each(operatori, function(i, operatore) {
                            operatoreSelect.append(`<option value="${operatore.id}">${operatore.nome}</option>`);
                        });
                    }
                });
            }
        });

        // Imposta automaticamente l'ora di fine in base alla durata del servizio
        $('#ora_inizio').change(function() {
            const servizioOption = $('#servizio_id option:selected');
            const oraInizio = $(this).val();

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
