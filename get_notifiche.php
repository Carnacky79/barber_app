<?php
// get_notifiche.php - Script AJAX per ottenere le notifiche dell'utente
session_start();
require_once 'config.php';
require_once 'functions/notifications.php';

// Verifica che l'utente sia loggato
if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Non autorizzato']);
    exit;
}

// Ottieni le ultime 10 notifiche dell'utente
$notifications = getNotifications('utente', $_SESSION['user_id'], 10);

// Restituisci JSON
header('Content-Type: application/json');
echo json_encode($notifications);
?>
