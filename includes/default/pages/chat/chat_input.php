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

?>

<div class="chat_bottom">

    <form class="ajax_form chat_form_ajax" action="chat/chat_ajax.php" data-callback="invioAzioneSuccess"
          data-swal="false" data-reset="false">
        <div class="chat_text chat_row">

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
                <input type="text" name="tag" placeholder="Tag" value="<?= Session::read('tag'); ?>">
            </div>

            <div class="input_container big">
                <input type="text" name="testo" placeholder="Testo azione">
            </div>

            <div class="input_container invia">
                <input type="submit" value="Invia">
                <input type="hidden" name="action" value="send_action">
                <input type="hidden" name="dir" value="<?= $chat->actualChatId(); ?>">
            </div>
        </div>
    </form>

    <div class="chat_dice chat_row chat_align_top">

        <div class="input_container medium">
            <form method="POST" class="ajax_form chat_form_ajax" action="chat/chat_ajax.php" data-callback="invioAzioneSuccess">

                <div class="general_title">
                    Lancio Abilità
                </div>
                <div class="single_input">
                    <select name="abilita">
                        <?= ChatAbilita::getInstance()->renderChatAbilita(); ?>
                    </select>
                </div>

                <div class="single_input">
                    <input type="submit" value="Invia">
                    <input type="hidden" name="action" value="roll_ability">
                    <input type="hidden" name="dir" value="<?= $chat->actualChatId(); ?>">
                </div>
            </form>
        </div>

        <div class="input_container medium">

            <form method="POST" class="ajax_form chat_form_ajax" action="chat/chat_ajax.php" data-callback="invioAzioneSuccess">

                <div class="general_title">
                    Lancio Statistica
                </div>
                <div class="single_input">
                    <select name="stat">
                        <?= Statistiche::getInstance()->listStats(); ?>
                    </select>
                </div>

                <div class="single_input">
                    <input type="submit" value="Invia">
                    <input type="hidden" name="action" value="roll_stat">
                    <input type="hidden" name="dir" value="<?= $chat->actualChatId(); ?>">

                </div>
            </form>
        </div>

        <div class="input_container medium">

            <form method="POST" class="ajax_form chat_form_ajax" action="chat/chat_ajax.php" data-callback="invioAzioneSuccess">


                <div class="general_title">
                    Lancio Oggetto
                </div>

                <div class="single_input">
                    <select name="oggetto_pg">
                        <?= PersonaggioOggetti::getInstance()->listChatPgEquipments(Functions::getInstance()->getMyId(), $chat->equippedOnly()); ?>
                    </select>
                </div>

                <div class="single_input">
                    <input name="cariche" placeholder="Cariche" type="number" min="1" max="100" step="1" required>
                </div>

                <div class="single_input">
                    <input type="submit" value="Invia">
                    <input type="hidden" name="action" value="roll_obj">
                    <input type="hidden" name="dir" value="<?= $chat->actualChatId(); ?>">
                </div>
            </form>
        </div>


    </div>

    <div class="chat_dice chat_row">
        <?php
        if ( $esiti_chat && $esiti ) { ?>
            <button name="esiti"
                    onclick="modalWindow('esiti', 'Esiti in chat', 'popup.php?page=chat_pannelli_index&pannello=esiti_chat')">
                Esiti
            </button>
        <?php } ?>
    </div>

    <script src="<?= Router::getPagesLink('chat/JS/chat_input.js'); ?>"></script>