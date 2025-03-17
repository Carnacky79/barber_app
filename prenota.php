<?php
// prenota.php - Pagina di prenotazione appuntamento
session_start();
require_once 'config.php';

// Verifica che l'utente sia loggato
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$conn = connectDB();

// Ottieni i dati dell'utente
$stmt = $conn->prepare("SELECT * FROM utenti WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Ottieni i dati del barbiere predefinito
if (!$user['barbiere_default']) {
    header("Location: scegli_barbiere.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM barbieri WHERE id = ?");
$stmt->bind_param("i", $user['barbiere_default']);
$stmt->execute();
$barbiere = $stmt->get_result()->fetch_assoc();

// Stato del processo di prenotazione
$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$error = '';
$servizio_id = isset($_GET['servizio_id']) ? intval($_GET['servizio_id']) : null;
$operatore_id = isset($_GET['operatore_id']) ? intval($_GET['operatore_id']) : null;
$data = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d', strtotime('+1 day'));

// Processo di prenotazione
switch ($step) {
    case 1: // Selezione servizio
        // Ottieni i servizi disponibili
        $stmt = $conn->prepare("SELECT * FROM servizi WHERE barbiere_id = ? ORDER BY nome");
        $stmt->bind_param("i", $user['barbiere_default']);
        $stmt->execute();
        $result = $stmt->get_result();
        $servizi = [];
        while ($row = $result->fetch_assoc()) {
            $servizi[] = $row;
        }
        break;

    case 2: // Selezione operatore
        if (!$servizio_id) {
            $error = "Servizio non selezionato.";
            $step = 1;
            break;
        }

        // Ottieni il servizio selezionato
        $stmt = $conn->prepare("SELECT * FROM servizi WHERE id = ? AND barbiere_id = ?");
        $stmt->bind_param("ii", $servizio_id, $user['barbiere_default']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $error = "Servizio non valido.";
            $step = 1;
            break;
        }

        $servizio = $result->fetch_assoc();

        // Ottieni gli operatori che offrono questo servizio
        $stmt = $conn->prepare("
            SELECT o.* 
            FROM operatori o
            JOIN operatori_servizi os ON o.id = os.operatore_id
            WHERE o.barbiere_id = ? AND os.servizio_id = ? AND o.attivo = 1
            ORDER BY o.nome
        ");
        $stmt->bind_param("ii", $user['barbiere_default'], $servizio_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $operatori = [];
        while ($row = $result->fetch_assoc()) {
            $operatori[] = $row;
        }

        if (count($operatori) == 0) {
            $error = "Non ci sono operatori disponibili per questo servizio.";
            $step = 1;
        }
        break;

    case 3: // Selezione data
        if (!$servizio_id || !$operatore_id) {
            $error = "Informazioni mancanti.";
            $step = 1;
            break;
        }

        // Verifica che servizio e operatore siano validi
        $stmt = $conn->prepare("
            SELECT s.*, o.nome as operatore_nome
            FROM servizi s
            JOIN operatori_servizi os ON s.id = os.servizio_id
            JOIN operatori o ON os.operatore_id = o.id
            WHERE s.id = ? AND o.id = ? AND s.barbiere_id = ? AND o.barbiere_id = ?
        ");
        $stmt->bind_param("iiii", $servizio_id, $operatore_id, $user['barbiere_default'], $user['barbiere_default']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $error = "Servizio o operatore non valido.";
            $step = 1;
            break;
        }

        $servizio = $result->fetch_assoc();

        // Determina le date disponibili (prossimi 30 giorni)
        $date_disponibili = [];
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime('+30 days'));

        $current_date = $start_date;
        while ($current_date <= $end_date) {
            $date_disponibili[] = $current_date;
            $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
        }
        break;

    case 4: // Selezione orario
        if (!$servizio_id || !$operatore_id || !$data) {
            $error = "Informazioni mancanti.";
            $step = 1;
            break;
        }
        // Verifica che servizio, operatore e data siano validi
        $stmt = $conn->prepare("
            SELECT s.*, o.nome as operatore_nome
            FROM servizi s
            JOIN operatori_servizi os ON s.id = os.servizio_id
            JOIN operatori o ON os.operatore_id = o.id
            WHERE s.id = ? AND o.id = ? AND s.barbiere_id = ? AND o.barbiere_id = ?
        ");
        $stmt->bind_param("iiii", $servizio_id, $operatore_id, $user['barbiere_default'], $user['barbiere_default']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $error = "Servizio o operatore non valido.";
            $step = 1;
            break;
        }

        $servizio = $result->fetch_assoc();

        // Verifica che la data sia valida (non passata e non oltre 30 giorni)
        $now = date('Y-m-d');
        $max_date = date('Y-m-d', strtotime('+30 days'));
        if ($data < $now || $data > $max_date) {
            $error = "Data non valida.";
            $step = 3;
            break;
        }

        // Ottieni il giorno della settimana
        $giornoSettimana = date('N', strtotime($data));
        $giorniMap = [1 => 'lunedi', 2 => 'martedi', 3 => 'mercoledi', 4 => 'giovedi', 5 => 'venerdi', 6 => 'sabato', 7 => 'domenica'];
        $giorno = $giorniMap[$giornoSettimana];

        // Verifica che il barbiere sia aperto in quel giorno
        $stmt = $conn->prepare("SELECT * FROM orari_apertura WHERE barbiere_id = ? AND giorno = ? AND aperto = 1");

        $stmt->bind_param("is", $user['barbiere_default'], $giorno);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $error = "Il barbiere è chiuso in questo giorno.";
            $step = 3;
            break;
        }

        $orario_apertura = $result->fetch_assoc();

        // Verifica che non sia un giorno di chiusura speciale
        $stmt = $conn->prepare("SELECT * FROM giorni_chiusura WHERE barbiere_id = ? AND data_chiusura = ?");
        $stmt->bind_param("is", $user['barbiere_default'], $data);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $chiusura = $result->fetch_assoc();
            $error = "Il barbiere è chiuso in questa data" . ($chiusura['descrizione'] ? ": " . $chiusura['descrizione'] : ".");
            $step = 3;
            break;
        }

        // Ottieni gli orari di disponibilità dell'operatore per quel giorno
        $stmt = $conn->prepare("SELECT * FROM orari_operatori WHERE operatore_id = ? AND giorno = ?");
        $stmt->bind_param("is", $operatore_id, $giorno);
        $stmt->execute();
        $result = $stmt->get_result();
        $disponibilita_operatore = [];
        while ($row = $result->fetch_assoc()) {
            $disponibilita_operatore[] = $row;
        }

        //var_dump($disponibilita_operatore);

        if (count($disponibilita_operatore) == 0) {
            $error = "L'operatore selezionato non è disponibile in questo giorno.";
            $step = 3;
            break;
        }

        // Ottieni gli appuntamenti già prenotati per quell'operatore in quel giorno
        $stmt = $conn->prepare("
            SELECT * FROM appuntamenti 
            WHERE operatore_id = ? AND data_appuntamento = ? AND stato IN ('in attesa', 'confermato')
            ORDER BY ora_inizio
        ");
        $stmt->bind_param("is", $operatore_id, $data);
        $stmt->execute();
        $result = $stmt->get_result();
        $appuntamenti_esistenti = [];
        while ($row = $result->fetch_assoc()) {
            $appuntamenti_esistenti[] = $row;
        }

        //var_dump($appuntamenti_esistenti);

         file_put_contents('debug.log', "Orario apertura: " . print_r($orario_apertura, true) . "\n", FILE_APPEND);
 file_put_contents('debug.log', "Operatore disponibilità: " . print_r($disponibilita_operatore, true) . "\n", FILE_APPEND);
 file_put_contents('debug.log', "Appuntamenti esistenti: " . print_r($appuntamenti_esistenti, true) . "\n", FILE_APPEND);


        // Calcola gli slot disponibili (incrementi di 15 minuti)
        $slot_disponibili = [];
        $durata_servizio = $servizio['durata_minuti'];

        foreach ($disponibilita_operatore as $disponibilita) {
            // Converti gli orari in timestamp per i calcoli
            $ora_inizio_turno = strtotime($disponibilita['ora_inizio']);
            $ora_fine_turno = strtotime($disponibilita['ora_fine']);
            $ora_chiusura = strtotime($orario_apertura['ora_chiusura']);

            // Assicurati che l'orario di fine rispetti la chiusura del negozio
            $ora_fine_turno = min($ora_fine_turno, $ora_chiusura);

            // Genera slot di 15 minuti all'interno del turno dell'operatore
            for ($slot_time = $ora_inizio_turno; $slot_time <= $ora_fine_turno - ($durata_servizio * 60); $slot_time += 15 * 60) {
                $slot_end_time = $slot_time + ($durata_servizio * 60);

                // Verifica la disponibilità (nessuna sovrapposizione con altri appuntamenti)
                $slot_disponibile = true;

                foreach ($appuntamenti_esistenti as $appuntamento) {
                    $app_start = strtotime($appuntamento['ora_inizio']);
                    $app_end = strtotime($appuntamento['ora_fine']);

                    // Se c'è sovrapposizione, lo slot non è disponibile
                    if (($slot_time < $app_end) && ($slot_end_time > $app_start)) {
                        $slot_disponibile = false;
                        break;
                    }
                }

                // Se lo slot è disponibile, aggiungilo alla lista
                if ($slot_disponibile) {
                    $slot_disponibili[] = [
                        'inizio' => date('H:i:s', $slot_time),
                        'fine' => date('H:i:s', $slot_end_time),
                        'inizio_formattato' => date('H:i', $slot_time),
                        'fine_formattata' => date('H:i', $slot_end_time)
                    ];
                }
            }
        }

// Debug del numero di slot trovati
file_put_contents('debug.log', "Numero di slot trovati: " . count($slot_disponibili) . "\n", FILE_APPEND);

        if (count($slot_disponibili) == 0) {
            // Debug: verifica le condizioni di base
            file_put_contents('debug.log', "Nessuno slot trovato. Barbiere: {$user['barbiere_default']}, Operatore: {$operatore_id}, Data: {$data}\n", FILE_APPEND);

            $error = "Non ci sono orari disponibili per la data selezionata.";
            $step = 3;
        }
        break;

    case 5: // Conferma prenotazione
        if (!$servizio_id || !$operatore_id || !$data || !isset($_GET['ora_inizio']) || !isset($_GET['ora_fine'])) {
            $error = "Informazioni mancanti.";
            $step = 1;
            break;
        }

        $ora_inizio = $_GET['ora_inizio'];
        $ora_fine = $_GET['ora_fine'];

        // Verifica che servizio e operatore siano validi
        $stmt = $conn->prepare("
            SELECT s.*, o.nome as operatore_nome
            FROM servizi s
            JOIN operatori_servizi os ON s.id = os.servizio_id
            JOIN operatori o ON os.operatore_id = o.id
            WHERE s.id = ? AND o.id = ? AND s.barbiere_id = ? AND o.barbiere_id = ?
        ");
        $stmt->bind_param("iiii", $servizio_id, $operatore_id, $user['barbiere_default'], $user['barbiere_default']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $error = "Servizio o operatore non valido.";
            $step = 1;
            break;
        }

        $servizio = $result->fetch_assoc();

        // Verifica che lo slot orario sia disponibile
        if (!isTimeSlotAvailable($user['barbiere_default'], $operatore_id, $data, $ora_inizio, $ora_fine)) {
            $error = "Lo slot orario selezionato non è più disponibile.";
            $step = 4;
            break;
        }
        break;

    case 6: // Salvataggio prenotazione
        if (!$servizio_id || !$operatore_id || !$data || !isset($_POST['ora_inizio']) || !isset($_POST['ora_fine'])) {
            $error = "Informazioni mancanti.";
            $step = 1;
            break;
        }

        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            $error = "Token CSRF non valido.";
            $step = 5;
            break;
        }

        $ora_inizio = $_POST['ora_inizio'];
        $ora_fine = $_POST['ora_fine'];
        $note = sanitizeInput($_POST['note'] ?? '');

        // Verifica che lo slot orario sia ancora disponibile
        if (!isTimeSlotAvailable($user['barbiere_default'], $operatore_id, $data, $ora_inizio, $ora_fine)) {
            $error = "Lo slot orario selezionato non è più disponibile.";
            $step = 4;
            break;
        }

        // Determina lo stato dell'appuntamento in base all'impostazione del barbiere
        $stmt = $conn->prepare("SELECT approvazione_automatica FROM barbieri WHERE id = ?");
        $stmt->bind_param("i", $user['barbiere_default']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stato = $row['approvazione_automatica'] ? 'confermato' : 'in attesa';

        // Salva l'appuntamento
        $stmt = $conn->prepare("
            INSERT INTO appuntamenti (utente_id, barbiere_id, operatore_id, servizio_id, data_appuntamento, ora_inizio, ora_fine, stato, note)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iiissssss", $_SESSION['user_id'], $user['barbiere_default'], $operatore_id, $servizio_id, $data, $ora_inizio, $ora_fine, $stato, $note);

        if (!$stmt->execute()) {
            $error = "Errore durante il salvataggio dell'appuntamento: " . $stmt->error;
            $step = 5;
            break;
        }

        $appuntamento_id = $conn->insert_id;

        // Invia email di conferma
        $stmt = $conn->prepare("
            SELECT u.email, u.nome, b.nome as barbiere_nome, o.nome as operatore_nome, s.nome as servizio_nome
            FROM utenti u
            JOIN barbieri b ON u.barbiere_default = b.id
            JOIN operatori o ON o.id = ?
            JOIN servizi s ON s.id = ?
            WHERE u.id = ?
        ");
        $stmt->bind_param("iii", $operatore_id, $servizio_id, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $info = $result->fetch_assoc();

        $subject = $stato === 'confermato' ? 'Conferma appuntamento' : 'Appuntamento in attesa di conferma';
        $message = "
            <h2>".($stato === 'confermato' ? 'Appuntamento confermato' : 'Appuntamento in attesa di conferma')."</h2>
            <p>Gentile {$info['nome']},</p>
            <p>".($stato === 'confermato' ? 'Il tuo appuntamento è stato confermato.' : 'Il tuo appuntamento è stato ricevuto ed è in attesa di conferma.')."</p>
            <p><strong>Dettagli:</strong></p>
            <ul>
                <li><strong>Barbiere:</strong> {$info['barbiere_nome']}</li>
                <li><strong>Servizio:</strong> {$info['servizio_nome']}</li>
                <li><strong>Operatore:</strong> {$info['operatore_nome']}</li>
                <li><strong>Data:</strong> ".date('d/m/Y', strtotime($data))."</li>
                <li><strong>Orario:</strong> ".date('H:i', strtotime($ora_inizio))." - ".date('H:i', strtotime($ora_fine))."</li>
            </ul>
            ".($stato === 'in attesa' ? '<p>Riceverai una email quando il tuo appuntamento sarà confermato.</p>' : '')."
            <p>Puoi modificare o cancellare l'appuntamento fino a 24 ore prima dell'orario previsto.</p>
            <p>Grazie per aver scelto BarberBook!</p>
        ";

        sendEmail($info['email'], $subject, $message);
        break;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prenota Appuntamento - BarberBook</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="booking-process">
        <h1>Prenota il tuo appuntamento</h1>

        <?php if($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="booking-steps">
            <div class="step <?php echo $step >= 1 ? 'active' : ''; ?><?php echo $step > 1 ? ' completed' : ''; ?>">1. Servizio</div>
            <div class="step <?php echo $step >= 2 ? 'active' : ''; ?><?php echo $step > 2 ? ' completed' : ''; ?>">2. Operatore</div>
            <div class="step <?php echo $step >= 3 ? 'active' : ''; ?><?php echo $step > 3 ? ' completed' : ''; ?>">3. Data</div>
            <div class="step <?php echo $step >= 4 ? 'active' : ''; ?><?php echo $step > 4 ? ' completed' : ''; ?>">4. Orario</div>
            <div class="step <?php echo $step >= 5 ? 'active' : ''; ?><?php echo $step > 5 ? ' completed' : ''; ?>">5. Conferma</div>
        </div>

        <div class="booking-content">
            <?php switch($step): case 1: // Selezione servizio ?>
                <h2>Scegli un servizio</h2>
                <div class="service-list">
                    <?php foreach($servizi as $servizio): ?>
                        <div class="service-card">
                            <h3><?php echo $servizio['nome']; ?></h3>
                            <p class="service-description"><?php echo $servizio['descrizione']; ?></p>
                            <div class="service-info">
                                <span class="service-price">€<?php echo number_format($servizio['prezzo'], 2); ?></span>
                                <span class="service-duration"><?php echo $servizio['durata_minuti']; ?> min</span>
                            </div>
                            <a href="prenota.php?step=2&servizio_id=<?php echo $servizio['id']; ?>" class="btn-primary">Seleziona</a>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php break; case 2: // Selezione operatore ?>
                <h2>Scegli un operatore per <?php echo $servizio['nome']; ?></h2>
                <div class="back-link">
                    <a href="prenota.php?step=1">&laquo; Torna alla selezione del servizio</a>
                </div>
                <div class="operator-list">
                    <?php foreach($operatori as $operatore): ?>
                        <div class="operator-card">
                            <?php if($operatore['foto']): ?>
                                <img src="uploads/operatori/<?php echo $operatore['foto']; ?>" alt="<?php echo $operatore['nome']; ?>" class="operator-photo">
                            <?php else: ?>
                                <div class="operator-photo-placeholder"></div>
                            <?php endif; ?>
                            <h3><?php echo $operatore['nome']; ?></h3>
                            <p class="operator-qualification"><?php echo $operatore['qualifica']; ?></p>
                            <a href="prenota.php?step=3&servizio_id=<?php echo $servizio_id; ?>&operatore_id=<?php echo $operatore['id']; ?>" class="btn-primary">Seleziona</a>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php break; case 3: // Selezione data ?>
                <h2>Scegli una data</h2>
                <div class="back-link">
                    <a href="prenota.php?step=2&servizio_id=<?php echo $servizio_id; ?>">&laquo; Torna alla selezione dell'operatore</a>
                </div>

                <div class="service-operator-info">
                    <p><strong>Servizio:</strong> <?php echo $servizio['nome']; ?> (<?php echo $servizio['durata_minuti']; ?> min, €<?php echo number_format($servizio['prezzo'], 2); ?>)</p>
                    <p><strong>Operatore:</strong> <?php echo $servizio['operatore_nome']; ?></p>
                </div>

                <div class="date-selector">
                    <div class="date-nav">
                        <button id="prev-month" class="btn-secondary">&lt; Mese precedente</button>
                        <h3 id="current-month"></h3>
                        <button id="next-month" class="btn-secondary">Mese successivo &gt;</button>
                    </div>
                    <div class="calendar" id="booking-calendar"></div>
                </div>
                <?php break; case 4: // Selezione orario ?>
                <h2>Scegli un orario per il <?php echo date('d/m/Y', strtotime($data)); ?></h2>
                <div class="back-link">
                    <a href="prenota.php?step=3&servizio_id=<?php echo $servizio_id; ?>&operatore_id=<?php echo $operatore_id; ?>">&laquo; Torna alla selezione della data</a>
                </div>

                <div class="service-operator-info">
                    <p><strong>Servizio:</strong> <?php echo $servizio['nome']; ?> (<?php echo $servizio['durata_minuti']; ?> min, €<?php echo number_format($servizio['prezzo'], 2); ?>)</p>
                    <p><strong>Operatore:</strong> <?php echo $servizio['operatore_nome']; ?></p>
                    <p><strong>Data:</strong> <?php echo date('d/m/Y', strtotime($data)); ?></p>
                </div>

                <div class="time-slots">
                    <?php if(count($slot_disponibili) > 0): ?>
                        <?php foreach($slot_disponibili as $slot): ?>
                            <a href="prenota.php?step=5&servizio_id=<?php echo $servizio_id; ?>&operatore_id=<?php echo $operatore_id; ?>&data=<?php echo $data; ?>&ora_inizio=<?php echo $slot['inizio']; ?>&ora_fine=<?php echo $slot['fine']; ?>" class="time-slot">
                                <?php echo $slot['inizio_formattato']; ?> - <?php echo $slot['fine_formattata']; ?>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-slots">Non ci sono orari disponibili per la data selezionata.</p>
                    <?php endif; ?>
                </div>
                <?php break; case 5: // Conferma prenotazione ?>
                <h2>Conferma appuntamento</h2>
                <div class="back-link">
                    <a href="prenota.php?step=4&servizio_id=<?php echo $servizio_id; ?>&operatore_id=<?php echo $operatore_id; ?>&data=<?php echo $data; ?>">&laquo; Torna alla selezione dell'orario</a>
                </div>

                <div class="appointment-summary">
                    <h3>Riepilogo prenotazione</h3>
                    <div class="summary-item">
                        <span class="summary-label">Barbiere:</span>
                        <span class="summary-value"><?php echo $barbiere['nome']; ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Servizio:</span>
                        <span class="summary-value"><?php echo $servizio['nome']; ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Durata:</span>
                        <span class="summary-value"><?php echo $servizio['durata_minuti']; ?> minuti</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Prezzo:</span>
                        <span class="summary-value">€<?php echo number_format($servizio['prezzo'], 2); ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Operatore:</span>
                        <span class="summary-value"><?php echo $servizio['operatore_nome']; ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Data:</span>
                        <span class="summary-value"><?php echo date('d/m/Y', strtotime($data)); ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Orario:</span>
                        <span class="summary-value"><?php echo date('H:i', strtotime($_GET['ora_inizio'])); ?> - <?php echo date('H:i', strtotime($_GET['ora_fine'])); ?></span>
                    </div>
                </div>

                <form method="post" action="prenota.php?step=6&servizio_id=<?php echo $servizio_id; ?>&operatore_id=<?php echo $operatore_id; ?>&data=<?php echo $data; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="ora_inizio" value="<?php echo $_GET['ora_inizio']; ?>">
                    <input type="hidden" name="ora_fine" value="<?php echo $_GET['ora_fine']; ?>">

                    <div class="form-group">
                        <label for="note">Note (opzionale):</label>
                        <textarea name="note" id="note" rows="3"></textarea>
                    </div>

                    <div class="confirmation-notice">
                        <p>
                            Proseguendo, accetti che la prenotazione sarà
                            <?php echo $barbiere['approvazione_automatica'] ? 'automaticamente confermata' : 'in attesa di conferma da parte del barbiere'; ?>.
                        </p>
                        <p>
                            Potrai modificare o cancellare l'appuntamento fino a 24 ore prima dell'orario previsto.
                        </p>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn-primary">Conferma prenotazione</button>
                    </div>
                </form>
                <?php break; case 6: // Prenotazione completata ?>
                <div class="booking-success">
                    <h2>Prenotazione completata!</h2>
                    <p>Il tuo appuntamento è stato <?php echo $stato === 'confermato' ? 'confermato' : 'registrato ed è in attesa di conferma'; ?>.</p>
                    <p>Abbiamo inviato una email di conferma all'indirizzo: <?php echo $info['email']; ?></p>

                    <div class="booking-success-actions">
                        <a href="dashboard.php" class="btn-primary">Torna alla dashboard</a>
                        <a href="prenota.php" class="btn-secondary">Prenota un altro appuntamento</a>
                    </div>
                </div>
                <?php break; endswitch; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="js/jquery.min.js"></script>
<script src="js/script.js"></script>

<?php if($step == 3): // Script per il calendario ?>
    <script>
        $(document).ready(function() {
            const date = new Date();
            let currentMonth = date.getMonth();
            let currentYear = date.getFullYear();

            function generateCalendar(month, year) {
                const firstDay = new Date(year, month, 1);
                const lastDay = new Date(year, month + 1, 0);
                const daysInMonth = lastDay.getDate();
                const startingDay = firstDay.getDay() || 7; // 1 (Monday) through 7 (Sunday)

                // Aggiorna l'intestazione del mese
                $("#current-month").text(new Date(year, month, 1).toLocaleDateString('it-IT', { month: 'long', year: 'numeric' }));

                let html = '<table>';
                html += '<tr>';
                ['Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'].forEach(day => {
                    html += `<th>${day}</th>`;
                });
                html += '</tr><tr>';

                // Riempie gli spazi vuoti prima del primo giorno del mese
                for (let i = 1; i < startingDay; i++) {
                    html += '<td></td>';
                }

                // Riempie il calendario con i giorni del mese
                let day = 1;
                while (day <= daysInMonth) {
                    if ((startingDay - 1 + day) % 7 === 1) {
                        html += '</tr><tr>';
                    }

                    // const currentDate = new Date(year, month, day);
                    // const formattedDate = currentDate.toISOString().split('T')[0];
                    // Modifica questa parte del codice
                    const currentDate = new Date(year, month, day);
// Usa il metodo toLocaleDateString() per evitare problemi di fuso orario
                    const formattedDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                    const now = new Date();
                    now.setHours(0, 0, 0, 0);

                    const isToday = currentDate.getTime() === now.getTime();
                    const isPast = currentDate < now;
                    const isFuture = currentDate > new Date(now.getTime() + 30 * 24 * 60 * 60 * 1000); // Oltre 30 giorni

                    let classNames = '';
                    if (isToday) classNames += ' today';
                    if (isPast) classNames += ' past';
                    if (isFuture) classNames += ' future-disabled';

                    if (isPast || isFuture) {
                        html += `<td class="${classNames}">${day}</td>`;
                    } else {
                        html += `<td class="${classNames}"><a href="prenota.php?step=4&servizio_id=<?php echo $servizio_id; ?>&operatore_id=<?php echo $operatore_id; ?>&data=${formattedDate}">${day}</a></td>`;
                    }

                    day++;
                }

                // Riempie gli spazi vuoti dopo l'ultimo giorno del mese
                const lastWeekDay = (startingDay - 1 + daysInMonth) % 7 || 7;
                if (lastWeekDay < 7) {
                    for (let i = lastWeekDay + 1; i <= 7; i++) {
                        html += '<td></td>';
                    }
                }

                html += '</tr></table>';

                $("#booking-calendar").html(html);

                // Disabilita i pulsanti di navigazione se necessario
                const today = new Date();
                const firstMonthDate = new Date(year, month, 1);
                const nextMonthDate = new Date(year, month + 1, 1);

                if (firstMonthDate <= today) {
                    $("#prev-month").prop('disabled', true).addClass('disabled');
                } else {
                    $("#prev-month").prop('disabled', false).removeClass('disabled');
                }

                if (nextMonthDate > new Date(today.getTime() + 90 * 24 * 60 * 60 * 1000)) {
                    $("#next-month").prop('disabled', true).addClass('disabled');
                } else {
                    $("#next-month").prop('disabled', false).removeClass('disabled');
                }
            }

            // Inizializza il calendario
            generateCalendar(currentMonth, currentYear);

            // Gestisce la navigazione tra i mesi
            $("#prev-month").click(function() {
                if ($(this).prop('disabled')) return;
                currentMonth--;
                if (currentMonth < 0) {
                    currentMonth = 11;
                    currentYear--;
                }
                generateCalendar(currentMonth, currentYear);
            });

            $("#next-month").click(function() {
                if ($(this).prop('disabled')) return;
                currentMonth++;
                if (currentMonth > 11) {
                    currentMonth = 0;
                    currentYear++;
                }
                generateCalendar(currentMonth, currentYear);
            });
        });
    </script>
<?php endif; ?>
</body>
</html>
