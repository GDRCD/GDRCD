<?php

require_once(__DIR__ . '/../../../includes/required.php');

$gathering_cat = GatheringCategory::getInstance();
?>

<div class="gestione_incipit">
    Lista delle Categorie degli oggetti ricercabili
</div>

<div class="fake-table gathering_list">
    <?= $gathering_cat->GatheringCatList(); ?>
</div>

<script src="/pages/gestione/gathering/JS/gathering_cat_delete.js"></script>

