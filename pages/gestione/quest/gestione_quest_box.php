<?php

require_once(__DIR__ . '/../../../includes/required.php');

$quest = Quest::getInstance();

?>


<div class="pagina_gestione gestione_quest">
    <?php if ($quest->questEnabled() && $quest->manageQuestPermission()) {
        ?>
        <!-- Titolo della pagina -->
        <div class="gestione_form_title">
            <h2>Gestione quest</h2>
        </div>
        <!-- Corpo della pagina -->
        <div class="page_body">
            <?php require(__DIR__ . '/' . $quest->loadManagementPage($_REQUEST['op'])); ?>
        </div><!-- pagina -->
    <?php } else { ?>

        <div class="warning"> Permessi insufficienti o funzione disabilitata.</div>
    <?php } ?>
</div>