<?php
$info = gdrcd_query("SELECT nome, stanza_apparente, invitati, privata, proprietario, scadenza FROM mappa WHERE id=".$_SESSION['luogo']." LIMIT 1");

?>
<div class="chat_bottom">
<form action="/main.php?dir=<?=$_REQUEST['dir'] ?>" method="post" id="azioneForm">

        <div class="chat_text chat_row" >

            <div class="input_container small">
                <select name="tipo" id="tipo">
                    <option value="P"><?php echo gdrcd_filter('out', $MESSAGE['chat']['type'][0]);//parlato?></option>
                    <option value="A"><?php echo gdrcd_filter('out', $MESSAGE['chat']['type'][1]);//azione?></option>
                    <option value="S"><?php echo gdrcd_filter('out', $MESSAGE['chat']['type'][4]);//sussurro?></option>
                    <?php if($_SESSION['permessi'] >= GAMEMASTER) { ?>
                        <option value="M"><?php echo gdrcd_filter('out', $MESSAGE['chat']['type'][2]);//master?></option>
                        <option value="N"><?php echo gdrcd_filter('out', $MESSAGE['chat']['type'][3]);//png?></option>
                    <?php } ?>
                    <?php if(($info['privata'] == 1) && (($info['proprietario'] == $_SESSION['login']) || ((is_numeric($info['proprietario']) === true) && (strpos($_SESSION['gilda'], ''.$info['proprietario']))))) { ?>
                        <option value="5"><?php echo gdrcd_filter('out', $MESSAGE['chat']['type'][5]);//invita?></option>
                        <option value="6"><?php echo gdrcd_filter('out', $MESSAGE['chat']['type'][6]);//caccia?></option>
                        <option value="7"><?php echo gdrcd_filter('out', $MESSAGE['chat']['type'][7]);//elenco?></option>
                    <?php }//if
                    ?>
                </select>

                <span >Tipo</span>
            </div>
            <div class="input_container small">
                <input type="text" id="tag" name="tag" placeholder="Tag" value="<?=$_SESSION['tag'] ?>">
                <span >
                <?php echo gdrcd_filter('out',$MESSAGE['chat']['tag']['info']['tag'].$MESSAGE['chat']['tag']['info']['dst']);
                if($_SESSION['permessi']>=GAMEMASTER){echo gdrcd_filter('out',$MESSAGE['chat']['tag']['info']['png']);} ?>
            </span>
            </div>
            <div class="input_container big">
                <input type="text" name="testo" id="testo" placeholder="Testo azione">

                <br /><span class="casella_info">
	                                    <?php echo gdrcd_filter('out', $MESSAGE['chat']['tag']['info']['msg']); ?>
	                                </span>
                <?php if($PARAMETERS['mode']['chatsave'] == 'ON') { ?>
                    <span class="casella_info">
                                        <a href="javascript:void(0);" onClick="window.open('chat_save.proc.php','Log','width=1,height=1,toolbar=no');">
                                            Salva Chat
                                        </a>
                                    </span>
                <?php }
                if (REG_ROLE) { ?>
                    | <a href="javascript:parent.modalWindow('rolesreg', '', 'popup.php?page=chat_pannelli_index&pannello=segnalazione_role');">
                        Registra giocata
                    </a>
                <?php  } ?>


	  | <span id="conta">Caratteri: 0</span>
            </span>
            </div>
            <div class="input_container invia">
                <input type="hidden" name="op" id="op" value="send_action">
                <input type="hidden" name="location" id="location" value="<?=$_REQUEST['dir']  ?>">
                <button type="button"  class="casella_chat" id="inviaAzione" onclick="postChat()">Invia</button>
            </div>
        </div>

