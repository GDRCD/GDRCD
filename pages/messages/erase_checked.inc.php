<?php

/**
 * Elimino i messaggi selezionati
 *
 * ATTENZIONE!: Se non è stato selezionato alcun messaggio, allora questa pagina non viene caricata
 */

// Eseguo l'operazione solo se è stato selezionato almeno un messaggio
if( !empty($_POST['ids']) ) {

    // Scorro i messaggi e rimuovo anomalie
    foreach($_POST['ids'] as $k => $v) {
        if(!is_numeric($v)) {
            $_POST['ids'][$k] = (int)$v;
        }
    }
    $msgs = implode(',', $_POST['ids']);

    // In base alla tipologia di visualizzazione, disabilito i relativi messaggi
    if(gdrcd_filter_in($_POST['type']) === 'destinatario_del') {
        $query = "UPDATE messaggi SET destinatario_del = 1 WHERE destinatario='".gdrcd_filter('in', $_SESSION['login'])."' AND id IN (".$msgs.")";
    } elseif(gdrcd_filter_in($_POST['type']) === 'mittente_del') {
        $query = "UPDATE messaggi SET mittente_del = 1 WHERE mittente='".gdrcd_filter('in', $_SESSION['login'])."' AND id IN (".$msgs.")";
    }

    // Avvio l'operazione
    if(isset($query)) {
        // Eseguo l'operazione
        gdrcd_query($query);

        // In base alle rige coinvolte, prevedo il messaggio
        if(gdrcd_query("", 'affected') > 0) { ?>
            <div class="warning">
                <?php echo gdrcd_filter('out', $PARAMETERS['names']['private_message']['plur'].$MESSAGE['interface']['messages']['all_erased']); ?>
            </div>
            <?php
        }
    }
}