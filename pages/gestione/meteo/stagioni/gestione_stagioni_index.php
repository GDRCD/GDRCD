<?php

require_once(__DIR__ . '/../../../../core/required.php');

$stagioni = MeteoStagioni::getInstance();

?>

<div class="servizi_pagina esiti_pagina">
    <?php  if ($stagioni->activeSeason() && $stagioni->permissionManageSeasons()) { ?>

        <!-- Titolo della pagina -->
        <div class="general_title">
            Gestione stagioni meteo
        </div>

        <!-- Corpo della pagina -->
        <div class="page_body">
            <?php require_once(__DIR__ . '/' . $stagioni->loadManagePage(Filters::out($_GET['op']))); ?>
        </div>

    <?php } else { ?>

        <div class="warning"> Funzione disabilitata.</div>

        <div class="link_back"><a href="/main.php?page=uffici">Indietro</a> </div>
    <?php } ?>
</div>