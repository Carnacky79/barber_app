<?php
// barbiere/orari.php - Gestione orari di apertura e giorni di chiusura
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

// Gestione salvataggio orari di apertura
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'save_hours' && isset($_POST['csrf_token']) && verifyCSRFToken($_POST['csrf_token'])) {
    $giorni = ['lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato', 'domenica'];

    foreach ($giorni as $giorno) {
        $aperto = isset($_POST['aperto'][$giorno]) ? 1 : 0;
        $ora_apertura = $aperto ? $_POST['ora_apertura'][$giorno] : NULL;
        $ora_chiusura = $aperto ? $_POST['ora_chiusura'][$giorno] : NULL;

        $stmt = $conn->prepare("UPDATE orari_apertura SET aperto = ?, ora_apertura = ?, ora_chiusura = ? WHERE barbiere_id = ? AND giorno = ?");
        $stmt->bind_param("isssi", $aperto, $ora_apertura, $ora_chiusura, $_SESSION['barbiere_id'], $giorno);
        $stmt->execute();
    }

    $success = "Orari di apertura aggiornati con successo.";
}

// Gestione aggiunta giorno di chiusura
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'add_closure' && isset($_POST['csrf_token']) && verifyCSRFToken($_POST['csrf_token'])) {
    $data_chiusura = $_POST['data_chiusura'];
    $descrizione = sanitizeInput($_POST['descrizione'] ?? '');

    // Verifica che la data sia valida e futura
    $data_timestamp = strtotime($data_chiusura);
    if ($data_timestamp === false) {
        $error = "Data non valida.";
    } else {
        // Verifica che non esista già un giorno di chiusura per questa data
        $stmt = $conn->prepare("SELECT id FROM giorni_chiusura WHERE barbiere_id = ? AND data_chiusura = ?");
        $stmt->bind_param("is", $_SESSION['barbiere_id'], $data_chiusura);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Esiste già un giorno di chiusura per questa data.";
        } else {
            $stmt = $conn->prepare("INSERT INTO giorni_chiusura (barbiere_id, data_chiusura, descrizione) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $_SESSION['barbiere_id'], $data_chiusura, $descrizione);

            if ($stmt->execute()) {
                $success = "Giorno di chiusura aggiunto con successo.";
            } else {
                $error = "Errore durante l'aggiunta del giorno di chiusura.";
            }
        }
    }
}

