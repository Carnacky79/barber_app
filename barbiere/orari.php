<?php
// barbiere/orari.php - Gestione orari di apertura e giorni di chiusura (con multiple fasce orarie)
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

    // Elimina tutti gli orari esistenti
    $stmt = $conn->prepare("DELETE FROM orari_apertura WHERE barbiere_id = ?");
    $stmt->bind_param("i", $_SESSION['barbiere_id']);
    $stmt->execute();

    // Inserisci i nuovi orari
    $stmt = $conn->prepare("INSERT INTO orari_apertura (barbiere_id, giorno, aperto, ora_apertura, ora_chiusura) VALUES (?, ?, ?, ?, ?)");

    foreach ($giorni as $giorno) {
        $aperto = isset($_POST['aperto'][$giorno]) ? 1 : 0;
        
        if ($aperto && isset($_POST['fasce'][$giorno])) {
            $fasce = $_POST['fasce'][$giorno];
            
            foreach ($fasce as $fascia) {
                if (!empty($fascia['inizio']) && !empty($fascia['fine'])) {
                    $ora_apertura = $fascia['inizio'];
                    $ora_chiusura = $fascia['fine'];
                    
                    // Inserisci la fascia oraria
                    $stmt->bind_param("isiss", $_SESSION['barbiere_id'], $giorno, $aperto, $ora_apertura, $ora_chiusura);
                    $stmt->execute();
                }
            }
        } else {
            // Se il giorno è chiuso, inserisci comunque un record con aperto=0
            $ora_apertura = NULL;
            $ora_chiusura = NULL;
            $stmt->bind_param("isiss", $_SESSION['barbiere_id'], $giorno, $aperto, $ora_apertura, $ora_chiusura);
            $stmt->execute();
        }
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
$stmt = $conn->prepare("
    SELECT * FROM orari_apertura 
    WHERE barbiere_id = ? 
    ORDER BY FIELD(giorno, 'lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato', 'domenica'), ora_apertura
");
$stmt->bind_param("i", $_SESSION['barbiere_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    if (!isset($orari_apertura[$row['giorno']])) {
        $orari_apertura[$row['giorno']] = ['aperto' => $row['aperto'], 'fasce' => []];
    }
    if ($row['aperto'] && $row['ora_apertura'] && $row['ora_chiusura']) {
        $orari_apertura[$row['giorno']]['fasce'][] = [
            'ora_apertura' => $row['ora_apertura'],
            'ora_chiusura' => $row['ora_chiusura']
        ];
    }
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
                <p class="form-hint">
                    Puoi impostare più fasce orarie per ogni giorno (ad esempio: mattina e pomeriggio).
                </p>

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
                            $orario = $orari_apertura[$giorno_key] ?? ['aperto' => 0, 'fasce' => []];
                            $aperto = $orario['aperto'];
                            ?>
                            <div class="day-schedule">
                                <div class="day-header">
                                    <label>
                                        <input type="checkbox" name="aperto[<?php echo $giorno_key; ?>]" class="day-toggle" <?php echo $aperto ? 'checked' : ''; ?>>
                                        <?php echo $giorno_nome; ?>
                                    </label>
                                </div>
                                <div class="day-slots <?php echo $aperto ? '' : 'hidden'; ?>">
                                    <div class="slots-container" id="slots-<?php echo $giorno_key; ?>">
                                        <?php if (empty($orario['fasce'])): ?>
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
                                            <?php foreach ($orario['fasce'] as $i => $fascia): ?>
                                                <div class="time-slot">
                                                    <div class="slot-inputs">
                                                        <input type="time" name="fasce[<?php echo $giorno_key; ?>][<?php echo $i; ?>][inizio]" value="<?php echo substr($fascia['ora_apertura'], 0, 5); ?>">
                                                        <span>-</span>
                                                        <input type="time" name="fasce[<?php echo $giorno_key; ?>][<?php echo $i; ?>][fine]" value="<?php echo substr($fascia['ora_chiusura'], 0, 5); ?>">
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
            const slotCount = slotsContainer.find('.time-slot').length;
            
            // Impedisci la rimozione se è l'unica fascia oraria
            if (slotCount <= 1) {
                alert('Deve esserci almeno una fascia oraria per i giorni aperti.');
                return;
            }
            
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
        
        // Validazione orari sovrapposizione
        $('form').on('submit', function() {
            let valid = true;
            
            $('.day-toggle:checked').each(function() {
                const day = $(this).attr('name').match(/aperto\[(.*?)\]/)[1];
                const slots = $(`#slots-${day}`).find('.time-slot');
                const times = [];
                
                // Raccogli tutti gli orari
                slots.each(function() {
                    const startInput = $(this).find('input[name*="[inizio]"]');
                    const endInput = $(this).find('input[name*="[fine]"]');
                    const start = startInput.val();
                    const end = endInput.val();
                    
                    if (start >= end) {
                        alert(`L'ora di inizio deve essere precedente all'ora di fine (${day}).`);
                        startInput.focus();
                        valid = false;
                        return false;
                    }
                    
                    times.push({ start, end });
                });
                
                // Verifica sovrapposizioni
                for (let i = 0; i < times.length; i++) {
                    for (let j = i + 1; j < times.length; j++) {
                        if ((times[i].start < times[j].end && times[i].end > times[j].start) ||
                            (times[j].start < times[i].end && times[j].end > times[i].start)) {
                            alert(`Ci sono orari sovrapposti per ${day}.`);
                            valid = false;
                            return false;
                        }
                    }
                }
            });
            
            return valid;
        });
    });
</script>
</body>
</html>