<?php
// includes/header.php - Header per le pagine utente
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BarberBook</title>
    <link rel="stylesheet" href="../css/style.css">
    <!-- Nel file head prima di chiudere </head> -->
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
                        sans: ['Inter var', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-out',
                        'slide-up': 'slideUp 0.5s ease-out',
                        'slide-down': 'slideDown 0.5s ease-out',
                        'bounce-slow': 'bounce 3s infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(20px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        },
                        slideDown: {
                            '0%': { transform: 'translateY(-20px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        },
                    },
                }
            }
        }
    </script>

    <!-- Font import -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- AlpineJS per effetti interattivi -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- Icone da Heroicons -->
    <script src="https://cdn.jsdelivr.net/npm/@heroicons/vue@2.0.16/dist/heroicons.min.js"></script>

    <!-- CSS per compatibilità -->
    <style>
        /* Stili di compatibilità per stile esistente */
        .container {
            width: 100% !important;
            max-width: 1280px !important;
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }

        /* Stili personalizzati */
        .ripple {
            position: relative;
            overflow: hidden;
        }

        .ripple:after {
            content: "";
            display: block;
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            background-image: radial-gradient(circle, rgba(255, 255, 255, 0.7) 10%, transparent 10.01%);
            background-repeat: no-repeat;
            background-position: 50%;
            transform: scale(10, 10);
            opacity: 0;
            transition: transform 0.5s, opacity 0.8s;
        }

        .ripple:active:after {
            transform: scale(0, 0);
            opacity: 0.3;
            transition: 0s;
        }

        /* Stili per Drawer Menu Mobile */
        .drawer {
            transition: transform 0.3s ease-in-out;
        }

        .drawer-open {
            transform: translateX(0);
        }

        .drawer-closed {
            transform: translateX(-100%);
        }

        /* Effetto Hover per bottoni */
        .btn-hover-effect {
            transition: all 0.3s ease;
        }

        .btn-hover-effect:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        /* Effetto fade-in per sezioni */
        .fade-in-section {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }

        .fade-in-section.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Animazione pulsazione */
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(59, 130, 246, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
            }
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body>
<header>
    <div class="header-container">
        <div class="logo">
            <a href="index.php">BarberBook</a>
        </div>
        <button class="mobile-menu-toggle">&#9776;</button>
        <ul class="nav-menu">
            <?php if (isLoggedIn()): ?>
                <li><a href="dashboard.php" class="<?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a></li>
                <li><a href="prenota.php" class="<?php echo $current_page === 'prenota.php' ? 'active' : ''; ?>">Prenota</a></li>
                <li><a href="profilo.php" class="<?php echo $current_page === 'profilo.php' ? 'active' : ''; ?>">Profilo</a></li>
                <li><a href="logout.php">Esci</a></li>
            <?php else: ?>
                <li><a href="login.php" class="<?php echo $current_page === 'login.php' ? 'active' : ''; ?>">Accedi</a></li>
                <li><a href="register.php" class="<?php echo $current_page === 'register.php' ? 'active' : ''; ?>">Registrati</a></li>
            <?php endif; ?>
        </ul>
    </div>
</header>
