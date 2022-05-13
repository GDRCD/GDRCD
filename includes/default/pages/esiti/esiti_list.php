<?php

require_once(__DIR__ . '/../../core/required.php');

$esiti = Esiti::getInstance();

?>

<div class="gestione_incipit">
    Lista delle conversazioni degli esiti.
</div>

<div class="fake-table esiti_list">
    <?= $esiti->esitiListPlayer(); ?>

</div>

