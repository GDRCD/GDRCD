<?php

require_once(__DIR__.'/../../../includes/required.php');

$mercato = Mercato::getInstance();


?>

<div class="shops_box">
    <?=$mercato->getShopVisual();?>
</div>
