<div class="servizi_pagina">

    <!-- Titolo della pagina -->
    <div class="servizi_incipit">
        <div class="title"><?php echo gdrcd_filter('out', $MESSAGE['interface']['hotel']['page_name']); ?></div>
    </div>

    <div class="servizi_body">
        <!-- Corpo della pagina -->
        <?php
        /*
         * Richieste POST
         */
        switch(gdrcd_filter_get($_POST['op'])) {
            default: // Pagina di default
                include ('servizi/prenotazioni/index.inc.php');
                break;
        }
        ?>
    </div>
</div>