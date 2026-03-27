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
     * INVIO MESSAGGIO STANDARD
     * In caso di invio messaggio a destinatari multipli, occorre separare i destinatari da virgola
     */
    default:
        // Ottengo i destinatari
        $destinatari = explode(',', $_POST['destinatario']);
        // Rimuovo eventuale sporcizia nella scrittura dei nomi dei Personaggi
        $destinatari = array_map('trim', $destinatari);
        // Sanitizzo i destinatari per evitare possibili problemi di sicurezza
        $destinatari = array_map(function($destinatario){
            return gdrcd_filter_in('in', $destinatario);
        }, $destinatari);

        /**
         * Controllo che i destinatari siano effettivamente dei personaggi
         * I personaggi vengono concatenati nel seguente formato:
         *
         * 'NOMEPERSONAGGIO','NOMEPERSONAGGIO2'
         *
         * in modo che poi possano essere inseriti nel controllo IN.
         * Aggiungo che il campo personaggio.nome non sia NULL, per evitare possibili errori.
         */
        $destinatariCheck_stmt = gdrcd_stmt(
            'SELECT nome,
                    id_personaggio
            FROM personaggio
            WHERE nome IN (?)
            AND nome IS NOT NULL
            GROUP BY nome
            ',
            ['i' => implode(',', $destinatari)]
        );
        $result = gdrcd_query($destinatariCheck_stmt, 'result');
        $sended = gdrcd_query($result,'num_rows');
        $num_dest = count($destinatari);

        $not_all_sended = ($num_dest > $sended);

        // Se sono stati individuati record, procedo
        if(gdrcd_query($result, 'num_rows') > 0){
            // In caso di segnalazione
            if (gdrcd_filter('get', $_POST['url']) != "") {
                $_POST['testo'] = $_SESSION['login'] . ' ti ha segnalato questo [url=' . $_POST['url'] . ']link[/url].';
            }

            // Scorro tutti i personaggi
            while ($record = gdrcd_query($result, 'fetch')) {
                // Creo l'inserimento
                $queryInsert[] = "('" . gdrcd_filter('in', $_SESSION['id_personaggio']) . "', '" . $record['id_personaggio'] . "', NOW(), '" . gdrcd_filter('in', $_POST['tipo']) . "', '" . gdrcd_filter('in', $_POST['oggetto']) . "', '" . gdrcd_filter('in', $_POST['testo']) . "')";
            }

            // Se ho costruito delle query di inserimento, prevedo la query
            if(isset($queryInsert)){
                $query = gdrcd_query("INSERT INTO messaggi (id_personaggio_mittente, id_personaggio_destinatario, spedito, tipo, oggetto, testo) VALUES ".implode(",", $queryInsert));
                $query = gdrcd_query("INSERT INTO backmessaggi (id_personaggio_mittente, id_personaggio_destinatario, spedito, tipo, oggetto, testo) VALUES ".implode(",", $queryInsert));
            }


            echo '<div class="warning">'.$PARAMETERS['names']['private_message']['sing'] . $MESSAGE['interface']['messages']['sent'].'</div>';
        }
        else{
            echo '<div class="warning">Attenzione: Non hai selezionato nessun destinatario.</div>';
        }

        if($not_all_sended && ($num_dest > 0) ) {
            echo '<div class="warning">Attenzione: Alcuni dei destinatari selezionati sono inesistenti.</div>';
        }

        break;

    /**
     * INVIO MESSAGGIO A TUTTI I PRESENTI
     */
    case 'presenti':
        // Ottengo i presenti correnti
        $query = "SELECT id_personaggio
                    FROM personaggio
                    WHERE ora_entrata > ora_uscita
                      AND DATE_ADD(ultimo_refresh, INTERVAL 4 MINUTE) > NOW()";
        $result = gdrcd_query($query, 'result');

        // Scorro tutti i presenti individuati
        while ($record = gdrcd_query($result, 'fetch')) {
            gdrcd_query("INSERT INTO messaggi (id_personaggio_mittente, id_personaggio_destinatario, spedito, tipo, oggetto, testo)
                                VALUES ('" . gdrcd_filter('in', $_SESSION['id_personaggio']) . "', '" . $record['id_personaggio'] . "', NOW(), '" . gdrcd_filter('in', $_POST['tipo']) . "', '" . gdrcd_filter('in', $_POST['oggetto']) . "', '" . gdrcd_filter('in', $_POST['testo']) . "')");
        }

        echo '<div class="warning">'.$PARAMETERS['names']['private_message']['sing'] . $MESSAGE['interface']['messages']['sent'].'</div>';
        break;


    /**
     * INVIO MESSAGGIO A TUTTI
     */
    case 'broadcast':
        // Controllo sui permessi dell'utente
        if($_SESSION['permessi'] >= MODERATOR) {
            // Ottengo tutti i personaggi e li scorro per l'invio del messaggio
            $query = gdrcd_query("SELECT id_personaggio FROM personaggio", 'result');
            while ($row = gdrcd_query($query, 'fetch')) {
                // Creo l'inserimento
                gdrcd_query("INSERT INTO messaggi (id_personaggio_mittente, id_personaggio_destinatario, spedito, tipo, oggetto, testo)
                                    VALUES ('" . gdrcd_filter('in', $_SESSION['id_personaggio']) . "', '" . $row['id_personaggio'] . "' , NOW(), '" . gdrcd_filter('in', $_POST['tipo']) . "', '" . gdrcd_filter('in', $_POST['oggetto']) . "', '" . gdrcd_filter('in', $_POST['testo']) . "')");
            }

            echo '<div class="warning">'.$PARAMETERS['names']['private_message']['sing'] . $MESSAGE['interface']['messages']['sent'].'</div>';
        }
        break;
}

?>
