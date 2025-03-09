<?php
// count_unread_notifications.php - Script AJAX per contare le notifiche non lette
session_start();
require_once 'config.php';
require_once 'functions/notifications.php';

// Verifica che l'utente sia loggato
if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Non autorizzato']);
    exit;
}

// Conta le notifiche non lette
$count = countUnreadNotifications('utente', $_SESSION['user_id']);

// Restituisci JSON
header('Content-Type: application/json');
echo json_encode(['count' => $count]);
?>
