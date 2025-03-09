<?php
// modifica_appuntamento.php - Modifica di un appuntamento da parte dell'utente
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
    SELECT a.*, b.nome as barbiere_nome, b.id as barbiere_id, o.nome as operatore_nome, 
           o.id as operatore_id, s.nome as servizio_nome, s.id as servizio_id, s.durata_minuti
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
    $_SESSION['error'] = "Appuntamento non trovato o non può essere modificato.";
    header("Location: dashboard.php");
    exit;
}

$appuntamento = $result->fetch_assoc();

// Verifica che l'appuntamento possa essere modificato (almeno 24 ore prima)
$now = new DateTime();
$appDate = new DateTime($appuntamento['data_appuntamento'] . ' ' . $appuntamento['ora_inizio']);
$diff = $now->diff($appDate);
$canModify = ($diff->days > 0 || ($diff->days == 0 && $diff->h >= 24));

if (!$canModify) {
    $_SESSION['error'] = "Non è possibile modificare l'appuntamento meno di 24 ore prima dell'orario previsto.";
    header("Location: dashboard.php");
    exit;
}

// Ottieni i servizi disponibili
$stmt = $conn->prepare("SELECT * FROM servizi WHERE barbiere_id = ? ORDER BY nome");
$stmt->bind_param("i", $appuntamento['barbiere_id']);
$stmt->execute();
$result = $stmt->get_result();
$servizi = [];
while ($row = $result->fetch_assoc()) {
    $servizi[] = $row;
}

