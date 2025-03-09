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
function isTimeSlotAvailable($barbiereId, $operatoreId, $data, $oraInizio, $oraFine) {
    $conn = connectDB();

    // Controlla se il barbiere è aperto in quel giorno
    $giornoSettimana = date('N', strtotime($data));
    $giorniMap = [1 => 'lunedi', 2 => 'martedi', 3 => 'mercoledi', 4 => 'giovedi', 5 => 'venerdi', 6 => 'sabato', 7 => 'domenica'];
    $giorno = $giorniMap[$giornoSettimana];

    $stmt = $conn->prepare("SELECT * FROM orari_apertura WHERE barbiere_id = ? AND giorno = ? AND aperto = 1");
    $stmt->bind_param("is", $barbiereId, $giorno);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        return false; // Il barbiere è chiuso in questo giorno
    }

    $row = $result->fetch_assoc();
    if ($oraInizio < $row['ora_apertura'] || $oraFine > $row['ora_chiusura']) {
        return false; // Fuori dall'orario di apertura
    }

    // Controlla se è un giorno di chiusura speciale
    $stmt = $conn->prepare("SELECT * FROM giorni_chiusura WHERE barbiere_id = ? AND data_chiusura = ?");
    $stmt->bind_param("is", $barbiereId, $data);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return false; // È un giorno di chiusura speciale
    }

    // Controlla la disponibilità dell'operatore
    $stmt = $conn->prepare("SELECT * FROM orari_operatori WHERE operatore_id = ? AND giorno = ?");
    $stmt->bind_param("is", $operatoreId, $giorno);
    $stmt->execute();
    $result = $stmt->get_result();

    $disponibile = false;
    while ($row = $result->fetch_assoc()) {
        if ($oraInizio >= $row['ora_inizio'] && $oraFine <= $row['ora_fine']) {
            $disponibile = true;
            break;
        }
    }

    if (!$disponibile) {
        return false; // L'operatore non è disponibile in questo orario
    }

    // Controlla se ci sono altri appuntamenti che si sovrappongono
    $stmt = $conn->prepare("SELECT * FROM appuntamenti WHERE operatore_id = ? AND data_appuntamento = ? AND stato IN ('in attesa', 'confermato') AND ((ora_inizio <= ? AND ora_fine > ?) OR (ora_inizio < ? AND ora_fine >= ?) OR (ora_inizio >= ? AND ora_fine <= ?))");
    $stmt->bind_param("isssssss", $operatoreId, $data, $oraFine, $oraInizio, $oraFine, $oraInizio, $oraInizio, $oraFine);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return false; // C'è già un appuntamento in questo slot
    }

    return true; // Lo slot è disponibile
}