// Gestione eliminazione giorno di chiusura
if (isset($_GET['delete_closure']) && $_GET['delete_closure'] > 0) {
    $closure_id = intval($_GET['delete_closure']);
    $stmt = $conn->prepare("SELECT id FROM giorni_chiusura WHERE id = ? AND barbiere_id = ?");
    $stmt->bind_param("ii", $closure_id, $_SESSION['barbiere_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $stmt = $conn->prepare("DELETE FROM giorni_chiusura WHERE id = ?");
        $stmt->bind_param("i", $closure_id);
        if ($stmt->execute()) {
            $success = "Giorno di chiusura eliminato con successo.";
        } else {
            $error = "Errore durante l'eliminazione del giorno di chiusura.";
        }
    }
}

// Ottieni gli orari di apertura
$orari_apertura = [];
$stmt = $conn->prepare("SELECT * FROM orari_apertura WHERE barbiere_id = ? ORDER BY FIELD(giorno, 'lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato', 'domenica')");
$stmt->bind_param("i", $_SESSION['barbiere_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $orari_apertura[$row['giorno']] = $row;
}

// Ottieni i giorni di chiusura futuri
$oggi = date('Y-m-d');
$stmt = $conn->prepare("SELECT * FROM giorni_chiusura WHERE barbiere_id = ? AND data_chiusura >= ? ORDER BY data_chiusura");
$stmt->bind_param("is", $_SESSION['barbiere_id'], $oggi);
$stmt->execute();
$result = $stmt->get_result();
$giorni_chiusura = [];
while ($row = $result->fetch_assoc()) {
    $giorni_chiusura[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Orari - BarberBook</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="admin-section">
        <h1>Gestione Orari</h1>

        <?php if($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="admin-content">
            <div class="section-tabs">
                <button class="tab-btn active" data-target="orari-apertura">Orari di Apertura</button>
                <button class="tab-btn" data-target="giorni-chiusura">Giorni di Chiusura</button>
            </div>

            <div class="tab-content active" id="orari-apertura">
                <h2>Orari di Apertura</h2>

                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="save_hours">

                    <div class="opening-hours">
                        <?php
                        $giorni_it = [
                            'lunedi' => 'Lunedì',
                            'martedi' => 'Martedì',
                            'mercoledi' => 'Mercoledì',
                            'giovedi' => 'Giovedì',
                            'venerdi' => 'Venerdì',
                            'sabato' => 'Sabato',
                            'domenica' => 'Domenica'
                        ];

                        foreach ($giorni_it as $giorno_key => $giorno_nome):
                            $orario = $orari_apertura[$giorno_key] ?? null;
                            $aperto = $orario ? $orario['aperto'] : false;
                            $ora_apertura = $orario && $orario['ora_apertura'] ? $orario['ora_apertura'] : '09:00';
                            $ora_chiusura = $orario && $orario['ora_chiusura'] ? $orario['ora_chiusura'] : '19:00';
                            ?>
                            <div class="opening-day">
                                <div class="day-header">
                                    <label>
                                        <input type="checkbox" name="aperto[<?php echo $giorno_key; ?>]" class="day-toggle" <?php echo $aperto ? 'checked' : ''; ?>>
                                        <?php echo $giorno_nome; ?>
                                    </label>
                                </div>
                                <div class="day-hours <?php echo $aperto ? '' : 'hidden'; ?>">
                                    <div class="time-inputs">
                                        <div class="time-input">
                                            <label for="ora_apertura_<?php echo $giorno_key; ?>">Apertura:</label>
                                            <input type="time" name="ora_apertura[<?php echo $giorno_key; ?>]" id="ora_apertura_<?php echo $giorno_key; ?>" value="<?php echo substr($ora_apertura, 0, 5); ?>">
                                        </div>
                                        <div class="time-input">
                                            <label for="ora_chiusura_<?php echo $giorno_key; ?>">Chiusura:</label>
                                            <input type="time" name="ora_chiusura[<?php echo $giorno_key; ?>]" id="ora_chiusura_<?php echo $giorno_key; ?>" value="<?php echo substr($ora_chiusura, 0, 5); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn-primary">Salva Orari</button>
                    </div>
                </form>
            </div>

            <div class="tab-content" id="giorni-chiusura">
                <h2>Giorni di Chiusura</h2>

                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="closure-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="add_closure">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="data_chiusura">Data:</label>
                            <input type="date" name="data_chiusura" id="data_chiusura" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="descrizione">Descrizione (opzionale):</label>
                            <input type="text" name="descrizione" id="descrizione" placeholder="Es. Festività, Ferie, ecc.">
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn-primary">Aggiungi</button>
                        </div>
                    </div>
                </form>

                <div class="closure-list">
                    <h3>Giorni di chiusura futuri</h3>

                    <?php if(empty($giorni_chiusura)): ?>
                        <p>Non ci sono giorni di chiusura programmati.</p>
                    <?php else: ?>
                        <div class="items-list">
                            <?php foreach($giorni_chiusura as $chiusura): ?>
                                <div class="item-card">
                                    <div class="item-info">
                                        <div class="item-details">
                                            <h3><?php echo date('d/m/Y', strtotime($chiusura['data_chiusura'])); ?></h3>
                                            <p class="item-description"><?php echo $chiusura['descrizione'] ? $chiusura['descrizione'] : 'Nessuna descrizione'; ?></p>
                                        </div>
                                    </div>
                                    <div class="item-actions">
                                        <a href="orari.php?delete_closure=<?php echo $chiusura['id']; ?>" class="btn-danger" onclick="return confirm('Sei sicuro di voler eliminare questo giorno di chiusura?');">Elimina</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="../js/jquery.min.js"></script>
<script src="../js/script.js"></script>
<script>
    $(document).ready(function() {
        // Gestione tabs
        $('.tab-btn').click(function() {
            $('.tab-btn').removeClass('active');
            $(this).addClass('active');

            const target = $(this).data('target');
            $('.tab-content').removeClass('active');
            $('#' + target).addClass('active');
        });

        // Gestione toggle orari
        $('.day-toggle').change(function() {
            const dayHours = $(this).closest('.opening-day').find('.day-hours');
            if ($(this).is(':checked')) {
                dayHours.removeClass('hidden');
            } else {
                dayHours.addClass('hidden');
            }
        });
    });
</script>
</body>
</html>
