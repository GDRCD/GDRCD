<?php

    /*HELP: */
    /*Controllo permessi utente*/
    if(!gdrcd_controllo_permessi($PARAMETERS['administration']['maps']['access_level'])) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
        die();
    }

    ?>

<div class="gestione_pagina">

    <!-- Titolo della pagina -->
    <div class="gestione_incipit">
        <div class="title"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['page_name']); ?></div>
    </div>
    <!-- Corpo della pagina -->
    <div class="gestione_body">
    <?php
    /*
     * Richieste POST
     */
    switch(gdrcd_filter_get($_POST['op'])) {
        case 'save': // Crea/Modifica mappa
            include('save.inc.php');
            break;
        case 'erase': // Cancella mappa
            include('erase.inc.php');
            break;
        default: // Pagina di default
            break;
    }
    /*
     * Richieste GET
     */
    switch(gdrcd_filter_get($_GET['op'])) {
        case 'edit': // Modifica mappa
            include('edit.inc.php');
            break;
        case 'create': // Crea nuova mappa
            include('create.inc.php');
            break;
        default:    // Visualizzazione di base
            include('view.inc.php');
            break;
    }
    ?>
    </div>
</div><!-- pagina -->
