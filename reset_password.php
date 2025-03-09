<?php
// reset_password.php
session_start();
require_once 'config.php';

$error = '';
$success = '';
$email = '';
$token = '';
$step = 1; // 1: richiesta email, 2: inserimento nuova password

// Controllo se è stato passato un token di reset
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = trim($_GET['token']);
    $email = isset($_GET['email']) ? trim($_GET['email']) : '';

    // Verifica validità del token
    $stmt = $conn->prepare("SELECT * FROM password_reset WHERE token = ? AND email = ? AND expires_at > NOW() AND used = 0");
    $stmt->bind_param("ss", $token, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $step = 2; // Token valido, mostra form di reset password
    } else {
        $error = "Il link di reset non è valido o è scaduto. Richiedine uno nuovo.";
        $step = 1;
    }
}

// Gestione della richiesta di reset
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['request_reset'])) {
        // Richiesta di reset password
        $email = trim($_POST['email']);

        if (empty($email)) {
            $error = "Inserisci un indirizzo email valido.";
        } else {
            // Verifica che l'email esista nel database
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? UNION SELECT id FROM barbieri WHERE email = ?");
            $stmt->bind_param("ss", $email, $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Genera token unico
                $token = bin2hex(random_bytes(32));
                $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Cancella eventuali token precedenti
                $stmt = $conn->prepare("DELETE FROM password_reset WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();

                // Salva il nuovo token
                $stmt = $conn->prepare("INSERT INTO password_reset (email, token, expires_at) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $email, $token, $expires_at);
                $stmt->execute();

                // Invia email con il link di reset
                $reset_link = "https://" . $_SERVER['HTTP_HOST'] . "/reset_password.php?token=$token&email=$email";
                $subject = "Reset Password - Barbiere App";
                $message = "Ciao,\n\nHai richiesto il reset della password. Clicca sul link seguente per reimpostare la tua password:\n\n$reset_link\n\nIl link sarà valido per 1 ora.\n\nSe non hai richiesto tu il reset della password, ignora questa email.\n\nSaluti,\nTeam Barbiere App";

                // Utilizzo la funzione mail() o una libreria di invio email
                mail($email, $subject, $message, "From: noreply@barbiereapp.com");

                $success = "Ti abbiamo inviato un'email con le istruzioni per reimpostare la tua password. Controlla la tua casella di posta.";
            } else {
                $error = "L'indirizzo email non è registrato nel sistema.";
            }
        }
    } else if (isset($_POST['reset_password'])) {
        // Reset della password
        $email = trim($_POST['email']);
        $token = trim($_POST['token']);
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);

        if (empty($password) || strlen($password) < 8) {
            $error = "La password deve contenere almeno 8 caratteri.";
            $step = 2;
        } else if ($password !== $confirm_password) {
            $error = "Le password non coincidono.";
            $step = 2;
        } else {
            // Verifica validità del token
            $stmt = $conn->prepare("SELECT * FROM password_reset WHERE token = ? AND email = ? AND expires_at > NOW() AND used = 0");
            $stmt->bind_param("ss", $token, $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Determina se è un utente o un barbiere
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $user_result = $stmt->get_result();

                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                if ($user_result->num_rows > 0) {
                    // Aggiorna la password dell'utente
                    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
                    $stmt->bind_param("ss", $hashed_password, $email);
                    $stmt->execute();
                } else {
                    // Aggiorna la password del barbiere
                    $stmt = $conn->prepare("UPDATE barbieri SET password = ? WHERE email = ?");
                    $stmt->bind_param("ss", $hashed_password, $email);
                    $stmt->execute();
                }

                // Marca il token come utilizzato
                $stmt = $conn->prepare("UPDATE password_reset SET used = 1 WHERE token = ?");
                $stmt->bind_param("s", $token);
                $stmt->execute();

                $success = "La tua password è stata reimpostata con successo. Ora puoi <a href='login.php' class='text-blue-600 hover:underline'>accedere</a> con la nuova password.";
                $step = 1;
            } else {
                $error = "Il link di reset non è valido o è scaduto. Richiedine uno nuovo.";
                $step = 1;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - BarberBooking</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.6.5/flowbite.min.css" rel="stylesheet" />
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Custom Style -->
    <style>
        .btn-primary {
            @apply inline-block py-3 px-6 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-lg shadow-md hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-75 transition-all duration-300 relative overflow-hidden;
        }

        .btn-primary::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, 0.5);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%);
            transform-origin: 50% 50%;
        }

        .btn-primary:hover::after {
            animation: ripple 1s ease-out;
        }

        @keyframes ripple {
            0% {
                transform: scale(0, 0);
                opacity: 0.5;
            }
            100% {
                transform: scale(20, 20);
                opacity: 0;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

<?php include 'includes/header.php'; ?>

<main class="flex-grow">
    <div class="container mx-auto px-4 py-16">
        <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-lg" data-aos="fade-up" data-aos-duration="800">
            <div class="p-8">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-800">Reset Password</h2>
                    <p class="text-gray-600 mt-2">
                        <?php echo ($step == 1) ? "Inserisci la tua email per ricevere un link di reset." : "Inserisci la tua nuova password."; ?>
                    </p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                        <p><?php echo $error; ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                        <p><?php echo $success; ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($step == 1 && empty($success)): ?>
                    <!-- Form per richiedere il reset -->
                    <form method="POST" action="reset_password.php" class="space-y-6">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   required>
                        </div>

                        <div>
                            <button type="submit" name="request_reset" class="btn-primary w-full">
                                Invia link di reset
                            </button>
                        </div>
                    </form>
                <?php elseif ($step == 2): ?>
                    <!-- Form per inserire la nuova password -->
                    <form method="POST" action="reset_password.php" class="space-y-6">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Nuova Password</label>
                            <input type="password" id="password" name="password"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   required minlength="8">
                            <p class="text-xs text-gray-500 mt-1">Almeno 8 caratteri</p>
                        </div>

                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700">Conferma Password</label>
                            <input type="password" id="confirm_password" name="confirm_password"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   required minlength="8">
                        </div>

                        <div>
                            <button type="submit" name="reset_password" class="btn-primary w-full">
                                Reimposta Password
                            </button>
                        </div>
                    </form>
                <?php endif; ?>

                <div class="text-center mt-6">
                    <p class="text-sm text-gray-600">
                        Ricordi la password? <a href="login.php" class="text-blue-600 hover:underline">Accedi</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

<!-- Scripts -->
<script src="https://unpkg.com/alpinejs@3.10.3/dist/cdn.min.js" defer></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        AOS.init();
    });
</script>
</body>
</html>
