<?php

require_once(__DIR__.'/../../includes/required.php');

$online = OnlineStatus::getInstance();

?>

<div class="floating_box_status">
    <form id="online_time_form">
        <div class="subtitle"> Quanto resterai online?</div>
        <ul>
            <?=$online->renderOnlineStatusOptions('online_time');?>
        </ul>

        <div class="subtitle"> Tempo di azione previsto?</div>
        <ul>
            <?=$online->renderOnlineStatusOptions('online_action_time');?>
        </ul>

        <input type="submit" value="Seleziona">
        <input type="hidden" name="op" value="choose_status">

    </form>

</div>

<script src="pages/online_status/choose_status.js"></script>