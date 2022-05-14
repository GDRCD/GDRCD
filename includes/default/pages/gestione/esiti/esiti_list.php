<?php

Router::loadRequired();

$esiti = Esiti::getInstance();
?>

<div class="gestione_incipit">
    Lista delle conversazioni degli esiti.
</div>

<div class="fake-table esiti_list">
    <?= $esiti->esitiListManagement(); ?>
</div>

<script src="<?=Router::getPagesLink('gestione/esiti/JS/esiti_list.js');?>"></script>

