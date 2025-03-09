<?php
// barbiere/appuntamenti.php - Gestione appuntamenti
session_start();
require_once '../config.php';

// Verifica che il barbiere sia loggato
if (!isBarbiere()) {
    header("Location: ../login.php");
    exit;
}

$conn = connectDB();

// Parametri di filtro
$data = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');
$stato = isset($_GET['stato']) ? $_GET['stato'] : null;
$operatore_id = isset($_GET['operatore_id']) ? intval($_GET['operatore_id']) : null;

// Query base
$query = "
    SELECT a.*, u.nome as utente_nome, u.telefono as utente_telefono, 
           o.nome as operatore_nome, s.nome as servizio_nome, s.prezzo, s.durata_minuti
    FROM appuntamenti a
    JOIN utenti u ON a.utente_id = u.id
    JOIN operatori o ON a.operatore_id = o.id
    JOIN servizi s ON a.servizio_id = s.id
    WHERE a.barbiere_id = ?
";

$params = [$_SESSION['barbiere_id']];
$types = "i";

// Aggiungi filtri
if ($stato) {
    $query .= " AND a.stato = ?";
    $params[] = $stato;
    $types .= "s";
} else if (!isset($_GET['stato'])) {
    // Se non è stato specificato un filtro di stato, escludi gli appuntamenti completati o cancellati
    $query .= " AND a.stato IN ('in attesa', 'confermato')";
}

if ($data) {
    $query .= " AND a.data_appuntamento = ?";
    $params[] = $data;
    $types .= "s";
}

if ($operatore_id) {
    $query .= " AND a.operatore_id = ?";
    $params[] = $operatore_id;
    $types .= "i";
}

// Ordinamento
$query .= " ORDER BY a.data_appuntamento, a.ora_inizio";

// Esegui query
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$appuntamenti = [];
while ($row = $result->fetch_assoc()) {
    $appuntamenti[] = $row;
}

// Ottieni gli operatori per il filtro
$stmt = $conn->prepare("SELECT id, nome FROM operatori WHERE barbiere_id = ? ORDER BY nome");
$stmt->bind_param("i", $_SESSION['barbiere_id']);
$stmt->execute();
$result = $stmt->get_result();
$operatori = [];
while ($row = $result->fetch_assoc()) {
    $operatori[] = $row;
}

