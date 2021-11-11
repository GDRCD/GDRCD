<?php

require_once(__DIR__ . '/../../../includes/required.php');

$chat = Chat::getInstance();
$esiti = Esiti::getInstance();

if ($esiti->esitiEnabled() && $esiti->esitiTiriEnabled()) {
    ?>

    <div class="pagina_chat">

        <div class="general_incipit">
            In questa sezione sono elencati tutti gli esiti disponibili per il pg all'interno della presente chat.
            Gli esiti riportati sono gli esiti che ancora devono essere "scoperti", tramite l'apposito tiro in chat.
            Una volta ottenuto l'esito, questi spariscono.
        </div>

        <div class="fake-table esiti_in_chat">
            <div class="tr header">
                <div class="td">Titolo</div>
                <div class="td">Data</div>
                <div class="td">Tiro</div>
                <div class="td">Abilità</div>
                <div class="td">Controlli</div>
            </div>
            <?=$chat->esitiChatList();?>
        </div>

    </div>

    <script src="pages/chat/JS/chat_dadi.js"></script>

<?php } else { ?>
    <div class="warning">Permesso negato</div>
<?php } ?>
