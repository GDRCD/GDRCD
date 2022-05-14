<?php

Router::loadRequired();

$online = OnlineStatus::getInstance();

$cls = ($online->refreshOnLogin()) ? 'minimized' : '';

?>

<div class="floating_box_status <?=$cls;?>">
    <div class="change-dimension">X</div>

    <form class="ajax_form" action="online_status/online_status_ajax.php">
        <?=$online->renderOnlineStatusOptions();?>
        <input type="submit" value="Seleziona">
        <input type="hidden" name="op" value="choose_status">
    </form>

</div>

<script src="<?=Router::getPagesLink('online_status/choose_status.js');?>"></script>