</form>
    <?php if(($PARAMETERS['mode']['skillsystem'] == 'ON') || ($PARAMETERS['mode']['dices'] == 'ON')) { ?>
    <form action="/main.php?dir=<?=$_REQUEST['dir'] ?>" method="post" id="statForm">

        <div class="chat_text chat_row">
                    <?php if($PARAMETERS['mode']['skillsystem'] == 'ON') { ?>
                        <div class="casella_chat">
                            <?php $result = gdrcd_query("SELECT id_abilita, nome FROM abilita WHERE id_razza=-1 OR id_razza IN (SELECT id_razza FROM personaggio WHERE nome = '".$_SESSION['login']."') ORDER BY nome", 'result'); ?>
                            <select name="id_ab" id="id_ab">
                                <option value="no_skill"></option>
                                <?php while($row = gdrcd_query($result, 'fetch')) { ?>
                                    <option value="<?php echo $row['id_abilita']; ?>">
                                        <?php echo gdrcd_filter('out', $row['nome']); ?>
                                    </option>
                                <?php }//while
                                ?>
                            </select>
                            <br /><span class="casella_info"><?php echo gdrcd_filter('out', $MESSAGE['chat']['commands']['skills']); ?></span>
                        </div>
                        <div class="casella_chat">
                            <select name="id_stats" id="id_stats">
                                <option value="no_stats"></option>
                                <?php
                                /** * Questo modulo aggiunge la possibilità di eseguire prove col dado e caratteristica.
                                 * Pertanto sono qui elencate tutte le caratteristiche del pg.
                                 * @author Blancks
                                 */
                                foreach($PARAMETERS['names']['stats'] as $id_stats => $name_stats) {
                                    if(is_numeric(substr($id_stats, 3))) {
                                        ?>
                                        <option value="stats_<?php echo substr($id_stats, 3); ?>"><?php echo $name_stats; ?></option>
                                        <?php
                                    }
                                }
                                ?>
                            </select>
                            <br /><span class="casella_info"><?php echo gdrcd_filter('out', $MESSAGE['chat']['commands']['stats']); ?></span>
                        </div>
                        <?php
                    } else {
                        echo '<input type="hidden" name="id_ab" id="id_ab" value="no_skill">';
                    }
                    if($PARAMETERS['mode']['dices'] == 'ON') { ?>
                        <div class="casella_chat">
                            <select name="dice" id="dice">
                                <option value="no_dice"></option>
                                <?php
                                /** * Tipi di dado personalizzati da config
                                 * @author Blancks
                                 */
                                foreach($PARAMETERS['settings']['skills_dices'] as $dice_name => $dice_value) { ?>
                                    <option
                                            value="<?php echo $dice_value; ?>"><?php echo $dice_name; ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                            <br /><span class="casella_info"><?php echo gdrcd_filter('out', $MESSAGE['chat']['commands']['dice']); ?></span>
                        </div>
                        <?php
                    } else {
                        echo '<input type="hidden" name="dice" id="dice" value="no_dice">';
                    }
                    if($PARAMETERS['mode']['skillsystem'] == 'ON') { ?>
                        <div class="casella_chat">
                            <?php
                            $result = gdrcd_query("SELECT clgpersonaggiooggetto.id_oggetto, oggetto.nome, clgpersonaggiooggetto.cariche FROM clgpersonaggiooggetto JOIN oggetto ON clgpersonaggiooggetto.id_oggetto = oggetto.id_oggetto WHERE clgpersonaggiooggetto.nome = '".$_SESSION['login']."' AND posizione > 0 ORDER BY oggetto.nome", 'result'); ?>
                            <select name="id_item" id="id_item">
                                <option value="no_item"></option>
                                <?php while($row = gdrcd_query($result, 'fetch')) { ?>
                                    <option value="<?php echo $row['id_oggetto'];?>">
                                        <?php echo $row['nome']; ?>
                                    </option>
                                    <?php
                                }//while
                                gdrcd_query($result, 'free');
                                ?>
                            </select>
                            <br /><span class="casella_info"><?php echo gdrcd_filter('out', $MESSAGE['chat']['commands']['item']); ?></span>
                        </div>
                        <?php
                    } else {
                        echo '<input type="hidden" name="id_item" id="id_item" value="no_item">';
                    } ?>
                    <div class="casella_chat">
                        <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>" />
                        <input type="hidden" name="op" value="take_action">
                    </div>
                </form>
            </div>


</div>
        <?php } ?>
</div>

<script type="text/javascript" src="/includes/jquery-3.7.1.min.js"></script>
<script>
    $(function() {
        $("#azioneForm").submit(function(event) {
            event.preventDefault(); // Previeni l'invio del form normale
            postChat();
        });

        $("#testo").on('keypress', function (event) {
            if (event.which == 13) { // 13 corrisponde al tasto Invio
                event.preventDefault(); // Previeni il comportamento predefinito del tasto Invio
                $("#azioneForm").submit(); // Simula l'invio del form
            }
        });
    });
    function postChat() {
        var testo = $("#testo").val().trim();
        var tag = $("#tag").val();
        var tipo = $("#tipo").val();
        var locationValue = $("#location").val();
        var op = $("#op").val();

        if ((testo !== "")||(tipo == 5)) {
            $.post("/pages/chat.inc.php", { tag, testo, tipo, location: locationValue, op  })
                .done(function () {
                    // Gestisci il caso di successo (puoi aggiungere del codice qui se necessario)
                    $("#testo").val("");
                    $("#chat_azioni_box").load(" #chat_azioni_box > *", function() {

                        var chatElement = document.getElementById('chat_azioni_box');
                        chatElement.scrollTop = chatElement.scrollHeight;
                    });
                })
                .fail(function () {
                    alert("Si è verificato un errore durante l'invio.");
                });
        }
    }
    function open_notes() {
        var notes = window.open('popup.php?page=blocco_note','Blocco','width=500,height=250,toolbar=no');
        notes.onload = function() {
            notes.document.getElementById('tipo').value = document.getElementById('tipo').value;
            notes.document.getElementById('testo').value = document.getElementById('testo').value;
            notes.document.getElementById('tag').value = document.getElementById('tag').value;
        }
    }
    function conta() {
        document.getElementById("conta").innerHTML = 'Caratteri: '+document.getElementById("testo").value.length;
    }
    setInterval(conta,10);

    function updateNumberOptions() {
        var selectedItem = $('#id_item').val(); // Ottieni il valore selezionato dal primo select
        var numberSelect = $('#number_item'); // Riferimento al secondo select


        // Pulisci le opzioni precedenti
        numberSelect.empty();
        // Aggiungi nuove opzioni in base al numero massimo dell'oggetto selezionato
        if (selectedItem !== 'no_item') {
            $.ajax({
                url: '/pages/chat.inc.php', // Sostituisci con il percorso del tuo script PHP per ottenere il numero massimo
                method: 'POST',
                data: { id_item: selectedItem, op: 'get_max_number' }, // Passa l'ID dell'oggetto al server
                success: function(data) {
                    var jsonData = data.match(/\{.*\}/);
                    var parsedData = JSON.parse(jsonData[0]);
                    var maxNumber =  (parsedData.esito);

                  //  var maxNumber = parseInt(response); // Converti la risposta in un numero intero
                    // Aggiungi le nuove opzioni in base al numero massimo dell'oggetto selezionato
                    for (var i = 1; i <= maxNumber; i++) {
                        numberSelect.append($('<option>', {
                            value: i,
                            text: i
                        }));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Errore durante il recupero del numero massimo:', error);
                }
            });
        } else {
            // Aggiungi un'opzione vuota se non è stato selezionato nulla nel primo select
            numberSelect.append($('<option>', {
                value: 'no_number',
                text: ''
            }));
        }
    }

    // Aggiungi un listener per l'evento change sul primo select
    $('#id_item').change(updateNumberOptions);

    // Chiama la funzione per aggiornare le opzioni iniziali del secondo select
    updateNumberOptions();
    function inviaStat() {
        var forma = $("#forma").val().trim();
        var id_stats = $("#id_stats").val();
        var dice = $("#dice").val();
        var id_item = $("#id_item").val();
        var number_item = $("#number_item").val();
        var locationValue = $("#location").val();
        var op = $("#opstat").val();

        // Chiamata AJAX per inviare i dati al server
        $.post("/pages/chat.inc.php", { forma, id_stats, dice, id_item, number_item, location: locationValue, op })
            .done(function () {
                // Gestisci il caso di successo (puoi aggiungere del codice qui se necessario)
                $("#chat_azioni_box").load(" #chat_azioni_box > *", function () {
                    var chatElement = document.getElementById('chat_azioni_box');
                    chatElement.scrollTop = chatElement.scrollHeight;
                });
                $("#forma").val("nor");
                $("#id_stats").val("no_stats");
                $("#dice").val("no_dice");
                $("#id_item").val("no_item");
                $("#number_item").val("no_number");

            })


        // Aggiorna le opzioni del select number_item in base all'oggetto selezionato
        updateNumberOptions();
    }
</script>