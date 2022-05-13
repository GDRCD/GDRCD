<?php

require_once(__DIR__ . '/../../../core/required.php');

$quest = Quest::getInstance();

?>


<div class="gestione_pagina gestione_trame">
    <?php if ($quest->trameEnabled() && $quest->viewTramePermission()) {
        ?>
        <!-- Titolo della pagina -->
        <div class="general_title">
            Gestione trame
        </div>
        <!-- Corpo della pagina -->
        <div class="page_body">
            <?php require(__DIR__ . 'gestione_trame_index.php/' . $quest->loadManagementTramePage(Filters::out($_GET['op']))); ?>
        </div><!-- pagina -->
    <?php } else { ?>

        <div class="warning"> Permessi insufficienti o funzione disabilitata.</div>
    <?php } ?>
</div>