<?php
// contatti.php - Pagina Contatti
session_start();
require_once 'config.php';

$success = '';
$error = '';

// Gestione dell'invio del form di contatto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['csrf_token']) && verifyCSRFToken($_POST['csrf_token'])) {
    $nome = sanitizeInput($_POST['nome']);
    $email = sanitizeInput($_POST['email']);
    $oggetto = sanitizeInput($_POST['oggetto']);
    $messaggio = sanitizeInput($_POST['messaggio']);

    // Validazione
    if (empty($nome) || empty($email) || empty($oggetto) || empty($messaggio)) {
        $error = "Tutti i campi sono obbligatori.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email non valida.";
    } else {
        // Invia email
        $to = "info@barberbook.it"; // Email di destinazione
        $subject = "Messaggio dal form di contatto: $oggetto";
        $message = "
            <h2>Nuovo messaggio dal form di contatto</h2>
            <p><strong>Nome:</strong> $nome</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Oggetto:</strong> $oggetto</p>
            <p><strong>Messaggio:</strong></p>
            <p>$messaggio</p>
        ";

        if (sendEmail($to, $subject, $message)) {
            $success = "Messaggio inviato con successo! Ti risponderemo il prima possibile.";
        } else {
            $error = "Si è verificato un errore durante l'invio del messaggio. Riprova più tardi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contatti - BarberBook</title>
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
                        },
                        accent: {
                            50: '#FFF6ED',
                            100: '#FFEAD5',
                            200: '#FECBA6',
                            300: '#FEA46B',
                            400: '#FD813A',
                            500: '#FB6514',
                            600: '#E14B09',
                            700: '#B83B0B',
                            800: '#932F0F',
                            900: '#792C11',
                            950: '#451306',
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

    <!-- AOS (Animate On Scroll) -->
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
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
            <a href="contatti.php" class="text-primary-700 font-medium hover:text-primary-500 transition duration-300">Contatti</a>
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
            <a href="contatti.php" class="text-primary-700 font-medium py-2">Contatti</a>
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

<!-- Hero Section -->
<section class="relative py-16 bg-gradient-to-r from-primary-700 to-primary-800 text-white">
    <div class="container mx-auto px-4">
        <div class="text-center" data-aos="fade-up">
            <h1 class="text-4xl md:text-5xl font-bold font-serif mb-4">Contattaci</h1>
            <p class="text-xl text-white/80 max-w-2xl mx-auto">Hai domande o suggerimenti? Siamo qui per aiutarti. Contattaci in qualsiasi momento.</p>
        </div>
    </div>
</section>

<!-- Contact Form & Info Section -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Form -->
            <div data-aos="fade-right">
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <h2 class="text-2xl font-bold font-serif text-gray-900 mb-6">Inviaci un messaggio</h2>

                    <?php if($success): ?>
                        <div class="mb-6 p-4 bg-green-50 text-green-700 rounded-lg">
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <?php if($error): ?>
                        <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-lg">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                        <div class="mb-4">
                            <label for="nome" class="block text-gray-700 font-medium mb-2">Nome completo</label>
                            <input type="text" name="nome" id="nome" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent" required>
                        </div>

                        <div class="mb-4">
                            <label for="email" class="block text-gray-700 font-medium mb-2">Email</label>
                            <input type="email" name="email" id="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent" required>
                        </div>

                        <div class="mb-4">
                            <label for="oggetto" class="block text-gray-700 font-medium mb-2">Oggetto</label>
                            <input type="text" name="oggetto" id="oggetto" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent" required>
                        </div>

                        <div class="mb-6">
                            <label for="messaggio" class="block text-gray-700 font-medium mb-2">Messaggio</label>
                            <textarea name="messaggio" id="messaggio" rows="5" class="w-full px-4 py-2 border border-gray-300 rounded-lg resize-none focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent" required></textarea>
                        </div>

                        <button type="submit" class="w-full bg-primary-600 text-white font-medium py-3 px-4 rounded-lg hover:bg-primary-700 transition duration-300">Invia messaggio</button>
                    </form>
                </div>
            </div>

            <!-- Info -->
            <div data-aos="fade-left">
                <h2 class="text-2xl font-bold font-serif text-gray-900 mb-6">Informazioni di contatto</h2>

                <div class="space-y-8">
                    <div class="flex items-start space-x-4">
                        <div class="bg-primary-100 rounded-full p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">Email</h3>
                            <p class="text-gray-700 mb-1">Per informazioni generali:</p>
                            <p class="text-primary-600 font-medium">info@barberbook.it</p>
                            <p class="text-gray-700 mt-2 mb-1">Per supporto tecnico:</p>
                            <p class="text-primary-600 font-medium">supporto@barberbook.it</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="bg-primary-100 rounded-full p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">Telefono</h3>
                            <p class="text-gray-700 mb-1">Servizio clienti:</p>
                            <p class="text-primary-600 font-medium">+39 02 1234567</p>
                            <p class="text-gray-600 text-sm mt-1">Lun-Ven: 9:00-18:00</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="bg-primary-100 rounded-full p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">Indirizzo</h3>
                            <p class="text-gray-700 mb-1">Sede principale:</p>
                            <p class="text-primary-600 font-medium">Via Roma 123, 20121 Milano, Italia</p>
                        </div>
                    </div>
                </div>

                <div class="mt-12">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Seguici sui social</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="bg-primary-100 p-3 rounded-full text-primary-600 hover:bg-primary-200 transition duration-300">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
                            </svg>
                        </a>
                        <a href="#" class="bg-primary-100 p-3 rounded-full text-primary-600 hover:bg-primary-200 transition duration-300">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" />
                            </svg>
                        </a>
                        <a href="#" class="bg-primary-100 p-3 rounded-full text-primary-600 hover:bg-primary-200 transition duration-300">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
                            </svg>
                        </a>
                        <a href="#" class="bg-primary-100 p-3 rounded-full text-primary-600 hover:bg-primary-200 transition duration-300">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map Section -->
        <div class="mt-16" data-aos="fade-up">
            <div class="bg-white rounded-lg shadow-lg p-4">
                <div class="aspect-w-16 aspect-h-9">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2798.1871200114686!2d9.186757015555071!3d45.46458547910095!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x4786c6ace2c87cbd%3A0x98cf4d943fe99c9e!2sVia%20Roma%2C%20Milano%20MI!5e0!3m2!1sit!2sit!4v1647274410213!5m2!1sit!2sit" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" class="rounded-lg"></iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12" data-aos="fade-up">
            <h2 class="text-3xl font-bold font-serif text-gray-900 mb-4">Domande frequenti</h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">Ecco alcune risposte alle domande più comuni che riceviamo.</p>
        </div>

        <div class="max-w-3xl mx-auto" x-data="{active: 0}">
            <!-- FAQ Item 1 -->
            <div class="mb-4" data-aos="fade-up" data-aos-delay="100">
                <button @click="active = active === 1 ? 0 : 1" class="flex items-center justify-between w-full px-6 py-4 text-left bg-white shadow-md rounded-lg focus:outline-none">
                    <span class="font-semibold text-gray-900">Come posso registrare la mia barberia su BarberBook?</span>
                    <svg class="h-5 w-5 text-primary-600 transform transition-transform duration-300" :class="{'rotate-180': active === 1}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
                <div x-show="active === 1" x-transition class="px-6 py-4 bg-white rounded-b-lg shadow-md">
                    <p class="text-gray-700">Registrare la tua barberia è semplice. Vai alla pagina "Registra la tua attività", compila il modulo con i dati della tua attività (nome, indirizzo, contatti, ecc.), carica il tuo logo se disponibile e scegli una password sicura. Una volta completata la registrazione, potrai accedere al pannello di controllo e iniziare a configurare i tuoi servizi e orari.</p>
                </div>
            </div>

            <!-- FAQ Item 2 -->
            <div class="mb-4" data-aos="fade-up" data-aos-delay="200">
                <button @click="active = active === 2 ? 0 : 2" class="flex items-center justify-between w-full px-6 py-4 text-left bg-white shadow-md rounded-lg focus:outline-none">
                    <span class="font-semibold text-gray-900">Come funziona il sistema di prenotazione?</span>
                    <svg class="h-5 w-5 text-primary-600 transform transition-transform duration-300" :class="{'rotate-180': active === 2}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
                <div x-show="active === 2" x-transition class="px-6 py-4 bg-white rounded-b-lg shadow-md">
                    <p class="text-gray-700">Il sistema di prenotazione è molto intuitivo. Una volta registrato e loggato, seleziona il tuo barbiere preferito, scegli il servizio desiderato, l'operatore e la data/orario disponibile. Conferma la prenotazione e riceverai una email di conferma con tutti i dettagli. Potrai gestire i tuoi appuntamenti dalla dashboard, con possibilità di modificarli o cancellarli fino a 24 ore prima dell'orario previsto.</p>
                </div>
            </div>

            <!-- FAQ Item 3 -->
            <div class="mb-4" data-aos="fade-up" data-aos-delay="300">
                <button @click="active = active === 3 ? 0 : 3" class="flex items-center justify-between w-full px-6 py-4 text-left bg-white shadow-md rounded-lg focus:outline-none">
                    <span class="font-semibold text-gray-900">Posso modificare o cancellare un appuntamento?</span>
                    <svg class="h-5 w-5 text-primary-600 transform transition-transform duration-300" :class="{'rotate-180': active === 3}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
                <div x-show="active === 3" x-transition class="px-6 py-4 bg-white rounded-b-lg shadow-md">
                    <p class="text-gray-700">Sì, puoi modificare o cancellare i tuoi appuntamenti fino a 24 ore prima dell'orario previsto. Questo ti dà flessibilità ma permette anche ai barbieri di organizzarsi adeguatamente. Accedi alla tua dashboard, trova l'appuntamento che desideri modificare o cancellare e clicca sul relativo pulsante. In caso di modifica, potrai selezionare una nuova data, orario o anche un operatore diverso, in base alla disponibilità.</p>
                </div>
            </div>

            <!-- FAQ Item 4 -->
            <div class="mb-4" data-aos="fade-up" data-aos-delay="400">
                <button @click="active = active === 4 ? 0 : 4" class="flex items-center justify-between w-full px-6 py-4 text-left bg-white shadow-md rounded-lg focus:outline-none">
                    <span class="font-semibold text-gray-900">BarberBook ha un costo per i clienti?</span>
                    <svg class="h-5 w-5 text-primary-600 transform transition-transform duration-300" :class="{'rotate-180': active === 4}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
                <div x-show="active === 4" x-transition class="px-6 py-4 bg-white rounded-b-lg shadow-md">
                    <p class="text-gray-700">No, BarberBook è completamente gratuito per i clienti. Non ci sono costi di registrazione o di prenotazione. Potrai utilizzare tutte le funzionalità della piattaforma senza alcun addebito. Il servizio è finanziato tramite abbonamenti mensili o annuali per i barbieri che desiderano offrire questo sistema di prenotazione ai propri clienti.</p>
                </div>
            </div>

            <!-- FAQ Item 5 -->
            <div data-aos="fade-up" data-aos-delay="500">
                <button @click="active = active === 5 ? 0 : 5" class="flex items-center justify-between w-full px-6 py-4 text-left bg-white shadow-md rounded-lg focus:outline-none">
                    <span class="font-semibold text-gray-900">Come posso contattare il supporto tecnico?</span>
                    <svg class="h-5 w-5 text-primary-600 transform transition-transform duration-300" :class="{'rotate-180': active === 5}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
                <div x-show="active === 5" x-transition class="px-6 py-4 bg-white rounded-b-lg shadow-md">
                    <p class="text-gray-700">Per contattare il nostro supporto tecnico, puoi utilizzare il modulo di contatto presente in questa pagina, inviare una email a supporto@barberbook.it o chiamare il numero +39 02 1234567 dal lunedì al venerdì, dalle 9:00 alle 18:00. Il nostro team di supporto è sempre pronto ad aiutarti con qualsiasi problema o domanda relativa all'utilizzo della piattaforma.</p>
                </div>
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

<!-- AOS Script -->
<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<script>
    // Initialize AOS
    AOS.init({
        duration: 800,
        once: true
    });
</script>
</body>
</html>
