<?php
// barbiere/orari_operatore.php - Gestione orari di disponibilità operatore
session_start();
require_once '../config.php';

// Verifica che il barbiere sia loggato
if (!isBarbiere()) {
    header("Location: ../login.php");
    exit;
}

// Verifica che sia stato specificato un operatore
if (!isset($_GET['id']) || $_GET['id'] <= 0) {
    header("Location: operatori.php");
    exit;
}

$operatore_id = intval($_GET['id']);

$conn = connectDB();
$error = '';
$success = '';

// Verifica che l'operatore appartenga a questo barbiere
$stmt = $conn->prepare("SELECT o.* FROM operatori o WHERE o.id = ? AND o.barbiere_id = ?");
$stmt->bind_param("ii", $operatore_id, $_SESSION['barbiere_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: operatori.php");
    exit;
}

$operatore = $result->fetch_assoc();

// Gestione salvataggio orari
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['csrf_token']) && verifyCSRFToken($_POST['csrf_token'])) {
    // Elimina orari esistenti
    $stmt = $conn->prepare("DELETE FROM orari_operatori WHERE operatore_id = ?");
    $stmt->bind_param("i", $operatore_id);
    $stmt->execute();

    // Giorni della settimana
    $giorni = ['lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato', 'domenica'];

    // Inserisci nuovi orari
    $stmt = $conn->prepare("INSERT INTO orari_operatori (operatore_id, giorno, ora_inizio, ora_fine) VALUES (?, ?, ?, ?)");

    foreach ($giorni as $giorno) {
        if (isset($_POST['disponibile'][$giorno]) && $_POST['disponibile'][$giorno] == 1) {
            // L'operatore è disponibile in questo giorno
            $fasce_orarie = $_POST['fasce'][$giorno] ?? [];

            foreach ($fasce_orarie as $i => $fascia) {
                if (!empty($fascia['inizio']) && !empty($fascia['fine'])) {
                    $ora_inizio = $fascia['inizio'];
                    $ora_fine = $fascia['fine'];

                    $stmt->bind_param("isss", $operatore_id, $giorno, $ora_inizio, $ora_fine);
                    $stmt->execute();
                }
            }
        }
    }

    $success = "Orari di disponibilità aggiornati con successo.";
}

// Ottieni orari attuali dell'operatore
$stmt = $conn->prepare("SELECT * FROM orari_operatori WHERE operatore_id = ? ORDER BY FIELD(giorno, 'lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato', 'domenica')");
$stmt->bind_param("i", $operatore_id);
$stmt->execute();
$result = $stmt->get_result();

$orari_operatore = [];
while ($row = $result->fetch_assoc()) {
    if (!isset($orari_operatore[$row['giorno']])) {
        $orari_operatore[$row['giorno']] = [];
    }
    $orari_operatore[$row['giorno']][] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orari Operatore - BarberBook</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="admin-section">
        <h1>Orari di Disponibilità - <?php echo $operatore['nome']; ?></h1>

        <?php if($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="operator-schedule">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo $operatore_id; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

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
                    $disponibile = isset($orari_operatore[$giorno_key]);
                    $fasce = $orari_operatore[$giorno_key] ?? [];
                    ?>
                    <div class="day-schedule">
                        <div class="day-header">
                            <label>
                                <input type="checkbox" name="disponibile[<?php echo $giorno_key; ?>]" value="1" class="day-toggle" <?php echo $disponibile ? 'checked' : ''; ?>>
                                <?php echo $giorno_nome; ?>
                            </label>
                        </div>

                        <div class="day-slots <?php echo $disponibile ? '' : 'hidden'; ?>">
                            <div class="slots-container" id="slots-<?php echo $giorno_key; ?>">
                                <?php if (empty($fasce)): ?>
                                    <!-- Fascia oraria predefinita -->
                                    <div class="time-slot">
                                        <div class="slot-inputs">
                                            <input type="time" name="fasce[<?php echo $giorno_key; ?>][0][inizio]" value="09:00">
                                            <span>-</span>
                                            <input type="time" name="fasce[<?php echo $giorno_key; ?>][0][fine]" value="19:00">
                                        </div>
                                        <button type="button" class="btn-danger remove-slot">Rimuovi</button>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($fasce as $i => $fascia): ?>
                                        <div class="time-slot">
                                            <div class="slot-inputs">
                                                <input type="time" name="fasce[<?php echo $giorno_key; ?>][<?php echo $i; ?>][inizio]" value="<?php echo substr($fascia['ora_inizio'], 0, 5); ?>">
                                                <span>-</span>
                                                <input type="time" name="fasce[<?php echo $giorno_key; ?>][<?php echo $i; ?>][fine]" value="<?php echo substr($fascia['ora_fine'], 0, 5); ?>">
                                            </div>
                                            <button type="button" class="btn-danger remove-slot">Rimuovi</button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <button type="button" class="btn-secondary add-slot" data-day="<?php echo $giorno_key; ?>">Aggiungi fascia oraria</button>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Salva Orari</button>
                    <a href="operatori.php" class="btn-secondary">Torna agli Operatori</a>
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
        // Toggle fasce orarie
        $('.day-toggle').change(function() {
            const daySlots = $(this).closest('.day-schedule').find('.day-slots');
            if ($(this).is(':checked')) {
                daySlots.removeClass('hidden');
            } else {
                daySlots.addClass('hidden');
            }
        });

        // Rimuovi fascia oraria
        $(document).on('click', '.remove-slot', function() {
            const slotsContainer = $(this).closest('.slots-container');
            $(this).closest('.time-slot').remove();

            // Aggiorna gli indici
            slotsContainer.find('.time-slot').each(function(index) {
                const day = slotsContainer.attr('id').replace('slots-', '');
                $(this).find('input').each(function() {
                    const name = $(this).attr('name');
                    const newName = name.replace(/fasce\[([^\]]+)\]\[\d+\]/, `fasce[$1][${index}]`);
                    $(this).attr('name', newName);
                });
            });
        });

        // Aggiungi fascia oraria
        $('.add-slot').click(function() {
            const day = $(this).data('day');
            const slotsContainer = $(`#slots-${day}`);
            const slotCount = slotsContainer.find('.time-slot').length;

            const newSlot = `
                    <div class="time-slot">
                        <div class="slot-inputs">
                            <input type="time" name="fasce[${day}][${slotCount}][inizio]" value="09:00">
                            <span>-</span>
                            <input type="time" name="fasce[${day}][${slotCount}][fine]" value="19:00">
                        </div>
                        <button type="button" class="btn-danger remove-slot">Rimuovi</button>
                    </div>
                `;

            slotsContainer.append(newSlot);
        });
    });
</script>
</body>
</html>
