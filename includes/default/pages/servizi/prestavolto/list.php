<?php

Router::loadRequired();
$prestavolto = PersonaggioPrestavolto::getInstance();

?>

<div class="fake-table works_list">
    <?= $prestavolto->serviziPrestavoltoList(); ?>
</div>