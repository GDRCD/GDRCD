<?php

    // Diamo un nome a determinati controlli per semplificare la leggibilità di eventuali condizioni
    $skillsystem_attivo = $PARAMETERS['mode']['skillsystem'] == 'ON';
    $dadi_attivi = $PARAMETERS['mode']['dices'] == 'ON';
    $login_gamemaster = $_SESSION['permessi'] >= GAMEMASTER;

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

                    <?php if (gdrcd_chat_room_is_login_owner($_SESSION['luogo'])) { ?>

                        <!-- Invita in chat -->
                        <option value="<?php echo gdrcd_filter('out', GDRCD_CHAT_PRIVATE_INVITE_TYPE); ?>">
                            <?php echo gdrcd_filter('out', $MESSAGE['chat']['type'][GDRCD_CHAT_PRIVATE_INVITE_TYPE]); ?>
                        </option>

                        <!-- Espelli dalla chat -->
                        <option value="<?php echo gdrcd_filter('out', GDRCD_CHAT_PRIVATE_KICK_TYPE); ?>">
                            <?php echo gdrcd_filter('out', $MESSAGE['chat']['type'][GDRCD_CHAT_PRIVATE_KICK_TYPE]); ?>
                        </option>

                        <!-- Elenco invitati -->
                        <option value="<?php echo gdrcd_filter('out', GDRCD_CHAT_PRIVATE_LIST_TYPE); ?>">
                            <?php echo gdrcd_filter('out', $MESSAGE['chat']['type'][GDRCD_CHAT_PRIVATE_LIST_TYPE]); ?>
                        </option>

                    <?php } ?>
                </select>

                <span>Tipo</span>
            </div>

            <!-- Campo TAG/Destinatario -->
            <div class="input_container small">
                <input type="text" id="tag" name="tag" placeholder="Tag" value="<?= gdrcd_filter('out', gdrcd_chat_get_tag()) ?>">
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
                <input type="text" name="message" id="testo" placeholder="Testo azione" autocomplete="off">

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
                <button class="casella_chat" id="inviaAzione">
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>
                </button>
            </div>

        </div>
    </form>

    <?php if ($skills || $stats || $dice || $items) { ?>

        <!-- Form per tiro caratteristica/abilità/dadi e utilizzo oggetti in chat -->
        <form action="pages/chat/ajax.php?op=chat_skillsystem" method="post" id="skillsystemForm">
            <div class="chat_text chat_row">

                <?php if ($skills) { ?>

                    <!-- Tendina abilità -->
                    <div class="input_container small">
                        <select name="id_ab" id="id_ab">

                            <option value=""></option>

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
                    <div class="input_container small">
                        <select name="id_stats" id="id_stats">

                            <option value=""></option>

                            <?php foreach($stats as $row) { ?>
                                <option value="<?php echo $row['id_stats']; ?>">
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
                    <div class="input_container small">
                        <select name="dice" id="dice">

                            <option value=""></option>

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
                    <div class="input_container small">
                        <select name="id_item" id="id_item">

                            <option value=""></option>

                            <?php foreach ($items as $row) { ?>
                                <option value="<?php echo $row['id_oggetto'];?>">
                                    <?php echo $row['nome']; ?>
                                    (
                                        <!-- quantità oggetto -->
                                        x<?php echo $row['numero']; ?>

                                        <!-- numero cariche oggetto -->
                                        <?php if ($row['max_cariche'] > 0) { ?>
                                            - <?php echo $row['cariche']; ?>/<?php echo $row['max_cariche']; ?>
                                        <?php } ?>
                                    )
                                </option>
                            <?php } ?>

                        </select>

                        <br />

                        <span class="casella_info">
                            <?php echo gdrcd_filter('out', $MESSAGE['chat']['commands']['item']); ?>
                        </span>
                    </div>

                <?php } ?>

                <div class="input_container small">
                    <button class="casella_chat" id="inviaSkill">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>
                    </button>
                </div>

            </div>
        </form>

    <?php } ?>

</div>

<script type="text/javascript" src="/includes/jquery-3.7.1.min.js"></script>
<script>

    /**
     * Script responsabile per l'invio dei form di chat
     */

    $(function() {

        // Conteggio caratteri nell'input di testo
        const $conta = $('#conta');
        const contaCaratteri = e => $conta.html(e.target.value.length);

        // Registra la funzione di conteggio caratteri alla pressione dei tasti nell'input di testo
        $('#testo')
            .keypress(e => contaCaratteri(e))
            .keyup(e => contaCaratteri(e));

        // Invio form azione
        $('#azioneForm').submit(function(event) {
            event.preventDefault();
            postChat($(this).prop('action'), $(this).serialize());

        });

        // Invio form skillsystem
        $('#skillsystemForm').submit(function(event) {
            event.preventDefault();
            postSkillsystem($(this).prop('action'), $(this).serialize());
        });

    });

    /**
     * Invia un messaggio con metodo POST e aggiorna automaticamente la chat
     * Dopo l'invio riuscito, ricarica i messaggi della chat per mostrare il nuovo contenuto
     * @param {string} url - URL dell'endpoint per l'invio del messaggio
     * @param {Object} data - Dati del messaggio da inviare al server
     */
    function postChat(url, data)
    {
        httpPostRequest(url, data)
            .then(() => {
                // svuota l'input di testo
                $('#testo').val('');
            });
    }

    /**
     * Invia dati del sistema di abilità tramite POST
     * Dopo l'invio riuscito, azzera le tendine di selezione e aggiorna la chat
     * @param {string} url - URL dell'endpoint per l'invio dei dati del skillsystem
     * @param {Object} data - Dati delle abilità/caratteristiche/dadi/oggetti da inviare
     */
    function postSkillsystem(url, data)
    {
        httpPostRequest(url, data)
            .then(response => {
                // aggiorna la tendina oggetti
                updateItemSelect(response.message);

                // reset tendine
                $('#id_ab').val('nor');
                $('#id_stats').val('no_stats');
                $('#dice').val('no_dice');
                $('#id_item').val('no_item');
            });
    }

    /**
     * Esegue una richiesta POST HTTP e restituisce una Promise
     * Aggiorna automaticamente la chat in caso di successo e gestisce gli errori
     * @param {string} url - URL dell'endpoint per la richiesta POST
     * @param {Object} data - Dati da inviare con la richiesta POST
     * @returns {Promise} Promise che si risolve con la risposta del server o si rifiuta con i dettagli dell'errore
     */
    function httpPostRequest(url, data)
    {
        return new Promise((resolve, reject) => {
            $.post(url, data)
                .done(function(response) {
                    httpGetChatRead();
                    resolve(response);
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    // TODO: scrivere errore in chat
                    console.error('[GDRCD] HTTP Error Status:', jqXHR.status);

                    reject(jqXHR.status, textStatus, errorThrown);
                });
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

    /**
     * Aggiorna la tendina di selezione oggetti con i dati ricevuti dal server.
     * Ricostruisce le opzioni mostrando nome, quantità e cariche (se presenti) per ogni oggetto.
     *
     * @param {Array<Object>} items - Array di oggetti equipaggiati, ciascuno con proprietà:
     *   id_oggetto: number,
     *   nome: string,
     *   numero: number,
     *   cariche: number,
     *   max_cariche: number|string
     */
    function updateItemSelect(items) {
        const $select = $('#id_item');
        $select.empty();
        $select.append('<option value=""></option>');

        items.forEach(item => {
            let charges = '';

            if (parseInt(item.max_cariche) > 0) {
                charges = `- ${item.cariche}/${item.max_cariche}`;
            }

            $select.append(
                `<option value="${item.id_oggetto}">
                    ${item.nome} ( x${item.numero} ${charges} )
                </option>`
            );
        });
    }
</script>
