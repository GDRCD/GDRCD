<div class="servizi_pagina">

    <!-- Titolo della pagina -->
    <div class="servizi_incipit">
        <div class="title"><?php echo gdrcd_filter('out', $MESSAGE['interface']['esiti']['page_name']); ?></div>
    </div>

    <div class="servizi_body">
        <!-- Corpo della pagina -->
        <?php
        /*
         * Richieste POST
         */
        switch(gdrcd_filter_get($_POST['op'])) {
            case 'view': // Visualizza dettaglio serie esiti
                include('view.inc.php');
                break;

            default:    // Pagina di default
                break;
        }
        /*
         * Richieste GET
         */
        switch(gdrcd_filter_get($_GET['op'])) {
            case 'new':  // Form di inserimento nuova serie esiti (Solo visualizzazione)
                include('new.inc.php');
                break;

            default: // Pagina di default
                include('list.inc.php');
                break;
        }
        ?>
    </div>
</div>