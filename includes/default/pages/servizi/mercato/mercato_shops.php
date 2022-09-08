<?php

Router::loadRequired();

$mercato = Mercato::getInstance();

?>

<div class="general_subtitle">Negozi</div>

<div class="shops_box">
    <?= $mercato->getShopVisual(); ?>
</div>
