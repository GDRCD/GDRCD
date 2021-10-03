<?php

/**
 * In base alla tipologia di richiesta, eseguo le relative operazioni di invio messaggio
 *
 * La tipologia di richiesta viene determinata all'interno di $_POST['multipli'],
 * per aggiungere nuove richieste è sufficiente integrare un nuovo caso nello switch
 */

// Ottengo la richiesta
$opRequest = gdrcd_filter('get', $_POST['multipli']);

switch($opRequest) {
    /**
     * INVIO MESSAGGIO A SINGOLO
     * ATTENZIONE: nel caso vengano individuati più destinatari (separati da virgola),
     * verrà comunque inviato il messaggio solo al primo individuo
     */
    default:
    case 'singolo':
        // Ottengo il destinatario, prendo il primo inviato
        $destinatari = explode(',', gdrcd_filter('get', $_POST['destinatario']));
        $destinatario = trim($destinatari[0]);

        // In caso di segnalazione
        if (gdrcd_filter('get', $_POST['url']) != "") {
            $_POST['testo'] = $_SESSION['login'] . ' ti ha segnalato questo [url=' . $_POST['url'] . ']link[/url].';
        }

        // Determino se il Personaggio a cui sto inviando esiste
        $result = gdrcd_query("SELECT nome FROM personaggio WHERE nome = '" . $destinatario . "'", 'result');
        if ((gdrcd_query($result, 'num_rows') > 0) && (empty($destinatario) === false)) {
            // Inserisco i messaggi
            gdrcd_query("INSERT INTO messaggi (mittente, destinatario, spedito, oggetto, testo) VALUES ('" . $_SESSION['login'] . "', '" . gdrcd_capital_letter(gdrcd_filter('in', $destinatario)) . "', NOW(), '" . gdrcd_filter('in', $_POST['otteggo']) . "', '" . gdrcd_filter('in', $_POST['testo']) . "')");
            gdrcd_query("INSERT INTO backmessaggi (mittente, destinatario, spedito, oggetto, testo) VALUES ('" . $_SESSION['login'] . "', '" . gdrcd_capital_letter(gdrcd_filter('in', $destinatario)) . "', NOW(), '" . gdrcd_filter('in', $_POST['otteggo']) . "', '" . gdrcd_filter('in', $_POST['testo']) . "')");
        }
        break;

    /**
     * INVIO MESSAGGIO A TUTTI I PRESENTI
     */
    case 'presenti':
        $query = "SELECT personaggio.nome 
                    FROM personaggio 
                    WHERE personaggio.ora_entrata > personaggio.ora_uscita 
                      AND DATE_ADD(personaggio.ultimo_refresh, INTERVAL 4 MINUTE) > NOW()";
        $result = gdrcd_query($query, 'result');

        // Scorro tutti i presenti individuati
        while ($record = gdrcd_query($result, 'fetch')) {
            gdrcd_query("INSERT INTO messaggi (mittente, destinatario, spedito, oggetto, testo) VALUES ('" . $_SESSION['login'] . "', '" . $record['nome'] . "', NOW(), '" . gdrcd_filter('in', $_POST['oggetto']) . "', '" . gdrcd_filter('in', $_POST['testo']) . "')");
        }
        break;

    /**
     * INVIO MESSAGGIO MULTIPLO
     * I destinatari vengono separati da virgola
     */
    case 'multiplo':
        // Ottengo i destinatari
        $destinatari = explode(',', $_POST['destinatario']);

        /**
         * Controllo che i destinatari siano effettivamente dei personaggi
         * I personaggi vengono concatenati nel seguente formato:
         *
         * 'NOMEPERSONAGGIO','NOMEPERSONAGGIO2'
         *
         * in modo che poi possano essere inseriti nel controllo IN.
         * Aggiungo che il campo personaggio.nome non sia NULL, per evitare possibili errori.
         */
        //
        $destinatariCheck = "'".implode("','", $destinatari)."'";
        $result = gdrcd_query("SELECT nome FROM personaggio WHERE nome IN (" . $destinatariCheck . ") AND nome IS NOT NULL", 'result');

        // Se sono stati individuati dei personaggi, proseguo con l'invio dei messaggi
        if(gdrcd_query($result, 'num_rows') > 0){
            // Scorro tutti i personaggi
            while ($record = gdrcd_query($result, 'fetch')) {
                // Creo l'inserimento
                $queryInsert[] = "('" . $_SESSION['login'] . "', '" . $record['nome'] . "', NOW(), '" . gdrcd_filter('in', $_POST['oggetto']) . "', '" . gdrcd_filter('in', $_POST['testo']) . "')";
            }

            // Se ho costruito delle query di inserimento, prevedo la query
            if(isset($queryInsert)){
                $query = gdrcd_query("INSERT INTO messaggi (mittente, destinatario, spedito, oggetto, testo) VALUES ".implode(",", $queryInsert));
                $query = gdrcd_query("INSERT INTO backmessaggi (mittente, destinatario, spedito, oggetto, testo) VALUES ".implode(",", $queryInsert));
                gdrcd_query($query, 'free');
            }
        }
        break;


    /**
     * INVIO MESSAGGIO A TUTTI
     */
    case 'broadcast':
        // Controllo sui permessi dell'utente
        if($_SESSION['permessi'] >= MODERATOR) {
            // Ottengo tutti i personaggi e li scorro per l'invio del messaggio
            $query = gdrcd_query("SELECT nome FROM personaggio", 'result');
            while ($row = gdrcd_query($query, 'fetch')) {
                // Creo l'inserimento
                gdrcd_query("INSERT INTO messaggi (mittente, destinatario, spedito, oggetto, testo) VALUES ('" . $_SESSION['login'] . "', '" . $row['nome'] . "' , NOW(), '" . gdrcd_filter('in', $_POST['oggetto']) . "', '" . gdrcd_filter('in', $_POST['testo']) . "')");
            }
            gdrcd_query($query, 'free');
        }
        break;
}

?>
    <div class="warning">
        <?php echo $PARAMETERS['names']['private_message']['sing'] . $MESSAGE['interface']['messages']['sent']; ?>
    </div>