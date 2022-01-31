<?php

require_once(__DIR__ . '/../../../includes/required.php');

$gathering = GatheringChat::getInstance();
?>

<div class="gestione_incipit">
    Lista delle Chat con oggetti ricercabili
</div>

<div class="fake-table gathering_list">
    <?= $gathering->GatheringChatList(); ?>
</div>

<script src="/pages/gestione/gathering/JS/gathering_chat_delete.js"></script>

