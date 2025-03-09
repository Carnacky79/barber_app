<?php
// barbiere/operatori.php - Gestione operatori
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

// Gestione eliminazione operatore
if (isset($_GET['delete']) && $_GET['delete'] > 0) {
    $operatore_id = intval($_GET['delete']);
    $stmt = $conn->prepare("SELECT id FROM operatori WHERE id = ? AND barbiere_id = ?");
    $stmt->bind_param("ii", $operatore_id, $_SESSION['barbiere_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $stmt = $conn->prepare("DELETE FROM operatori WHERE id = ?");
        $stmt->bind_param("i", $operatore_id);
        if ($stmt->execute()) {
            $success = "Operatore eliminato con successo.";
        } else {
            $error = "Errore durante l'eliminazione dell'operatore.";
        }
    }
}

// Gestione aggiunta/modifica operatore
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['csrf_token']) && verifyCSRFToken($_POST['csrf_token'])) {
    $nome = sanitizeInput($_POST['nome']);
    $qualifica = sanitizeInput($_POST['qualifica']);
    $attivo = isset($_POST['attivo']) ? 1 : 0;
    $servizi = isset($_POST['servizi']) ? $_POST['servizi'] : [];

    if (isset($_POST['operatore_id']) && $_POST['operatore_id'] > 0) {
        // Modifica operatore esistente
        $operatore_id = intval($_POST['operatore_id']);

        // Verifica che l'operatore appartenga a questo barbiere
        $stmt = $conn->prepare("SELECT id FROM operatori WHERE id = ? AND barbiere_id = ?");
        $stmt->bind_param("ii", $operatore_id, $_SESSION['barbiere_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            // Gestione upload foto
            $foto = NULL;
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $upload = uploadImage($_FILES['foto'], '../uploads/operatori/');
                if ($upload['success']) {
                    $foto = $upload['filename'];

                    // Aggiorna l'operatore con la nuova foto
                    $stmt = $conn->prepare("UPDATE operatori SET nome = ?, qualifica = ?, foto = ?, attivo = ? WHERE id = ?");
                    $stmt->bind_param("sssii", $nome, $qualifica, $foto, $attivo, $operatore_id);
                } else {
                    $error = $upload['message'];
                }
            } else {
                // Aggiorna l'operatore senza modificare la foto
                $stmt = $conn->prepare("UPDATE operatori SET nome = ?, qualifica = ?, attivo = ? WHERE id = ?");
                $stmt->bind_param("ssii", $nome, $qualifica, $attivo, $operatore_id);
            }

            if (empty($error) && $stmt->execute()) {
                // Aggiorna i servizi dell'operatore
                $stmt = $conn->prepare("DELETE FROM operatori_servizi WHERE operatore_id = ?");
                $stmt->bind_param("i", $operatore_id);
                $stmt->execute();

                if (!empty($servizi)) {
                    $stmt = $conn->prepare("INSERT INTO operatori_servizi (operatore_id, servizio_id) VALUES (?, ?)");
                    foreach ($servizi as $servizio_id) {
                        $stmt->bind_param("ii", $operatore_id, $servizio_id);
                        $stmt->execute();
                    }
                }

                $success = "Operatore aggiornato con successo.";
            } elseif (empty($error)) {
                $error = "Errore durante l'aggiornamento dell'operatore.";
            }
        } else {
            $error = "Operatore non valido.";
        }
    } else {
        // Nuovo operatore
        // Gestione upload foto
        $foto = NULL;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $upload = uploadImage($_FILES['foto'], '../uploads/operatori/');
            if ($upload['success']) {
                $foto = $upload['filename'];
            } else {
                $error = $upload['message'];
            }
        }

        if (empty($error)) {
            $stmt = $conn->prepare("INSERT INTO operatori (barbiere_id, nome, qualifica, foto, attivo) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("isssi", $_SESSION['barbiere_id'], $nome, $qualifica, $foto, $attivo);

            if ($stmt->execute()) {
                $operatore_id = $conn->insert_id;

                // Associa i servizi all'operatore
                if (!empty($servizi)) {
                    $stmt = $conn->prepare("INSERT INTO operatori_servizi (operatore_id, servizio_id) VALUES (?, ?)");
                    foreach ($servizi as $servizio_id) {
                        $stmt->bind_param("ii", $operatore_id, $servizio_id);
                        $stmt->execute();
                    }
                }

                $success = "Operatore aggiunto con successo.";
            } else {
                $error = "Errore durante l'aggiunta dell'operatore.";
            }
        }
    }
}

