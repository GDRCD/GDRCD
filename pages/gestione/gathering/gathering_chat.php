<?php

require_once(__DIR__ . '/../../../includes/required.php');

$gathering = Gathering::getInstance();

?>

<div class="gestione_pagina gestione_gathering">
    <?php  if ($gathering->gatheringEnabled()&& ($gathering->gatheringManage() )) { ?>

        <!-- Titolo della pagina -->
        <div class="general_title">
            Impostazioni Gathering
        </div>

        <!-- Corpo della pagina -->
        <div class="page_body">
            <?php require_once(__DIR__ . '/' . $gathering->loadManagementGatheringPage(Filters::out($_GET['op']))); ?>
        </div>

    <?php } else { ?>

        <div class="warning"> Permessi insufficienti o funzione disabilitata.</div>

        <div class="link_back"><a href="/main.php?page=gestione">Indietro</a> </div>
    <?php } ?>
</div>