<?php

/*HELP: */
/*Controllo permessi utente*/
if(!gdrcd_controllo_permessi($PARAMETERS['administration']['maintenance']['access_level'])) {
    echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
    die();
}

if((is_numeric($_POST['mesi']) === true) && ($_POST['mesi'] >= 0) && ($_POST['mesi'] <= 12)) {
    /*Eseguo l'aggiornamento*/
    gdrcd_query("DELETE FROM messaggi WHERE DATE_SUB(NOW(), INTERVAL ".gdrcd_filter('num', $_POST['mesi'])." MONTH) > spedito");
    gdrcd_query("OPTIMIZE TABLE messaggi");
    gdrcd_query("DELETE FROM backmessaggi WHERE DATE_SUB(NOW(), INTERVAL ".gdrcd_filter('num', $_POST['mesi'])." MONTH) > spedito");
    gdrcd_query("OPTIMIZE TABLE backmessaggi");
    ?>
    <!-- Conferma -->
    <div class="success">
        <?php echo gdrcd_filter('out', $MESSAGE['warning']['modified']); ?>
    </div>
    <?php
} else {
    ?>
    <div class="error">
        <?php echo gdrcd_filter('out', $MESSAGE['warning']['cant_do']); ?>
    </div>
    <?php
}