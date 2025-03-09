<?php
// functions/notifications.php - Funzioni per gestire le notifiche

/**
 * Crea una nuova notifica
 *
 * @param int|null $utente_id ID dell'utente (null se per barbiere)
 * @param int|null $barbiere_id ID del barbiere (null se per utente)
 * @param string $tipo Tipo di notifica ('appuntamento', 'modifica', 'cancellazione', 'sistema')
 * @param string $messaggio Contenuto della notifica
 * @return bool True se l'inserimento è riuscito, false altrimenti
 */
function createNotification($utente_id, $barbiere_id, $tipo, $messaggio) {
    $conn = connectDB();

    $stmt = $conn->prepare("
        INSERT INTO notifiche (utente_id, barbiere_id, tipo, messaggio)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("iiss", $utente_id, $barbiere_id, $tipo, $messaggio);
    $result = $stmt->execute();

    $conn->close();
    return $result;
}

/**
 * Ottiene le notifiche per un utente o barbiere
 *
 * @param string $tipo_utente 'utente' o 'barbiere'
 * @param int $id ID dell'utente o barbiere
 * @param int $limit Numero massimo di notifiche da restituire
 * @param bool $unread_only Se true, restituisce solo le notifiche non lette
 * @return array Array di notifiche
 */
function getNotifications($tipo_utente, $id, $limit = 10, $unread_only = false) {
    $conn = connectDB();

    $query = "
        SELECT * FROM notifiche
        WHERE " . ($tipo_utente === 'utente' ? 'utente_id' : 'barbiere_id') . " = ?
    ";

    if ($unread_only) {
        $query .= " AND letto = 0";
    }

    $query .= " ORDER BY data_creazione DESC LIMIT ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }

    $conn->close();
    return $notifications;
}

/**
 * Marca una notifica come letta
 *
 * @param int $notifica_id ID della notifica
 * @return bool True se l'aggiornamento è riuscito, false altrimenti
 */
function markNotificationAsRead($notifica_id) {
    $conn = connectDB();

    $stmt = $conn->prepare("UPDATE notifiche SET letto = 1 WHERE id = ?");
    $stmt->bind_param("i", $notifica_id);
    $result = $stmt->execute();

    $conn->close();
    return $result;
}

/**
 * Conta le notifiche non lette
 *
 * @param string $tipo_utente 'utente' o 'barbiere'
 * @param int $id ID dell'utente o barbiere
 * @return int Numero di notifiche non lette
 */
function countUnreadNotifications($tipo_utente, $id) {
    $conn = connectDB();

    $query = "
        SELECT COUNT(*) as count FROM notifiche
        WHERE " . ($tipo_utente === 'utente' ? 'utente_id' : 'barbiere_id') . " = ?
        AND letto = 0
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $conn->close();
    return $row['count'];
}

// Esempio di utilizzo quando viene creato un nuovo appuntamento
// Nella funzione di creazione dell'appuntamento, aggiungi:

// Notifica per il barbiere
createNotification(
    null,
    $barbiere_id,
    'appuntamento',
    "Nuovo appuntamento prenotato da {$nome_utente} per il servizio {$nome_servizio} il {$data} alle {$ora}"
);

// Notifica per l'utente
createNotification(
    $utente_id,
    null,
    'appuntamento',
    "Il tuo appuntamento per {$nome_servizio} è stato prenotato con successo per il {$data} alle {$ora}"
);

// Quando un appuntamento viene confermato dal barbiere:
createNotification(
    $utente_id,
    null,
    'appuntamento',
    "Il tuo appuntamento per {$nome_servizio} il {$data} alle {$ora} è stato confermato"
);
?>
