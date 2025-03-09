<?php
// barbiere/conferma_appuntamento.php - Conferma appuntamento
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

// Verifica che l'appuntamento appartenga al barbiere e sia in attesa
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

if ($result->num_rows != 1) {
    // L'appuntamento non esiste, non appartiene al barbiere o non è in attesa
    $_SESSION['error'] = "Appuntamento non trovato o non in attesa di conferma.";
    header("Location: appuntamenti.php");
    exit;
}

$appuntamento = $result->fetch_assoc();

// Conferma l'appuntamento
$stmt = $conn->prepare("UPDATE appuntamenti SET stato = 'confermato' WHERE id = ?");
$stmt->bind_param("i", $appuntamento_id);

if ($stmt->execute()) {
    // Invia email di conferma
    $subject = 'Conferma appuntamento';
    $message = "
        <h2>Appuntamento confermato</h2>
        <p>Gentile {$appuntamento['utente_nome']},</p>
        <p>Il tuo appuntamento è stato confermato.</p>
        <p><strong>Dettagli:</strong></p>
        <ul>
            <li><strong>Barbiere:</strong> {$appuntamento['barbiere_nome']}</li>
            <li><strong>Servizio:</strong> {$appuntamento['servizio_nome']}</li>
            <li><strong>Operatore:</strong> {$appuntamento['operatore_nome']}</li>
            <li><strong>Data:</strong> ".date('d/m/Y', strtotime($appuntamento['data_appuntamento']))."</li>
            <li><strong>Orario:</strong> ".date('H:i', strtotime($appuntamento['ora_inizio']))." - ".date('H:i', strtotime($appuntamento['ora_fine']))."</li>
        </ul>
        <p>Puoi modificare o cancellare l'appuntamento fino a 24 ore prima dell'orario previsto.</p>
        <p>Grazie per aver scelto BarberBook!</p>
    ";

    sendEmail($appuntamento['email'], $subject, $message);

    $_SESSION['success'] = "Appuntamento confermato con successo.";
} else {
    $_SESSION['error'] = "Errore durante la conferma dell'appuntamento.";
}

$conn->close();

// Redirect alla pagina degli appuntamenti
header("Location: appuntamenti.php");
exit;
?>