// Ottieni gli operatori che offrono il servizio selezionato
$stmt = $conn->prepare("
    SELECT o.* FROM operatori o
    JOIN operatori_servizi os ON o.id = os.operatore_id
    WHERE o.barbiere_id = ? AND os.servizio_id = ? AND o.attivo = 1
    ORDER BY o.nome
");
$stmt->bind_param("ii", $appuntamento['barbiere_id'], $appuntamento['servizio_id']);
$stmt->execute();
$result = $stmt->get_result();
$operatori = [];
while ($row = $result->fetch_assoc()) {
    $operatori[] = $row;
}

// Gestione del form di modifica
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['csrf_token']) && verifyCSRFToken($_POST['csrf_token'])) {
    $servizio_id = intval($_POST['servizio_id']);
    $operatore_id = intval($_POST['operatore_id']);
    $data = $_POST['data'];
    $ora_inizio = $_POST['ora_inizio'];
    $note = sanitizeInput($_POST['note'] ?? '');

    // Verifica che il servizio appartenga al barbiere
    $stmt = $conn->prepare("SELECT * FROM servizi WHERE id = ? AND barbiere_id = ?");
    $stmt->bind_param("ii", $servizio_id, $appuntamento['barbiere_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $error = "Servizio non valido.";
    } else {
        $servizio = $result->fetch_assoc();

        // Verifica che l'operatore offra il servizio
        $stmt = $conn->prepare("
            SELECT o.* FROM operatori o
            JOIN operatori_servizi os ON o.id = os.operatore_id
            WHERE o.id = ? AND o.barbiere_id = ? AND os.servizio_id = ? AND o.attivo = 1
        ");
        $stmt->bind_param("iii", $operatore_id, $appuntamento['barbiere_id'], $servizio_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $error = "Operatore non valido o non offre il servizio selezionato.";
        } else {
            $operatore = $result->fetch_assoc();

            // Calcola l'ora di fine in base alla durata del servizio
            $ora_inizio_obj = new DateTime($ora_inizio);
            $ora_fine_obj = clone $ora_inizio_obj;
            $ora_fine_obj->add(new DateInterval('PT' . $servizio['durata_minuti'] . 'M'));
            $ora_fine = $ora_fine_obj->format('H:i:s');

            // Verifica che lo slot sia disponibile
            if (!isTimeSlotAvailable($appuntamento['barbiere_id'], $operatore_id, $data, $ora_inizio, $ora_fine, $appuntamento_id)) {
                $error = "Lo slot orario selezionato non è disponibile.";
            } else {
                // Aggiorna l'appuntamento
                $stmt = $conn->prepare("
                    UPDATE appuntamenti 
                    SET servizio_id = ?, operatore_id = ?, data_appuntamento = ?, 
                        ora_inizio = ?, ora_fine = ?, note = ?
                    WHERE id = ?
                ");
                $stmt->bind_param("iissssi", $servizio_id, $operatore_id, $data, $ora_inizio, $ora_fine, $note, $appuntamento_id);

                if ($stmt->execute()) {
                    // Invia email di notifica al barbiere
                    $stmt = $conn->prepare("SELECT email FROM barbieri WHERE id = ?");
                    $stmt->bind_param("i", $appuntamento['barbiere_id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $barbiere_email = $result->fetch_assoc()['email'];

                    $subject = 'Modifica appuntamento';
                    $message = "
                        <h2>Appuntamento modificato</h2>
                        <p>Un cliente ha modificato il seguente appuntamento:</p>
                        <p><strong>Dettagli:</strong></p>
                        <ul>
                            <li><strong>Cliente:</strong> ".$_SESSION['user_name']."</li>
                            <li><strong>Servizio:</strong> {$servizio['nome']}</li>
                            <li><strong>Operatore:</strong> {$operatore['nome']}</li>
                            <li><strong>Data:</strong> ".date('d/m/Y', strtotime($data))."</li>
                            <li><strong>Orario:</strong> ".date('H:i', strtotime($ora_inizio))." - ".date('H:i', strtotime($ora_fine))."</li>
                        </ul>
                    ";

                    sendEmail($barbiere_email, $subject, $message);

                    $_SESSION['success'] = "Appuntamento modificato con successo.";
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $error = "Errore durante l'aggiornamento dell'appuntamento.";
                }
            }
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Appuntamento - BarberBook</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="page-content">
        <h1>Modifica Appuntamento</h1>

        <?php if($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="appointment-form">
            <div class="current-appointment">
                <h2>Dettagli Attuali</h2>
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
            </div>

            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo $appuntamento_id; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <div class="form-group">
                    <label for="servizio_id">Servizio:</label>
                    <select name="servizio_id" id="servizio_id" required>
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
                        <?php foreach($operatori as $operatore): ?>
                            <option value="<?php echo $operatore['id']; ?>" <?php echo $operatore['id'] == $appuntamento['operatore_id'] ? 'selected' : ''; ?>>
                                <?php echo $operatore['nome']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="data">Data:</label>
                    <input type="date" name="data" id="data" required value="<?php echo $appuntamento['data_appuntamento']; ?>" min="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label for="ora_inizio">Orario:</label>
                    <input type="time" name="ora_inizio" id="ora_inizio" required value="<?php echo substr($appuntamento['ora_inizio'], 0, 5); ?>">
                </div>

                <div class="form-group">
                    <label for="note">Note (opzionale):</label>
                    <textarea name="note" id="note" rows="3"><?php echo $appuntamento['note']; ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Aggiorna Appuntamento</button>
                    <a href="dashboard.php" class="btn-secondary">Annulla</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="js/jquery.min.js"></script>
<script src="js/script.js"></script>
<script>
    $(document).ready(function() {
        // Quando cambia il servizio, aggiorna la lista degli operatori
        $('#servizio_id').change(function() {
            const servizioId = $(this).val();
            const barbiereId = <?php echo $appuntamento['barbiere_id']; ?>;

            $.ajax({
                url: 'get_operatori.php',
                type: 'POST',
                data: {
                    barbiere_id: barbiereId,
                    servizio_id: servizioId
                },
                dataType: 'json',
                success: function(operatori) {
                    const operatoreSelect = $('#operatore_id');
                    operatoreSelect.empty();

                    $.each(operatori, function(i, operatore) {
                        operatoreSelect.append(`<option value="${operatore.id}">${operatore.nome}</option>`);
                    });
                }
            });
        });

        // Quando cambia la data o l'ora, verifica la disponibilità dello slot
        $('#data, #ora_inizio').change(function() {
            checkAvailability();
        });

        function checkAvailability() {
            const servizioId = $('#servizio_id').val();
            const operatoreId = $('#operatore_id').val();
            const data = $('#data').val();
            const oraInizio = $('#ora_inizio').val();

            if (servizioId && operatoreId && data && oraInizio) {
                const durata = $('#servizio_id option:selected').data('durata');

                $.ajax({
                    url: 'check_availability.php',
                    type: 'POST',
                    data: {
                        appuntamento_id: <?php echo $appuntamento_id; ?>,
                        operatore_id: operatoreId,
                        data: data,
                        ora_inizio: oraInizio,
                        durata: durata
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (!response.available) {
                            alert('Lo slot orario selezionato non è disponibile. Scegli un altro orario.');
                        }
                    }
                });
            }
        }
    });
</script>
</body>
</html>
