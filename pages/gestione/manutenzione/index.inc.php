<?php

    /*HELP: */
    /*Controllo permessi utente*/
    if(!gdrcd_controllo_permessi($PARAMETERS['administration']['maintenance']['access_level'])) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
        die();
    }

    ?>

<div class="gestione_pagina">

    <!-- Titolo della pagina -->
    <div class="gestione_incipit">
        <div class="title"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['maintenance']['page_name']); ?></div>
    </div>
        <!-- Corpo della pagina -->
        <div class="gestione_body">
        <?php
        /*
         * Richieste POST
         */
        switch(gdrcd_filter_get($_POST['op'])) {
            case 'blacklisted': //Elimina blacklist
                include('blacklisted.inc.php');
                break;
            case 'deleted': //Elimina personaggi che non si loggano più
                include('deleted.inc.php');
                break;
            case 'old_chat': //Elimina vecchi log
                include('old_chat.inc.php');
                break;
            case 'old_log': //Elimina vecchi log
                include('old_log.inc.php');
                break;
            case 'old_messages': //Elimina vecchi messaggi
                include('old_messages.inc.php');
                break;
            case 'missing': //Elimina personaggi che non si loggano più
                include('missing.inc.php');
                break;
            default: // Pagina di default
                break;
        }
        /*
         * Richieste GET
         */
        switch(gdrcd_filter_get($_GET['op'])) {
            default: //visualizzazione di base
                include('view.inc.php');
                break;
        }

        echo '</div>'; //<!-- page_body -->
    ?>
</div><!-- pagina -->
