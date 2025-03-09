<?php
// barbiere/cancella_appuntamento.php - Cancellazione appuntamento
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
$conn = connectDB();

// Verifica che l'appuntamento appartenga al barbiere e sia in stato valido
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

if ($result->num_rows != 1) {
    // L'appuntamento non esiste, non appartiene al barbiere o è già completato/cancellato
    $_SESSION['error'] = "Appuntamento non trovato o non può essere cancellato.";
    header("Location: appuntamenti.php");
    exit;
}

$appuntamento = $result->fetch_assoc();

// Cancella l'appuntamento
$stmt = $conn->prepare("UPDATE appuntamenti SET stato = 'cancellato' WHERE id = ?");
$stmt->bind_param("i", $appuntamento_id);

if ($stmt->execute()) {
    // Invia email di cancellazione
    $subject = 'Appuntamento cancellato';
    $message = "
        <h2>Appuntamento cancellato</h2>
        <p>Gentile {$appuntamento['utente_nome']},</p>
        <p>Il tuo appuntamento è stato cancellato.</p>
        <p><strong>Dettagli:</strong></p>
        <ul>
            <li><strong>Barbiere:</strong> {$appuntamento['barbiere_nome']}</li>
            <li><strong>Servizio:</strong> {$appuntamento['servizio_nome']}</li>
            <li><strong>Operatore:</strong> {$appuntamento['operatore_nome']}</li>
            <li><strong>Data:</strong> ".date('d/m/Y', strtotime($appuntamento['data_appuntamento']))."</li>
            <li><strong>Orario:</strong> ".date('H:i', strtotime($appuntamento['ora_inizio']))." - ".date('H:i', strtotime($appuntamento['ora_fine']))."</li>
        </ul>
        <p>Ci scusiamo per l'inconveniente. Se desideri, puoi prenotare un nuovo appuntamento.</p>
        <p>Grazie per la comprensione.</p>
    ";

    sendEmail($appuntamento['email'], $subject, $message);

    $_SESSION['success'] = "Appuntamento cancellato con successo.";
} else {
    $_SESSION['error'] = "Errore durante la cancellazione dell'appuntamento.";
}

$conn->close();

// Redirect alla pagina degli appuntamenti
header("Location: appuntamenti.php");
exit;
?>
