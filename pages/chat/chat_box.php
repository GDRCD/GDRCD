<?php

    // All'apertura della chat, resetto il lastmessage, in questo modo verranno
    // caricati tutti i messaggi disponibili con la prima richiesta.
    gdrcd_chat_set_lastmessage_id(0);

    // Recupero le informazioni sulla chat corrente
    $chat_info = gdrcd_chat_info($_SESSION['luogo']);

?>
<div class="chat_box">
    <div class="page_title"><?= gdrcd_chat_name($chat_info) ?></div>

    <?php if( !gdrcd_chat_is_accessible($chat_info) ) { ?>

        <div class="warning">Non sei abilitato a visualizzare questa chat.</div>

    <?php } else { ?>

        <div id="chat_azioni_box" class="chat_azioni_box">
            <div id="chat_azioni" class="chat_azioni"></div>
        </div>

        <div class="chat_input_box">
            <?php require dirname(__FILE__) . '/chat_input.php'; ?>
        </div>

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
                    .done(function(json) {

                        const data = chatReadResponseDecode(json);

                        // Se non ci sono nuove azioni usciamo qui
                        if (!data.message || data.message.length === 0) {
                            return;
                        }

                        chatScreenAppendMessages(data.message.join(''));

                    })
                    .fail(function(jqXHR, textStatus, errorThrown) {

                        // TODO: scrivere errore in chat e interrompere il pollig in caso di 403

                        console.error('[GDRCD] HTTP Error Status:', jqXHR.status);

                    });
            }

            /**
             * Decodifica la risposta JSON ricevuta dal server
             * @param {string} json - Stringa JSON da decodificare
             * @returns {string[]} Array di messaggi decodificati o undefined in caso di errore
             */
            function chatReadResponseDecode(json)
            {
                if (typeof json !== 'string') {
                    return json;
                }

                try {
                    return JSON.parse(json);
                } catch (e) {
                    console.error("[GDRCD] Impossibile decodificare la risposta:", e);
                    throw e;
                }
            }

            /**
             * Aggiunge i nuovi messaggi alla chat e gestisce lo scroll automatico
             * @param {string} html - HTML dei messaggi da aggiungere al container della chat
             */
            function chatScreenAppendMessages(html)
            {
                // Aggiunge i nuovi messaggi al contenitore della chat
                $('#chat_azioni').append(html);

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
