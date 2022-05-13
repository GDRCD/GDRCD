<?php

require_once(__DIR__.'/../../core/required.php');

$chat_id = Filters::int($_GET['dir']);
$_SESSION['last_action_id'] = 0;

$chat = Chat::getInstance();
$chat->resetClass();

?>

<script src="/plugins/Form.js"></script>

<div class="chat_box">

    <?php if ($chat->chatAccess()) {

        if ($chat->chat_notify && $chat->audioActivated()) { ?>
            <audio src="/sounds/beep.wav" id="chat_audio"></audio>
        <?php } ?>

        <div class="chat_azioni_box">
            <?php require_once(__DIR__ . '/chat_azioni.php'); ?>
        </div>
        <div class="chat_input_box">
            <?php require_once(__DIR__ . '/chat_input.php'); ?>
        </div>

    <?php } else {?>
        <div class="warning">Non hai i permessi per visualizzare questa chat.</div>
    <?php } ?>
</div>