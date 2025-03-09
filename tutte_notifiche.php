<?php
// tutte_notifiche.php - Pagina per visualizzare tutte le notifiche dell'utente
session_start();
require_once 'config.php';
require_once 'functions/notifications.php';

// Verifica che l'utente sia loggato
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

// Ottieni tutte le notifiche dell'utente (senza limite)
$notifications = getNotifications('utente', $_SESSION['user_id'], 100);

// Quando la pagina viene caricata, segna tutte le notifiche come lette
$conn = connectDB();
$stmt = $conn->prepare("UPDATE notifiche SET letto = 1 WHERE utente_id = ? AND letto = 0");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$conn->close();

// Formatta il tipo di notifica
function formatNotificationType($type) {
    switch ($type) {
        case 'appuntamento':
            return '<span class="notification-type appointment">Appuntamento</span>';
        case 'modifica':
            return '<span class="notification-type modification">Modifica</span>';
        case 'cancellazione':
            return '<span class="notification-type cancellation">Cancellazione</span>';
        case 'sistema':
            return '<span class="notification-type system">Sistema</span>';
        default:
            return '<span class="notification-type">Notifica</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le mie Notifiche - BarberBook</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .notifications-container {
            background-color: var(--bg-color);
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .notifications-list {
            margin-top: 1.5rem;
        }

        .notification-item {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: var(--border-radius);
            background-color: var(--bg-light);
            border-left: 5px solid var(--primary-color);
            transition: all 0.3s ease;
        }

        .notification-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--box-shadow);
        }

        .notification-item.unread {
            background-color: var(--primary-light);
        }

        .notification-type {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: var(--border-radius);
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
            background-color: var(--primary-color);
            color: white;
        }

        .notification-type.appointment {
            background-color: var(--primary-color);
        }

        .notification-type.modification {
            background-color: var(--warning-color);
        }

        .notification-type.cancellation {
            background-color: var(--danger-color);
        }

        .notification-type.system {
            background-color: var(--secondary-color);
        }

        .notification-date {
            font-size: 0.875rem;
            color: var(--text-light);
            margin-top: 0.5rem;
        }

        .no-notifications {
            padding: 2rem;
            text-align: center;
            color: var(--text-light);
            font-style: italic;
        }

        .notification-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .notification-badge {
            background-color: var(--primary-color);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: var(--border-radius);
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="notifications-container">
        <div class="notification-controls">
            <h1>Le mie Notifiche</h1>
            <span class="notification-badge"><?php echo count($notifications); ?> notifiche</span>
        </div>

        <?php if (empty($notifications)): ?>
            <div class="no-notifications">
                <p>Non hai notifiche.</p>
            </div>
        <?php else: ?>
            <div class="notifications-list">
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item <?php echo $notification['letto'] == 0 ? 'unread' : ''; ?>">
                        <?php echo formatNotificationType($notification['tipo']); ?>
                        <div class="notification-content">
                            <?php echo $notification['messaggio']; ?>
                        </div>
                        <div class="notification-date">
                            <?php echo date('d/m/Y H:i', strtotime($notification['data_creazione'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="back-link" style="margin-top: 1.5rem;">
            <a href="dashboard.php">&laquo; Torna alla dashboard</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="js/jquery.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>
