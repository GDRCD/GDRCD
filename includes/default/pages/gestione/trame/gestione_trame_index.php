<?php

Router::loadRequired();

$quest = Quest::getInstance();

?>


<div class="gestione_pagina gestione_trame">
    <?php if ( $quest->trameEnabled() && $quest->viewTramePermission() ) {
        ?>
        <!-- Titolo della pagina -->
        <div class="general_title">
            Gestione trame
        </div>
        <!-- Corpo della pagina -->
        <div class="page_body">
            <?php Router::loadPages('gestione/trame/' . $quest->loadManagementTramePage(Filters::out($_GET['op']))); ?>
        </div><!-- pagina -->
    <?php } else { ?>

        <div class="warning"> Permessi insufficienti o funzione disabilitata.</div>
    <?php } ?>
</div>