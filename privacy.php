<?php
// privacy.php - Pagina Privacy Policy
session_start();
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - BarberBook</title>
    <link rel="stylesheet" href="css/style.css">

    <!-- Integrazione Tailwind CSS e altri script -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f5f8ff',
                            100: '#ebf1ff',
                            200: '#d6e4ff',
                            300: '#b4ceff',
                            400: '#8bb0ff',
                            500: '#5a8eff',
                            600: '#3a6fff',
                            700: '#2954e5',
                            800: '#2445b8',
                            900: '#233b8f',
                            950: '#172554',
                        },
                        secondary: {
                            50: '#f8f6f4',
                            100: '#eeeae4',
                            200: '#dfd4cc',
                            300: '#cbb7ab',
                            400: '#b49382',
                            500: '#a37d6c',
                            600: '#93685d',
                            700: '#7a524d',
                            800: '#654642',
                            900: '#533c39',
                            950: '#2c1e1c',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    },
                }
            }
        }
    </script>

    <!-- Font import -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- AlpineJS -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-slate-50 font-sans text-secondary-950">
<!-- Header con Menu -->
<header class="bg-white shadow-md sticky top-0 z-50" x-data="{ isOpen: false }">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <div class="flex items-center">
            <a href="index.php" class="flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary-700" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M13 5.5C13 6.88071 11.8807 8 10.5 8C9.11929 8 8 6.88071 8 5.5C8 4.11929 9.11929 3 10.5 3C11.8807 3 13 4.11929 13 5.5Z" />
                    <path d="M10.5 10C7.46243 10 5 12.4624 5 15.5V19H16V15.5C16 12.4624 13.5376 10 10.5 10Z" />
                    <path d="M19 8.5L19 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    <path d="M15 8.5L15 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
                <span class="text-2xl font-semibold font-serif text-primary-700">BarberBook</span>
            </a>
        </div>

        <!-- Desktop Menu -->
        <nav class="hidden md:flex items-center space-x-8">
            <a href="index.php" class="text-gray-700 font-medium hover:text-primary-500 transition duration-300">Home</a>
            <a href="chi_siamo.php" class="text-gray-700 font-medium hover:text-primary-500 transition duration-300">Chi Siamo</a>
            <a href="contatti.php" class="text-gray-700 font-medium hover:text-primary-500 transition duration-300">Contatti</a>
            <?php if (isLoggedIn()): ?>
                <a href="dashboard.php" class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition duration-300 btn-hover-effect">Dashboard</a>
            <?php else: ?>
                <div class="flex space-x-3">
                    <a href="login.php" class="bg-white border border-primary-600 text-primary-600 px-4 py-2 rounded-lg hover:bg-primary-50 transition duration-300">Accedi</a>
                    <a href="register.php" class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition duration-300 btn-hover-effect">Registrati</a>
                </div>
            <?php endif; ?>
        </nav>

        <!-- Mobile menu button -->
        <button @click="isOpen = !isOpen" class="md:hidden text-gray-500 hover:text-primary-500 focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path x-show="!isOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                <path x-show="isOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Mobile Menu -->
    <div class="md:hidden bg-white" x-show="isOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95">
        <div class="container mx-auto px-4 py-4 flex flex-col space-y-4">
            <a href="index.php" class="text-gray-700 font-medium py-2">Home</a>
            <a href="chi_siamo.php" class="text-gray-700 font-medium py-2">Chi Siamo</a>
            <a href="contatti.php" class="text-gray-700 font-medium py-2">Contatti</a>
            <?php if (isLoggedIn()): ?>
                <a href="dashboard.php" class="text-center bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition duration-300">Dashboard</a>
            <?php else: ?>
                <div class="flex flex-col space-y-3 pt-2">
                    <a href="login.php" class="text-center bg-white border border-primary-600 text-primary-600 px-4 py-2 rounded-lg hover:bg-primary-50 transition duration-300">Accedi</a>
                    <a href="register.php" class="text-center bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition duration-300">Registrati</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- Header Section -->
<section class="relative py-16 bg-gradient-to-r from-primary-700 to-primary-800 text-white">
    <div class="container mx-auto px-4">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold font-serif mb-4">Privacy Policy</h1>
            <p class="text-xl text-white/80 max-w-2xl mx-auto">Ultima modifica: <?php echo date('d/m/Y'); ?></p>
        </div>
    </div>
</section>

