<?php
// barbiere/completa_appuntamento.php - Completa appuntamento
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
    SELECT a.id FROM appuntamenti a
    WHERE a.id = ? AND a.barbiere_id = ? AND a.stato IN ('in attesa', 'confermato')
");

$stmt->bind_param("ii", $appuntamento_id, $_SESSION['barbiere_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    // L'appuntamento non esiste, non appartiene al barbiere o è già completato/cancellato
    $_SESSION['error'] = "Appuntamento non trovato o non può essere completato.";
    header("Location: appuntamenti.php");
    exit;
}

// Completa l'appuntamento
$stmt = $conn->prepare("UPDATE appuntamenti SET stato = 'completato' WHERE id = ?");
$stmt->bind_param("i", $appuntamento_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Appuntamento contrassegnato come completato.";
} else {
    $_SESSION['error'] = "Errore durante l'aggiornamento dell'appuntamento.";
}

$conn->close();

// Redirect alla pagina degli appuntamenti
header("Location: appuntamenti.php");
exit;
?>