// Gestione conferma appuntamento
if (isset($_GET['confirm']) && $_GET['confirm'] > 0) {
    $appuntamento_id = intval($_GET['confirm']);
    $stmt = $conn->prepare("
        SELECT a.*, u.email, u.nome as utente_nome, b.nome as barbiere_nome, 
               o.nome as operatore_nome, s.nome as servizio_nome
        FROM appuntamenti a
        JOIN utenti u ON a.utente_id = u.id
        JOIN barbieri b ON a.barbiere_id = b.id
        JOIN operatori o ON a.operatore_id = o.id
        JOIN servizi s ON a.servizio_id = s.id
        WHERE a.id = ? AND a.barbiere_id = ? AND a.stato = 'in attesa'
    ");
    $stmt->bind_param("ii", $appuntamento_id, $_SESSION['barbiere_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $app = $result->fetch_assoc();

        // Aggiorna lo stato dell'appuntamento
        $stmt = $conn->prepare("UPDATE appuntamenti SET stato = 'confermato' WHERE id = ?");
        $stmt->bind_param("i", $appuntamento_id);

        if ($stmt->execute()) {
            // Invia email di conferma
            $subject = 'Conferma appuntamento';
            $message = "
                <h2>Appuntamento confermato</h2>
                <p>Gentile {$app['utente_nome']},</p>
                <p>Il tuo appuntamento è stato confermato.</p>
                <p><strong>Dettagli:</strong></p>
                <ul>
                    <li><strong>Barbiere:</strong> {$app['barbiere_nome']}</li>
                    <li><strong>Servizio:</strong> {$app['servizio_nome']}</li>
                    <li><strong>Operatore:</strong> {$app['operatore_nome']}</li>
                    <li><strong>Data:</strong> ".date('d/m/Y', strtotime($app['data_appuntamento']))."</li>
                    <li><strong>Orario:</strong> ".date('H:i', strtotime($app['ora_inizio']))." - ".date('H:i', strtotime($app['ora_fine']))."</li>
                </ul>
                <p>Puoi modificare o cancellare l'appuntamento fino a 24 ore prima dell'orario previsto.</p>
                <p>Grazie per aver scelto BarberBook!</p>
            ";

            sendEmail($app['email'], $subject, $message);

            $success = "Appuntamento confermato con successo.";
        } else {
            $error = "Errore durante la conferma dell'appuntamento.";
        }
    }
}

// Gestione completamento appuntamento
if (isset($_GET['complete']) && $_GET['complete'] > 0) {
    $appuntamento_id = intval($_GET['complete']);
    $stmt = $conn->prepare("
        SELECT id FROM appuntamenti 
        WHERE id = ? AND barbiere_id = ? AND stato IN ('in attesa', 'confermato')
    ");
    $stmt->bind_param("ii", $appuntamento_id, $_SESSION['barbiere_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $stmt = $conn->prepare("UPDATE appuntamenti SET stato = 'completato' WHERE id = ?");
        $stmt->bind_param("i", $appuntamento_id);

        if ($stmt->execute()) {
            $success = "Appuntamento contrassegnato come completato.";
        } else {
            $error = "Errore durante l'aggiornamento dell'appuntamento.";
        }
    }
}

// Gestione cancellazione appuntamento
if (isset($_GET['cancel']) && $_GET['cancel'] > 0) {
    $appuntamento_id = intval($_GET['cancel']);
    $stmt = $conn->prepare("
        SELECT a.*, u.email, u.nome as utente_nome, b.nome as barbiere_nome, 
               o.nome as operatore_nome, s.nome as servizio_nome
        FROM appuntamenti a
        JOIN utenti u ON a.utente_id = u.id
        JOIN barbieri b ON a.barbiere_id = b.id
        JOIN operatori o ON a.operatore_id = o.id
        JOIN servizi s ON a.servizio_id = s.id
        WHERE a.id = ? AND a.barbiere_id = ? AND a.stato IN ('in attesa', 'confermato')
    ");
    $stmt->bind_param("ii", $appuntamento_id, $_SESSION['barbiere_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $app = $result->fetch_assoc();

        // Aggiorna lo stato dell'appuntamento
        $stmt = $conn->prepare("UPDATE appuntamenti SET stato = 'cancellato' WHERE id = ?");
        $stmt->bind_param("i", $appuntamento_id);

        if ($stmt->execute()) {
            // Invia email di cancellazione
            $subject = 'Appuntamento cancellato';
            $message = "
                <h2>Appuntamento cancellato</h2>
                <p>Gentile {$app['utente_nome']},</p>
                <p>Il tuo appuntamento è stato cancellato.</p>
                <p><strong>Dettagli:</strong></p>
                <ul>
                    <li><strong>Barbiere:</strong> {$app['barbiere_nome']}</li>
                    <li><strong>Servizio:</strong> {$app['servizio_nome']}</li>
                    <li><strong>Operatore:</strong> {$app['operatore_nome']}</li>
                    <li><strong>Data:</strong> ".date('d/m/Y', strtotime($app['data_appuntamento']))."</li>
                    <li><strong>Orario:</strong> ".date('H:i', strtotime($app['ora_inizio']))." - ".date('H:i', strtotime($app['ora_fine']))."</li>
                </ul>
                <p>Ci scusiamo per l'inconveniente. Se desideri, puoi prenotare un nuovo appuntamento.</p>
                <p>Grazie per la comprensione.</p>
            ";

            sendEmail($app['email'], $subject, $message);

            $success = "Appuntamento cancellato con successo.";
        } else {
            $error = "Errore durante la cancellazione dell'appuntamento.";
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
    <title>Gestione Appuntamenti - BarberBook</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="admin-section">
            <h1>Gestione Appuntamenti</h1>

            <?php if(isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if(isset($success)): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="filters">
                <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="filter-form">
                    <div class="form-group">
                        <label for="data">Data:</label>
                        <input type="date" name="data" id="data" value="<?php echo $data; ?>">
                    </div>

                    <div class="form-group">
                        <label for="stato">Stato:</label>
                        <select name="stato" id="stato">
                            <option value="">Tutti (attivi)</option>
                            <option value="in attesa" <?php echo $stato === 'in attesa' ? 'selected' : ''; ?>>In attesa</option>
                            <option value="confermato" <?php echo $stato === 'confermato' ? 'selected' : ''; ?>>Confermato</option>
                            <option value="completato" <?php echo $stato === 'completato' ? 'selected' : ''; ?>>Completato</option>
                            <option value="cancellato" <?php echo $stato === 'cancellato' ? 'selected' : ''; ?>>Cancellato</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="operatore_id">Operatore:</label>
                        <select name="operatore_id" id="operatore_id">
                            <option value="">Tutti</option>
                            <?php foreach($operatori as $operatore): ?>
                                <option value="<?php echo $operatore['id']; ?>" <?php echo $operatore_id === $operatore['id'] ? 'selected' : ''; ?>><?php echo $operatore['nome']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn-primary">Filtra</button>
                        <a href="appuntamenti.php" class="btn-secondary">Reset</a>
                    </div>
                </form>

                <div class="action-buttons">
                    <a href="nuovo_appuntamento.php" class="btn-primary">Nuovo Appuntamento</a>
                </div>
            </div>

            <div class="appointments-section">
                <h2>
                    <?php if($data && !$stato && !$operatore_id): ?>
                        Appuntamenti del <?php echo date('d/m/Y', strtotime($data)); ?>
                    <?php elseif($stato && !$data && !$operatore_id): ?>
                        Appuntamenti <?php echo $stato === 'in attesa' ? 'in attesa di conferma' : ($stato === 'confermato' ? 'confermati' : ($stato === 'completato' ? 'completati' : 'cancellati')); ?>
                    <?php elseif($operatore_id && !$data && !$stato): ?>
                        Appuntamenti per <?php
                            foreach($operatori as $op) {
                                if($op['id'] == $operatore_id) echo $op['nome'];
                            }
                        ?>
                    <?php else: ?>
                        Appuntamenti
                    <?php endif; ?>
                </h2>

                <?php if(empty($appuntamenti)): ?>
                    <p>Nessun appuntamento trovato con i filtri selezionati.</p>
                <?php else: ?>
                    <div class="appointments-list">
                        <?php foreach($appuntamenti as $appuntamento): ?>
                            <div class="appointment-item <?php echo $appuntamento['stato']; ?>">
                                <div class="appointment-date">
                                    <?php echo date('d/m/Y', strtotime($appuntamento['data_appuntamento'])); ?>
                                    <span class="appointment-time">
                                        <?php echo date('H:i', strtotime($appuntamento['ora_inizio'])); ?> -
                                        <?php echo date('H:i', strtotime($appuntamento['ora_fine'])); ?>
                                    </span>
                                </div>
                                <div class="appointment-info">
                                    <h3><?php echo $appuntamento['utente_nome']; ?></h3>
                                    <p><strong>Tel:</strong> <?php echo $appuntamento['utente_telefono']; ?></p>
                                    <p><strong>Servizio:</strong> <?php echo $appuntamento['servizio_nome']; ?> (€<?php echo number_format($appuntamento['prezzo'], 2); ?>)</p>
                                    <p><strong>Operatore:</strong> <?php echo $appuntamento['operatore_nome']; ?></p>
                                    <?php if (!empty($appuntamento['note'])): ?>
                                        <p><strong>Note:</strong> <?php echo $appuntamento['note']; ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="appointment-actions">
                                    <?php if ($appuntamento['stato'] === 'in attesa'): ?>
                                        <a href="appuntamenti.php?confirm=<?php echo $appuntamento['id']; ?>" class="btn-primary">Conferma</a>
                                    <?php endif; ?>
                                    <?php if ($appuntamento['stato'] === 'in attesa' || $appuntamento['stato'] === 'confermato'): ?>
                                        <a href="modifica_appuntamento.php?id=<?php echo $appuntamento['id']; ?>" class="btn-secondary">Modifica</a>
                                        <?php if (strtotime($appuntamento['data_appuntamento'] . ' ' . $appuntamento['ora_inizio']) <= time()): ?>
                                            <a href="appuntamenti.php?complete=<?php echo $appuntamento['id']; ?>" class="btn-secondary">Completa</a>
                                        <?php endif; ?>
                                        <a href="appuntamenti.php?cancel=<?php echo $appuntamento['id']; ?>" class="btn-danger" onclick="return confirm('Sei sicuro di voler cancellare questo appuntamento?');">Cancella</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="../js/jquery.min.js"></script>
    <script src="../js/script.js"></script>

    <script>
        // Script per gestire il cambio di data dinamicamente
        $(document).ready(function() {
            // Quando cambia la data, invia il form automaticamente
            $('#data').change(function() {
                $(this).closest('form').submit();
            });

            // Quando cambia lo stato, invia il form automaticamente
            $('#stato').change(function() {
                $(this).closest('form').submit();
            });

            // Quando cambia l'operatore, invia il form automaticamente
            $('#operatore_id').change(function() {
                $(this).closest('form').submit();
            });
        });
    </script>
</body>
</html>
