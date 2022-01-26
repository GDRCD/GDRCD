<?php

require_once(__DIR__ . '/../../../includes/required.php');

$gathering_cat = GatheringItem::getInstance();
?>

<div class="gestione_incipit">
    Lista degli oggetti ricercabili
</div>

<div class="fake-table gathering_list">
    <?= $gathering_cat->GatheringItemList(); ?>
</div>

<script src="/pages/gestione/gathering/JS/gathering_item_delete.js"></script>