<!-- Privacy Policy Content -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto bg-white p-8 rounded-lg shadow-lg">
            <div class="prose prose-lg max-w-none">
                <p class="text-gray-700 mb-8">La presente Privacy Policy descrive le modalità con cui BarberBook ("noi", "nostro" o "ci") raccoglie, utilizza, condivide e protegge le informazioni personali degli utenti ("voi" o "vostro") del sito web e del servizio BarberBook (il "Servizio").</p>

                <h2 class="text-2xl font-bold font-serif text-gray-900 mb-4">1. Informazioni raccolte</h2>
                <p class="text-gray-700 mb-4">Raccogliamo diverse tipologie di informazioni per fornire e migliorare il nostro Servizio:</p>
                <h3 class="text-xl font-bold text-gray-900 mb-2">1.1 Informazioni personali</h3>
                <p class="text-gray-700 mb-4">Durante la registrazione e l'utilizzo del nostro Servizio, potremmo chiedervi di fornirci alcune informazioni di identificazione personale, tra cui:</p>
                <ul class="list-disc pl-6 mb-4 text-gray-700 space-y-1">
                    <li>Nome e cognome</li>
                    <li>Nickname</li>
                    <li>Indirizzo email</li>
                    <li>Numero di telefono</li>
                    <li>Città di residenza</li>
                    <li>Foto profilo (opzionale)</li>
                </ul>

                <h3 class="text-xl font-bold text-gray-900 mb-2">1.2 Informazioni sui barbieri</h3>
                <p class="text-gray-700 mb-4">Per i barbieri o i proprietari di attività, raccogliamo ulteriori informazioni quali:</p>
                <ul class="list-disc pl-6 mb-4 text-gray-700 space-y-1">
                    <li>Nome dell'attività</li>
                    <li>Indirizzo dell'attività</li>
                    <li>Numero di telefono aziendale</li>
                    <li>Email aziendale</li>
                    <li>Sito web (opzionale)</li>
                    <li>Logo (opzionale)</li>
                </ul>

                <h3 class="text-xl font-bold text-gray-900 mb-2">1.3 Informazioni sugli appuntamenti</h3>
                <p class="text-gray-700 mb-4">Raccogliamo e memorizziamo informazioni relative agli appuntamenti prenotati attraverso il nostro Servizio, inclusi:</p>
                <ul class="list-disc pl-6 mb-4 text-gray-700 space-y-1">
                    <li>Data e ora dell'appuntamento</li>
                    <li>Servizio richiesto</li>
                    <li>Operatore selezionato</li>
                    <li>Barbiere selezionato</li>
                    <li>Note aggiuntive fornite dall'utente</li>
                </ul>

                <h3 class="text-xl font-bold text-gray-900 mb-2">1.4 Informazioni raccolte automaticamente</h3>
                <p class="text-gray-700 mb-4">Raccogliamo automaticamente alcune informazioni quando utilizzate il nostro Servizio, tra cui:</p>
                <ul class="list-disc pl-6 mb-4 text-gray-700 space-y-1">
                    <li>Indirizzi IP</li>
                    <li>Tipo di browser</li>
                    <li>Pagine visualizzate</li>
                    <li>Tempo trascorso sul sito</li>
                    <li>Riferimenti ad altri siti</li>
                    <li>Dispositivo utilizzato</li>
                </ul>

                <h2 class="text-2xl font-bold font-serif text-gray-900 mb-4">2. Utilizzo delle informazioni</h2>
                <p class="text-gray-700 mb-4">Utilizziamo le informazioni raccolte per i seguenti scopi:</p>
                <ul class="list-disc pl-6 mb-4 text-gray-700 space-y-1">
                    <li>Fornire, gestire e mantenere il nostro Servizio</li>
                    <li>Migliorare, personalizzare ed espandere il nostro Servizio</li>
                    <li>Comprendere e analizzare come utilizzate il nostro Servizio</li>
                    <li>Sviluppare nuovi prodotti, servizi, caratteristiche e funzionalità</li>
                    <li>Comunicare con voi, direttamente o tramite uno dei nostri partner, anche per il servizio clienti</li>
                    <li>Inviarvi email di conferma degli appuntamenti e promemoria</li>
                    <li>Inviare aggiornamenti e notifiche relative al Servizio</li>
                    <li>Prevenire frodi e abusi del Servizio</li>
                </ul>

                <h2 class="text-2xl font-bold font-serif text-gray-900 mb-4">3. Condivisione delle informazioni</h2>
                <p class="text-gray-700 mb-4">Non vendiamo, scambiamo o trasferiamo a terzi le vostre informazioni personali, ad eccezione di quanto descritto in questa Privacy Policy. Potremmo condividere le vostre informazioni nelle seguenti circostanze:</p>
                <ul class="list-disc pl-6 mb-4 text-gray-700 space-y-1">
                    <li><strong>Con i barbieri:</strong> Quando prenotate un appuntamento, condividiamo le informazioni necessarie con il barbiere selezionato per garantire il corretto svolgimento del servizio.</li>
                    <li><strong>Con fornitori di servizi:</strong> Potremmo condividere le vostre informazioni con terze parti che ci forniscono servizi e ci assistono nelle nostre attività, come l'hosting del sito web, l'analisi dei dati e il servizio clienti.</li>
                    <li><strong>Per adempiere a obblighi legali:</strong> Potremmo divulgare le vostre informazioni se richiesto dalla legge o in risposta a richieste legali, come ordini del tribunale o citazioni in giudizio.</li>
                    <li><strong>Per proteggere i nostri diritti:</strong> Potremmo divulgare le vostre informazioni per proteggere i nostri diritti, la nostra proprietà o la sicurezza dei nostri utenti o di altri.</li>
                    <li><strong>In caso di trasferimento aziendale:</strong> In caso di fusione, acquisizione o vendita di beni, le vostre informazioni potrebbero essere trasferite come parte di tale transazione.</li>
                </ul>

                <h2 class="text-2xl font-bold font-serif text-gray-900 mb-4">4. Cookie e tecnologie di tracciamento</h2>
                <p class="text-gray-700 mb-4">Utilizziamo cookie e tecnologie di tracciamento simili per raccogliere informazioni e migliorare la vostra esperienza sul nostro sito web. I cookie sono piccoli file di testo che vengono memorizzati sul vostro dispositivo quando visitate il nostro sito.</p>
                <p class="text-gray-700 mb-4">Potete controllare i cookie attraverso le impostazioni del vostro browser. Tuttavia, se disabilitate i cookie, alcune funzionalità del nostro sito potrebbero non funzionare correttamente.</p>
                <p class="text-gray-700 mb-4">Per maggiori informazioni sui cookie che utilizziamo, consultate la nostra <a href="cookie.php" class="text-primary-600 hover:text-primary-800">Cookie Policy</a>.</p>

                <h2 class="text-2xl font-bold font-serif text-gray-900 mb-4">5. Sicurezza dei dati</h2>
                <p class="text-gray-700 mb-4">La sicurezza delle vostre informazioni personali è importante per noi. Adottiamo misure tecniche, amministrative e fisiche ragionevoli per proteggere le vostre informazioni da accessi non autorizzati, usi impropri o divulgazioni.</p>
                <p class="text-gray-700 mb-4">Tuttavia, nessun sistema di sicurezza è impenetrabile e non possiamo garantire la sicurezza assoluta delle vostre informazioni. È importante che proteggiate la vostra password e il vostro computer da accessi non autorizzati.</p>

                <h2 class="text-2xl font-bold font-serif text-gray-900 mb-4">6. I vostri diritti sulla privacy</h2>
                <p class="text-gray-700 mb-4">In conformità con le leggi sulla protezione dei dati applicabili, potete avere i seguenti diritti riguardo alle vostre informazioni personali:</p>
                <ul class="list-disc pl-6 mb-4 text-gray-700 space-y-1">
                    <li><strong>Diritto di accesso:</strong> Potete richiedere una copia delle vostre informazioni personali che trattiamo.</li>
                    <li><strong>Diritto di rettifica:</strong> Potete richiedere la correzione di informazioni inesatte o incomplete.</li>
                    <li><strong>Diritto alla cancellazione:</strong> Potete richiedere la cancellazione delle vostre informazioni personali in determinate circostanze.</li>
                    <li><strong>Diritto alla limitazione del trattamento:</strong> Potete richiedere la limitazione del trattamento delle vostre informazioni personali in determinate circostanze.</li>
                    <li><strong>Diritto alla portabilità dei dati:</strong> Potete richiedere il trasferimento delle vostre informazioni personali in un formato strutturato, di uso comune e leggibile da dispositivo automatico.</li>
                    <li><strong>Diritto di opposizione:</strong> Potete opporvi al trattamento delle vostre informazioni personali in determinate circostanze.</li>
                </ul>
                <p class="text-gray-700 mb-4">Per esercitare questi diritti, contattate <a href="mailto:privacy@barberbook.it" class="text-primary-600 hover:text-primary-800">privacy@barberbook.it</a>.</p>

                <h2 class="text-2xl font-bold font-serif text-gray-900 mb-4">7. Conservazione dei dati</h2>
                <p class="text-gray-700 mb-4">Conserviamo le vostre informazioni personali solo per il tempo necessario a fornire il Servizio e per gli scopi descritti in questa Privacy Policy, a meno che una conservazione più lunga sia richiesta o consentita dalla legge.</p>

                <h2 class="text-2xl font-bold font-serif text-gray-900 mb-4">8. Minori</h2>
                <p class="text-gray-700 mb-4">Il nostro Servizio non è destinato a persone di età inferiore ai 18 anni. Non raccogliamo consapevolmente informazioni personali da minori di 18 anni. Se siete genitori o tutori e ritenete che vostro figlio ci abbia fornito informazioni personali, contattate <a href="mailto:privacy@barberbook.it" class="text-primary-600 hover:text-primary-800">privacy@barberbook.it</a> per richiederne la rimozione.</p>

                <h2 class="text-2xl font-bold font-serif text-gray-900 mb-4">9. Modifiche alla Privacy Policy</h2>
                <p class="text-gray-700 mb-4">Potremmo aggiornare la nostra Privacy Policy di tanto in tanto. Vi consigliamo di rivedere periodicamente questa pagina per le informazioni più recenti sulle nostre pratiche sulla privacy. La data dell'ultima modifica è indicata all'inizio di questa Privacy Policy.</p>
                <p class="text-gray-700 mb-4">Continuando a utilizzare il nostro Servizio dopo l'entrata in vigore di eventuali modifiche, accettate di essere vincolati dalla Privacy Policy rivista.</p>

                <h2 class="text-2xl font-bold font-serif text-gray-900 mb-4">10. Contatti</h2>
                <p class="text-gray-700 mb-4">Se avete domande o dubbi sulla nostra Privacy Policy, contattate:</p>
                <p class="text-gray-700 mb-4">Email: <a href="mailto:privacy@barberbook.it" class="text-primary-600 hover:text-primary-800">privacy@barberbook.it</a></p>
                <p class="text-gray-700 mb-4">Indirizzo: Via Roma 123, 20121 Milano, Italia</p>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-gray-900 text-white py-12">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="md:col-span-1">
                <a href="index.php" class="flex items-center space-x-2 mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary-400" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M13 5.5C13 6.88071 11.8807 8 10.5 8C9.11929 8 8 6.88071 8 5.5C8 4.11929 9.11929 3 10.5 3C11.8807 3 13 4.11929 13 5.5Z" />
                        <path d="M10.5 10C7.46243 10 5 12.4624 5 15.5V19H16V15.5C16 12.4624 13.5376 10 10.5 10Z" />
                        <path d="M19 8.5L19 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        <path d="M15 8.5L15 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg>
                    <span class="text-2xl font-semibold font-serif text-white">BarberBook</span>
                </a>
                <p class="text-gray-400 mb-4">Prenota il tuo barbiere in pochi click. Semplice, veloce e conveniente.</p>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
                        </svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" />
                        </svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="md:col-span-3 grid grid-cols-1 sm:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">Pagine</h3>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="text-gray-400 hover:text-white transition-colors duration-300">Home</a></li>
                        <li><a href="chi_siamo.php" class="text-gray-400 hover:text-white transition-colors duration-300">Chi Siamo</a></li>
                        <li><a href="contatti.php" class="text-gray-400 hover:text-white transition-colors duration-300">Contatti</a></li>
                        <li><a href="register_barbiere.php" class="text-gray-400 hover:text-white transition-colors duration-300">Registra la tua attività</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-4">Legali</h3>
                    <ul class="space-y-2">
                        <li><a href="termini.php" class="text-gray-400 hover:text-white transition-colors duration-300">Termini e Condizioni</a></li>
                        <li><a href="privacy.php" class="text-gray-400 hover:text-white transition-colors duration-300">Privacy Policy</a></li>
                        <li><a href="cookie.php" class="text-gray-400 hover:text-white transition-colors duration-300">Cookie Policy</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-4">Contattaci</h3>
                    <ul class="space-y-2">
                        <li class="flex items-start space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span class="text-gray-400">info@barberbook.it</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span class="text-gray-400">+39 02 1234567</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="text-gray-400">Via Roma 123, Milano, Italia</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-800 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
            <p class="text-gray-400 text-sm">&copy; <?php echo date('Y'); ?> BarberBook. Tutti i diritti riservati.</p>
            <p class="text-gray-400 text-sm mt-4 md:mt-0">Realizzato con ❤️ in Italia</p>
        </div>
    </div>
</footer>
</body>
</html>
