<?php

Router::loadRequired();

$mercato = Mercato::getInstance();

?>

<div class="shops_box">
    <?= $mercato->getShopVisual(); ?>
</div>
