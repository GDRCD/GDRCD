<?php

Router::loadRequired();

$esiti = Esiti::getInstance();

var_dump(1);
?>

<div class="gestione_pagina gestione_esiti">
    <?php  if ($esiti->esitiEnabled() && ($esiti->esitiManage() || $esiti->esitiManageAll())) { ?>

        <!-- Titolo della pagina -->
        <div class="general_title">
            Gestione esiti
        </div>

        <!-- Corpo della pagina -->
        <div class="page_body">
            <?php  Router::loadPages('gestione/esiti/' . $esiti->loadManagementEsitiPage(Filters::out($_GET['op']))); ?>
        </div>

    <?php } else { ?>

        <div class="warning"> Permessi insufficienti o funzione disabilitata.</div>

        <div class="link_back"><a href="/main.php?page=gestione">Indietro</a> </div>
    <?php } ?>
</div>