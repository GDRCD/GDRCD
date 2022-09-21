<?php

Router::loadRequired();

$chat_id = Filters::int($_GET['dir']);

$chat = Chat::getInstance();
$chat->resetClass();
$chat->setLastAction(0);

?>

<div class="chat_box">

    <?php if ( $chat->chatAccess() ) {

        if ( $chat->activeNotify() && $chat->audioActivated() ) { ?>
            <audio src="/sounds/beep.wav" id="chat_audio"></audio>
        <?php } ?>

        <div class="chat_azioni_box">
            <?php require_once(__DIR__ . '/chat_azioni.php'); ?>
        </div>
        <div class="chat_input_box">
            <?php require_once(__DIR__ . '/chat_input.php'); ?>
        </div>

    <?php } else { ?>
        <div class="warning">Non hai i permessi per visualizzare questa chat.</div>
    <?php } ?>
</div>