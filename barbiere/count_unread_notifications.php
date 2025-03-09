<?php
// barbiere/count_unread_notifications.php - Script AJAX per contare le notifiche non lette
session_start();
require_once '../config.php';
require_once '../functions/notifications.php';

// Verifica che il barbiere sia loggato
if (!isBarbiere()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Non autorizzato']);
    exit;
}

// Conta le notifiche non lette
$count = countUnreadNotifications('barbiere', $_SESSION['barbiere_id']);

// Restituisci JSON
header('Content-Type: application/json');
echo json_encode(['count' => $count]);
?>
