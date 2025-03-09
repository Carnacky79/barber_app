/* script.js - File JavaScript principale */
$(document).ready(function() {
    // Gestione menu mobile
    $('.mobile-menu-toggle').click(function() {
        $('.nav-menu').toggleClass('active');
    });

    // Chiudi menu mobile quando si clicca su un link
    $('.nav-menu a').click(function() {
        if ($('.nav-menu').hasClass('active')) {
            $('.nav-menu').removeClass('active');
        }
    });

    // Animazione smooth scroll per gli ancoraggi
    $('a[href^="#"]').on('click', function(event) {
        var target = $(this.getAttribute('href'));
        if (target.length) {
            event.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 800);
        }
    });

    // Attiva tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Attiva popovers
    $('[data-toggle="popover"]').popover();

    // Validazione form
    $('form').on('submit', function() {
        var valid = true;

        // Validazione campi obbligatori
        $(this).find('[required]').each(function() {
            if ($(this).val() === '') {
                $(this).addClass('is-invalid');
                valid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // Validazione email
        $(this).find('input[type="email"]').each(function() {
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if ($(this).val() !== '' && !emailRegex.test($(this).val())) {
                $(this).addClass('is-invalid');
                valid = false;
            }
        });

        // Validazione password
        var password = $(this).find('input[name="password"]');
        var confirmPassword = $(this).find('input[name="confirm_password"]');

        if (password.length && confirmPassword.length) {
            if (password.val() !== confirmPassword.val()) {
                password.addClass('is-invalid');
                confirmPassword.addClass('is-invalid');
                valid = false;
            }
        }

        return valid;
    });

    // Rimuovi classe is-invalid quando l'utente modifica un campo
    $('input, select, textarea').on('input change', function() {
        $(this).removeClass('is-invalid');
    });

    // Animazione fade-out per messaggi di successo/errore dopo 5 secondi
    setTimeout(function() {
        $('.success-message, .error-message').fadeOut(500);
    }, 5000);
});
