<?php

/**
 * Eliminao i messaggi letti
 */

// In base alla tipologia di visualizzazione, elimino i relativi messaggi letti
if(gdrcd_filter_in($_POST['type']) === 'destinatario_del') {
    $query = "UPDATE messaggi SET destinatario_del = 1 WHERE destinatario='".gdrcd_filter('in', $_SESSION['login'])."' AND letto = 1";
} elseif(gdrcd_filter_in($_POST['type']) === 'mittente_del') {
    $query = "UPDATE messaggi SET mittente_del = 1 WHERE mittente='".gdrcd_filter('in', $_SESSION['login'])."' AND letto = 1";
}

// Avvio l'operazione
if(isset($query)){
    // Eseguo la query
    gdrcd_query($query);

    // Mostro il messaggio
    ?>
    <div class="warning">
        <?php echo gdrcd_filter('out', $PARAMETERS['names']['private_message']['plur'].$MESSAGE['interface']['messages']['all_erased']); ?>
    </div>
    <?php
}
