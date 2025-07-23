<?php

    // Recupero le informazioni sulla chat corrente
    $chat_info ??= gdrcd_chat_info($_SESSION['luogo']);

    // Diamo un nome a determinati controlli per semplificare la leggibilità di eventuali condizioni
    $skillsystem_attivo = $PARAMETERS['mode']['skillsystem'] == 'ON';
    $dadi_attivi = $PARAMETERS['mode']['dices'] == 'ON';
    $login_gamemaster = $_SESSION['permessi'] >= GAMEMASTER;
    $chat_privata = $chat_info['privata'] == 1;
    $login_proprietario_chat = $chat_info['proprietario'] == $_SESSION['login'];
    $gilda_proprietaria_chat = is_numeric($chat_info['proprietario'])
        && str_contains($_SESSION['gilda'], (string)$chat_info['proprietario']);

    // Elenco abilità del personaggio connesso
    $skills = $skillsystem_attivo
        ? gdrcd_chat_player_skills($_SESSION['login'])
        : null;

    // Elenco caratteristiche
    $stats = $skillsystem_attivo
        ? gdrcd_chat_player_stats()
        : null;

    // Elenco dadi disponibili
    $dice = $dadi_attivi
        ? gdrcd_chat_dice_list()
        : null;

    // Elenco oggetti equipaggiati del personaggio connesso
    $items = $skillsystem_attivo
        ? gdrcd_chat_player_items($_SESSION['login'])
        : null;

?>
<div class="chat_bottom">

    <!-- Form per invio messaggi in chat -->
    <form action="pages/chat/ajax.php?op=chat_write" method="post" id="azioneForm">
        <div class="chat_text chat_row">

            <!-- Tendina selezione tipo di azione -->
            <div class="input_container small">
                <select name="type" id="type">
                    <option value="">Default</option>

                    <!-- Parlato -->
                    <option value="<?php echo gdrcd_filter('out', GDRCD_CHAT_MESSAGE_TYPE); ?>">
                        <?php echo gdrcd_filter('out', $MESSAGE['chat']['type'][GDRCD_CHAT_MESSAGE_TYPE]); ?>
                    </option>

                    <!-- Azione -->
                    <option value="<?php echo gdrcd_filter('out', GDRCD_CHAT_ACTION_TYPE); ?>">
                        <?php echo gdrcd_filter('out', $MESSAGE['chat']['type'][GDRCD_CHAT_ACTION_TYPE]); ?>
                    </option>

                    <!-- Sussurra -->
                    <option value="<?php echo gdrcd_filter('out', GDRCD_CHAT_WHISPER_TYPE); ?>">
                        <?php echo gdrcd_filter('out', $MESSAGE['chat']['type'][GDRCD_CHAT_WHISPER_TYPE]); ?>
                    </option>

                    <?php if($login_gamemaster) { ?>

                        <!-- Master -->
                        <option value="<?php echo gdrcd_filter('out', GDRCD_CHAT_MASTER_TYPE); ?>">
                            <?php echo gdrcd_filter('out', $MESSAGE['chat']['type'][GDRCD_CHAT_MASTER_TYPE]); ?>
                        </option>

                        <!-- PNG -->
                        <option value="<?php echo gdrcd_filter('out', GDRCD_CHAT_PNG_TYPE); ?>">
                            <?php echo gdrcd_filter('out', $MESSAGE['chat']['type'][GDRCD_CHAT_PNG_TYPE]); ?>
                        </option>

                        <!-- Immagine -->
                        <option value="<?php echo gdrcd_filter('out', GDRCD_CHAT_IMAGE_TYPE); ?>">
                            <?php echo gdrcd_filter('out', $MESSAGE['chat']['type'][GDRCD_CHAT_IMAGE_TYPE]); ?>
                        </option>

                    <?php } ?>

                    <?php if ($chat_privata && ($login_proprietario_chat || $gilda_proprietaria_chat)) { ?>

                        <!-- Invita in chat -->
                        <option value="5"><?php echo gdrcd_filter('out', $MESSAGE['chat']['type'][5]); ?></option>

                        <!-- Espelli dalla chat -->
                        <option value="6"><?php echo gdrcd_filter('out', $MESSAGE['chat']['type'][6]); ?></option>

                        <!-- Elenco invitati -->
                        <option value="7"><?php echo gdrcd_filter('out', $MESSAGE['chat']['type'][7]); ?></option>

                    <?php } ?>
                </select>

                <span>Tipo</span>
            </div>

            <!-- Campo TAG/Destinatario -->
            <div class="input_container small">
                <input type="text" id="tag" name="tag" placeholder="Tag" value="<?=$_SESSION['tag'] ?>">
                <span>
                    <?php
                        $descrizione_tag = $MESSAGE['chat']['tag']['info']['tag']
                            . $MESSAGE['chat']['tag']['info']['dst'];

                        if($login_gamemaster){
                            $descrizione_tag .= $MESSAGE['chat']['tag']['info']['png'];
                        }

                        echo gdrcd_filter('out', $descrizione_tag);
                    ?>
                </span>
            </div>

            <!-- Campo Testo Azione -->
            <div class="input_container big">
                <input type="text" name="message" id="testo" placeholder="Testo azione">

                <br />

                <span class="casella_info">
                    <?php echo gdrcd_filter('out', $MESSAGE['chat']['tag']['info']['msg']); ?>
                </span>

                <?php if($PARAMETERS['mode']['chatsave'] == 'ON') { ?>
                    | <span class="casella_info">
                        <a href="javascript:void(0);" onClick="window.open('chat_save.proc.php','Log','width=1,height=1,toolbar=no');">
                            Salva Chat
                        </a>
                    </span>
                <?php } ?>

                <?php if (REG_ROLE) { ?>
                    | <span class="casella_info">
                        <a href="javascript:parent.modalWindow('rolesreg', '', 'popup.php?page=chat_pannelli_index&pannello=segnalazione_role');">
                            Registra giocata
                        </a>
                    </span>
                <?php  } ?>

	            | <span id="conta">Caratteri: 0</span>
            </div>

            <!-- Tasto invio azione -->
            <div class="input_container invia">
                <button class="casella_chat" id="inviaAzione">Invia</button>
            </div>

        </div>
    </form>

    <?php if ($skills || $stats || $dice || $items) { ?>

        <!-- Form per tiro caratteristica/abilità/dadi e utilizzo oggetti in chat -->
        <form action="pages/chat/ajax.php?op=chat_skillsystem" method="post" id="statForm">
            <div class="chat_text chat_row">

                <?php if ($skills) { ?>

                    <!-- Tendina abilità -->
                    <div class="casella_chat">
                        <select name="id_ab" id="id_ab">

                            <option value="no_skill"></option>

                            <?php foreach ($skills as $row) { ?>
                                <option value="<?php echo $row['id_abilita']; ?>">
                                    <?php echo gdrcd_filter('out', $row['nome']); ?>
                                </option>
                            <?php } ?>

                        </select>

                        <br />

                        <span class="casella_info">
                            <?php echo gdrcd_filter('out', $MESSAGE['chat']['commands']['skills']); ?>
                        </span>
                    </div>

                <?php } ?>

                <?php if ($stats) { ?>

                    <!-- Tendina caratteristiche -->
                    <div class="casella_chat">
                        <select name="id_stats" id="id_stats">

                            <option value="no_stats"></option>

                            <?php foreach($stats as $row) { ?>
                                <option value="stats_<?php echo $row['id_stats']; ?>">
                                    <?php echo $row['nome']; ?>
                                </option>
                            <?php } ?>

                        </select>

                        <br />

                        <span class="casella_info">
                            <?php echo gdrcd_filter('out', $MESSAGE['chat']['commands']['stats']); ?>
                        </span>
                    </div>

                <?php } ?>

                <?php if ($dice) { ?>

                    <!-- Tendina dadi -->
                    <div class="casella_chat">
                        <select name="dice" id="dice">

                            <option value="no_dice"></option>

                            <?php foreach($dice as $row) { ?>
                                <option value="<?php echo $row['facce']; ?>">
                                    <?php echo $row['nome']; ?>
                                </option>
                            <?php } ?>

                        </select>

                        <br />

                        <span class="casella_info">
                            <?php echo gdrcd_filter('out', $MESSAGE['chat']['commands']['dice']); ?>
                        </span>
                    </div>

                <?php } ?>

                <?php if ($items) { ?>

                    <!-- Tendina oggetti -->
                    <div class="casella_chat">
                        <select name="id_item" id="id_item">

                            <option value="no_item"></option>

                            <?php foreach ($items as $row) { ?>
                                <option value="<?php echo $row['id_oggetto'];?>">
                                    <?php echo $row['nome']; ?>
                                </option>
                            <?php } ?>

                        </select>

                        <br />

                        <span class="casella_info">
                            <?php echo gdrcd_filter('out', $MESSAGE['chat']['commands']['item']); ?>
                        </span>
                    </div>

                <?php } ?>

                <div class="casella_chat">
                    <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>" onclick="inviaStat()" />
                </div>

            </div>
        </form>

    <?php } ?>

