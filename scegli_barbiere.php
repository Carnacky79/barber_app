<?php
// scegli_barbiere.php - Pagina per scegliere un barbiere
session_start();
require_once 'config.php';

// Verifica che l'utente sia loggato
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$conn = connectDB();

// Ottieni i dati dell'utente
$stmt = $conn->prepare("SELECT * FROM utenti WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$filter_citta = isset($_GET['citta']) ? sanitizeInput($_GET['citta']) : $user['citta'];
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Ottieni la lista delle città
$sql = "SELECT DISTINCT citta FROM barbieri ORDER BY citta";
$result = $conn->query($sql);
$citta_list = [];
while ($row = $result->fetch_assoc()) {
    $citta_list[] = $row['citta'];
}

// Ottieni i barbieri in base ai filtri
$query = "SELECT * FROM barbieri WHERE 1=1";
$params = [];
$types = "";

if ($filter_citta) {
    $query .= " AND citta = ?";
    $params[] = $filter_citta;
    $types .= "s";
}

if ($search) {
    $query .= " AND (nome LIKE ? OR indirizzo LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

$query .= " ORDER BY nome";

$stmt = $conn->prepare($query);
if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$barbieri = [];
while ($row = $result->fetch_assoc()) {
    $barbieri[] = $row;
}

// Gestione del cambio di barbiere predefinito
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['barbiere_id']) && isset($_POST['csrf_token']) && verifyCSRFToken($_POST['csrf_token'])) {
    $barbiere_id = intval($_POST['barbiere_id']);

    // Verifica che il barbiere esista
    $stmt = $conn->prepare("SELECT id FROM barbieri WHERE id = ?");
    $stmt->bind_param("i", $barbiere_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Aggiorna il barbiere predefinito dell'utente
        $stmt = $conn->prepare("UPDATE utenti SET barbiere_default = ? WHERE id = ?");
        $stmt->bind_param("ii", $barbiere_id, $_SESSION['user_id']);
        $stmt->execute();

        $_SESSION['barbiere_default'] = $barbiere_id;
        header("Location: dashboard.php");
        exit;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scegli Barbiere - BarberBook</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="barbieri-list-page">
        <h1>Scegli il tuo barbiere</h1>

        <div class="filters">
            <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="filter-form">
                <div class="form-group">
                    <label for="citta">Città:</label>
                    <select name="citta" id="citta">
                        <option value="">Tutte le città</option>
                        <?php foreach($citta_list as $citta): ?>
                            <option value="<?php echo $citta; ?>" <?php echo $citta === $filter_citta ? 'selected' : ''; ?>><?php echo $citta; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="search">Cerca:</label>
                    <input type="text" name="search" id="search" value="<?php echo $search; ?>" placeholder="Nome o indirizzo">
                </div>

                <div class="form-group">
                    <button type="submit" class="btn-primary">Filtra</button>
                    <a href="scegli_barbiere.php" class="btn-secondary">Reset</a>
                </div>
            </form>
        </div>

        <div class="barbieri-list">
            <?php if(count($barbieri) > 0): ?>
                <?php foreach($barbieri as $barbiere): ?>
                    <div class="barbiere-card">
                        <div class="barbiere-info">
                            <?php if($barbiere['logo']): ?>
                                <img src="uploads/barbieri/<?php echo $barbiere['logo']; ?>" alt="<?php echo $barbiere['nome']; ?>" class="barbiere-logo">
                            <?php endif; ?>
                            <div>
                                <h3><?php echo $barbiere['nome']; ?></h3>
                                <p><?php echo $barbiere['indirizzo']; ?>, <?php echo $barbiere['citta']; ?> (<?php echo $barbiere['cap']; ?>)</p>
                                <p>Tel: <?php echo $barbiere['telefono']; ?></p>
                                <?php if($barbiere['sito_web']): ?>
                                    <p>Sito web: <a href="<?php echo $barbiere['sito_web']; ?>" target="_blank"><?php echo $barbiere['sito_web']; ?></a></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="barbiere-actions">
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="barbiere_id" value="<?php echo $barbiere['id']; ?>">
                                <button type="submit" class="btn-primary">Seleziona come predefinito</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results">
                    <p>Nessun barbiere trovato con i filtri selezionati.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="js/jquery.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>
