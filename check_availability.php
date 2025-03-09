<?php
// check_availability.php - Script AJAX per verificare la disponibilità di uno slot orario
session_start();
require_once 'config.php';

// Verifica che l'utente sia loggato
if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Non autorizzato']);
    exit;
}

// Verifica che siano stati forniti i parametri necessari
if (!isset($_POST['operatore_id']) || !is_numeric($_POST['operatore_id']) ||
    !isset($_POST['data']) || !isset($_POST['ora_inizio']) || !isset($_POST['durata'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Parametri non validi']);
    exit;
}

$operatore_id = intval($_POST['operatore_id']);
$data = $_POST['data'];
$ora_inizio = $_POST['ora_inizio'] . ':00';
$durata = intval($_POST['durata']);
$appuntamento_id = isset($_POST['appuntamento_id']) ? intval($_POST['appuntamento_id']) : 0;

// Calcola l'ora di fine
$ora_inizio_obj = new DateTime($ora_inizio);
$ora_fine_obj = clone $ora_inizio_obj;
$ora_fine_obj->add(new DateInterval('PT' . $durata . 'M'));
$ora_fine = $ora_fine_obj->format('H:i:s');

$conn = connectDB();

// Ottieni il barbiere associato all'operatore
$stmt = $conn->prepare("SELECT barbiere_id FROM operatori WHERE id = ?");
$stmt->bind_param("i", $operatore_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Operatore non valido', 'available' => false]);
    exit;
}

$barbiere_id = $result->fetch_assoc()['barbiere_id'];

// Verifica la disponibilità dello slot
$available = isTimeSlotAvailable($barbiere_id, $operatore_id, $data, $ora_inizio, $ora_fine, $appuntamento_id);

$conn->close();

// Restituisci JSON
header('Content-Type: application/json');
echo json_encode(['available' => $available]);
?>
