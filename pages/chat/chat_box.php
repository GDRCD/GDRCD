<?php

    $chat_id = gdrcd_filter("num",$_GET['dir']);

?>
<div class="chat_box">
    <div class="page_title"><?=chat_name($chat_id)?></div>
    <?php
    if(controlloChat($chat_id)) {?>
        <div class="chat_azioni_box" id="chat_azioni_box">
            <?php require_once(__DIR__ . '/chat_azioni.php'); ?>
        </div>
        <div class="chat_input_box">
            <?php require_once(__DIR__ . '/chat_input.php'); ?>
        </div>
</div>
    <div style="height:0;">
        <?php
        // Prevedo la notifica in caso di nuovi messaggi
        if($_SESSION['last_message'] > 0 && (isset($isLastMessageFromUser) && !$isLastMessageFromUser) && (isset($add_chat) && $add_chat != '')){
        $playAudioController = AudioController::play('chat', TRUE);;
        }
        ?>
    </div>

    <script>
            document.addEventListener('DOMContentLoaded', function() {
                var isFirstLoad = true;
                var lastMessageCount = document.getElementById('countmessages').value;


                // Imposta lo scroll verso il basso durante la prima carica
                var chatElement = document.getElementById('chat_azioni_box');
                chatElement.scrollTop = chatElement.scrollHeight;

                // Riferimento all'elemento audio
                var audioElement = parent.document.getElementById('sound_player');

                // Evento 'loadedmetadata' per assicurarsi che l'audio sia pronto
                audioElement.addEventListener('loadedmetadata', function() {
                    console.log('Audio ready');
                    audioElement.pause();
                });

                function checkForNewMessages(callback) {
                    $.post('/pages/chat.inc.php', { op: "check_chat" }, function(data) {
                        var jsonData = data.match(/\{.*\}/);

                        if (jsonData) {
                            var parsedData = JSON.parse(jsonData[0]);
                            callback(parsedData.esito);
                            console.log("Controllo chat: " + parsedData.esito);
                        } else {
                            console.error("Errore nella risposta del server: nessun dato JSON trovato");
                        }
                    });
                }

                function updateChat() {
                    checkForNewMessages(function(newMessageCount) {
                        if ((newMessageCount != lastMessageCount) || isFirstLoad) {
                            isFirstLoad = false;

                            $("#chat_azioni_box").load(" #chat_azioni_box > *", function() {
                                setTimeout(function() {
                                    var chatElement = document.getElementById('chat_azioni_box');
                                    chatElement.scrollTop = chatElement.scrollHeight;

                                    if(lastMessageCount>0){
                                        audioElement.play();
                                    }

                                    if (document.hidden && lastMessageCount>0) {

                                        parent.blink_title("Nuova azione!", true);
                                    }
                                    }, 600);
                            });
                            lastMessageCount = newMessageCount;
                        } else {
                            audioElement.pause();

                        }
                    });
                }

                setInterval(function() {
                    updateChat();
                    console.log("Messaggi presenti: " + lastMessageCount);
                }, 15000);

                // Aggiungi un listener per l'evento visibilitychange
                document.addEventListener("visibilitychange", function() {
                    // Se la finestra diventa visibile, ferma il lampeggio
                    if (!document.hidden) {
                        parent.stop_blinking_title();
                    }
                });
            });

    </script>


    <?php

    }else{
        echo  '<div class="warning">Non hai i permessi per visualizzare questa chat.</div>';
    }
    ?>









