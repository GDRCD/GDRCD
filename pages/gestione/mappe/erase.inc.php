<?php

    /*HELP: */
    /*Controllo permessi utente*/
    if(!gdrcd_controllo_permessi($PARAMETERS['administration']['maps']['access_level'])) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
        die();
    }


    /*Eseguo la cancellatura*/
    gdrcd_query("DELETE FROM mappa_click WHERE id_click=".gdrcd_filter('num', $_POST['id_click'])." LIMIT 1");
    // Feedback
    echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['warning']['deleted']).'</div>';