<?php

require_once(__DIR__ . '/../../../includes/required.php');

$quest = Quest::getInstance();

?>


<div class="gestione_pagina gestione_quest">
    <?php if ($quest->questEnabled() && $quest->manageQuestPermission()) {
        ?>
        <!-- Titolo della pagina -->
        <div class="form_title">
            <h2>Gestione quest</h2>
        </div>
        <!-- Corpo della pagina -->
        <div class="page_body">
            <?php require(__DIR__ . '/' . $quest->loadManagementQuestPage($_GET['op'])); ?>
        </div><!-- pagina -->
    <?php } else { ?>

        <div class="warning"> Permessi insufficienti o funzione disabilitata.</div>
    <?php } ?>
</div>