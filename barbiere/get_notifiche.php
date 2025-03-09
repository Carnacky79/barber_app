<?php
// barbiere/get_notifiche.php - Script AJAX per ottenere le notifiche del barbiere
session_start();
require_once '../config.php';
require_once '../functions/notifications.php';

// Verifica che il barbiere sia loggato
if (!isBarbiere()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Non autorizzato']);
    exit;
}

// Ottieni le ultime 10 notifiche del barbiere
$notifications = getNotifications('barbiere', $_SESSION['barbiere_id'], 10);

// Restituisci JSON
header('Content-Type: application/json');
echo json_encode($notifications);
?>
