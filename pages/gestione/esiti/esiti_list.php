<?php

require_once(__DIR__ . '/../../../includes/required.php');

$esiti = Esiti::getInstance();
?>

<div class="gestione_incipit">
    Lista delle conversazioni degli esiti.
</div>

<div class="fake-table esiti_list">
    <?= $esiti->esitiListManagement(); ?>
</div>

<script src="/pages/gestione/esiti/JS/esiti_list.js"></script>

