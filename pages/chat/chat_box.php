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

            // TODO: icona di caricamento di default in chat che viene rimossa appena completata la prima richiesta

            $(document).ready(() => {
                // Avvio lettura azioni chat - inizializza il polling automatico
                let chatInterval = chatReadStart();

                /**
                 * Event listener per il cambio di visibilità della pagina
                 * Ferma il lampeggiamento del titolo quando l'utente torna sulla tab
                 */
                $(document).on('visibilitychange', () => chatBlinkTitleStop());
            });

            /**
             * Inizializza il sistema di aggiornamento automatico della chat
             * Esegue subito una lettura e poi imposta un intervallo di 15 secondi
             * @returns {number} ID dell'intervallo per eventuali cancellazioni future
             */
            function chatReadStart()
            {
                // Prima esecuzione immediata
                httpGetChatRead();

                // Imposta polling ogni 10 secondi (10000 ms)
                return setInterval(() => httpGetChatRead(), 10000);
            }

            /**
             * Effettua la richiesta AJAX per leggere i nuovi messaggi della chat
             */
            function httpGetChatRead()
            {
                $.get('pages/chat/ajax.php?op=chat_read')
                    .done(function(data) {

                        // Se non ci sono nuove azioni usciamo qui
                        if (!data.message || data.message.length === 0) {
                            return;
                        }

                        // inserisce le azioni in chat
                        chatScreenAppendMessages(data.message);

                    })
                    .fail(function(jqXHR, textStatus, errorThrown) {

                        // TODO: scrivere errore in chat e interrompere il pollig in caso di 403

                        console.error('[GDRCD] HTTP Error Status:', jqXHR.status);

                    });
            }

            /**
             * Aggiunge i nuovi messaggi formattati alla chat e gestisce lo scroll automatico.
             *
             * Riceve un array di oggetti messaggio, estrae l'HTML da ciascun elemento e lo aggiunge
             * al contenitore della chat. Dopo l'inserimento, esegue lo scroll automatico verso il basso
             * e avvia la notifica visiva se la finestra non è attiva.
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

                // Cerca nei nuovi messaggi almeno un messaggio scritto da un altro utente
                // in tal caso valorizza canPlayAudio a true. Altrimenti sarà false.
                const canPlayAudio = data.reduce(
                    (result, item) => result || item.mittente !== '<?= $_SESSION['login'] ?>',
                    false
                );

                if (canPlayAudio) {
                    <?= AudioController::playFunction('chat') ?>
                }

                // Timeout per permettere al DOM di aggiornarsi prima dello scroll (aspetta 500ms)
                setTimeout(function() {
                    chatScreenAutoScroll();
                    chatBlinkTitleStart();
                }, 500);
            }

            /**
             * Gestisce lo scroll automatico della chat verso il basso
             * @param {number} time - Durata dell'animazione in millisecondi (default: 300)
             */
            function chatScreenAutoScroll(time = 300)
            {
                const $chatAzioniBox = $('#chat_azioni_box');
                $chatAzioniBox.animate({ scrollTop: $chatAzioniBox.height() }, time);
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
