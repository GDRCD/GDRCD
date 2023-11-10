<?php

Router::loadRequired();

$class = Meteo::getInstance();

if ( $class->permissionEditChat() ) { ?>
    <div class="servizi_pagina esiti_pagina">
        <?php if ( $class->activeSeason() ) { ?>

            <!-- Titolo della pagina -->
            <div class="general_title">
                Modifica Meteo chat
            </div>

            <!-- Corpo della pagina -->
            <div class="page_body">
                <?php Router::loadPages('/meteo_edit.php'); ?>
            </div>

        <?php } else { ?>

            <div class="warning"> Funzione disabilitata.</div>

            <div class="link_back"><a href="/main.php?page=servizi">Indietro</a></div>
        <?php } ?>
    </div>
<?php } ?>