</div>

<script type="text/javascript" src="/includes/jquery-3.7.1.min.js"></script>
<script>
    $(function() {
        $("#azioneForm").submit(function(event) {
            event.preventDefault(); // Previeni l'invio del form normale
            postChat($(this).prop('action'), $(this).serialize());
        });

        $("#testo").on('keypress', function (event) {
            if (event.which == 13) { // 13 corrisponde al tasto Invio
                event.preventDefault(); // Previeni il comportamento predefinito del tasto Invio
                $("#azioneForm").submit(); // Simula l'invio del form
            }
        });
    });

    function postChat(url, data) {
        $.post(url, data)
            .done(function () {
                httpGetChatRead();
            })
            .fail(function () {
                alert("Si è verificato un errore durante l'invio.");
            });
    }

    function open_notes() {
        var notes = window.open('popup.php?page=blocco_note','Blocco','width=500,height=250,toolbar=no');
        notes.onload = function() {
            notes.document.getElementById('type').value = document.getElementById('type').value;
            notes.document.getElementById('testo').value = document.getElementById('testo').value;
            notes.document.getElementById('tag').value = document.getElementById('tag').value;
        }
    }

    function conta() {
        document.getElementById("conta").innerHTML = 'Caratteri: '+document.getElementById("testo").value.length;
    }

    setInterval(conta,10);


    function inviaStat() {

        var id_ab = $("#id_ab").val();
        var id_stats = $("#id_stats").val();
        var dice = $("#dice").val();
        var id_item = $("#id_item").val();
        var locationValue = $("#location").val();
        var op = $("#opstat").val();

        // Chiamata AJAX per inviare i dati al server
        $.post("/pages/chat.inc.php", { id_ab, id_stats, dice, id_item,  location: locationValue, op })
            .done(function () {
                // Gestisci il caso di successo (puoi aggiungere del codice qui se necessario)
                $("#chat_azioni_box").load(" #chat_azioni_box > *", function () {
                    var chatElement = document.getElementById('chat_azioni_box');
                    chatElement.scrollTop = chatElement.scrollHeight;
                });
                $("#id_ab").val("nor");
                $("#id_stats").val("no_stats");
                $("#dice").val("no_dice");
                $("#id_item").val("no_item");


            })

    }
</script>
