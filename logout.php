<?php
// logout.php - Funzione di logout
session_start();
session_unset();
session_destroy();
header("Location: login.php");
exit;
?>

<?php
// index.php - Pagina principale
session_start();
require_once 'config.php';

// Se l'utente è già loggato, redirect alla dashboard
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
} elseif (isBarbiere()) {
    header("Location: barbiere/dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BarberBook - Prenota il tuo barbiere</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .hero {
            background-color: var(--primary-light);
            padding: 4rem 2rem;
            text-align: center;
            margin-bottom: 3rem;
        }

        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .hero p {
            font-size: 1.2rem;
            max-width: 800px;
            margin: 0 auto 2rem;
        }

        .hero-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .feature {
            padding: 2rem;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            text-align: center;
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .feature h3 {
            margin-bottom: 1rem;
        }

        .how-it-works {
            margin-bottom: 3rem;
        }

        .steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .step {
            position: relative;
            padding: 2rem;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .step-number {
            position: absolute;
            top: -1rem;
            left: -1rem;
            width: 3rem;
            height: 3rem;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .section-title {
            text-align: center;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<section class="hero">
    <h1>Prenota il tuo barbiere online</h1>
    <p>Con BarberBook, prenotare un appuntamento dal tuo barbiere preferito è semplice e veloce! Scegli il servizio, l'operatore e l'orario più comodo per te, tutto con pochi click.</p>
    <div class="hero-buttons">
        <a href="register.php" class="btn-primary">Registrati ora</a>
        <a href="login.php" class="btn-secondary">Accedi</a>
    </div>
</section>

<div class="container">
    <section class="features">
        <div class="feature">
            <div class="feature-icon">⏰</div>
            <h3>Risparmia tempo</h3>
            <p>Niente più chiamate o attese. Prenota il tuo appuntamento in qualsiasi momento, 24/7.</p>
        </div>
        <div class="feature">
            <div class="feature-icon">📱</div>
            <h3>Facile da usare</h3>
            <p>Interface intuitiva, utilizzabile da qualsiasi dispositivo, senza bisogno di app.</p>
        </div>
        <div class="feature">
            <div class="feature-icon">✓</div>
            <h3>Conferme immediate</h3>
            <p>Ricevi conferme istantanee e promemoria per i tuoi appuntamenti via email.</p>
        </div>
    </section>

    <section class="how-it-works">
        <h2 class="section-title">Come funziona</h2>
        <div class="steps">
            <div class="step">
                <div class="step-number">1</div>
                <h3>Registrati</h3>
                <p>Crea un account in pochi secondi inserendo i tuoi dati principali.</p>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <h3>Scegli</h3>
                <p>Seleziona il barbiere, il servizio, l'operatore e l'orario desiderato.</p>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <h3>Conferma</h3>
                <p>Ricevi la conferma dell'appuntamento e presentati all'orario stabilito.</p>
            </div>
        </div>
    </section>

    <section class="call-to-action">
        <h2 class="section-title">Sei un Barbiere?</h2>
        <p style="text-align: center; margin-bottom: 2rem;">Offri ai tuoi clienti un sistema di prenotazione online moderno ed efficiente.</p>
        <div style="text-align: center;">
            <a href="register_barbiere.php" class="btn-primary">Registra la tua attività</a>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>

<script src="js/jquery.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>
