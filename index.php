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
    <title>BarberBook - Prenota il tuo barbiere online</title>
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
            <a href="index.php" class="text-primary-700 font-medium hover:text-primary-500 transition duration-300">Home</a>
            <a href="chi_siamo.php" class="text-gray-700 font-medium hover:text-primary-500 transition duration-300">Chi Siamo</a>
            <a href="contatti.php" class="text-gray-700 font-medium hover:text-primary-500 transition duration-300">Contatti</a>
            <div class="flex space-x-3">
                <a href="login.php" class="bg-white border border-primary-600 text-primary-600 px-4 py-2 rounded-lg hover:bg-primary-50 transition duration-300">Accedi</a>
                <a href="register.php" class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition duration-300 btn-hover-effect">Registrati</a>
            </div>
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
            <a href="index.php" class="text-primary-700 font-medium py-2">Home</a>
            <a href="chi_siamo.php" class="text-gray-700 font-medium py-2">Chi Siamo</a>
            <a href="contatti.php" class="text-gray-700 font-medium py-2">Contatti</a>
            <div class="flex flex-col space-y-3 pt-2">
                <a href="login.php" class="text-center bg-white border border-primary-600 text-primary-600 px-4 py-2 rounded-lg hover:bg-primary-50 transition duration-300">Accedi</a>
                <a href="register.php" class="text-center bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition duration-300">Registrati</a>
            </div>
        </div>
    </div>
</header>

<!-- Hero Section -->
<section class="relative bg-gradient-to-r from-primary-600 to-primary-800 text-white overflow-hidden">
    <!-- Decorative elements -->
    <div class="absolute inset-0 overflow-hidden">
        <svg class="absolute left-0 bottom-0 w-full h-64 text-white opacity-20" viewBox="0 0 1440 320" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <path fill="currentColor" d="M0,96L60,122.7C120,149,240,203,360,202.7C480,203,600,149,720,149.3C840,149,960,203,1080,213.3C1200,224,1320,192,1380,176L1440,160L1440,320L1380,320C1320,320,1200,320,1080,320C960,320,840,320,720,320C600,320,480,320,360,320C240,320,120,320,60,320L0,320Z"></path>
        </svg>
        <svg class="absolute right-0 top-0 w-full h-64 text-white opacity-20 transform rotate-180" viewBox="0 0 1440 320" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <path fill="currentColor" d="M0,96L60,122.7C120,149,240,203,360,202.7C480,203,600,149,720,149.3C840,149,960,203,1080,213.3C1200,224,1320,192,1380,176L1440,160L1440,320L1380,320C1320,320,1200,320,1080,320C960,320,840,320,720,320C600,320,480,320,360,320C240,320,120,320,60,320L0,320Z"></path>
        </svg>
    </div>

    <!-- Content -->
    <div class="container mx-auto px-4 py-20 relative z-10">
        <div class="flex flex-col lg:flex-row items-center justify-between">
            <div class="lg:w-1/2 mb-10 lg:mb-0" data-aos="fade-right" data-aos-duration="1000">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold font-serif mb-6 leading-tight">Prenota il tuo <span class="text-accent-300">barbiere</span> in pochi click</h1>
                <p class="text-lg md:text-xl mb-8 text-white/90 max-w-lg">Dimentica code e chiamate. Con BarberBook scegli servizio, barbiere e orario ideale per il tuo look perfetto, tutto online.</p>
                <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                    <a href="register.php" class="bg-accent-500 text-white px-6 py-3 rounded-lg shadow-lg hover:bg-accent-600 transition-all duration-300 text-center font-medium btn-hover-effect pulse-animation">Inizia ora</a>
                    <a href="chi_siamo.php" class="bg-white/20 backdrop-blur-sm text-white px-6 py-3 rounded-lg hover:bg-white/30 transition-all duration-300 text-center font-medium">Scopri di più</a>
                </div>
            </div>
            <div class="lg:w-1/2 flex justify-center relative" data-aos="fade-left" data-aos-duration="1000">
                <div class="relative w-full max-w-md">
                    <div class="bg-white p-2 rounded-xl shadow-2xl overflow-hidden relative z-10">
                        <img src="https://images.unsplash.com/photo-1605497788044-5a32c7078486?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80" alt="Barbiere al lavoro" class="rounded-lg w-full">
                        <div class="absolute -bottom-4 -right-4 bg-white rounded-lg px-6 py-3 shadow-xl">
                            <div class="flex items-center space-x-3">
                                <div class="flex -space-x-2">
                                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Cliente" class="w-10 h-10 rounded-full border-2 border-white">
                                    <img src="https://randomuser.me/api/portraits/men/58.jpg" alt="Cliente" class="w-10 h-10 rounded-full border-2 border-white">
                                    <img src="https://randomuser.me/api/portraits/women/24.jpg" alt="Cliente" class="w-10 h-10 rounded-full border-2 border-white">
                                </div>
                                <div class="text-sm">
                                    <p class="text-gray-800 font-semibold">500+ clienti</p>
                                    <p class="text-gray-500">soddisfatti</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Decorative elements -->
                    <div class="absolute w-32 h-32 bg-accent-400 rounded-full top-0 -left-10 filter blur-2xl opacity-60"></div>
                    <div class="absolute w-24 h-24 bg-primary-400 rounded-full bottom-10 -right-10 filter blur-xl opacity-60"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16" data-aos="fade-up">
            <h2 class="text-3xl md:text-4xl font-bold font-serif text-gray-900 mb-4">Perché scegliere BarberBook?</h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">La soluzione moderna per gestire e prenotare appuntamenti dal barbiere, semplice ed efficiente.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
            <!-- Feature 1 -->
            <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300" data-aos="fade-up" data-aos-delay="100">
                <div class="w-14 h-14 bg-primary-100 rounded-lg flex items-center justify-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Risparmia tempo</h3>
                <p class="text-gray-600">Prenota il tuo appuntamento in pochi secondi, senza chiamate o attese, a qualsiasi ora del giorno o della notte.</p>
            </div>

            <!-- Feature 2 -->
            <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300" data-aos="fade-up" data-aos-delay="200">
                <div class="w-14 h-14 bg-primary-100 rounded-lg flex items-center justify-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Scegli i servizi</h3>
                <p class="text-gray-600">Visualizza e seleziona tra tutti i servizi offerti, con prezzi trasparenti e descrizioni dettagliate.</p>
            </div>

            <!-- Feature 3 -->
            <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300" data-aos="fade-up" data-aos-delay="300">
                <div class="w-14 h-14 bg-primary-100 rounded-lg flex items-center justify-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Barbiere preferito</h3>
                <p class="text-gray-600">Scegli il tuo barbiere di fiducia e l'operatore che preferisci per ogni servizio.</p>
            </div>

            <!-- Feature 4 -->
            <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300" data-aos="fade-up" data-aos-delay="400">
                <div class="w-14 h-14 bg-primary-100 rounded-lg flex items-center justify-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Disponibilità in tempo reale</h3>
                <p class="text-gray-600">Visualizza gli slot disponibili in tempo reale e scegli l'orario più comodo per te.</p>
            </div>

            <!-- Feature 5 -->
            <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300" data-aos="fade-up" data-aos-delay="500">
                <div class="w-14 h-14 bg-primary-100 rounded-lg flex items-center justify-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Notifiche e promemoria</h3>
                <p class="text-gray-600">Ricevi conferme e promemoria via email per non dimenticare mai i tuoi appuntamenti.</p>
            </div>

            <!-- Feature 6 -->
            <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300" data-aos="fade-up" data-aos-delay="600">
                <div class="w-14 h-14 bg-primary-100 rounded-lg flex items-center justify-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Prezzi trasparenti</h3>
                <p class="text-gray-600">Visualizza i prezzi di tutti i servizi prima di prenotare, senza sorprese al momento del pagamento.</p>
            </div>
        </div>
    </div>
