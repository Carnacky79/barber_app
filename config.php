<?php
// config.php - File di configurazione
define('DB_SERVER', 'sql.easybarber.it');
define('DB_USERNAME', 'easybarb97941');
define('DB_PASSWORD', 'easy51841');
define('DB_NAME', 'easybarb97941');
define('SITE_URL', 'http://easybarber.it'); // Modificare con l'URL del tuo sito

//define('DB_SERVER', 'localhost');
//define('DB_USERNAME', 'root');
//define('DB_PASSWORD', 'password');
//define('DB_NAME', 'barber_booking');
//define('SITE_URL', 'http://easyapp.local.com'); // Modificare con l'URL del tuo sito

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

    $barbiere_id = intval($barbiere_id);
    $operatore_id = intval($operatore_id);
// Assicurati che data e orari siano nel formato corretto
    $data = date('Y-m-d', strtotime($data));
    $ora_inizio = date('H:i:s', strtotime($ora_inizio));
    $ora_fine = date('H:i:s', strtotime($ora_fine));

    $conn = connectDB();

    // Debug - rimuovere o commentare in produzione
    //echo("isTimeSlotAvailable: barbiere=$barbiere_id, operatore=$operatore_id, data=$data, inizio=$ora_inizio, fine=$ora_fine");


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
        error_log("Il barbiere è chiuso in questo giorno");
        $conn->close();
        return false;
    }

    // Verifica se è un giorno di chiusura speciale
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count FROM giorni_chiusura 
        WHERE barbiere_id = ? AND data_chiusura = ?
    ");
    $stmt->bind_param("is", $barbiere_id, $data);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row['count'] > 0) {
        error_log("È un giorno di chiusura speciale");
        $conn->close();
        return false;
    }

    // Verifica che lo slot rientri in almeno una fascia oraria
    // Converte gli orari in timestamp per un confronto corretto
    $inizio_timestamp = strtotime("1970-01-01 " . $ora_inizio);
    $fine_timestamp = strtotime("1970-01-01 " . $ora_fine);




    $slot_in_fascia = false;
    $stmt = $conn->prepare("
        SELECT ora_apertura, ora_chiusura FROM orari_apertura 
        WHERE barbiere_id = ? AND giorno = ? AND aperto = 1
    ");
    $stmt->bind_param("is", $barbiere_id, $giorno);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $apertura_timestamp = strtotime("1970-01-01 " . $row['ora_apertura']);
        $chiusura_timestamp = strtotime("1970-01-01 " . $row['ora_chiusura']);
        if ($inizio_timestamp >= $apertura_timestamp && $fine_timestamp <= $chiusura_timestamp) {
            $slot_in_fascia = true;
            break;
        }
    }


    if (!$slot_in_fascia) {
        error_log("Lo slot non rientra in nessuna fascia oraria");
        $conn->close();
        return false;
    }

    // Verifica sovrapposizioni con altri appuntamenti
    $query = "
    SELECT COUNT(*) as count FROM appuntamenti 
    WHERE barbiere_id = ? AND operatore_id = ? AND data_appuntamento = ? 
    AND stato IN ('in attesa', 'confermato')
    AND (
        (? > ora_inizio AND ? < ora_fine) OR
        (? > ora_inizio AND ? < ora_fine) OR
        (? <= ora_inizio AND ? >= ora_fine)
    )
";

    $params = [
        $barbiere_id,
        $operatore_id,
        $data,
        $ora_inizio,
        $ora_inizio,
        $ora_fine,
        $ora_fine,
        $ora_inizio,
        $ora_fine
    ];
    $types = "iisssssss";

    if ($appuntamento_id) {
        $query .= " AND id != ?";
        $params[] = $appuntamento_id;
        $types .= "i";
    }
try{
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    $row = $stmt->get_result()->fetch_assoc();
} catch (Exception $e) {
    error_log("Errore SQL in isTimeSlotAvailable: " . $e->getMessage());
    $conn->close();
    return false;
}


    $is_available = ($row['count'] == 0);

    if (!$is_available) {
        error_log("C'è una sovrapposizione con un altro appuntamento");
    }

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
