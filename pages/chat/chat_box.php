<?php

    // All'apertura della chat, resetto il lastmessage, in questo modo verranno
    // caricati tutti i messaggi disponibili con la prima richiesta.
    gdrcd_chat_set_lastmessage_id(0);

    // Recupero le informazioni sulla chat corrente
    $chat_info = gdrcd_chat_room_info($_SESSION['luogo']);

?>
<div class="chat_box">
    <div class="page_title"><?= gdrcd_chat_room_name($chat_info) ?></div>

    <?php if( !gdrcd_chat_room_is_login_allowed($chat_info) ) { ?>

        <div class="warning">Non sei abilitato a visualizzare questa chat.</div>

    <?php } else { ?>

        <div id="chat_azioni_box" class="chat_azioni_box">
            <div id="chat_loading"></div>
            <div id="chat_azioni" class="chat_azioni"></div>
        </div>

        <div class="chat_input_box">
            <?php require dirname(__FILE__) . '/chat_input.php'; ?>
        </div>

        <?php
            // Costruisco il controllore audio
            echo AudioController::build('chat');
        ?>

        <script>

            /**
             * Script responsabile del caricamento delle nuove azioni in chat
             */

            $(document).ready(() => {
                // Avvio lettura azioni chat - inizializza il polling automatico
                let chatInterval = chatReadPollig();

                /**
                 * Event listener per il cambio di visibilità della pagina
                 * Ferma il lampeggiamento del titolo quando l'utente torna sulla tab
                 */
                $(document).on('visibilitychange', () => chatBlinkTitleStop());
            });

            /**
             * Inizializza il sistema di aggiornamento automatico della chat
             * Esegue subito una lettura e poi una successiva 10 secondi
             * @returns {number} ID dell'intervallo per eventuali cancellazioni future
             */
            function chatReadPollig()
            {
                // Prima esecuzione immediata
                chatReadHandler();

                // Imposta polling ogni 10 secondi (10000 ms)
                return setInterval(() => chatReadHandler(), 10000);
            }

            /**
             * Gestisce la lettura dei nuovi messaggi dalla chat tramite richiesta XHR.
             *
             * Effettua una chiamata al server per recuperare i nuovi messaggi, nasconde l'icona
             * di caricamento e aggiunge i messaggi alla chat secondo la modalità configurata
             * (dall'alto verso il basso o dal basso verso l'alto).
             *
             * @returns {void}
             */
            function chatReadHandler()
            {
                const $chat_loading = $('#chat_loading');

                httpGetChatRead()
                    .then(data => {

                        // nasconde l'icona di caricamento in chat
                        $chat_loading.css('display', 'none');

                        // Se non ci sono nuove azioni usciamo qui
                        if (!data.message || data.message.length === 0) {
                            return;
                        }

                        // Aggiunge i messaggi letti nella chat
                        chatAddMessages(data.message);

                    });
            }

            /**
             * Effettua la richiesta XHR per leggere i nuovi messaggi della chat.
             * Ritorna una promise.
             */
            function httpGetChatRead()
            {
                return new Promise((resolve, reject) => {
                    $.get('pages/chat/ajax.php?op=chat_read')
                        .done(function(response) {

                            resolve(response);

                        })
                        .fail(function(jqXHR, textStatus, errorThrown) {

                            let errorResponse = jqXHR.responseText;

                            try {
                                errorResponse = JSON.parse(errorResponse);
                            } catch (e) {
                                console.error('[GDRCD] Error decoding response:', errorResponse);
                                errorResponse = {code: jqXHR.status, message: errorResponse};
                            }

                            // TODO: flusso da testare. Potrebbe essere necessario sospendere il pollig
                            chatTransientError(errorResponse.code, errorResponse);
                            reject(jqXHR.status, errorResponse, errorThrown);

                        });
                });
            }

            /**
             * Aggiunge i nuovi messaggi alla chat utilizzando la modalità di visualizzazione configurata.
             *
             * Controlla la configurazione del sito per determinare se i messaggi devono essere aggiunti
             * dall'alto verso il basso (append) o dal basso verso l'alto (prepend) e chiama la funzione
             * appropriata per gestire l'inserimento e lo scroll.
             *
             * @param {Array<{id: number, azione: string, mittente: string}>} messages - Array di messaggi da aggiungere alla chat
             */
            function chatAddMessages(messages)
            {
                <?php if ($PARAMETERS['mode']['chat_from_bottom'] === 'OFF') { ?>

                    // Aggiunge azioni in chat: dall'alto verso il basso
                    chatScreenAppendMessages(messages);

                <?php } else { ?>

                    // Aggiunge azioni in chat: dal basso verso l'alto
                    chatScreenPrependMessages(messages);

                <?php } ?>
            }

            /**
             * Aggiunge i nuovi messaggi formattati alla chat e gestisce lo scroll automatico.
             *
             * Riceve un array di oggetti messaggio, estrae l'HTML da ciascun elemento e lo aggiunge
             * al contenitore della chat. Dopo l'inserimento, riproduce la notifica sonora, esegue
             * lo scroll automatico verso il basso e avvia la notifica visiva se la finestra non è attiva.
             *
             * @param {Array<{id: number, azione: string}>} data - Array di messaggi da aggiungere, ciascuno con id e HTML formattato
             */
            function chatScreenAppendMessages(data)
            {
                // Le azioni arrivano in un formato che contiene
                // l'id del messaggio e l'azione formattata in html.
                // Questa parte di codice crea un array nella variabile azioni
                // dove ogni elemento è un azione formattata in html.
                const azioni = data.map(item => item.azione);

                // Aggiunge i nuovi messaggi al contenitore della chat
                $('#chat_azioni').append(azioni.join(''));

                // Riproduce la notifica sonora per avvisare dei nuovi messaggi in chat
                chatPlayAudio(data);

                // Timeout per permettere al DOM di aggiornarsi prima dello scroll (aspetta 500ms)
                setTimeout(function() {
                    chatScreenAutoScroll('down');
                    chatBlinkTitleStart();
                }, 500);
            }

            /**
             * Inserisce messaggi all'inizio della chat e gestisce lo scroll automatico.
             *
             * Riceve un array di oggetti messaggio (id e HTML formattato), li ordina dal più recente al più vecchio,
             * e li aggiunge all'inizio del contenitore della chat. Dopo l'inserimento riproduce la notifica sonora,
             * esegue lo scroll automatico verso l'alto e avvia la notifica visiva se la finestra non è attiva.
             *
             * @param {Array<{id: number, azione: string}>} data - Array di messaggi da inserire, ciascuno con id e HTML formattato
             */
            function chatScreenPrependMessages(data)
            {
                // Le azioni arrivano in un formato che contiene
                // l'id del messaggio e l'azione formattata in html.
                // Questa parte di codice crea un array nella variabile azioni
                // dove ogni elemento è un azione formattata in html.
                const azioni = data.map(item => item.azione).reverse();

                // Aggiunge i nuovi messaggi in cima alla chat
                $('#chat_azioni').prepend(azioni.join(''));

                // Riproduce la notifica sonora per avvisare dei nuovi messaggi in chat
                chatPlayAudio(data);

                // Timeout per permettere al DOM di aggiornarsi prima dello scroll (aspetta 500ms)
                setTimeout(function() {
                    chatScreenAutoScroll('up');
                    chatBlinkTitleStart();
                }, 500);
            }

            /**
             * Riproduce la notifica sonora per nuovi messaggi in chat.
             * Esegue l'audio solo se almeno uno dei nuovi messaggi è stato inviato da un altro utente.
             *
             * @param {Array<{mittente: string, id: number, azione: string}>} data - Array di messaggi ricevuti
             */
            function chatPlayAudio(data) {
                const canPlayAudio = data.reduce(
                    (result, item) => result || item.mittente !== '<?= $_SESSION['login'] ?>',
                    false
                );

                if (canPlayAudio) {
                    <?= AudioController::playFunction('chat') ?>
                }
            }

            /**
             * Gestisce lo scroll automatico della chat verso il basso
             * @param {number} time - Durata dell'animazione in millisecondi (default: 300)
             */
            function chatScreenAutoScroll(direction = 'down', time = 300)
            {
                const $chatAzioniBox = $('#chat_azioni_box');
                $chatAzioniBox.animate({
                    scrollTop: direction === 'down'
                        ? $chatAzioniBox[0].scrollHeight
                        : 0
                }, time);
            }

            /**
             * Avvia il lampeggiamento del titolo della pagina se la finestra non è visibile
             * Utilizzato per notificare all'utente la presenza di nuovi messaggi
             */
            function chatBlinkTitleStart()
            {
                if (document.hidden) {
                    blink_title("Nuova azione!", true);
                }
            }

            /**
             * Ferma il lampeggiamento del titolo della pagina se la finestra è visibile
             * Utilizzato quando l'utente torna a visualizzare la chat
             */
            function chatBlinkTitleStop()
            {
                if (!document.hidden) {
                    stop_blinking_title();
                }
            }

        </script>

    <?php } ?>
</div>
