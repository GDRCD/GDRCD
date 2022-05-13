<?php

require_once(__DIR__ . '/../../../core/required.php');

$mercato = Mercato::getInstance();


?>

<div class="shops_box">
    <?=$mercato->getShopVisual();?>
</div>
