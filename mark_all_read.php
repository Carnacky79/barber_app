<?php
// mark_all_read.php - Script per marcare tutte le notifiche come lette
session_start();
require_once 'config.php';

// Verifica che l'utente sia loggato
if (!isLoggedIn()) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        // Richiesta AJAX
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Non autorizzato']);
    } else {
        // Richiesta normale
        header("Location: login.php");
    }
    exit;
}

// Aggiorna tutte le notifiche non lette dell'utente
$conn = connectDB();
$stmt = $conn->prepare("UPDATE notifiche SET letto = 1 WHERE utente_id = ? AND letto = 0");
$stmt->bind_param("i", $_SESSION['user_id']);
$success = $stmt->execute();
$conn->close();

// Gestisci la risposta in base al tipo di richiesta
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    // Richiesta AJAX
    header('Content-Type: application/json');
    echo json_encode(['success' => $success]);
} else {
    // Richiesta normale
    if ($success) {
        $_SESSION['success'] = "Tutte le notifiche sono state segnate come lette.";
    } else {
        $_SESSION['error'] = "Si è verificato un errore durante l'aggiornamento delle notifiche.";
    }

    // Redirect alla pagina precedente o alla dashboard
    $referer = $_SERVER['HTTP_REFERER'] ?? 'dashboard.php';
    header("Location: $referer");
}
exit;
?>
