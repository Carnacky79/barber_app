<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyBarber - Prenota il tuo barbiere in pochi click</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#EEEDF5',
                            100: '#DEDCEB',
                            200: '#BDB9D8',
                            300: '#9C96C4',
                            400: '#7B73B0',
                            500: '#5A509C',
                            600: '#483E88', /* Logo purple */
                            700: '#3C3472',
                            800: '#302A5C',
                            900: '#242046',
                            950: '#1B1835',
                        },
                        secondary: {
                            50: '#F7FBFA',
                            100: '#EFF7F5',
                            200: '#DFEFEB',
                            300: '#CFE7E1',
                            400: '#B0D7CD',
                            500: '#8EC7B9',
                            600: '#4CB39A', /* Logo teal */
                            700: '#3A9A84',
                            800: '#2C7C6A',
                            900: '#1E5E50',
                            950: '#143F36',
                        },
                        accent: {
                            50: '#F0FDF9',
                            100: '#CCFBEF',
                            200: '#99F6E0',
                            300: '#5CECD1',
                            400: '#2DDEC0',
                            500: '#14B8A2',
                            600: '#0E9384',
                            700: '#0F766E',
                            800: '#115E59',
                            900: '#134E4A',
                            950: '#042F2E',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    },
                    animation: {
                        'pulse-subtle': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    }
                }
            }
        }
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- AlpineJS -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- Custom Styles -->
    <style>
        .gradient-hero {
            background: linear-gradient(135deg, #483E88 0%, #302A5C 100%);
        }
        .gradient-cta {
            background: linear-gradient(135deg, #3A9A84 0%, #2C7C6A 100%);
        }
        .btn-hover {
            transition: all 0.3s ease;
        }
        .btn-hover:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
        }
        .testimonial-card {
            position: relative;
        }
        .testimonial-card::before {
            content: """;
            position: absolute;
            top: -15px;
            left: 20px;
            font-size: 120px;
            font-family: 'Playfair Display', serif;
            color: rgba(76, 179, 154, 0.1);
            line-height: 1;
        }
    </style>
</head>

<body class="font-sans text-gray-800 bg-gray-50">
<!-- Header -->
<header class="bg-white shadow-sm sticky top-0 z-50" x-data="{ isOpen: false }">
    <div class="container mx-auto px-4 py-4">
        <div class="flex justify-between items-center">
            <a href="index.php" class="flex items-center space-x-2">
                <img src="img/logo_orizzontale.png" alt="EasyBarber" style="height:80px">
            </a>

            <!-- Desktop Menu -->
            <nav class="hidden md:flex items-center space-x-8">
                <a href="#" class="text-primary-600 font-medium hover:text-primary-500 transition-colors">Home</a>
                <a href="#" class="text-gray-600 font-medium hover:text-primary-500 transition-colors">Chi Siamo</a>
                <a href="#" class="text-gray-600 font-medium hover:text-primary-500 transition-colors">Contatti</a>
                <div class="flex space-x-3">
                    <a href="login.php" class="border border-primary-600 text-primary-600 px-4 py-2 rounded-lg hover:bg-primary-50 transition-colors font-medium">Accedi</a>
                    <a href="register.php" class="bg-secondary-600 text-white px-4 py-2 rounded-lg hover:bg-secondary-700 transition-colors font-medium btn-hover">Registrati</a>
                </div>
            </nav>

            <!-- Mobile menu button -->
            <button @click="isOpen = !isOpen" class="md:hidden text-gray-500 hover:text-primary-500 focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-show="!isOpen">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                </svg>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-show="isOpen" style="display: none;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Mobile Menu -->
        <div class="md:hidden bg-white mt-4 rounded-lg shadow-lg overflow-hidden" x-show="isOpen" style="display: none;"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95">
            <div class="py-2 space-y-1">
                <a href="#" class="block px-4 py-2 text-primary-600 font-medium hover:bg-primary-50">Home</a>
                <a href="#" class="block px-4 py-2 text-gray-600 font-medium hover:bg-primary-50">Chi Siamo</a>
                <a href="#" class="block px-4 py-2 text-gray-600 font-medium hover:bg-primary-50">Contatti</a>
                <a href="login.php" class="block px-4 py-2 text-gray-600 font-medium hover:bg-primary-50">Accedi</a>
                <a href="register.php" class="block px-4 py-2 bg-secondary-600 text-white font-medium hover:bg-secondary-700 mx-4 my-2 rounded-lg text-center">Registrati</a>
            </div>
        </div>
    </div>
</header>

<!-- Hero Section -->
<section class="gradient-hero text-white overflow-hidden">
    <div class="container mx-auto px-4 py-16 lg:py-24">
        <div class="flex flex-col lg:flex-row items-center justify-between">
            <div class="lg:w-1/2 lg:pr-12 mb-10 lg:mb-0">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold font-serif mb-6">
                    Il taglio perfetto è a soli <span class="text-secondary-400">tre click</span> di distanza
                </h1>
                <p class="text-lg md:text-xl mb-8 text-white/90 max-w-lg">
                    Dimentica code e chiamate. Con EasyBarber trovi il tuo stile, prenoti quando vuoi e risparmi tempo prezioso. Tutto online, tutto per te.
                </p>
                <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                    <a href="register.php" class="bg-secondary-600 text-white px-6 py-3.5 rounded-lg font-medium text-center btn-hover shadow-lg">
                        Inizia Ora
                    </a>
                    <a href="#" class="bg-white/10 backdrop-blur-sm text-white border border-white/20 px-6 py-3.5 rounded-lg font-medium text-center hover:bg-white/20 transition-colors">
                        Scopri di più
                    </a>
                </div>
            </div>
            <div class="lg:w-1/2 relative">
                <div class="relative mx-auto max-w-md">
                    <div class="bg-white p-3 rounded-xl shadow-2xl overflow-hidden relative z-10 card-hover">
                        <img src="https://images.unsplash.com/photo-1622286342621-4bd786c2447c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80"
                             alt="Barbiere professionista al lavoro"
                             class="rounded-lg w-full h-auto object-cover">
                        <div class="absolute top-1 -right-2 bg-white rounded-lg px-5 py-3 shadow-xl">
                            <div class="flex items-center space-x-3">
                                <div class="flex -space-x-2">
                                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Cliente" class="w-10 h-10 rounded-full border-2 border-white">
                                    <img src="https://randomuser.me/api/portraits/men/42.jpg" alt="Cliente" class="w-10 h-10 rounded-full border-2 border-white">
                                    <img src="https://randomuser.me/api/portraits/women/24.jpg" alt="Cliente" class="w-10 h-10 rounded-full border-2 border-white">
                                </div>
                                <div class="text-sm">
                                    <p class="text-gray-800 font-semibold">200+ clienti</p>
                                    <p class="text-gray-500">soddisfatti</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Decorative elements -->
                    <div class="absolute w-32 h-32 bg-secondary-500 rounded-full -top-10 -left-10 filter blur-2xl opacity-50"></div>
                    <div class="absolute w-24 h-24 bg-primary-400 rounded-full -bottom-10 -right-10 filter blur-xl opacity-50"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold font-serif text-gray-900 mb-4">Perché scegliere EasyBarber?</h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                Un'esperienza di prenotazione senza stress, per clienti moderni che valorizzano il proprio tempo.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 card-hover border border-gray-100">
                <div class="w-14 h-14 bg-primary-50 rounded-lg flex items-center justify-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Risparmia tempo</h3>
                <p class="text-gray-600">
                    Prenota in meno di 60 secondi, 24 ore su 24. Mai più attese al telefono o in negozio.
                </p>
            </div>

            <!-- Feature 2 -->
            <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 card-hover border border-gray-100">
                <div class="w-14 h-14 bg-primary-50 rounded-lg flex items-center justify-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Servizi personalizzati</h3>
                <p class="text-gray-600">
                    Scopri l'offerta completa di ogni barbiere, con descrizioni dettagliate e prezzi trasparenti.
                </p>
            </div>

            <!-- Feature 3 -->
            <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 card-hover border border-gray-100">
                <div class="w-14 h-14 bg-primary-50 rounded-lg flex items-center justify-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Scegli il tuo barbiere</h3>
                <p class="text-gray-600">
                    Trova il professionista che fa per te, sfoglia portfolio e recensioni per scegliere il migliore.
                </p>
            </div>

            <!-- Feature 4 -->
            <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 card-hover border border-gray-100">
                <div class="w-14 h-14 bg-primary-50 rounded-lg flex items-center justify-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Disponibilità in tempo reale</h3>
                <p class="text-gray-600">
                    Visualizza gli slot liberi aggiornati istantaneamente e scegli l'orario perfetto per te.
                </p>
            </div>

            <!-- Feature 5 -->
            <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 card-hover border border-gray-100">
                <div class="w-14 h-14 bg-primary-50 rounded-lg flex items-center justify-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Promemoria intelligenti</h3>
                <p class="text-gray-600">
                    Ricevi notifiche personalizzate prima dell'appuntamento, mai più dimenticanze o ritardi.
                </p>
            </div>

            <!-- Feature 6 -->
            <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 card-hover border border-gray-100">
                <div class="w-14 h-14 bg-primary-50 rounded-lg flex items-center justify-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Nessuna sorpresa</h3>
                <p class="text-gray-600">
                    Prezzi sempre chiari e visibili prima di prenotare. Paga in negozio o online, come preferisci.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- How it works -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold font-serif text-gray-900 mb-4">Come funziona?</h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                Basta un minuto per iniziare. Tre semplici passaggi, un'esperienza perfetta.
            </p>
        </div>

        <div class="max-w-4xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                <!-- Step 1 -->
                <div class="relative">
                    <div class="text-center mb-6">
                        <div class="relative mx-auto w-20 h-20 bg-primary-600 rounded-full text-white flex items-center justify-center text-2xl font-bold shadow-lg mb-4">
                            1
                            <!-- Hidden on mobile, visible on medium screens and up -->
                            <div class="absolute -right-5 top-1/2 transform -translate-y-1/2 hidden md:block">
                                <svg width="40" height="16" viewBox="0 0 40 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M39.7071 8.70711C40.0976 8.31658 40.0976 7.68342 39.7071 7.29289L33.3431 0.928932C32.9526 0.538408 32.3195 0.538408 31.9289 0.928932C31.5384 1.31946 31.5384 1.95262 31.9289 2.34315L37.5858 8L31.9289 13.6569C31.5384 14.0474 31.5384 14.6805 31.9289 15.0711C32.3195 15.4616 32.9526 15.4616 33.3431 15.0711L39.7071 8.70711ZM0 9H39V7H0V9Z" fill="#483E88"/>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Crea il tuo account</h3>
                        <p class="text-gray-600">
                            Registrati in pochi secondi con email o social. Nessuna complicazione, solo vantaggi.
                        </p>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="relative">
                    <div class="text-center mb-6">
                        <div class="relative mx-auto w-20 h-20 bg-primary-600 rounded-full text-white flex items-center justify-center text-2xl font-bold shadow-lg mb-4">
                            2
                            <!-- Hidden on mobile, visible on medium screens and up -->
                            <div class="absolute -right-5 top-1/2 transform -translate-y-1/2 hidden md:block">
                                <svg width="40" height="16" viewBox="0 0 40 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M39.7071 8.70711C40.0976 8.31658 40.0976 7.68342 39.7071 7.29289L33.3431 0.928932C32.9526 0.538408 32.3195 0.538408 31.9289 0.928932C31.5384 1.31946 31.5384 1.95262 31.9289 2.34315L37.5858 8L31.9289 13.6569C31.5384 14.0474 31.5384 14.6805 31.9289 15.0711C32.3195 15.4616 32.9526 15.4616 33.3431 15.0711L39.7071 8.70711ZM0 9H39V7H0V9Z" fill="#483E88"/>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Trova e prenota</h3>
                        <p class="text-gray-600">
                            Scegli barbiere, servizi e orario ideale. Personalizza ogni aspetto dell'esperienza.
                        </p>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="relative">
                    <div class="text-center mb-6">
                        <div class="mx-auto w-20 h-20 bg-primary-600 rounded-full text-white flex items-center justify-center text-2xl font-bold shadow-lg mb-4">
                            3
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Rilassati</h3>
                        <p class="text-gray-600">
                            Ricevi conferma immediata e promemoria. Presentati in negozio senza stress né attese.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold font-serif text-gray-900 mb-4">
                Cosa dicono i nostri clienti
            </h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                Non crederci sulla parola. Ecco le esperienze di chi usa EasyBarber ogni giorno.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
            <!-- Testimonial 1 -->
            <div class="bg-white p-8 rounded-xl shadow-lg testimonial-card card-hover border border-gray-100">
                <div class="flex items-center mb-4">
                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Marco Rossi" class="w-12 h-12 rounded-full mr-4">
                    <div>
                        <h4 class="font-bold text-gray-900">Marco Rossi</h4>
                        <p class="text-gray-500 text-sm">Cliente dal 2023</p>
                    </div>
                </div>
                <div class="flex text-yellow-400 mb-3">
                    <!-- 5 stars -->
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
                <p class="text-gray-600">
                    "Addio alle chiamate infinite! Prenoto a qualsiasi ora, vedo subito i servizi disponibili e non perdo tempo in attesa. Un cambio di vita."
                </p>
            </div>

            <!-- Testimonial 2 -->
            <div class="bg-white p-8 rounded-xl shadow-lg testimonial-card card-hover border border-gray-100">
                <div class="flex items-center mb-4">
                    <img src="https://randomuser.me/api/portraits/men/42.jpg" alt="Alessandro Bianchi" class="w-12 h-12 rounded-full mr-4">
                    <div>
                        <h4 class="font-bold text-gray-900">Alessandro Bianchi</h4>
                        <p class="text-gray-500 text-sm">Cliente dal 2022</p>
                    </div>
                </div>
                <div class="flex text-yellow-400 mb-3">
                    <!-- 5 stars -->
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
                <p class="text-gray-600">
                    "Interfaccia pulita, funzionale, senza complicazioni inutili. Finalmente posso prenotare il mio barbiere preferito senza compromessi."
                </p>
            </div>

            <!-- Testimonial 3 -->
            <div class="bg-white p-8 rounded-xl shadow-lg testimonial-card card-hover border border-gray-100">
                <div class="flex items-center mb-4">
                    <img src="https://randomuser.me/api/portraits/women/24.jpg" alt="Giulia Ferretti" class="w-12 h-12 rounded-full mr-4">
                    <div>
                        <h4 class="font-bold text-gray-900">Giulia Ferretti</h4>
                        <p class="text-gray-500 text-sm">Cliente dal 2023</p>
                    </div>
                </div>
                <div class="flex text-yellow-400 mb-3">
                    <!-- 5 stars -->
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
                <p class="text-gray-600">
                    "I promemoria sono fantastici! Non dimentico più gli appuntamenti e posso modificarli facilmente se ho imprevisti. Semplicemente geniale."
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-20 gradient-cta text-white">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto text-center">
            <h2 class="text-3xl md:text-4xl font-bold font-serif mb-6">Pronto a risparmiare tempo?</h2>
            <p class="text-lg text-white/90 mb-8">
                Unisciti a migliaia di clienti soddisfatti. Prenota il tuo primo appuntamento in meno di un minuto.
            </p>
            <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                <a href="register.php" class="bg-secondary-600 text-white px-8 py-4 rounded-lg shadow-lg hover:bg-secondary-700 transition-all duration-300 text-center font-medium btn-hover">
                    Inizia ora
                </a>
                <a href="register_barbiere.php" class="bg-white/10 backdrop-blur-sm border border-white/20 text-white px-8 py-4 rounded-lg hover:bg-white/20 transition-all duration-300 text-center font-medium">
                    Sei un barbiere?
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-gray-900 text-white py-12">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="md:col-span-1">
                <a href="#" class="flex items-center space-x-2 mb-6 justify-center">
                    <img src="img/logo_easy_barber.png" alt="EasyBarber" style="height:150px">
                </a>
                <p class="text-gray-400 mb-4">La piattaforma per prenotare il tuo barbiere in pochi click.</p>
                <div class="flex space-x-4">
                    <!-- Social icons removed as in original -->
                </div>
            </div>

            <div class="md:col-span-3 grid grid-cols-1 sm:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">Pagine</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">Home</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">Chi Siamo</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">Contatti</a></li>
                        <li><a href="register_barbiere.php" class="text-gray-400 hover:text-white transition-colors duration-300">Registra la tua attività</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-4">Legali</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">Termini e Condizioni</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">Cookie Policy</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-4">Contattaci</h3>
                    <ul class="space-y-3">
                        <li class="flex items-center space-x-3 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span>info@easybarber.it</span>
                        </li>
                        <li class="flex items-center space-x-3 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span>+39 3483760064</span>
                        </li>
                        <!-- Address line removed as in original -->
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-800 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
            <p class="text-gray-400 text-sm">&copy; 2025 EasyBarber. Tutti i diritti riservati.</p>
            <p class="text-gray-400 text-sm mt-4 md:mt-0">Realizzato con ❤️ da EggWebNapoli</p>
        </div>
    </div>
</footer>
</body>
</html>
