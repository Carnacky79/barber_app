<?php
// barbiere/get_operatori.php - Script AJAX per ottenere operatori per un servizio
session_start();
require_once '../config.php';

// Verifica che il barbiere sia loggato
if (!isBarbiere()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Non autorizzato']);
    exit;
}

// Verifica che sia stato fornito un ID servizio
if (!isset($_POST['servizio_id']) || !is_numeric($_POST['servizio_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID servizio non valido']);
    exit;
}

$servizio_id = intval($_POST['servizio_id']);
$conn = connectDB();

// Ottieni operatori che offrono questo servizio
$stmt = $conn->prepare("
    SELECT o.* FROM operatori o
    JOIN operatori_servizi os ON o.id = os.operatore_id
    WHERE o.barbiere_id = ? AND o.attivo = 1 AND os.servizio_id = ?
    ORDER BY o.nome
");
$stmt->bind_param("ii", $_SESSION['barbiere_id'], $servizio_id);
$stmt->execute();
$result = $stmt->get_result();

$operatori = [];
while ($row = $result->fetch_assoc()) {
    $operatori[] = [
        'id' => $row['id'],
        'nome' => $row['nome']
    ];
}

$conn->close();

// Restituisci JSON
header('Content-Type: application/json');
echo json_encode($operatori);
?>
