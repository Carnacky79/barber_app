<?php
// barbiere/dashboard.php - Dashboard barbiere
session_start();
require_once '../config.php';

// Verifica che il barbiere sia loggato
if (!isBarbiere()) {
    header("Location: ../login.php");
    exit;
}

$conn = connectDB();

// Ottieni i dati del barbiere
$stmt = $conn->prepare("SELECT * FROM barbieri WHERE id = ?");
$stmt->bind_param("i", $_SESSION['barbiere_id']);
$stmt->execute();
$barbiere = $stmt->get_result()->fetch_assoc();

// Ottieni il numero di operatori
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM operatori WHERE barbiere_id = ?");
$stmt->bind_param("i", $_SESSION['barbiere_id']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_operatori = $row['total'];

// Ottieni il numero di servizi
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM servizi WHERE barbiere_id = ?");
$stmt->bind_param("i", $_SESSION['barbiere_id']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_servizi = $row['total'];

// Ottieni gli appuntamenti di oggi
$today = date('Y-m-d');
$stmt = $conn->prepare("
    SELECT a.*, u.nome as utente_nome, u.telefono as utente_telefono, 
           o.nome as operatore_nome, s.nome as servizio_nome, s.prezzo, s.durata_minuti
    FROM appuntamenti a
    JOIN utenti u ON a.utente_id = u.id
    JOIN operatori o ON a.operatore_id = o.id
    JOIN servizi s ON a.servizio_id = s.id
    WHERE a.barbiere_id = ? AND a.data_appuntamento = ? AND a.stato IN ('in attesa', 'confermato')
    ORDER BY a.ora_inizio
");
$stmt->bind_param("is", $_SESSION['barbiere_id'], $today);
$stmt->execute();
$result = $stmt->get_result();
$appuntamenti_oggi = [];
while ($row = $result->fetch_assoc()) {
    $appuntamenti_oggi[] = $row;
}

// Ottieni gli appuntamenti in attesa di conferma
$stmt = $conn->prepare("
    SELECT a.*, u.nome as utente_nome, u.telefono as utente_telefono, 
           o.nome as operatore_nome, s.nome as servizio_nome, s.prezzo, s.durata_minuti
    FROM appuntamenti a
    JOIN utenti u ON a.utente_id = u.id
    JOIN operatori o ON a.operatore_id = o.id
    JOIN servizi s ON a.servizio_id = s.id
    WHERE a.barbiere_id = ? AND a.stato = 'in attesa'
    ORDER BY a.data_appuntamento, a.ora_inizio
");
$stmt->bind_param("i", $_SESSION['barbiere_id']);
$stmt->execute();
$result = $stmt->get_result();
$appuntamenti_in_attesa = [];
while ($row = $result->fetch_assoc()) {
    $appuntamenti_in_attesa[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Barbiere - BarberBook</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="dashboard-barbiere">
        <h1>Dashboard di <?php echo $barbiere['nome']; ?></h1>

        <div class="stats-cards">
            <div class="stat-card">
                <h3>Operatori</h3>
                <div class="stat-value"><?php echo $total_operatori; ?></div>
                <a href="operatori.php" class="btn-secondary">Gestisci</a>
            </div>
            <div class="stat-card">
                <h3>Servizi</h3>
                <div class="stat-value"><?php echo $total_servizi; ?></div>
                <a href="servizi.php" class="btn-secondary">Gestisci</a>
            </div>
            <div class="stat-card">
                <h3>Appuntamenti oggi</h3>
                <div class="stat-value"><?php echo count($appuntamenti_oggi); ?></div>
                <a href="appuntamenti.php?data=<?php echo $today; ?>" class="btn-secondary">Vedi tutti</a>
            </div>
            <div class="stat-card">
                <h3>Da confermare</h3>
                <div class="stat-value"><?php echo count($appuntamenti_in_attesa); ?></div>
                <a href="appuntamenti.php?stato=in attesa" class="btn-secondary">Vedi tutti</a>
            </div>
        </div>

        <div class="quick-actions">
            <h2>Azioni rapide</h2>
            <div class="action-buttons">
                <a href="nuovo_appuntamento.php" class="btn-primary">Nuovo appuntamento</a>
                <a href="orari.php" class="btn-secondary">Gestisci orari</a>
                <a href="impostazioni.php" class="btn-secondary">Impostazioni</a>
            </div>
        </div>

        <div class="today-appointments">
            <h2>Appuntamenti di oggi (<?php echo date('d/m/Y'); ?>)</h2>

            <?php if (empty($appuntamenti_oggi)): ?>
                <p>Non ci sono appuntamenti per oggi.</p>
            <?php else: ?>
                <div class="appointments-timeline">
                    <?php foreach ($appuntamenti_oggi as $appuntamento): ?>
                        <div class="appointment-item <?php echo $appuntamento['stato']; ?>">
                            <div class="appointment-time">
                                <?php echo date('H:i', strtotime($appuntamento['ora_inizio'])); ?> -
                                <?php echo date('H:i', strtotime($appuntamento['ora_fine'])); ?>
                            </div>
                            <div class="appointment-info">
                                <h3><?php echo $appuntamento['utente_nome']; ?></h3>
                                <p><strong>Tel:</strong> <?php echo $appuntamento['utente_telefono']; ?></p>
                                <p><strong>Servizio:</strong> <?php echo $appuntamento['servizio_nome']; ?> (€<?php echo number_format($appuntamento['prezzo'], 2); ?>)</p>
                                <p><strong>Operatore:</strong> <?php echo $appuntamento['operatore_nome']; ?></p>
                                <?php if (!empty($appuntamento['note'])): ?>
                                    <p><strong>Note:</strong> <?php echo $appuntamento['note']; ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="appointment-actions">
                                <?php if ($appuntamento['stato'] === 'in attesa'): ?>
                                    <a href="conferma_appuntamento.php?id=<?php echo $appuntamento['id']; ?>" class="btn-primary">Conferma</a>
                                <?php endif; ?>
                                <a href="modifica_appuntamento.php?id=<?php echo $appuntamento['id']; ?>" class="btn-secondary">Modifica</a>
                                <a href="completa_appuntamento.php?id=<?php echo $appuntamento['id']; ?>" class="btn-secondary">Completa</a>
                                <a href="cancella_appuntamento.php?id=<?php echo $appuntamento['id']; ?>" class="btn-danger" onclick="return confirm('Sei sicuro di voler cancellare questo appuntamento?');">Cancella</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="pending-appointments">
            <h2>Appuntamenti in attesa di conferma</h2>

            <?php if (empty($appuntamenti_in_attesa)): ?>
                <p>Non ci sono appuntamenti in attesa di conferma.</p>
            <?php else: ?>
                <div class="appointments-list">
                    <?php foreach ($appuntamenti_in_attesa as $appuntamento): ?>
                        <div class="appointment-item in-attesa">
                            <div class="appointment-date">
                                <?php echo date('d/m/Y', strtotime($appuntamento['data_appuntamento'])); ?>
                                <span class="appointment-time">
                                        <?php echo date('H:i', strtotime($appuntamento['ora_inizio'])); ?> -
                                        <?php echo date('H:i', strtotime($appuntamento['ora_fine'])); ?>
                                    </span>
                            </div>
                            <div class="appointment-info">
                                <h3><?php echo $appuntamento['utente_nome']; ?></h3>
                                <p><strong>Tel:</strong> <?php echo $appuntamento['utente_telefono']; ?></p>
                                <p><strong>Servizio:</strong> <?php echo $appuntamento['servizio_nome']; ?> (€<?php echo number_format($appuntamento['prezzo'], 2); ?>)</p>
                                <p><strong>Operatore:</strong> <?php echo $appuntamento['operatore_nome']; ?></p>
                                <?php if (!empty($appuntamento['note'])): ?>
                                    <p><strong>Note:</strong> <?php echo $appuntamento['note']; ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="appointment-actions">
                                <a href="conferma_appuntamento.php?id=<?php echo $appuntamento['id']; ?>" class="btn-primary">Conferma</a>
                                <a href="cancella_appuntamento.php?id=<?php echo $appuntamento['id']; ?>" class="btn-danger" onclick="return confirm('Sei sicuro di voler cancellare questo appuntamento?');">Rifiuta</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="../js/jquery.min.js"></script>
<script src="../js/script.js"></script>
</body>
</html>
