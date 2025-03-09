<?php
// dashboard.php - Dashboard utente
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

// Ottieni i dati del barbiere predefinito
$barbiere = null;
if ($user['barbiere_default']) {
    $stmt = $conn->prepare("SELECT * FROM barbieri WHERE id = ?");
    $stmt->bind_param("i", $user['barbiere_default']);
    $stmt->execute();
    $barbiere = $stmt->get_result()->fetch_assoc();
}

// Ottieni gli appuntamenti futuri dell'utente
$stmt = $conn->prepare("
    SELECT a.*, b.nome as barbiere_nome, o.nome as operatore_nome, s.nome as servizio_nome, s.prezzo, s.durata_minuti
    FROM appuntamenti a
    JOIN barbieri b ON a.barbiere_id = b.id
    JOIN operatori o ON a.operatore_id = o.id
    JOIN servizi s ON a.servizio_id = s.id
    WHERE a.utente_id = ? AND a.data_appuntamento >= CURDATE() AND a.stato IN ('in attesa', 'confermato')
    ORDER BY a.data_appuntamento, a.ora_inizio
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$appuntamenti = [];
while ($row = $result->fetch_assoc()) {
    $appuntamenti[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - BarberBook</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="dashboard">
        <h1>Benvenuto, <?php echo $user['nickname']; ?>!</h1>

        <?php if (!$barbiere): ?>
            <div class="alert alert-warning">
                Non hai ancora selezionato un barbiere predefinito.
                <a href="scegli_barbiere.php">Scegli ora</a>
            </div>
        <?php else: ?>
            <div class="barbiere-card">
                <h2>Il tuo barbiere</h2>
                <div class="barbiere-info">
                    <?php if ($barbiere['logo']): ?>
                        <img src="uploads/barbieri/<?php echo $barbiere['logo']; ?>" alt="<?php echo $barbiere['nome']; ?>" class="barbiere-logo">
                    <?php endif; ?>
                    <div>
                        <h3><?php echo $barbiere['nome']; ?></h3>
                        <p><?php echo $barbiere['indirizzo']; ?>, <?php echo $barbiere['citta']; ?> (<?php echo $barbiere['cap']; ?>)</p>
                        <p>Tel: <?php echo $barbiere['telefono']; ?></p>
                        <div class="barbiere-actions">
                            <a href="prenota.php" class="btn-primary">Prenota un appuntamento</a>
                            <a href="cambia_barbiere.php" class="btn-secondary">Cambia barbiere</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="appointments-section">
            <h2>I tuoi prossimi appuntamenti</h2>

            <?php if (empty($appuntamenti)): ?>
                <p>Non hai appuntamenti futuri.</p>
                <?php if ($barbiere): ?>
                    <a href="prenota.php" class="btn-primary">Prenota ora</a>
                <?php endif; ?>
            <?php else: ?>
                <div class="appointments-list">
                    <?php foreach ($appuntamenti as $appuntamento): ?>
                        <div class="appointment-card">
                            <div class="appointment-status <?php echo $appuntamento['stato']; ?>">
                                <?php echo $appuntamento['stato'] === 'in attesa' ? 'In attesa di conferma' : 'Confermato'; ?>
                            </div>
                            <div class="appointment-date">
                                <?php echo date('d/m/Y', strtotime($appuntamento['data_appuntamento'])); ?>
                                <span class="appointment-time">
                                        <?php echo date('H:i', strtotime($appuntamento['ora_inizio'])); ?> -
                                        <?php echo date('H:i', strtotime($appuntamento['ora_fine'])); ?>
                                    </span>
                            </div>
                            <div class="appointment-details">
                                <p><strong>Barbiere:</strong> <?php echo $appuntamento['barbiere_nome']; ?></p>
                                <p><strong>Servizio:</strong> <?php echo $appuntamento['servizio_nome']; ?> (€<?php echo number_format($appuntamento['prezzo'], 2); ?>)</p>
                                <p><strong>Operatore:</strong> <?php echo $appuntamento['operatore_nome']; ?></p>
                            </div>

                            <?php
                            // Verifica se l'appuntamento può essere cancellato (24 ore prima)
                            $now = new DateTime();
                            $appDate = new DateTime($appuntamento['data_appuntamento'] . ' ' . $appuntamento['ora_inizio']);
                            $diff = $now->diff($appDate);
                            $canCancel = ($diff->days > 0 || ($diff->days == 0 && $diff->h >= 24));
                            ?>

                            <?php if ($canCancel): ?>
                                <div class="appointment-actions">
                                    <a href="modifica_appuntamento.php?id=<?php echo $appuntamento['id']; ?>" class="btn-secondary">Modifica</a>
                                    <a href="cancella_appuntamento.php?id=<?php echo $appuntamento['id']; ?>" class="btn-danger" onclick="return confirm('Sei sicuro di voler cancellare questo appuntamento?');">Cancella</a>
                                </div>
                            <?php else: ?>
                                <div class="appointment-note">
                                    Non è più possibile modificare o cancellare questo appuntamento (meno di 24 ore all'inizio).
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
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
