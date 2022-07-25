<?php

Router::loadRequired();

$gruppi = GruppiLavori::getInstance();

?>
<div class="gestione_incipit">
    Lista dei lavori presenti.
</div>

<div class="fake-table works_list">
    <?= $gruppi->worksList(); ?>
</div>

<script src="<?= Router::getPagesLink('servizi/lavori/view.js'); ?>"></script>