// Ottieni operatore per modifica
$operatore = null;
if (isset($_GET['edit']) && $_GET['edit'] > 0) {
    $operatore_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM operatori WHERE id = ? AND barbiere_id = ?");
    $stmt->bind_param("ii", $operatore_id, $_SESSION['barbiere_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $operatore = $result->fetch_assoc();

        // Ottieni i servizi dell'operatore
        $stmt = $conn->prepare("SELECT servizio_id FROM operatori_servizi WHERE operatore_id = ?");
        $stmt->bind_param("i", $operatore_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $operatore['servizi'] = [];
        while ($row = $result->fetch_assoc()) {
            $operatore['servizi'][] = $row['servizio_id'];
        }
    }
}

// Ottieni tutti gli operatori
$stmt = $conn->prepare("SELECT * FROM operatori WHERE barbiere_id = ? ORDER BY nome");
$stmt->bind_param("i", $_SESSION['barbiere_id']);
$stmt->execute();
$result = $stmt->get_result();
$operatori = [];
while ($row = $result->fetch_assoc()) {
    $operatori[] = $row;
}

// Ottieni tutti i servizi per il form
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
    <title>Gestione Operatori - BarberBook</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="admin-section">
        <h1>Gestione Operatori</h1>

        <?php if($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="admin-content">
            <div class="form-section">
                <h2><?php echo $operatore ? 'Modifica Operatore' : 'Nuovo Operatore'; ?></h2>

                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <?php if($operatore): ?>
                        <input type="hidden" name="operatore_id" value="<?php echo $operatore['id']; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="nome">Nome:</label>
                        <input type="text" name="nome" id="nome" required value="<?php echo $operatore ? $operatore['nome'] : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="qualifica">Qualifica:</label>
                        <input type="text" name="qualifica" id="qualifica" required value="<?php echo $operatore ? $operatore['qualifica'] : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="foto">Foto (opzionale):</label>
                        <?php if($operatore && $operatore['foto']): ?>
                            <div class="current-photo">
                                <img src="../uploads/operatori/<?php echo $operatore['foto']; ?>" alt="<?php echo $operatore['nome']; ?>" width="100">
                                <p>Foto attuale</p>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="foto" id="foto" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label>Servizi offerti:</label>
                        <div class="checkbox-group">
                            <?php foreach($servizi as $servizio): ?>
                                <div class="checkbox-item">
                                    <input type="checkbox" name="servizi[]" id="servizio_<?php echo $servizio['id']; ?>" value="<?php echo $servizio['id']; ?>" <?php echo $operatore && in_array($servizio['id'], $operatore['servizi']) ? 'checked' : ''; ?>>
                                    <label for="servizio_<?php echo $servizio['id']; ?>"><?php echo $servizio['nome']; ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if(empty($servizi)): ?>
                            <p>Non ci sono servizi. <a href="servizi.php">Aggiungi servizi</a></p>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="attivo">Attivo:</label>
                        <input type="checkbox" name="attivo" id="attivo" <?php echo !$operatore || $operatore['attivo'] ? 'checked' : ''; ?>>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn-primary"><?php echo $operatore ? 'Aggiorna' : 'Aggiungi'; ?> Operatore</button>
                        <?php if($operatore): ?>
                            <a href="operatori.php" class="btn-secondary">Annulla</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="list-section">
                <h2>Operatori Attuali</h2>

                <?php if(empty($operatori)): ?>
                    <p>Non ci sono operatori. Aggiungi il primo operatore usando il form a sinistra.</p>
                <?php else: ?>
                    <div class="items-list">
                        <?php foreach($operatori as $operatore_item): ?>
                            <div class="item-card <?php echo $operatore_item['attivo'] ? 'active' : 'inactive'; ?>">
                                <div class="item-info">
                                    <div class="item-image">
                                        <?php if($operatore_item['foto']): ?>
                                            <img src="../uploads/operatori/<?php echo $operatore_item['foto']; ?>" alt="<?php echo $operatore_item['nome']; ?>">
                                        <?php else: ?>
                                            <div class="placeholder-image">
                                                <span><?php echo substr($operatore_item['nome'], 0, 1); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="item-details">
                                        <h3><?php echo $operatore_item['nome']; ?></h3>
                                        <p class="item-description"><?php echo $operatore_item['qualifica']; ?></p>
                                        <p class="item-status"><?php echo $operatore_item['attivo'] ? 'Attivo' : 'Non attivo'; ?></p>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <a href="operatori.php?edit=<?php echo $operatore_item['id']; ?>" class="btn-secondary">Modifica</a>
                                    <a href="orari_operatore.php?id=<?php echo $operatore_item['id']; ?>" class="btn-secondary">Orari</a>
                                    <a href="operatori.php?delete=<?php echo $operatore_item['id']; ?>" class="btn-danger" onclick="return confirm('Sei sicuro di voler eliminare questo operatore?');">Elimina</a>
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
