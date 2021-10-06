<?php

/**
 * Elimino un messaggio
 */

// Ottengo le variabili passate al modulo
$id_messaggio = gdrcd_filter('num', $_POST['id_messaggio']);
$delType = gdrcd_filter('in', $_POST['type']);

// In base alla tipologia di visualizzazione, disabilito i relativi messaggi
if($delType === 'destinatario_del') {
    $query = "UPDATE messaggi SET destinatario_del = 1 WHERE destinatario='".gdrcd_filter('in', $_SESSION['login'])."' AND id = ".gdrcd_filter('in', $id_messaggio);
} elseif(gdrcd_filter_in($_POST['type']) === 'mittente_del') {
    $query = "UPDATE messaggi SET mittente_del = 1 WHERE mittente='".gdrcd_filter('in', $_SESSION['login'])."' AND id = ".gdrcd_filter('in', $id_messaggio);
}

// Avvio l'operazione
if(isset($query)) {
    // Eseguo l'operazione
    gdrcd_query($query);

    // In base alle rige coinvolte, prevedo il messaggio
    if(gdrcd_query("", 'affected') > 0) { ?>
        <div class="warning">
            <?php echo gdrcd_filter('out', $PARAMETERS['names']['private_message']['sing'].$MESSAGE['interface']['messages']['erased']); ?>
        </div>
        <?php
    }
    // Se non ho modificato nulla, controllo l'esistenza del messaggio
    else {
        // Determino il messaggio in base alla tipologia passata
        if($delType === 'destinatario_del') {
            $query = "SELECT destinatario FROM messaggi WHERE destinatario='".gdrcd_filter('in', $_SESSION['login'])."' AND id = ".gdrcd_filter('in', $id_messaggio)." LIMIT 1";
        } elseif(gdrcd_filter_in($_POST['type']) === 'mittente_del') {
            $query = "SELECT mittente FROM messaggi WHERE mittente='".gdrcd_filter('in', $_SESSION['login'])."' AND id = ".gdrcd_filter('in', $id_messaggio)." LIMIT 1";
        }

        // Avvio il controllo
        if(isset($query)) {
            if(gdrcd_query(gdrcd_query($query, 'result'), 'num_rows') == 0) { ?>
                <div class="warning">
                    Il messaggio che stai tentando di cancellare non esiste
                </div>
                <?php
            }
        }
    }
}