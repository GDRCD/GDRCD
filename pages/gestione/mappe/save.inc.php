<?php

    /*HELP: */
    /*Controllo permessi utente*/
    if(!gdrcd_controllo_permessi($PARAMETERS['administration']['maps']['access_level'])) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
        die();
    }

    /*Processo le informazioni ricevute dal form*/
    $id_click = gdrcd_filter('num', $_POST['id_click']);
    $is_mobile = ((isset($_POST['mobile']) == true) && ($_POST['mobile'] == 'is_mobile')) ? 1 : 0;
    $is_main = ((isset($_POST['principale']) == true) && ($_POST['principale'] == 'is_main')) ? 1 : 0;
    $immagine = empty($_POST['immagine']) ? "standard_mappa.png" : gdrcd_filter('in', $_POST['immagine']);

    // Se la mappa è principale, devo togliere la principale a tutte le altre
    if($is_main == 1) {
        gdrcd_query("UPDATE mappa_click SET principale = 0 WHERE principale = 1");
    }

    // Se id_click è 0, vuol dire che si sta creando una nuova mappa
    if($id_click == 0) {
        /*Eseguo l'inserimento*/
        gdrcd_query("INSERT INTO mappa_click (nome, posizione, mobile, principale, immagine, larghezza, altezza) VALUES ('".gdrcd_filter('in', $_POST['nome'])."', ".gdrcd_filter('num', $_POST['posizione']).", ".$is_mobile.", ".$is_main.", '".$immagine."', ".gdrcd_filter('num', $_POST['larghezza']).", ".gdrcd_filter('num', $_POST['altezza']).")");
        // Feedback
        echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['warning']['inserted']).'</div>';
    }
    // Altrimenti si sta aggiornando una mappa esistente
    else {
        /*Eseguo l'aggiornamento*/
        gdrcd_query("UPDATE mappa_click SET nome ='".gdrcd_filter('in', $_POST['nome'])."', mobile = ".$is_mobile.", principale = ".$is_main.", immagine = '".gdrcd_filter('in', $immagine)."', posizione = ".gdrcd_filter('num', $_POST['posizione']).", larghezza = ".gdrcd_filter('num', $_POST['larghezza']).", altezza = ".gdrcd_filter('num', $_POST['altezza'])."  WHERE id_click = ".$id_click." LIMIT 1");
        // Feedback
        echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['warning']['modified']).'</div>';
    }