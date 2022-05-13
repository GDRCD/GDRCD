<?php

require_once(__DIR__.'/../../core/required.php');

$online = OnlineStatus::getInstance();

$cls = ($online->refreshOnLogin()) ? 'minimized' : '';

?>

<div class="floating_box_status <?=$cls;?>">
    <div class="change-dimension">X</div>

    <form id="online_time_form">
        <?=$online->renderOnlineStatusOptions();?>
        <input type="submit" value="Seleziona">
        <input type="hidden" name="op" value="choose_status">

    </form>

</div>

<script src="pages/online_status/choose_status.js"></script>