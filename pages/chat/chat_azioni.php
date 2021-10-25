<?php

# Inserisco i file necessari se non gia' inseriti
require_once(__DIR__.'/../../includes/required.php');

# Se la classe "chat" non esiste la inizializzo (necessario per i caricamenti in ajax che perdono i file inizializzati via include)
if(!isset($chat)){
    $chat = Chat::getInstance();
    $chat->resetClass();
}

?>

<div class="chat_azioni">
    <?=$chat->printChat();?>
</div>
