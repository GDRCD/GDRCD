<?php

/*HELP: */
/*Controllo permessi utente*/
if(!gdrcd_controllo_permessi($PARAMETERS['administration']['maintenance']['access_level'])) {
    echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
    die();
}

/*Eseguo l'aggiornamento*/
gdrcd_query("DELETE FROM blacklist WHERE 1");
gdrcd_query("OPTIMIZE TABLE blacklist");
?>
<!-- Conferma -->
<div class="success">
    <?php echo gdrcd_filter('out', $MESSAGE['warning']['modified']); ?>
</div>