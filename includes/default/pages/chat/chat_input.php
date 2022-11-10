<?php

# Inserisco i file necessari se non gia' inseriti
Router::loadRequired();

# Se la classe "chat" non esiste la inizializzo (necessario per i caricamenti in ajax che perdono i file inizializzati via include)
if ( !isset($chat) ) {
    $chat = Chat::getInstance();
    $chat->resetClass();
}

$esiti_chat = Functions::get_constant('ESITI_CHAT');
$esiti = Functions::get_constant('ESITI_ENABLE');

$stat_class = Statistiche::getInstance();
$chat_abi_class = ChatAbilita::getInstance();

?>

<div class="chat_bottom">

    <form class="ajax_form chat_form_ajax" action="chat/chat_ajax.php" data-callback="invioAzioneSuccess"
          data-swal="false" data-reset="false">
        <div class="chat_text chat_internal_box">

            <div class="input_container small">
                <select name="tipo">
                    <option value="A">Azione</option>
                    <option value="S">Sussurro</option>
                    <option value="F">Sussurro globale</option>

                    <?php if ( Chat::getInstance()->permissionChatMaster() ) { ?>
                        <option value="N">PNG</option>
                        <option value="M">Master</option>
                        <option value="I">Immagine</option>
                    <?php } ?>

                    <?php if ( Chat::getInstance()->permissionChatModerator() ) { ?>
                        <option value="MOD">Moderazione</option>
                    <?php } ?>

                </select>
            </div>
            <div class="input_container small">

                <select name="wispTo" class="wisp-chosen" data-placeholder="Sussurra a:">
                    <?= Personaggio::getInstance()->listPgs(); ?>
                </select>
            </div>

            <div class="input_container small">
                <input type="text" name="tag" placeholder="Tag" value="<?=Session::read('tag');?>">
            </div>

            <div class="input_container big">
                <input type="text" name="testo" placeholder="Testo azione">
            </div>

            <div class="input_container invia">
                <input type="submit" value="Invia">
                <input type="hidden" name="action" value="send_action">
                <input type="hidden" name="dir" value="<?=$chat->actualChatId();?>">
            </div>
        </div>
    </form>


    <form method="POST" class="ajax_form chat_form_ajax" action="chat/chat_ajax.php">
        <div class="chat_dice chat_internal_box">

            <div class="input_container small">
                <select name="abilita">
                    <option value="">Abilita</option>
                    <?= $chat_abi_class->renderChatAbilita(); ?>
                </select>
            </div>

            <div class="input_container small">
                <select name="caratteristica">
                    <option value="">Caratteristica</option>
                    <?= $stat_class->listStats(); ?>
                </select>
            </div>

            <div class="input_container small">
                <select name="oggetto">
                    <?= PersonaggioOggetti::getInstance()->listPgEquipments(Functions::getInstance()->getMyId(),$chat->equippedOnly(),'Oggetti'); ?>
                </select>
            </div>

            <div class="input_container invia">
                <input type="submit" value="Invia">
                <input type="hidden" name="action" value="roll_dice">
                <input type="hidden" name="dir" value="<?=$chat->actualChatId();?>">
            </div>

        </div>
    </form>
    <div class="chat_dice chat_internal_box">
        <?php
        if ( $esiti_chat && $esiti ) { ?>
            <button name="esiti"
                    onclick="modalWindow('esiti', 'Esiti in chat', 'popup.php?page=chat_pannelli_index&pannello=esiti_chat')">
                Esiti
            </button>
        <?php } ?>
    </div>

</div>

<script src="<?= Router::getPagesLink('chat/JS/chat_input.js'); ?>"></script>