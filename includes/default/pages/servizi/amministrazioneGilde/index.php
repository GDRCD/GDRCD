<?php

Router::loadRequired();

$gruppi = Gruppi::getInstance();
$op = Filters::out($_GET['op']);

if ( $gruppi->permissionServiceGroups() ) { ?>
    <div class="groups_page">
        <?php if ( true ) { ?>

            <!-- Titolo della pagina -->
            <div class="general_title">
                Gruppi
            </div>

            <!-- Corpo della pagina -->
            <div class="page_body">
                <?php Router::loadPages('servizi/amministrazioneGilde/' . $gruppi->loadGroupAdministrationPage($op)); ?>
            </div>

        <?php } else { ?>

            <div class="warning"> Permessi insufficienti o funzione disabilitata.</div>

            <div class="link_back"><a href="/main.php?page=gestione">Indietro</a></div>
        <?php } ?>
    </div>
<?php } ?>