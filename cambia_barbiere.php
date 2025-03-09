<?php
// cambia_barbiere.php - Cambio del barbiere predefinito
session_start();
require_once 'config.php';

// Verifica che l'utente sia loggato
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$conn = connectDB();
$error = '';
$success = '';

// Ottieni i dati dell'utente
$stmt = $conn->prepare("SELECT * FROM utenti WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Ottieni barbiere attuale
$barbiere_attuale = null;
if ($user['barbiere_default']) {
    $stmt = $conn->prepare("SELECT * FROM barbieri WHERE id = ?");
    $stmt->bind_param("i", $user['barbiere_default']);
    $stmt->execute();
    $barbiere_attuale = $stmt->get_result()->fetch_assoc();
}

// Gestione del cambio di barbiere
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

        if ($stmt->execute()) {
            $_SESSION['barbiere_default'] = $barbiere_id;
            $success = "Barbiere predefinito aggiornato con successo.";

            // Ricarica i dati
            $stmt = $conn->prepare("SELECT * FROM barbieri WHERE id = ?");
            $stmt->bind_param("i", $barbiere_id);
            $stmt->execute();
            $barbiere_attuale = $stmt->get_result()->fetch_assoc();
        } else {
            $error = "Errore durante l'aggiornamento del barbiere predefinito.";
        }
    } else {
        $error = "Barbiere non valido.";
    }
}

// Ottieni la lista dei barbieri nella stessa città dell'utente
$stmt = $conn->prepare("SELECT * FROM barbieri WHERE citta = ? ORDER BY nome");
$stmt->bind_param("s", $user['citta']);
$stmt->execute();
$result = $stmt->get_result();
$barbieri_citta = [];
while ($row = $result->fetch_assoc()) {
    $barbieri_citta[] = $row;
}

// Ottieni la lista di tutte le città con barbieri
$sql = "SELECT DISTINCT citta FROM barbieri ORDER BY citta";
$result = $conn->query($sql);
$citta_list = [];
while ($row = $result->fetch_assoc()) {
    $citta_list[] = $row['citta'];
}

// Ottieni barbieri in altre città (se l'utente cerca in altre città)
$filter_citta = isset($_GET['citta']) ? sanitizeInput($_GET['citta']) : '';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$barbieri_altre_citta = [];

if ($filter_citta || $search) {
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

    if ($filter_citta !== $user['citta']) {
        $query .= " AND citta != ?";
        $params[] = $user['citta'];
        $types .= "s";
    }

    $query .= " ORDER BY nome";

    $stmt = $conn->prepare($query);
    if (count($params) > 0) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $barbieri_altre_citta[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambia Barbiere - BarberBook</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="page-content">
        <h1>Cambia Barbiere</h1>

        <?php if($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
            <p><a href="dashboard.php" class="btn-primary">Torna alla Dashboard</a></p>
        <?php endif; ?>

        <?php if($barbiere_attuale): ?>
            <div class="current-selection">
                <h2>Il tuo barbiere attuale</h2>
                <div class="barbiere-card">
                    <div class="barbiere-info">
                        <?php if($barbiere_attuale['logo']): ?>
                            <img src="uploads/barbieri/<?php echo $barbiere_attuale['logo']; ?>" alt="<?php echo $barbiere_attuale['nome']; ?>" class="barbiere-logo">
                        <?php endif; ?>
                        <div>
                            <h3><?php echo $barbiere_attuale['nome']; ?></h3>
                            <p><?php echo $barbiere_attuale['indirizzo']; ?>, <?php echo $barbiere_attuale['citta']; ?> (<?php echo $barbiere_attuale['cap']; ?>)</p>
                            <p>Tel: <?php echo $barbiere_attuale['telefono']; ?></p>
                            <?php if($barbiere_attuale['sito_web']): ?>
                                <p>Sito web: <a href="<?php echo $barbiere_attuale['sito_web']; ?>" target="_blank"><?php echo $barbiere_attuale['sito_web']; ?></a></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="barbieri-section">
            <h2>Barbieri nella tua città (<?php echo $user['citta']; ?>)</h2>

            <?php if(empty($barbieri_citta)): ?>
                <p>Non ci sono barbieri registrati nella tua città.</p>
            <?php else: ?>
                <div class="barbieri-list">
                    <?php foreach($barbieri_citta as $barbiere): ?>
                        <div class="barbiere-card <?php echo $barbiere['id'] == $user['barbiere_default'] ? 'selected' : ''; ?>">
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
                            <?php if($barbiere['id'] != $user['barbiere_default']): ?>
                                <div class="barbiere-actions">
                                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        <input type="hidden" name="barbiere_id" value="<?php echo $barbiere['id']; ?>">
                                        <button type="submit" class="btn-primary">Seleziona come predefinito</button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <div class="barbiere-actions">
                                    <span class="current-badge">Barbiere attuale</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="other-barbieri-section">
            <h2>Cerca barbieri in altre città</h2>

            <div class="filters">
                <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="filter-form">
                    <div class="form-group">
                        <label for="citta">Città:</label>
                        <select name="citta" id="citta">
                            <option value="">Tutte le città</option>
                            <?php foreach($citta_list as $citta): ?>
                                <?php if($citta != $user['citta']): ?>
                                    <option value="<?php echo $citta; ?>" <?php echo $citta === $filter_citta ? 'selected' : ''; ?>><?php echo $citta; ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="search">Cerca:</label>
                        <input type="text" name="search" id="search" value="<?php echo $search; ?>" placeholder="Nome o indirizzo">
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn-primary">Cerca</button>
                        <a href="cambia_barbiere.php" class="btn-secondary">Reset</a>
                    </div>
                </form>
            </div>

            <?php if(!empty($barbieri_altre_citta)): ?>
                <div class="barbieri-list">
                    <?php foreach($barbieri_altre_citta as $barbiere): ?>
                        <div class="barbiere-card <?php echo $barbiere['id'] == $user['barbiere_default'] ? 'selected' : ''; ?>">
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
                            <?php if($barbiere['id'] != $user['barbiere_default']): ?>
                                <div class="barbiere-actions">
                                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        <input type="hidden" name="barbiere_id" value="<?php echo $barbiere['id']; ?>">
                                        <button type="submit" class="btn-primary">Seleziona come predefinito</button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <div class="barbiere-actions">
                                    <span class="current-badge">Barbiere attuale</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif($filter_citta || $search): ?>
                <p>Nessun barbiere trovato con i filtri selezionati.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="js/jquery.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>
