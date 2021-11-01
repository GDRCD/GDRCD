<?php

# Inserisco i file necessari se non gia' inseriti
require_once(__DIR__.'/../../includes/required.php');

# Se la classe "chat" non esiste la inizializzo (necessario per i caricamenti in ajax che perdono i file inizializzati via include)
if (!isset($chat)) {
    $chat = Chat::getInstance();
    $chat->resetClass();
}

?>

<div class="chat_bottom">
    <form method="POST" class="chat_form_ajax">
        <div class="chat_text chat_internal_box">

            <div class="input_container small">
                <select name="tipo">
                    <option value="A">Azione</option>
                    <option value="S">Sussurro</option>
                    <option value="F">Sussurro globale</option>

                    <?php if ($_SESSION['permessi'] >= GAMEMASTER) { ?>
                        <option value="N">PNG</option>
                        <option value="M">Master</option>
                    <?php } ?>

                    <?php if ($_SESSION['permessi'] >= MODERATOR) { ?>
                        <option value="MOD">Moderazione</option>
                    <?php } ?>

                </select>
            </div>

            <div class="input_container small">
                <input type="text" name="tag" placeholder="Tag">
            </div>

            <div class="input_container big">
                <input type="text" name="testo" placeholder="Testo azione">
            </div>

            <div class="input_container invia">
                <input type="submit" value="Invia">
                <input type="hidden" name="action" value="send_action">
            </div>
        </div>
    </form>


    <form method="POST" class="chat_form_ajax">
        <div class="chat_dice chat_internal_box">

            <div class="input_container small">
                <select name="abilita">
                    <option value="">Abilita</option>
                    <?=$chat->abilityList();?>
                </select>
            </div>

            <div class="input_container small">
                <select name="caratteristica">
                    <option value="">Caratteristica</option>
                    <option value="0"><?= Filters::out($PARAMETERS['names']['stats']['car0']);?></option>
                    <option value="1"><?= Filters::out($PARAMETERS['names']['stats']['car1']);?></option>
                    <option value="2"><?= Filters::out($PARAMETERS['names']['stats']['car2']);?></option>
                    <option value="3"><?= Filters::out($PARAMETERS['names']['stats']['car3']);?></option>
                    <option value="4"><?= Filters::out($PARAMETERS['names']['stats']['car4']);?></option>
                    <option value="5"><?= Filters::out($PARAMETERS['names']['stats']['car5']);?></option>
                </select>
            </div>

            <div class="input_container small">
                <select name="oggetto">
                    <option value="">Oggetto</option>
                    <?=$chat->objectsList();?>
                </select>
            </div>

            <div class="input_container invia">
                <input type="submit" value="Invia">
                <input type="hidden" name="action" value="roll_dice">
            </div>

        </div>
    </form>
    <div class="chat_dice chat_internal_box">
        <button name="reg_role"
                onclick="modalWindow('reg_role', 'Registra giocata', 'popup.php?page=chat_pannelli_index&pannello=segnalazione_role')">
            Registra role
        </button>
        <button name="esiti" onclick="modalWindow('esiti', 'Esiti in chat', 'popup.php?page=chat_pannelli_index&pannello=esiti_chat')">
            Esiti
        </button>
    </div>

</div>

<script src="/pages/chat/JS/chat_input.js"></script>