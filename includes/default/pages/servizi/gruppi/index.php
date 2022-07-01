<?php

Router::loadRequired();

$gruppi = Gruppi::getInstance();

if ($gruppi->activeGroups()) { ?>
    <div class="groups_page">
        <?php if (true) { ?>

            <!-- Titolo della pagina -->
            <div class="general_title">
                Gruppi
            </div>

            <!-- Corpo della pagina -->
            <div class="page_body">
                <?php Router::loadPages('servizi/gruppi/' . $gruppi->loadServicePage(Filters::out($_GET['op']))); ?>
            </div>

        <?php } else { ?>

            <div class="warning"> Permessi insufficienti o funzione disabilitata.</div>

            <div class="link_back"><a href="/main.php?page=gestione">Indietro</a></div>
        <?php } ?>
    </div>
<?php } ?>