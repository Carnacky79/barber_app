<?php
// barbiere/calendario.php - Vista calendario settimanale degli appuntamenti
session_start();
require_once '../config.php';

// Verifica che il barbiere sia loggato
if (!isBarbiere()) {
    header("Location: ../login.php");
    exit;
}

$conn = connectDB();

// Gestione parametri data
$today = date('Y-m-d');
$current_day = isset($_GET['data']) ? $_GET['data'] : $today;

// Calcola la data di inizio settimana (lunedì) e fine settimana (domenica)
$timestamp = strtotime($current_day);
$day_of_week = date('N', $timestamp); // 1 (lunedì) a 7 (domenica)
$start_date = date('Y-m-d', strtotime("-" . ($day_of_week - 1) . " days", $timestamp));
$end_date = date('Y-m-d', strtotime("+" . (7 - $day_of_week) . " days", $timestamp));

// Calcola la settimana precedente e successiva
$prev_week = date('Y-m-d', strtotime("-7 days", strtotime($start_date)));
$next_week = date('Y-m-d', strtotime("+7 days", strtotime($start_date)));

// Ottieni gli operatori per il filtro
$stmt = $conn->prepare("SELECT id, nome FROM operatori WHERE barbiere_id = ? AND attivo = 1 ORDER BY nome");
$stmt->bind_param("i", $_SESSION['barbiere_id']);
$stmt->execute();
$result = $stmt->get_result();
$operatori = [];
while ($row = $result->fetch_assoc()) {
    $operatori[] = $row;
}

// Filtraggio per operatore
$operatore_id = isset($_GET['operatore_id']) ? intval($_GET['operatore_id']) : 0;

// Query per ottenere gli appuntamenti della settimana
$query = "
    SELECT a.*, u.nome as utente_nome, u.telefono as utente_telefono, 
           o.nome as operatore_nome, s.nome as servizio_nome, s.prezzo, s.durata_minuti,
           s.colore
    FROM appuntamenti a
    JOIN utenti u ON a.utente_id = u.id
    JOIN operatori o ON a.operatore_id = o.id
    JOIN servizi s ON a.servizio_id = s.id
    WHERE a.barbiere_id = ? 
    AND a.data_appuntamento BETWEEN ? AND ? 
    AND a.stato IN ('in attesa', 'confermato')
";

$params = [$_SESSION['barbiere_id'], $start_date, $end_date];
$types = "iss";

if ($operatore_id > 0) {
    $query .= " AND a.operatore_id = ?";
    $params[] = $operatore_id;
    $types .= "i";
}

$query .= " ORDER BY a.data_appuntamento, a.ora_inizio";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$appuntamenti = [];
while ($row = $result->fetch_assoc()) {
    $date = $row['data_appuntamento'];
    if (!isset($appuntamenti[$date])) {
        $appuntamenti[$date] = [];
    }
    $appuntamenti[$date][] = $row;
}

