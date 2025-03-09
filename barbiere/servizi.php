<?php
// barbiere/servizi.php - Gestione servizi
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

// Gestione eliminazione servizio
if (isset($_GET['delete']) && $_GET['delete'] > 0) {
    $servizio_id = intval($_GET['delete']);
    $stmt = $conn->prepare("SELECT id FROM servizi WHERE id = ? AND barbiere_id = ?");
    $stmt->bind_param("ii", $servizio_id, $_SESSION['barbiere_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $stmt = $conn->prepare("DELETE FROM servizi WHERE id = ?");
        $stmt->bind_param("i", $servizio_id);
        if ($stmt->execute()) {
            $success = "Servizio eliminato con successo.";
        } else {
            $error = "Errore durante l'eliminazione del servizio.";
        }
    }
}

// Gestione aggiunta/modifica servizio
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['csrf_token']) && verifyCSRFToken($_POST['csrf_token'])) {
    $nome = sanitizeInput($_POST['nome']);
    $descrizione = sanitizeInput($_POST['descrizione'] ?? '');
    $prezzo = floatval($_POST['prezzo']);
    $durata_minuti = intval($_POST['durata_minuti']);

    if (isset($_POST['servizio_id']) && $_POST['servizio_id'] > 0) {
        // Modifica servizio esistente
        $servizio_id = intval($_POST['servizio_id']);

        // Verifica che il servizio appartenga a questo barbiere
        $stmt = $conn->prepare("SELECT id FROM servizi WHERE id = ? AND barbiere_id = ?");
        $stmt->bind_param("ii", $servizio_id, $_SESSION['barbiere_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $stmt = $conn->prepare("UPDATE servizi SET nome = ?, descrizione = ?, prezzo = ?, durata_minuti = ? WHERE id = ?");
            $stmt->bind_param("ssdii", $nome, $descrizione, $prezzo, $durata_minuti, $servizio_id);

            if ($stmt->execute()) {
                $success = "Servizio aggiornato con successo.";
            } else {
                $error = "Errore durante l'aggiornamento del servizio.";
            }
        } else {
            $error = "Servizio non valido.";
        }
    } else {
        // Nuovo servizio
        $stmt = $conn->prepare("INSERT INTO servizi (barbiere_id, nome, descrizione, prezzo, durata_minuti) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issdi", $_SESSION['barbiere_id'], $nome, $descrizione, $prezzo, $durata_minuti);

        if ($stmt->execute()) {
            $success = "Servizio aggiunto con successo.";
        } else {
            $error = "Errore durante l'aggiunta del servizio.";
        }
    }
}

// Ottieni servizio per modifica
$servizio = null;
if (isset($_GET['edit']) && $_GET['edit'] > 0) {
    $servizio_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM servizi WHERE id = ? AND barbiere_id = ?");
    $stmt->bind_param("ii", $servizio_id, $_SESSION['barbiere_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $servizio = $result->fetch_assoc();
    }
}

// Ottieni tutti i servizi
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
    <title>Gestione Servizi - BarberBook</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="admin-section">
        <h1>Gestione Servizi</h1>

        <?php if($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="admin-content">
            <div class="form-section">
                <h2><?php echo $servizio ? 'Modifica Servizio' : 'Nuovo Servizio'; ?></h2>

                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <?php if($servizio): ?>
                        <input type="hidden" name="servizio_id" value="<?php echo $servizio['id']; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="nome">Nome:</label>
                        <input type="text" name="nome" id="nome" required value="<?php echo $servizio ? $servizio['nome'] : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="descrizione">Descrizione (opzionale):</label>
                        <textarea name="descrizione" id="descrizione" rows="3"><?php echo $servizio ? $servizio['descrizione'] : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="prezzo">Prezzo (€):</label>
                        <input type="number" step="0.01" min="0" name="prezzo" id="prezzo" required value="<?php echo $servizio ? $servizio['prezzo'] : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="durata_minuti">Durata (minuti):</label>
                        <input type="number" min="5" step="5" name="durata_minuti" id="durata_minuti" required value="<?php echo $servizio ? $servizio['durata_minuti'] : '30'; ?>">
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn-primary"><?php echo $servizio ? 'Aggiorna' : 'Aggiungi'; ?> Servizio</button>
                        <?php if($servizio): ?>
                            <a href="servizi.php" class="btn-secondary">Annulla</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="list-section">
                <h2>Servizi Attuali</h2>

                <?php if(empty($servizi)): ?>
                    <p>Non ci sono servizi. Aggiungi il primo servizio usando il form a sinistra.</p>
                <?php else: ?>
                    <div class="items-list">
                        <?php foreach($servizi as $servizio_item): ?>
                            <div class="item-card">
                                <div class="item-info">
                                    <div class="item-details">
                                        <h3><?php echo $servizio_item['nome']; ?></h3>
                                        <p class="item-description"><?php echo $servizio_item['descrizione'] ? $servizio_item['descrizione'] : 'Nessuna descrizione'; ?></p>
                                        <div class="item-meta">
                                            <span class="item-price">€<?php echo number_format($servizio_item['prezzo'], 2); ?></span>
                                            <span class="item-duration"><?php echo $servizio_item['durata_minuti']; ?> min</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <a href="servizi.php?edit=<?php echo $servizio_item['id']; ?>" class="btn-secondary">Modifica</a>
                                    <a href="servizi.php?delete=<?php echo $servizio_item['id']; ?>" class="btn-danger" onclick="return confirm('Sei sicuro di voler eliminare questo servizio?');">Elimina</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="../js/jquery.min.js"></script>
<script src="../js/script.js"></script>
</body>
</html>
