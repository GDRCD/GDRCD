<?php

Router::loadRequired();

$esiti = Esiti::getInstance();

?>

<div class="servizi_pagina esiti_pagina">
    <?php  if ($esiti->esitiEnabled()) { ?>

        <!-- Titolo della pagina -->
        <div class="general_title">
            Visualizzazione esiti
        </div>

        <!-- Corpo della pagina -->
        <div class="page_body">
            <?php Router::loadPages('esiti/' . $esiti->loadServicePageEsiti(Filters::out($_GET['op']))); ?>
        </div>

    <?php } else { ?>

        <div class="warning"> Funzione disabilitata.</div>

        <div class="link_back"><a href="/main.php?page=uffici">Indietro</a> </div>
    <?php } ?>
</div>