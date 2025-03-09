/* script.js - File JavaScript principale con funzionalità Bootstrap incorporate */

// Implementazione minima di tooltip e popover
(function ($) {
	// Tooltip semplificato
	if (typeof $.fn.tooltip !== 'function') {
		$.fn.tooltip = function (options) {
			var defaults = {
				placement: 'top',
				trigger: 'hover',
				title: '',
				container: 'body',
			};

			var settings = $.extend({}, defaults, options);

			return this.each(function () {
				var $this = $(this);
				var title = $this.attr('title') || $this.data('original-title') || '';

				if (title) {
					$this.attr('data-original-title', title).removeAttr('title');

					var $tooltip = $(
						'<div class="custom-tooltip tooltip-' +
							settings.placement +
							'">' +
							title +
							'</div>'
					);

					$this.on('mouseenter', function () {
						$('body').append($tooltip);

						var position = {};
						var thisOffset = $this.offset();
						var thisWidth = $this.outerWidth();
						var thisHeight = $this.outerHeight();
						var tooltipWidth = $tooltip.outerWidth();
						var tooltipHeight = $tooltip.outerHeight();

						switch (settings.placement) {
							case 'top':
								position = {
									top: thisOffset.top - tooltipHeight - 5,
									left: thisOffset.left + (thisWidth / 2 - tooltipWidth / 2),
								};
								break;
							case 'bottom':
								position = {
									top: thisOffset.top + thisHeight + 5,
									left: thisOffset.left + (thisWidth / 2 - tooltipWidth / 2),
								};
								break;
							case 'left':
								position = {
									top: thisOffset.top + (thisHeight / 2 - tooltipHeight / 2),
									left: thisOffset.left - tooltipWidth - 5,
								};
								break;
							case 'right':
								position = {
									top: thisOffset.top + (thisHeight / 2 - tooltipHeight / 2),
									left: thisOffset.left + thisWidth + 5,
								};
								break;
						}

						$tooltip
							.css({
								position: 'absolute',
								top: position.top + 'px',
								left: position.left + 'px',
								'z-index': 1070,
								display: 'block',
								padding: '3px 8px',
								color: '#fff',
								'background-color': '#000',
								'border-radius': '4px',
								'font-size': '12px',
								opacity: 0,
							})
							.animate({ opacity: 1 }, 200);
					});

					$this.on('mouseleave', function () {
						$tooltip.animate({ opacity: 0 }, 200, function () {
							$(this).remove();
						});
					});
				}
			});
		};
	}

	// Popover semplificato
	if (typeof $.fn.popover !== 'function') {
		$.fn.popover = function () {
			// Implementazione minima, solo per evitare errori
			return this;
		};
	}
})(jQuery);

// Aggiungi stili CSS per tooltip direttamente nel DOM
(function () {
	if (!document.getElementById('custom-tooltip-styles')) {
		var style = document.createElement('style');
		style.id = 'custom-tooltip-styles';
		style.innerHTML = `
            .custom-tooltip {
                max-width: 200px;
                text-align: center;
                font-family: sans-serif;
                font-size: 12px;
                line-height: 1.4;
            }
        `;
		document.head.appendChild(style);
	}
})();

