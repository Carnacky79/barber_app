<?php
// config.php - File di configurazione
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'password');
define('DB_NAME', 'barber_booking');
define('SITE_URL', 'http://easyapp.local.com'); // Modificare con l'URL del tuo sito

// Connessione al database
function connectDB() {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        die("Connessione fallita: " . $conn->connect_error);
    }

    $conn->set_charset("utf8");
    return $conn;
}

// Funzioni utility

// Funzione per sanitizzare gli input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Funzione per generare un token CSRF
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Funzione per verificare un token CSRF
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

// Funzione per verificare se l'utente è loggato
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Funzione per verificare se l'utente è un barbiere
function isBarbiere() {
    return isset($_SESSION['barbiere_id']);
}

// Funzione per caricare immagini
function uploadImage($file, $directory = 'uploads/') {
    // Crea la directory se non esiste
    if (!file_exists($directory)) {
        mkdir($directory, 0777, true);
    }

    $target_file = $directory . basename($file["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $newFileName = uniqid() . '.' . $imageFileType;
    $target_file = $directory . $newFileName;

    // Verifica se è un'immagine
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return ["success" => false, "message" => "Il file non è un'immagine."];
    }

    // Verifica la dimensione (limite a 5MB)
    if ($file["size"] > 5000000) {
        return ["success" => false, "message" => "Il file è troppo grande."];
    }

    // Consenti solo alcuni formati
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        return ["success" => false, "message" => "Sono permessi solo file JPG, JPEG, PNG e GIF."];
    }

    // Carica il file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ["success" => true, "filename" => $newFileName];
    } else {
        return ["success" => false, "message" => "Si è verificato un errore durante il caricamento."];
    }
}

// Funzione per inviare email
function sendEmail($to, $subject, $message) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: <noreply@tuosito.com>' . "\r\n";

    return mail($to, $subject, $message, $headers);
}

// Funzione per controllare se un orario è disponibile
/**
 * Questa funzione verifica se uno slot orario è disponibile per un appuntamento.
 * Verifica tutte le fasce orarie di apertura del barbiere.
 * 
 * @param int $barbiere_id ID del barbiere
 * @param int $operatore_id ID dell'operatore
 * @param string $data Data dell'appuntamento (formato Y-m-d)
 * @param string $ora_inizio Ora di inizio dell'appuntamento (formato H:i:s)
 * @param string $ora_fine Ora di fine dell'appuntamento (formato H:i:s)
 * @param int|null $appuntamento_id ID dell'appuntamento da escludere (in caso di modifica)
 * @return bool True se lo slot è disponibile, false altrimenti
 */
function isTimeSlotAvailable($barbiere_id, $operatore_id, $data, $ora_inizio, $ora_fine, $appuntamento_id = null) {
    $conn = connectDB();
    $giorno_settimana = strtolower(date('l', strtotime($data)));
    $giorni_it = ['monday' => 'lunedi', 'tuesday' => 'martedi', 'wednesday' => 'mercoledi', 
                 'thursday' => 'giovedi', 'friday' => 'venerdi', 'saturday' => 'sabato', 
                 'sunday' => 'domenica'];
    $giorno = $giorni_it[$giorno_settimana];
    
    // Verifica che il giorno sia aperto
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count FROM orari_apertura 
        WHERE barbiere_id = ? AND giorno = ? AND aperto = 1
    ");
    $stmt->bind_param("is", $barbiere_id, $giorno);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    
    if ($row['count'] == 0) {
        // Il barbiere è chiuso in questo giorno
        return false;
    }
    
    // Verifica se è un giorno di chiusura speciale (ferie, festività, ecc.)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count FROM giorni_chiusura 
        WHERE barbiere_id = ? AND data_chiusura = ?
    ");
    $stmt->bind_param("is", $barbiere_id, $data);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    
    if ($row['count'] > 0) {
        // È un giorno di chiusura speciale
        return false;
    }
    
    // Verifica che lo slot richiesto rientri in almeno una fascia oraria di apertura
    $slot_in_fascia = false;
    $stmt = $conn->prepare("
        SELECT ora_apertura, ora_chiusura FROM orari_apertura 
        WHERE barbiere_id = ? AND giorno = ? AND aperto = 1
    ");
    $stmt->bind_param("is", $barbiere_id, $giorno);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if ($ora_inizio >= $row['ora_apertura'] && $ora_fine <= $row['ora_chiusura']) {
            $slot_in_fascia = true;
            break;
        }
    }
    
    if (!$slot_in_fascia) {
        // Lo slot richiesto non rientra in nessuna fascia oraria di apertura
        return false;
    }
    
    // Verifica che non ci siano sovrapposizioni con altri appuntamenti dell'operatore
    $query = "
        SELECT COUNT(*) as count FROM appuntamenti 
        WHERE barbiere_id = ? AND operatore_id = ? AND data_appuntamento = ? 
        AND stato IN ('in attesa', 'confermato')
        AND ((ora_inizio < ? AND ora_fine > ?) OR (ora_inizio < ? AND ora_fine > ?) OR (ora_inizio >= ? AND ora_fine <= ?))
    ";
    $params = [$barbiere_id, $operatore_id, $data, $ora_fine, $ora_inizio, $ora_fine, $ora_inizio, $ora_inizio, $ora_fine];
    $types = "iissssssss";
    
    // Escludi l'appuntamento corrente se stiamo modificando un appuntamento esistente
    if ($appuntamento_id) {
        $query .= " AND id != ?";
        $params[] = $appuntamento_id;
        $types .= "i";
    }
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    
    // Se count è maggiore di 0, significa che c'è una sovrapposizione
    $is_available = ($row['count'] == 0);
    
    $conn->close();
    return $is_available;
}

// Funzione per sostituire i segnaposto nei messaggi
function formatMessage($template, $appuntamento) {
    $replacements = [
        '{nome}' => $appuntamento['utente_nome'] ?? 'Cliente',
        '{servizio}' => $appuntamento['servizio_nome'] ?? 'servizio',
        '{data}' => isset($appuntamento['data_appuntamento']) ? date('d/m/Y', strtotime($appuntamento['data_appuntamento'])) : 'data',
        '{ora}' => isset($appuntamento['ora_inizio']) ? date('H:i', strtotime($appuntamento['ora_inizio'])) : 'ora',
        '{operatore}' => $appuntamento['operatore_nome'] ?? 'operatore'
    ];

    return str_replace(array_keys($replacements), array_values($replacements), $template);
}

// Funzione per generare il link WhatsApp
function generateWhatsAppLink($telefono, $messaggio) {
    // Rimuovi spazi e caratteri non numerici dal telefono
    $telefono = preg_replace('/[^0-9]/', '', $telefono);

    // Aggiungi prefisso internazionale se non presente
    if (substr($telefono, 0, 1) !== '+' && substr($telefono, 0, 2) !== '00') {
        $telefono = '39' . $telefono; // Prefisso Italia
    }

    // Codifica il messaggio per URL
    $messaggio = urlencode($messaggio);

    return "https://wa.me/{$telefono}?text={$messaggio}";
}
