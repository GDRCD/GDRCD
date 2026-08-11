<?php

/*HELP: */
/*Controllo permessi utente*/
if(!gdrcd_controllo_permessi($PARAMETERS['administration']['maintenance']['access_level'])) {
    echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
    die();
}

if((is_numeric($_POST['mesi']) === true) && ($_POST['mesi'] >= 1) && ($_POST['mesi'] <= 12)) {
    /*Eseguo l'aggiornamento*/
    gdrcd_query("DELETE FROM clgpersonaggiooggetto WHERE nome IN (SELECT nome FROM personaggio WHERE DATE_SUB(NOW(), INTERVAL ".gdrcd_filter('num', $_POST['mesi'])." MONTH) > ora_entrata)");
    gdrcd_query("OPTIMIZE TABLE clgpersonaggiooggetto");

    gdrcd_query("DELETE FROM clgpersonaggioabilita WHERE nome IN (SELECT nome FROM personaggio WHERE DATE_SUB(NOW(), INTERVAL ".gdrcd_filter('num', $_POST['mesi'])." MONTH) > ora_entrata)");
    gdrcd_query("OPTIMIZE TABLE clgpersonaggioabilita");

    gdrcd_query("DELETE FROM clgpersonaggiomostrine WHERE nome IN (SELECT nome FROM personaggio WHERE DATE_SUB(NOW(), INTERVAL ".gdrcd_filter('num', $_POST['mesi'])." MONTH) > ora_entrata)");
    gdrcd_query("OPTIMIZE TABLE clgpersonaggiomostrine");

    gdrcd_query("DELETE FROM clgpersonaggioruolo WHERE personaggio IN (SELECT nome AS personaggio FROM personaggio WHERE DATE_SUB(NOW(), INTERVAL ".gdrcd_filter('num', $_POST['mesi'])." MONTH) > ora_entrata)");
    gdrcd_query("OPTIMIZE TABLE clgpersonaggioruolo");

    gdrcd_query("DELETE FROM personaggio WHERE DATE_SUB(NOW(), INTERVAL ".gdrcd_filter('num', $_POST['mesi'])." MONTH) > ora_entrata");
    gdrcd_query("OPTIMIZE TABLE personaggio");
    ?>
    <!-- Conferma -->
    <div class="success">
        <?php echo gdrcd_filter('out', $MESSAGE['warning']['modified']); ?>
    </div>
    <?php
} else {  ?>
    <div class="error">
        <?php echo gdrcd_filter('out', $MESSAGE['warning']['cant_do']); ?>
    </div>
    <?php
}