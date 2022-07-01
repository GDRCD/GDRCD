<?php

Router::loadRequired();

$gruppi = Gruppi::getInstance();

?>
<div class="gestione_incipit">
    Lista dei gruppi presenti.
</div>

<div class="fake-table gruppi_list">
    <?= $gruppi->groupsList(); ?>

</div>