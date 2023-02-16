<?php

    /*HELP: */
    /*Controllo permessi utente*/
    if(!gdrcd_controllo_permessi($PARAMETERS['administration']['maps']['access_level'])) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
        die();
    }

    // Inizializzo il container per gli errori
    $hasErrors = 0;

    // Se si sta cercando di cancellare la mappa principale, non si può
    if(gdrcd_query("SELECT principale FROM mappa_click WHERE id_click=".gdrcd_filter('num', $_POST['id_click'])." LIMIT 1")['principale'] == 1) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['no_erase_main']).'</div>';
        // Segnalo che ci sono errori
        $hasErrors = 1;
    }

    // Se sto per cancellare l'ultima mappa, non si può
    if(gdrcd_query("SELECT COUNT(*) FROM mappa_click")['COUNT(*)'] == 1) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['no_erase_last']).'</div>';
        // Segnalo che ci sono errori
        $hasErrors = 1;
    }

    // Se non ci sono errori, procedo con la cancellatura
    if($hasErrors == 0) {
        // Eseguo la cancellatura
        gdrcd_query("DELETE FROM mappa_click WHERE id_click=".gdrcd_filter('num', $_POST['id_click'])." LIMIT 1");
        // Feedback
        echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['warning']['deleted']).'</div>';
    }