// Funzionalità principale
$(document).ready(function () {
	// Gestione menu mobile
	$('.mobile-menu-toggle').click(function () {
		$('.nav-menu').toggleClass('active');
	});

	// Chiudi menu mobile quando si clicca su un link
	$('.nav-menu a').click(function () {
		if ($('.nav-menu').hasClass('active')) {
			$('.nav-menu').removeClass('active');
		}
	});

	// Animazione smooth scroll per gli ancoraggi
	$('a[href^="#"]').on('click', function (event) {
		var target = $(this.getAttribute('href'));
		if (target.length) {
			event.preventDefault();
			$('html, body')
				.stop()
				.animate(
					{
						scrollTop: target.offset().top - 100,
					},
					800
				);
		}
	});

	// Attiva tooltips
	$('[data-toggle="tooltip"]').tooltip();

	// Attiva popovers
	$('[data-toggle="popover"]').popover();

	// Validazione form
	$('form').on('submit', function () {
		var valid = true;

		// Validazione campi obbligatori
		$(this)
			.find('[required]')
			.each(function () {
				if ($(this).val() === '') {
					$(this).addClass('is-invalid');
					valid = false;
				} else {
					$(this).removeClass('is-invalid');
				}
			});

		// Validazione email
		$(this)
			.find('input[type="email"]')
			.each(function () {
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
	$('input, select, textarea').on('input change', function () {
		$(this).removeClass('is-invalid');
	});

	// Animazione fade-out per messaggi di successo/errore dopo 5 secondi
	setTimeout(function () {
		$('.success-message, .error-message').fadeOut(500);
	}, 5000);
});

/* notifications.js - Gestione delle notifiche */
document.addEventListener('DOMContentLoaded', function () {
	const notificationBell = document.getElementById('notification-bell');
	const notificationDropdown = document.getElementById('notification-dropdown');
	const notificationList = document.getElementById('notification-list');

	if (!notificationBell) return; // Se non c'è la campanella, esci

	// Gestione click sulla campanella
	notificationBell.addEventListener('click', function (e) {
		e.stopPropagation();

		// Mostra/nascondi il dropdown
		if (notificationDropdown.classList.contains('show')) {
			notificationDropdown.classList.remove('show');
		} else {
			notificationDropdown.classList.add('show');
			loadNotifications();
		}
	});

	// Chiudi il dropdown quando si clicca fuori
	document.addEventListener('click', function (e) {
		if (
			notificationDropdown.classList.contains('show') &&
			!notificationDropdown.contains(e.target)
		) {
			notificationDropdown.classList.remove('show');
		}
	});

	// Impedisci che il click sul dropdown chiuda il dropdown
	notificationDropdown.addEventListener('click', function (e) {
		e.stopPropagation();
	});

	// Funzione per caricare le notifiche
	function loadNotifications() {
		// Mostra l'indicatore di caricamento
		notificationList.innerHTML =
			'<li class="notification-item text-center">Caricamento...</li>';

		// Determina l'URL per l'AJAX in base alla directory corrente
		const isBarbiereArea = window.location.pathname.includes('/barbiere/');
		let ajaxUrl = isBarbiereArea ? 'get_notifiche.php' : 'get_notifiche.php';

		// Fai una richiesta AJAX per ottenere le notifiche
		fetch(ajaxUrl)
			.then((response) => response.json())
			.then((data) => {
				// Svuota la lista
				notificationList.innerHTML = '';

				// Se non ci sono notifiche
				if (data.length === 0) {
					notificationList.innerHTML =
						'<li class="notification-item text-center">Nessuna notifica</li>';
					return;
				}

				// Aggiungi ogni notifica alla lista
				data.forEach((notification) => {
					const li = document.createElement('li');
					li.className =
						'notification-item' + (notification.letto === '0' ? ' unread' : '');

					// Aggiungi l'icona appropriata in base al tipo di notifica
					let icon = '';
					switch (notification.tipo) {
						case 'appuntamento':
							icon =
								'<i class="fas fa-calendar-alt text-primary-600 mr-2"></i>';
							break;
						case 'modifica':
							icon = '<i class="fas fa-edit text-warning-600 mr-2"></i>';
							break;
						case 'cancellazione':
							icon = '<i class="fas fa-times-circle text-danger-600 mr-2"></i>';
							break;
						case 'sistema':
							icon = '<i class="fas fa-info-circle text-info-600 mr-2"></i>';
							break;
						default:
							icon = '<i class="fas fa-bell text-gray-600 mr-2"></i>';
					}

					// Formatta la data in modo leggibile
					const data = new Date(notification.data_creazione);
					const dataFormattata =
						data.toLocaleDateString('it-IT') +
						' ' +
						data.toLocaleTimeString('it-IT', {
							hour: '2-digit',
							minute: '2-digit',
						});

					// Imposta l'HTML della notifica
					li.innerHTML = `
                        <div class="notification-content">
                            ${icon} ${notification.messaggio}
                            <div class="notification-time">${dataFormattata}</div>
                        </div>
                    `;

					// Aggiungi il handler per il click che segna la notifica come letta
					li.addEventListener('click', function () {
						markAsRead(notification.id);
						li.classList.remove('unread');
					});

					// Aggiungi alla lista
					notificationList.appendChild(li);
				});
			})
			.catch((error) => {
				console.error('Errore nel caricamento delle notifiche:', error);
				notificationList.innerHTML =
					'<li class="notification-item text-center">Errore nel caricamento delle notifiche</li>';
			});
	}

	// Funzione per marcare una notifica come letta
	function markAsRead(notificationId) {
		// Determina l'URL per l'AJAX in base alla directory corrente
		const isBarbiereArea = window.location.pathname.includes('/barbiere/');
		let ajaxUrl = isBarbiereArea
			? 'mark_notification_read.php'
			: 'mark_notification_read.php';

		// Invia una richiesta per segnare la notifica come letta
		fetch(ajaxUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: 'notification_id=' + notificationId,
		})
			.then((response) => response.json())
			.then((data) => {
				if (data.success) {
					// Aggiorna il conteggio delle notifiche
					const badge = document.querySelector('.notification-badge');
					if (badge) {
						const count = parseInt(badge.textContent) - 1;
						if (count <= 0) {
							badge.style.display = 'none';
						} else {
							badge.textContent = count;
						}
					}
				}
			})
			.catch((error) => {
				console.error(
					'Errore nella marcatura della notifica come letta:',
					error
				);
			});
	}

	// Gestione "segna tutte come lette"
	const markAllReadBtn = document.querySelector('.mark-all-read');
	if (markAllReadBtn) {
		markAllReadBtn.addEventListener('click', function (e) {
			e.preventDefault();

			// Determina l'URL per l'AJAX in base alla directory corrente
			const isBarbiereArea = window.location.pathname.includes('/barbiere/');
			let ajaxUrl = isBarbiereArea ? 'mark_all_read.php' : 'mark_all_read.php';

			// Invia una richiesta per segnare tutte le notifiche come lette
			fetch(ajaxUrl)
				.then((response) => response.json())
				.then((data) => {
					if (data.success) {
						// Rimuovi la classe unread da tutte le notifiche
						document
							.querySelectorAll('.notification-item.unread')
							.forEach((item) => {
								item.classList.remove('unread');
							});

						// Nascondi il badge
						const badge = document.querySelector('.notification-badge');
						if (badge) {
							badge.style.display = 'none';
						}
					}
				})
				.catch((error) => {
					console.error(
						'Errore nella marcatura di tutte le notifiche come lette:',
						error
					);
				});
		});
	}

	// Controlla periodicamente se ci sono nuove notifiche (ogni 60 secondi)
	setInterval(function () {
		if (!notificationDropdown.classList.contains('show')) {
			checkNewNotifications();
		}
	}, 60000);

	// Funzione per controllare se ci sono nuove notifiche
	function checkNewNotifications() {
		// Determina l'URL per l'AJAX in base alla directory corrente
		const isBarbiereArea = window.location.pathname.includes('/barbiere/');
		let ajaxUrl = isBarbiereArea
			? 'count_unread_notifications.php'
			: 'count_unread_notifications.php';

		fetch(ajaxUrl)
			.then((response) => response.json())
			.then((data) => {
				const badge = document.querySelector('.notification-badge');

				if (data.count > 0) {
					// Se c'è il badge, aggiorna il conteggio
					if (badge) {
						badge.textContent = data.count;
						badge.style.display = 'flex';
					}
					// Altrimenti, crea il badge
					else {
						const newBadge = document.createElement('span');
						newBadge.className = 'notification-badge';
						newBadge.textContent = data.count;
						notificationBell.appendChild(newBadge);
					}
				} else {
					// Se non ci sono notifiche non lette, nascondi il badge
					if (badge) {
						badge.style.display = 'none';
					}
				}
			})
			.catch((error) => {
				console.error('Errore nel controllo delle notifiche:', error);
			});
	}
});
