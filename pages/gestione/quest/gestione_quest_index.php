<?php

require_once(__DIR__ . '/../../../includes/required.php');

$quest = Quest::getInstance();

?>


<div class="gestione_pagina gestione_quest">
    <?php if ($quest->questEnabled() && $quest->manageQuestPermission()) {
        ?>
        <!-- Titolo della pagina -->
        <div class="general_title">
            Gestione quest
        </div>
        <!-- Corpo della pagina -->
        <div class="page_body">
            <?php require(__DIR__ . '/' . $quest->loadManagementQuestPage(Filters::out($_GET['op']))); ?>
        </div><!-- pagina -->
    <?php } else { ?>

        <div class="warning"> Permessi insufficienti o funzione disabilitata.</div>
    <?php } ?>
</div>