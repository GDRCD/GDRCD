<?php

Router::loadRequired();

$mercato = Mercato::getInstance();

?>


<div class="shops_page">
    <?php  if (true) { ?>

        <!-- Titolo della pagina -->
        <div class="general_title">
            Mercato
        </div>

        <!-- Corpo della pagina -->
        <div class="page_body">
            <?php Router::loadPages('mercato/' . $mercato->loadShopsPage(Filters::out($_GET['op']))); ?>
        </div>

    <?php } else { ?>

        <div class="warning"> Permessi insufficienti o funzione disabilitata.</div>

        <div class="link_back"><a href="/main.php?page=gestione">Indietro</a> </div>
    <?php } ?>
</div>