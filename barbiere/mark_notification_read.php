<?php
// barbiere/mark_notification_read.php - Script AJAX per marcare una notifica come letta
session_start();
require_once '../config.php';
require_once '../functions/notifications.php';

// Verifica che il barbiere sia loggato
if (!isBarbiere()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Non autorizzato']);
    exit;
}

// Verifica che sia stato fornito un ID
if (!isset($_POST['notification_id']) || !is_numeric($_POST['notification_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'ID notifica non valido']);
    exit;
}

$notification_id = intval($_POST['notification_id']);

// Verifica che la notifica appartenga al barbiere
$conn = connectDB();
$stmt = $conn->prepare("SELECT id FROM notifiche WHERE id = ? AND barbiere_id = ?");
$stmt->bind_param("ii", $notification_id, $_SESSION['barbiere_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Notifica non trovata o non autorizzata']);
    exit;
}

// Marca la notifica come letta
$success = markNotificationAsRead($notification_id);

// Restituisci JSON
header('Content-Type: application/json');
echo json_encode(['success' => $success]);
?>