// Ottieni gli orari di apertura
$stmt = $conn->prepare("
    SELECT * FROM orari_apertura 
    WHERE barbiere_id = ? 
    ORDER BY FIELD(giorno, 'lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato', 'domenica')
");
$stmt->bind_param("i", $_SESSION['barbiere_id']);
$stmt->execute();
$result = $stmt->get_result();

$orari_apertura = [];
while ($row = $result->fetch_assoc()) {
    $orari_apertura[$row['giorno']] = $row;
}

// Ottieni i giorni di chiusura
$stmt = $conn->prepare("
    SELECT * FROM giorni_chiusura 
    WHERE barbiere_id = ? AND data_chiusura BETWEEN ? AND ?
");
$stmt->bind_param("iss", $_SESSION['barbiere_id'], $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

$giorni_chiusura = [];
while ($row = $result->fetch_assoc()) {
    $giorni_chiusura[$row['data_chiusura']] = $row;
}

$conn->close();

// Mappa dei giorni della settimana
$giorni_settimana = [
    1 => 'lunedi',
    2 => 'martedi',
    3 => 'mercoledi',
    4 => 'giovedi',
    5 => 'venerdi',
    6 => 'sabato',
    7 => 'domenica'
];

$giorni_settimana_it = [
    1 => 'Lunedì',
    2 => 'Martedì',
    3 => 'Mercoledì',
    4 => 'Giovedì',
    5 => 'Venerdì',
    6 => 'Sabato',
    7 => 'Domenica'
];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario Appuntamenti - BarberBook</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .calendar-container {
            background-color: var(--bg-color);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 1rem;
            margin-bottom: 2rem;
        }
        
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .calendar-nav {
            display: flex;
            gap: 0.5rem;
        }
        
        .calendar-title {
            text-align: center;
            margin: 0;
            font-size: 1.2rem;
        }
        
        .week-calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .calendar-day {
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            min-height: 150px;
            padding: 0.5rem;
            background-color: var(--bg-light);
        }
        
        .calendar-day.today {
            border: 2px solid var(--primary-color);
        }
        
        .calendar-day.closed {
            background-color: #f8f8f8;
            color: var(--text-light);
        }
        
        .day-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .day-name {
            font-weight: bold;
            font-size: 0.9rem;
        }
        
        .day-date {
            font-size: 0.8rem;
            color: var(--text-light);
        }
        
        .day-appointments {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .appointment-item {
            padding: 0.5rem;
            margin-bottom: 0.5rem;
            border-radius: var(--border-radius);
            font-size: 0.8rem;
            background-color: #e6f0ff;
            border-left: 3px solid var(--primary-color);
        }
        
        .appointment-item.in-attesa {
            background-color: #fff3cd;
            border-left-color: var(--warning-color);
        }
        
        .appointment-time {
            font-weight: bold;
            margin-bottom: 0.25rem;
        }
        
        .appointment-info {
            margin-bottom: 0.25rem;
        }
        
        .appointment-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.25rem;
        }
        
        .btn-xs {
            padding: 0.2rem 0.4rem;
            font-size: 0.7rem;
        }
        
        .closed-message {
            text-align: center;
            font-style: italic;
            color: var(--text-light);
            margin-top: 1rem;
        }
        
        .hours-info {
            font-size: 0.8rem;
            color: var(--text-light);
            text-align: center;
            margin-bottom: 0.5rem;
        }
        
        .add-appointment-btn {
            display: block;
            text-align: center;
            margin-top: 0.5rem;
            font-size: 0.8rem;
        }
        
        /* Media Query per vista mobile */
        @media (max-width: 768px) {
            .week-calendar {
                display: block;
                margin-bottom: 0;
            }
            
            .calendar-day {
                margin-bottom: 1rem;
                min-height: auto;
            }
            
            .day-header {
                flex-direction: row;
                justify-content: space-between;
            }
            
            .day-appointments {
                max-height: none;
                overflow-y: visible;
            }
            
            .calendar-filter {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .calendar-filter .form-group {
                margin-bottom: 0.5rem;
            }
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="admin-section">
        <h1>Calendario Appuntamenti</h1>
        
        <div class="calendar-container">
            <div class="calendar-header">
                <div class="calendar-nav">
                    <a href="?data=<?php echo $prev_week; ?><?php echo $operatore_id ? '&operatore_id='.$operatore_id : ''; ?>" class="btn-secondary">
                        <i class="fas fa-chevron-left"></i> Settimana precedente
                    </a>
                    <a href="?data=<?php echo $today; ?><?php echo $operatore_id ? '&operatore_id='.$operatore_id : ''; ?>" class="btn-secondary">
                        Oggi
                    </a>
                    <a href="?data=<?php echo $next_week; ?><?php echo $operatore_id ? '&operatore_id='.$operatore_id : ''; ?>" class="btn-secondary">
                        Settimana successiva <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
                
                <div class="calendar-filter">
                    <form method="get" action="" class="filter-form">
                        <input type="hidden" name="data" value="<?php echo $current_day; ?>">
                        <div class="form-group">
                            <select name="operatore_id" id="operatore_id" onchange="this.form.submit()">
                                <option value="0">Tutti gli operatori</option>
                                <?php foreach ($operatori as $operatore): ?>
                                    <option value="<?php echo $operatore['id']; ?>" <?php echo $operatore_id == $operatore['id'] ? 'selected' : ''; ?>>
                                        <?php echo $operatore['nome']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
            
            <h2 class="calendar-title">
                Settimana dal <?php echo date('d/m/Y', strtotime($start_date)); ?> 
                al <?php echo date('d/m/Y', strtotime($end_date)); ?>
            </h2>
            
            <div class="week-calendar">
                <?php 
                $current_date = $start_date;
                for ($day = 1; $day <= 7; $day++): 
                    $timestamp = strtotime($current_date);
                    $is_today = $current_date == $today;
                    $giorno_settimana = $giorni_settimana[$day];
                    $is_closed = isset($giorni_chiusura[$current_date]) || 
                                 (isset($orari_apertura[$giorno_settimana]) && !$orari_apertura[$giorno_settimana]['aperto']);
                ?>
                
                <div class="calendar-day <?php echo $is_today ? 'today' : ''; ?> <?php echo $is_closed ? 'closed' : ''; ?>">
                    <div class="day-header">
                        <div class="day-name"><?php echo $giorni_settimana_it[$day]; ?></div>
                        <div class="day-date"><?php echo date('d/m', $timestamp); ?></div>
                    </div>
                    
                    <?php if ($is_closed): ?>
                        <?php if (isset($giorni_chiusura[$current_date])): ?>
                            <div class="closed-message">
                                Chiuso: <?php echo $giorni_chiusura[$current_date]['descrizione']; ?>
                            </div>
                        <?php else: ?>
                            <div class="closed-message">Chiuso</div>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php if (isset($orari_apertura[$giorno_settimana]) && 
                                 $orari_apertura[$giorno_settimana]['aperto'] && 
                                 !empty($orari_apertura[$giorno_settimana]['fasce'])): ?>
                            <div class="hours-info">
                                <?php foreach ($orari_apertura[$giorno_settimana]['fasce'] as $i => $fascia): ?>
                                    <?php echo substr($fascia['ora_apertura'], 0, 5); ?> - 
                                    <?php echo substr($fascia['ora_chiusura'], 0, 5); ?>
                                    <?php if ($i < count($orari_apertura[$giorno_settimana]['fasce']) - 1): ?>
                                        <br>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="day-appointments">
                            <?php if (isset($appuntamenti[$current_date])): ?>
                                <?php foreach ($appuntamenti[$current_date] as $appuntamento): ?>
                                    <div class="appointment-item <?php echo $appuntamento['stato']; ?>" 
                                         style="border-left-color: <?php echo !empty($appuntamento['colore']) ? $appuntamento['colore'] : 'var(--primary-color)'; ?>">
                                        <div class="appointment-time">
                                            <?php echo date('H:i', strtotime($appuntamento['ora_inizio'])); ?> - 
                                            <?php echo date('H:i', strtotime($appuntamento['ora_fine'])); ?>
                                        </div>
                                        <div class="appointment-info">
                                            <strong><?php echo $appuntamento['utente_nome']; ?></strong><br>
                                            <?php echo $appuntamento['servizio_nome']; ?><br>
                                            <?php if ($operatore_id == 0): ?>
                                                <small>Op: <?php echo $appuntamento['operatore_nome']; ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="appointment-actions">
                                            <?php if ($appuntamento['stato'] === 'in attesa'): ?>
                                                <a href="conferma_appuntamento.php?id=<?php echo $appuntamento['id']; ?>" class="btn-primary btn-xs">✓</a>
                                            <?php endif; ?>
                                            <a href="modifica_appuntamento.php?id=<?php echo $appuntamento['id']; ?>" class="btn-secondary btn-xs">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-appointments">Nessun appuntamento</div>
                            <?php endif; ?>
                        </div>
                        
                        <a href="nuovo_appuntamento.php?data=<?php echo $current_date; ?>" class="add-appointment-btn">
                            <i class="fas fa-plus"></i> Aggiungi
                        </a>
                    <?php endif; ?>
                </div>
                
                <?php 
                $current_date = date('Y-m-d', strtotime("+1 day", strtotime($current_date)));
                endfor; 
                ?>
            </div>
            
            <div class="calendar-legend">
                <h3>Legenda servizi:</h3>
                <div class="legend-items">
                    <?php
                    // Ottieni i colori dei servizi per la legenda
                    $conn = connectDB();
                    $stmt = $conn->prepare("SELECT id, nome, colore FROM servizi WHERE barbiere_id = ? ORDER BY nome");
                    $stmt->bind_param("i", $_SESSION['barbiere_id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($servizio = $result->fetch_assoc()): ?>
                        <div class="legend-item">
                            <div class="legend-color" style="border-left-color: <?php echo $servizio['colore']; ?>"></div>
                            <span><?php echo $servizio['nome']; ?></span>
                        </div>
                    <?php endwhile;
                    $conn->close();
                    ?>
                </div>
            </div>
            
            <div class="calendar-footer">
                <a href="appuntamenti.php" class="btn-secondary">
                    <i class="fas fa-list"></i> Vista elenco
                </a>
                <a href="nuovo_appuntamento.php" class="btn-primary">
                    <i class="fas fa-plus"></i> Nuovo appuntamento
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="../js/jquery.min.js"></script>
<script src="../js/script.js"></script>
</body>
</html>