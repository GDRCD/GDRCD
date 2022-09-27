<?php

Router::loadRequired();

$chat = Chat::getInstance();
$esiti = Esiti::getInstance();

if ( $esiti->esitiEnabled() && $esiti->esitiTiriEnabled() ) {
    ?>

    <div class="pagina_chat">

        <div class="general_incipit" style="text-align: center">
            Di seguito sono elencati tutti gli esiti non ancora lanciati per questa chat.
        </div>

        <div class="fake-table esiti_in_chat">
            <?= $esiti->esitiChatList(); ?>
        </div>

    </div>

    <script src="<?= Router::getPagesLink('chat/JS/chat_dadi.js'); ?>"></script>

<?php } else { ?>
    <div class="warning">Permesso negato</div>
<?php } ?>
