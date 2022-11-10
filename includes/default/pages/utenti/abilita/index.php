<?php

Router::loadRequired();

?>

<div class="general_title">Lista Abilità</div>

<div class="fake-table user_service_ability_table">
    <?= UtentiAbilita::getInstance()->abilityPage();?>
</div>

<script src="<?= Router::getPagesLink('utenti/abilita/index.js'); ?>"></script>
