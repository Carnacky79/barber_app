<?php
// barbiere/impostazioni_messaggi.php - Gestione messaggi predefiniti
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

// Ottieni le impostazioni attuali dei messaggi
$stmt = $conn->prepare("SELECT messaggi_predefiniti FROM barbieri WHERE id = ?");
$stmt->bind_param("i", $_SESSION['barbiere_id']);
$stmt->execute();
$result = $stmt->get_result();
$barbiere = $result->fetch_assoc();

// Converti la stringa JSON in array (o inizializza se non esiste)
$messaggi = json_decode($barbiere['messaggi_predefiniti'] ?? '{}', true) ?: [
    'conferma' => 'Ciao {nome}, il tuo appuntamento per {servizio} è confermato per il {data} alle {ora}. Ti aspettiamo!',
    'promemoria' => 'Ciao {nome}, ti ricordiamo che hai un appuntamento per {servizio} domani alle {ora}. Ti aspettiamo!',
    'modifica' => 'Ciao {nome}, il tuo appuntamento è stato modificato. Nuovo appuntamento: {servizio} il {data} alle {ora}.',
    'cancellazione' => 'Ciao {nome}, il tuo appuntamento del {data} alle {ora} è stato cancellato. Per maggiori informazioni contattaci.'
];

// Gestione salvataggio messaggi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['csrf_token']) && verifyCSRFToken($_POST['csrf_token'])) {
    $nuovi_messaggi = [
        'conferma' => sanitizeInput($_POST['msg_conferma']),
        'promemoria' => sanitizeInput($_POST['msg_promemoria']),
        'modifica' => sanitizeInput($_POST['msg_modifica']),
        'cancellazione' => sanitizeInput($_POST['msg_cancellazione'])
    ];

    // Converti l'array in stringa JSON
    $messaggi_json = json_encode($nuovi_messaggi);

    // Aggiorna il database
    $stmt = $conn->prepare("UPDATE barbieri SET messaggi_predefiniti = ? WHERE id = ?");
    $stmt->bind_param("si", $messaggi_json, $_SESSION['barbiere_id']);

    if ($stmt->execute()) {
        $success = "Messaggi predefiniti aggiornati con successo.";
        $messaggi = $nuovi_messaggi; // Aggiorna l'array per la visualizzazione
    } else {
        $error = "Errore durante l'aggiornamento dei messaggi: " . $stmt->error;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messaggi Predefiniti - BarberBook</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .form-info {
            background-color: var(--primary-light);
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
        }

        .form-info ul {
            margin-top: 0.5rem;
            margin-left: 1.5rem;
        }

        .form-info code {
            background-color: rgba(255, 255, 255, 0.5);
            padding: 0.1rem 0.3rem;
            border-radius: 3px;
            font-family: monospace;
        }

        .preview-box {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            padding: 1rem;
            margin-top: 0.5rem;
            font-style: italic;
        }

        .settings-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .preview-toggle {
            cursor: pointer;
            color: var(--primary-color);
            text-decoration: underline;
            display: inline-block;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="admin-section">
        <h1>Messaggi Predefiniti</h1>

        <?php if($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="form-info">
            <p><strong>Personalizza i messaggi</strong> che verranno mostrati quando invii notifiche WhatsApp ai clienti. Puoi utilizzare i seguenti segnaposto:</p>
            <ul>
                <li><code>{nome}</code> - Nome del cliente</li>
                <li><code>{servizio}</code> - Nome del servizio</li>
                <li><code>{data}</code> - Data dell'appuntamento</li>
                <li><code>{ora}</code> - Orario dell'appuntamento</li>
                <li><code>{operatore}</code> - Nome dell'operatore</li>
            </ul>
        </div>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

            <div class="form-section">
                <h2>Messaggi WhatsApp</h2>

                <div class="form-group">
                    <label for="msg_conferma">Messaggio di conferma:</label>
                    <textarea name="msg_conferma" id="msg_conferma" rows="3" class="w-full"><?php echo $messaggi['conferma']; ?></textarea>
                    <a class="preview-toggle" onclick="togglePreview('preview-conferma', 'msg_conferma')">Mostra anteprima</a>
                    <div id="preview-conferma" class="preview-box" style="display: none;"></div>
                </div>

                <div class="form-group">
                    <label for="msg_promemoria">Messaggio di promemoria:</label>
                    <textarea name="msg_promemoria" id="msg_promemoria" rows="3" class="w-full"><?php echo $messaggi['promemoria']; ?></textarea>
                    <a class="preview-toggle" onclick="togglePreview('preview-promemoria', 'msg_promemoria')">Mostra anteprima</a>
                    <div id="preview-promemoria" class="preview-box" style="display: none;"></div>
                </div>

                <div class="form-group">
                    <label for="msg_modifica">Messaggio di modifica appuntamento:</label>
                    <textarea name="msg_modifica" id="msg_modifica" rows="3" class="w-full"><?php echo $messaggi['modifica']; ?></textarea>
                    <a class="preview-toggle" onclick="togglePreview('preview-modifica', 'msg_modifica')">Mostra anteprima</a>
                    <div id="preview-modifica" class="preview-box" style="display: none;"></div>
                </div>

                <div class="form-group">
                    <label for="msg_cancellazione">Messaggio di cancellazione:</label>
                    <textarea name="msg_cancellazione" id="msg_cancellazione" rows="3" class="w-full"><?php echo $messaggi['cancellazione']; ?></textarea>
                    <a class="preview-toggle" onclick="togglePreview('preview-cancellazione', 'msg_cancellazione')">Mostra anteprima</a>
                    <div id="preview-cancellazione" class="preview-box" style="display: none;"></div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Salva Messaggi</button>
                <a href="impostazioni.php" class="btn-secondary">Torna alle Impostazioni</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="../js/jquery.min.js"></script>
<script src="../js/script.js"></script>
<script>
    // Funzione per visualizzare l'anteprima del messaggio
    function togglePreview(previewId, textareaId) {
        const preview = document.getElementById(previewId);
        const textarea = document.getElementById(textareaId);
        const isVisible = preview.style.display !== 'none';

        if (isVisible) {
            preview.style.display = 'none';
            document.querySelector(`[onclick="togglePreview('${previewId}', '${textareaId}')"]`).textContent = 'Mostra anteprima';
        } else {
            // Sostituisci i segnaposto con esempio di dati
            let previewText = textarea.value
                .replace(/{nome}/g, 'Mario Rossi')
                .replace(/{servizio}/g, 'Taglio e Barba')
                .replace(/{data}/g, '15/06/2024')
                .replace(/{ora}/g, '15:30')
                .replace(/{operatore}/g, 'Luca');

            preview.textContent = previewText;
            preview.style.display = 'block';
            document.querySelector(`[onclick="togglePreview('${previewId}', '${textareaId}')"]`).textContent = 'Nascondi anteprima';
        }
    }
</script>
</body>
</html>