</section>

<!-- How it works -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16" data-aos="fade-up">
            <h2 class="text-3xl md:text-4xl font-bold font-serif text-gray-900 mb-4">Come funziona?</h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">Prenotare un appuntamento non è mai stato così semplice. Bastano tre semplici passaggi.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            <!-- Step 1 -->
            <div class="text-center" data-aos="fade-up" data-aos-delay="100">
                <div class="relative mx-auto w-24 h-24 bg-primary-600 rounded-full text-white flex items-center justify-center text-3xl font-bold shadow-lg mb-6">
                    1
                    <div class="absolute -right-4 top-1/2 hidden md:block">
                        <svg width="40" height="12" viewBox="0 0 40 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M39.5303 6.53033C39.8232 6.23744 39.8232 5.76256 39.5303 5.46967L34.7574 0.696699C34.4645 0.403806 33.9896 0.403806 33.6967 0.696699C33.4038 0.989593 33.4038 1.46447 33.6967 1.75736L37.9393 6L33.6967 10.2426C33.4038 10.5355 33.4038 11.0104 33.6967 11.3033C33.9896 11.5962 34.4645 11.5962 34.7574 11.3033L39.5303 6.53033ZM0 6.75H39V5.25H0V6.75Z" fill="#3a6fff"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Registrati</h3>
                <p class="text-gray-600">Crea un account gratuito in pochi secondi, inserendo i tuoi dati principali.</p>
            </div>

            <!-- Step 2 -->
            <div class="text-center" data-aos="fade-up" data-aos-delay="200">
                <div class="relative mx-auto w-24 h-24 bg-primary-600 rounded-full text-white flex items-center justify-center text-3xl font-bold shadow-lg mb-6">
                    2
                    <div class="absolute -right-4 top-1/2 hidden md:block">
                        <svg width="40" height="12" viewBox="0 0 40 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M39.5303 6.53033C39.8232 6.23744 39.8232 5.76256 39.5303 5.46967L34.7574 0.696699C34.4645 0.403806 33.9896 0.403806 33.6967 0.696699C33.4038 0.989593 33.4038 1.46447 33.6967 1.75736L37.9393 6L33.6967 10.2426C33.4038 10.5355 33.4038 11.0104 33.6967 11.3033C33.9896 11.5962 34.4645 11.5962 34.7574 11.3033L39.5303 6.53033ZM0 6.75H39V5.25H0V6.75Z" fill="#3a6fff"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Scegli e prenota</h3>
                <p class="text-gray-600">Seleziona il barbiere, il servizio, l'operatore e l'orario desiderato in pochi click.</p>
            </div>

            <!-- Step 3 -->
            <div class="text-center" data-aos="fade-up" data-aos-delay="300">
                <div class="mx-auto w-24 h-24 bg-primary-600 rounded-full text-white flex items-center justify-center text-3xl font-bold shadow-lg mb-6">
                    3
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Confermato!</h3>
                <p class="text-gray-600">Ricevi la conferma dell'appuntamento e presentati semplicemente all'orario stabilito.</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16" data-aos="fade-up">
            <h2 class="text-3xl md:text-4xl font-bold font-serif text-gray-900 mb-4">I nostri clienti parlano di noi</h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">Leggi le esperienze di chi utilizza BarberBook ogni giorno.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Testimonial 1 -->
            <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300" data-aos="fade-up" data-aos-delay="100">
                <div class="flex items-center mb-4">
                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Cliente" class="w-12 h-12 rounded-full mr-4">
                    <div>
                        <h4 class="font-bold text-gray-900">Marco Rossi</h4>
                        <p class="text-gray-500 text-sm">Cliente dal 2023</p>
                    </div>
                </div>
                <div class="flex text-yellow-400 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                </div>
                <p class="text-gray-600">"Finalmente posso prenotare quando voglio, anche di notte! Niente più telefonate e attese. Servizio impeccabile e sempre puntuale."</p>
            </div>

            <!-- Testimonial 2 -->
            <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300" data-aos="fade-up" data-aos-delay="200">
                <div class="flex items-center mb-4">
                    <img src="https://randomuser.me/api/portraits/men/58.jpg" alt="Cliente" class="w-12 h-12 rounded-full mr-4">
                    <div>
                        <h4 class="font-bold text-gray-900">Alessandro Bianchi</h4>
                        <p class="text-gray-500 text-sm">Cliente dal 2022</p>
                    </div>
                </div>
                <div class="flex text-yellow-400 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                </div>
                <p class="text-gray-600">"Il miglior servizio di prenotazione che abbia mai provato. Interfaccia semplice, promemoria puntuali e possibilità di modificare gli appuntamenti con facilità."</p>
            </div>

            <!-- Testimonial 3 -->
            <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300" data-aos="fade-up" data-aos-delay="300">
                <div class="flex items-center mb-4">
                    <img src="https://randomuser.me/api/portraits/women/24.jpg" alt="Cliente" class="w-12 h-12 rounded-full mr-4">
                    <div>
                        <h4 class="font-bold text-gray-900">Giulia Ferretti</h4>
                        <p class="text-gray-500 text-sm">Cliente dal 2023</p>
                    </div>
                </div>
                <div class="flex text-yellow-400 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                </div>
                <p class="text-gray-600">"Mi piace poter vedere tutti i servizi disponibili con i relativi prezzi, e scegliere in anticipo l'operatore che preferisco. Mai più stress per la prenotazione!"</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="py-20 bg-primary-600 text-white relative">
    <div class="absolute inset-0 overflow-hidden">
        <svg class="absolute left-0 top-0 h-20 w-full text-white opacity-10" viewBox="0 0 500 50" preserveAspectRatio="none">
            <path d="M0,50 L0,0 Q250,40 500,0 L500,50 Z" fill="currentColor" />
        </svg>
    </div>

    <div class="container mx-auto px-4 relative z-10">
        <div class="max-w-xl mx-auto text-center" data-aos="zoom-in">
            <h2 class="text-3xl md:text-4xl font-bold font-serif mb-6">Pronto a iniziare?</h2>
            <p class="text-white/90 text-lg mb-8">Unisciti a migliaia di utenti soddisfatti e scopri come BarberBook può semplificare le tue prenotazioni dal barbiere.</p>
            <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                <a href="register.php" class="bg-accent-500 text-white px-8 py-3 rounded-lg shadow-lg hover:bg-accent-600 transition-all duration-300 text-center font-medium btn-hover-effect">Registrati ora</a>
                <a href="register_barbiere.php" class="bg-white/10 backdrop-blur-sm text-white border border-white/30 px-8 py-3 rounded-lg hover:bg-white/20 transition-all duration-300 text-center font-medium">Sei un barbiere?</a>
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

    // Fade in sections on scroll
    document.addEventListener('DOMContentLoaded', function() {
        const sections = document.querySelectorAll('.fade-in-section');

        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                }
            });
        }, { threshold: 0.1 });

        sections.forEach(section => {
            observer.observe(section);
        });
    });
</script>
</body>
</html>
