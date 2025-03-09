<?php
// chi_siamo.php - Pagina Chi Siamo
session_start();
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Siamo - BarberBook</title>
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
            <a href="chi_siamo.php" class="text-primary-700 font-medium hover:text-primary-500 transition duration-300">Chi Siamo</a>
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
            <a href="chi_siamo.php" class="text-primary-700 font-medium py-2">Chi Siamo</a>
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

<!-- Hero Section -->
<section class="relative py-16 bg-gradient-to-r from-primary-700 to-primary-800 text-white">
    <div class="container mx-auto px-4">
        <div class="text-center" data-aos="fade-up">
            <h1 class="text-4xl md:text-5xl font-bold font-serif mb-4">Chi Siamo</h1>
            <p class="text-xl text-white/80 max-w-2xl mx-auto">Scopri la storia dietro BarberBook e la nostra missione per rivoluzionare il mondo delle prenotazioni in barberia.</p>
        </div>
    </div>
</section>

<!-- About Us Content -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto">
            <div class="mb-12" data-aos="fade-up">
                <h2 class="text-3xl font-bold font-serif text-gray-900 mb-4">La nostra storia</h2>
                <p class="text-gray-700 mb-4">BarberBook nasce nel 2022 da un'idea semplice ma rivoluzionaria: eliminare lo stress e le attese per prenotare un appuntamento dal barbiere.</p>
                <p class="text-gray-700 mb-4">Il nostro fondatore, un appassionato di tecnologia e cliente abituale di barberie, si è reso conto di quanto fosse complicato e inefficiente il sistema di prenotazione tradizionale. Le lunghe attese al telefono, l'impossibilità di vedere la disponibilità in tempo reale e la necessità di chiamare durante gli orari di apertura rappresentavano un vero problema sia per i clienti che per i barbieri stessi.</p>
                <p class="text-gray-700">Da questa esigenza è nata BarberBook: una piattaforma semplice, moderna ed efficiente per connettere barbieri e clienti, rendendo il processo di prenotazione completamente digitale e disponibile 24 ore su 24, 7 giorni su 7.</p>
            </div>

            <div class="mb-12" data-aos="fade-up">
                <h2 class="text-3xl font-bold font-serif text-gray-900 mb-4">La nostra missione</h2>
                <p class="text-gray-700 mb-4">La missione di BarberBook è chiara: rendere il processo di prenotazione dal barbiere semplice, veloce e piacevole sia per i clienti che per i professionisti del settore.</p>
                <p class="text-gray-700 mb-4">Ci impegniamo a:</p>
                <ul class="list-disc pl-6 mb-4 text-gray-700 space-y-2">
                    <li>Fornire una piattaforma intuitiva e facile da usare per tutti gli utenti, indipendentemente dalle loro competenze tecnologiche</li>
                    <li>Garantire trasparenza nei servizi e nei prezzi</li>
                    <li>Offrire ai barbieri uno strumento efficace per gestire la propria agenda e aumentare la produttività</li>
                    <li>Migliorare costantemente la piattaforma in base al feedback degli utenti</li>
                    <li>Supportare e promuovere le eccellenze locali nel settore della barberia</li>
                </ul>
                <p class="text-gray-700">Crediamo che la tecnologia debba semplificare la vita quotidiana, e questo è esattamente ciò che BarberBook fa nel mondo delle barberie.</p>
            </div>

            <div class="mb-12" data-aos="fade-up">
                <h2 class="text-3xl font-bold font-serif text-gray-900 mb-4">I nostri valori</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Semplicità</h3>
                        <p class="text-gray-700">Crediamo che le migliori soluzioni siano quelle semplici. La nostra piattaforma è progettata per essere intuitiva e facile da usare.</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Affidabilità</h3>
                        <p class="text-gray-700">La fiducia è fondamentale. Garantiamo un servizio affidabile e sicuro, rispettando gli appuntamenti e i dati personali.</p>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Personalizzazione</h3>
                        <p class="text-gray-700">Ogni barbiere è diverso, così come ogni cliente. La nostra piattaforma si adatta alle specifiche esigenze di tutti.</p>
                    </div>
                </div>
            </div>

            <div class="mb-12" data-aos="fade-up">
                <h2 class="text-3xl font-bold font-serif text-gray-900 mb-4">Il nostro team</h2>
                <p class="text-gray-700 mb-4">Siamo un gruppo di appassionati di tecnologia e amanti del settore della barberia. Il nostro team multidisciplinare comprende sviluppatori, designer, esperti di UX e professionisti del marketing, tutti uniti dalla visione di semplificare la prenotazione dal barbiere.</p>
                <p class="text-gray-700 mb-4">Ognuno di noi porta un contributo unico, ma condividiamo tutti la stessa passione per l'innovazione e la qualità.</p>
                <p class="text-gray-700">Siamo costantemente in ascolto del feedback degli utenti e alla ricerca di modi per migliorare la nostra piattaforma, garantendo un'esperienza sempre migliore.</p>
            </div>

            <div data-aos="fade-up">
                <h2 class="text-3xl font-bold font-serif text-gray-900 mb-4">Contattaci</h2>
                <p class="text-gray-700 mb-4">Vuoi saperne di più sul nostro progetto o hai domande sulla piattaforma? Non esitare a contattarci:</p>
                <ul class="space-y-2 text-gray-700">
                    <li class="flex items-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span>info@barberbook.it</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <span>+39 02 1234567</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Via Roma 123, Milano, Italia</span>
                    </li>
                </ul>
                <div class="mt-6">
                    <a href="contatti.php" class="inline-block bg-primary-600 text-white px-6 py-3 rounded-lg hover:bg-primary-700 transition-all duration-300 font-medium">Contattaci ora</a>
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
