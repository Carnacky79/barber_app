# Guida all'implementazione del sistema di notifiche in BarberBook

Questo documento spiega come è stato implementato il sistema di notifiche in BarberBook e come utilizzarlo.

## Struttura del sistema di notifiche

Il sistema di notifiche è composto da:

1. **Tabella nel database**: `notifiche` - contiene tutte le notifiche per utenti e barbieri
2. **File di funzioni**: `functions/notifications.php` - contiene le funzioni principali per gestire le notifiche
3. **File JavaScript**: `js/notifications.js` - gestisce le interazioni lato client per le notifiche
4. **Pagine per visualizzare le notifiche**: `tutte_notifiche.php` e `barbiere/tutte_notifiche.php`
5. **Endpoint AJAX**: vari file PHP per operazioni asincrone sulle notifiche

## Creazione di notifiche

Le notifiche vengono create utilizzando la funzione `createNotification()` definita in `functions/notifications.php`. Questa funzione può essere chiamata in vari punti dell'applicazione quando è necessario notificare un utente o un barbiere.

```php
// Esempio di creazione di una notifica per un utente
createNotification(
    $utente_id,     // ID dell'utente (null se la notifica è per un barbiere)
    null,           // ID del barbiere (null se la notifica è per un utente)
    'appuntamento', // Tipo di notifica
    "Il tuo appuntamento è stato confermato." // Messaggio
);

// Esempio di creazione di una notifica per un barbiere
createNotification(
    null,            // ID dell'utente (null se la notifica è per un barbiere)
    $barbiere_id,    // ID del barbiere (null se la notifica è per un utente)
    'appuntamento',  // Tipo di notifica
    "Nuovo appuntamento prenotato." // Messaggio
);
```

## Tipi di notifiche

I tipi di notifiche supportati sono:

- `appuntamento`: per notifiche relative a nuovi appuntamenti
- `modifica`: per notifiche relative a modifiche di appuntamenti
- `cancellazione`: per notifiche relative a cancellazioni di appuntamenti
- `sistema`: per notifiche di sistema

## Punti in cui generare notifiche

Ecco un elenco dei punti nell'applicazione dove è consigliabile generare notifiche:

1. **Creazione di un appuntamento**:
    - Notifica all'utente che ha prenotato
    - Notifica al barbiere che ha ricevuto una prenotazione

2. **Conferma di un appuntamento**:
    - Notifica all'utente che il suo appuntamento è stato confermato

3. **Modifica di un appuntamento**:
    - Notifica all'utente se l'appuntamento è stato modificato dal barbiere
    - Notifica al barbiere se l'appuntamento è stato modificato dall'utente

4. **Cancellazione di un appuntamento**:
    - Notifica all'utente se l'appuntamento è stato cancellato dal barbiere
    - Notifica al barbiere se l'appuntamento è stato cancellato dall'utente

5. **Promemoria di appuntamento**:
    - Notifica all'utente 24 ore prima dell'appuntamento (richiede un cron job)

## Implementazione nell'interfaccia utente

Le notifiche sono visualizzate nell'interfaccia utente tramite:

1. **Icona della campanella** nel menu di navigazione, con un badge che indica il numero di notifiche non lette
2. **Dropdown** che mostra le notifiche recenti quando si fa clic sulla campanella
3. **Pagina dedicata** (`tutte_notifiche.php`) che mostra tutte le notifiche

## Esempi pratici di implementazione

### Aggiungere una notifica quando viene creato un nuovo appuntamento

```php
// In prenota.php, dopo aver salvato l'appuntamento nel database
$appuntamento_id = $conn->insert_id;

// Notifica per l'utente
createNotification(
    $_SESSION['user_id'],
    null,
    'appuntamento',
    "Il tuo appuntamento per {$servizio['nome']} è stato prenotato con successo per il " . 
    date('d/m/Y', strtotime($data)) . " alle " . date('H:i', strtotime($ora_inizio)) . "."
);

// Notifica per il barbiere
createNotification(
    null,
    $barbiere_id,
    'appuntamento',
    "Nuovo appuntamento prenotato da {$_SESSION['user_name']} per il servizio {$servizio['nome']} il " . 
    date('d/m/Y', strtotime($data)) . " alle " . date('H:i', strtotime($ora_inizio)) . "."
);
```

### Aggiungere una notifica quando un appuntamento viene confermato

```php
// In barbiere/conferma_appuntamento.php, dopo aver aggiornato lo stato dell'appuntamento
createNotification(
    $appuntamento['utente_id'],
    null,
    'appuntamento',
    "Il tuo appuntamento per {$appuntamento['servizio_nome']} il " . 
    date('d/m/Y', strtotime($appuntamento['data_appuntamento'])) . 
    " alle " . date('H:i', strtotime($appuntamento['ora_inizio'])) . 
    " è stato confermato."
);
```

## Manutenzione

Per mantenere le prestazioni del sistema, è consigliabile implementare una pulizia periodica delle notifiche obsolete, ad esempio eliminando le notifiche più vecchie di 3 mesi. Questo può essere fatto con un cron job che esegue una query SQL come:

```sql
DELETE FROM notifiche WHERE data_creazione < DATE_SUB(NOW(), INTERVAL 3 MONTH);
```

## Schema tabella notifiche

```sql
CREATE TABLE `notifiche` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utente_id` int(11) DEFAULT NULL,
  `barbiere_id` int(11) DEFAULT NULL,
  `tipo` enum('appuntamento','modifica','cancellazione','sistema') NOT NULL,
  `messaggio` text NOT NULL,
  `letto` tinyint(1) NOT NULL DEFAULT 0,
  `data_creazione` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `utente_id` (`utente_id`),
  KEY `barbiere_id` (`barbiere_id`),
  CONSTRAINT `notifiche_ibfk_1` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notifiche_ibfk_2` FOREIGN KEY (`barbiere_id`) REFERENCES `barbieri` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```
