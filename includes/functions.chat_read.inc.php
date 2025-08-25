<?php
/**
 * Questo file contiene tutte le funzioni della chat
 * responsabili per la lettura e formattazione delle azioni
 */

/**
 * Recupera e formatta tutti i messaggi di chat successivi a un determinato ID per una stanza specifica.
 *
 * Esegue una query sul database per ottenere i messaggi della chat relativi alla stanza ($luogo)
 * con ID maggiore di $last_id e solo se inviati entro le ultime quattro ore.
 * Ogni messaggio viene formattato in HTML tramite gdrcd_chat_message_handler().
 *
 * @param int $luogo L'ID della stanza di chat da cui leggere i messaggi
 * @param int $last_id Facoltativo. L'ID dell'ultimo messaggio già letto (default: 0)
 * @return array{code: int, message: array<array{id: int, azione: string}>} Elenco dei messaggi formattati in HTML
 */
function gdrcd_chat_read_messages($luogo, $last_id = 0)
{
    // Query per recuperare i messaggi successivi a $last_id
    $query_azioni = gdrcd_stmt(
        "SELECT
            chat.id,
            chat.imgs,
            chat.mittente,
            chat.destinatario,
            chat.tipo,
            chat.ora,
            chat.testo,
            personaggio.url_img_chat

        FROM chat
            INNER JOIN mappa ON mappa.id = chat.stanza
            LEFT JOIN personaggio ON personaggio.nome = chat.mittente

        WHERE chat.id > ?
            AND chat.stanza = ?

            -- Nasconde i messaggi nella chat privata precedenti alla prenotazione
            AND chat.ora > IFNULL(mappa.ora_prenotazione, '0000-00-00 00:00:00')

            -- Impedisce di leggere dalla chat messaggi più vecchi di 4 ore
            AND chat.ora > DATE_SUB(NOW(), INTERVAL 4 HOUR)

        ORDER BY id ASC",
        [
            'ii',
            $last_id,
            $luogo,
        ]
    );

    // Contenitore per tutte le nuove azioni formattate in html
    $azioni = [];

    if (gdrcd_query($query_azioni, 'num_rows') > 0) {

        while ($riga_azione = gdrcd_query($query_azioni, 'assoc')) {

            // formatta l'azione da inviare in chat
            $azione = gdrcd_chat_message_handler($riga_azione);

            // se la formattazione ritorna un $azione vuota, skip alla prossima azione
            if (empty($azione)) {
                continue;
            }

            $azioni[] = [
                'id' => $riga_azione['id'],
                'mittente' => $riga_azione['mittente'],
                'azione' => $azione,
            ];

        }

        gdrcd_query($query_azioni, 'free');

    }

    return gdrcd_chat_status_ok($azioni);
}

/**
 * Ritorna la formattazione HTML più appropriata per l'azione in chat.
 * Sono supportati i seguenti tipi di azione:
 *  - P: parlato
 *  - A: azione
 *  - S: sussurro
 *  - C: tiro su caratteristica
 *  - F: tiro su abilità
 *  - D: tiro di dado
 *  - O: utilizzo oggetto
 *  - M: azione master
 *  - N: azione PNG
 *  - I: immagine
 *  - X: invito chat privata
 *  - Y: espulsione chat privata
 *  - Z: elenco invitati chat privata
 *
 * @param array{
 *      tipo: string,
 *      mittente: string,
 *      destinatario: string,
 *      url_img_chat: string,
 *      ora: string,
 *      imgs: string,
 *      testo: string,
 * } $azione
 * @return null|string La formattazione HTML dell'azione. Può ritornare invece `null` se l'azione è
 * di una tipologia non supportata o l'utente non ha i permessi per visionarla (esempio: sussurri)
 */
function gdrcd_chat_message_handler($azione)
{
    switch ($azione['tipo']) {
        case GDRCD_CHAT_MESSAGE_TYPE:
            return gdrcd_chat_message_format($azione);

        case GDRCD_CHAT_ACTION_TYPE:
            return gdrcd_chat_action_format($azione);

        case GDRCD_CHAT_WHISPER_TYPE:
            return gdrcd_chat_whisper_format($azione);

        case GDRCD_CHAT_STATS_TYPE:
            return gdrcd_chat_stats_format($azione);

        case GDRCD_CHAT_SKILL_TYPE:
            return gdrcd_chat_skill_format($azione);

        case GDRCD_CHAT_DICE_TYPE:
            return gdrcd_chat_dice_format($azione);

        case GDRCD_CHAT_ITEM_TYPE:
            return gdrcd_chat_item_format($azione);

        case GDRCD_CHAT_MASTER_TYPE:
            return gdrcd_chat_master_format($azione);

        case GDRCD_CHAT_PNG_TYPE:
            return gdrcd_chat_png_format($azione);

        case GDRCD_CHAT_IMAGE_TYPE:
            return gdrcd_chat_image_format($azione);

        case GDRCD_CHAT_PRIVATE_INVITE_TYPE:
            return gdrcd_chat_private_invite_format($azione);

        case GDRCD_CHAT_PRIVATE_KICK_TYPE:
            return gdrcd_chat_private_kick_format($azione);

        case GDRCD_CHAT_PRIVATE_LIST_TYPE:
            return gdrcd_chat_private_list_format($azione);

        default:
            return null;
    }
}

/**
 * Ritorna la formattazione html per un messaggio di tipo "Immagine" ( I ) in chat.
 *
 * @param array{
 *      tipo: string,
 *      mittente: string,
 *      destinatario: string,
 *      url_img_chat: string,
 *      ora: string,
 *      imgs: string,
 *      testo: string,
 * } $azione
 * @return string
 */
function gdrcd_chat_image_format($azione)
{
    // URL immagine da mostrare in chat
    $image_url = gdrcd_filter('fullurl', $azione['testo']);

    // Tipologia di azione. Es: I
    $azione_tipo = $azione['tipo'];

    // Assemblo la formattazione HTML per la tipologia di messaggio
    return gdrcd_chat_message_component(
        $azione_tipo,
        <<<HTML
            <img class="chat_img" src="{$image_url}" />
        HTML
    );
}

/**
 * Ritorna la formattazione html per un messaggio di tipo "PNG" ( N ) in chat.
 *
 * @param array{
 *      tipo: string,
 *      mittente: string,
 *      destinatario: string,
 *      url_img_chat: string,
 *      ora: string,
 *      imgs: string,
 *      testo: string,
 * } $azione
 * @return string
 */
function gdrcd_chat_png_format($azione)
{
    // formattazione orario del messaggio
    $chat_time = gdrcd_chat_time_component($azione);

    // formattazione corpo messaggio
    $chat_body = gdrcd_chat_body_with_colors_component($azione, null);

    // formattazione nome del png
    $chat_png_name = gdrcd_chat_name_component($azione['destinatario']);

    // Tipologia di azione. Es: N
    $azione_tipo = $azione['tipo'];

    // Assemblo la formattazione HTML per la tipologia di messaggio
    return gdrcd_chat_message_component(
        $azione_tipo,
        <<<HTML
            {$chat_time}
            {$chat_png_name}
            {$chat_body}
        HTML
    );
}

/**
 * Ritorna la formattazione html per un messaggio di tipo "Master" ( M ) in chat.
 *
 * @param array{
 *      tipo: string,
 *      mittente: string,
 *      destinatario: string,
 *      url_img_chat: string,
 *      ora: string,
 *      imgs: string,
 *      testo: string,
 * } $azione
 * @return string
 */
function gdrcd_chat_master_format($azione)
{
    // Ora dell'azione
    $azione_time = gdrcd_format_time($azione['ora']);

    // Formatta il testo per l'azione master
    $testo = gdrcd_chat_highlight_user(
        $_SESSION['login'],
        gdrcd_bbcoder(gdrcd_filter('out', $azione['testo'])),
        true
    );

    // Tipologia di azione. Es: M
    $azione_tipo = $azione['tipo'];

    // Assemblo la formattazione HTML per la tipologia di messaggio
    return gdrcd_chat_message_component(
        $azione_tipo,
        <<<HTML
            <div class="ora_ms">{$azione_time} Master Screen</div>
            <span class="chat_master">{$testo}</span>
        HTML
    );
}

/**
 * Ritorna la formattazione html per un messaggio di tipo "Utilizzo Oggetto" ( O ) in chat.
 *
 * @param array{
 *      tipo: string,
 *      mittente: string,
 *      destinatario: string,
 *      url_img_chat: string,
 *      ora: string,
 *      imgs: string,
 *      testo: string,
 * } $azione
 * @return string
 */
function gdrcd_chat_item_format($azione)
{
    $MESSAGE = $GLOBALS['MESSAGE'];

    // formattazione orario del messaggio
    $chat_time = gdrcd_chat_time_component($azione);

    // formattazione mittente del messaggio in chat
    $chat_mittente = gdrcd_chat_name_component($azione);

    // decodifica il messaggio
    $body = json_decode($azione['testo'], true);

    // Formatta le informazioni da scrivere in chat
    $messaggio_stats = <<<HTML
        {$chat_mittente}
        {$MESSAGE['chat']['commands']['use_items']['uses']}
        <span class="chat_items_name">{$body['name']}</span>
        HTML;

    // Tipologia di azione. Es: F
    $azione_tipo = $azione['tipo'];

    // Assemblo la formattazione HTML per la tipologia di messaggio
    return gdrcd_chat_message_component(
        $azione_tipo,
        <<<HTML
            {$chat_time}
            <span class="chat_items_body">{$messaggio_stats}</span>
        HTML
    );
}

/**
 * Ritorna la formattazione html per un messaggio di tipo "Tiro Dado" ( D ) in chat.
 *
 * @param array{
 *      tipo: string,
 *      mittente: string,
 *      destinatario: string,
 *      url_img_chat: string,
 *      ora: string,
 *      imgs: string,
 *      testo: string,
 * } $azione
 * @return string
 */
function gdrcd_chat_dice_format($azione)
{
    $MESSAGE = $GLOBALS['MESSAGE'];

    // formattazione orario del messaggio
    $chat_time = gdrcd_chat_time_component($azione);

    // formattazione mittente del messaggio in chat
    $chat_mittente = gdrcd_chat_name_component($azione);

    // Tipologia di azione. Es: C
    $azione_tipo = $azione['tipo'];

    // decodifica il messaggio
    $body = json_decode($azione['testo'], true);

    // Formatta le informazioni da scrivere in chat sul lancio
    $info = <<<HTML
        {$chat_mittente}

        {$MESSAGE['chat']['commands']['die']['cast']}
        <span class="chat_dice_number">{$body['number']}</span>
        <span class="chat_dice_faces">d{$body['faces']}</span>.

        {$MESSAGE['chat']['commands']['die']['sum']}
        <span class="chat_dice_sum">{$body['sum']}</span>.
        HTML;

    // Se i dadi hanno un modificatore lo aggiunge alle info
    if ($body['modifier'] !== null) {
        $info .= <<<HTML
                {$MESSAGE['chat']['commands']['die']['modifier']}
                <span class="chat_dice_modifier">{$body['modifier']}</span>.
            HTML;
    }

    // Se i dadi hanno sono stati lanciati con un riferimento per i successi, li aggiunge alle info
    if ($body['successes'] !== null) {
        $info .= <<<HTML
                {$MESSAGE['chat']['commands']['die']['successes']}
                <span class="chat_dice_successes">{$body['successes']}</span>.
            HTML;
    }

    // Formatta il risultato di ogni dado lanciato
    $lanci = '';

    foreach ($body['rolls'] as $roll) {
        // Definisce 4 diverse classi css per ciascun dado:
        // - chat_dice_roll_min se il dado totalizza 1
        // - chat_dice_roll_max se il dado fa il valore massimo
        // - chat_dice_roll_success se il dado ha ottenuto un successo
        // - chat_dice_roll per tutti gli altri casi
        //
        $class = match(true) {
            $roll === 1 => 'chat_dice_roll_min',
            $roll === $body['faces'] => 'chat_dice_roll_max',
            $body['threshold'] && $roll >= $body['threshold'] => 'chat_dice_roll_success',
            default => 'chat_dice_roll'
        };

        $lanci .= <<<HTML
            <span class="{$class}">{$roll}</span>
            HTML;
    }

    // Assembla la formattazione HTML per la tipologia di messaggio
    return gdrcd_chat_message_component(
        $azione_tipo,
        <<<HTML
            {$chat_time}
            <span class="chat_dice_body">
                <span class="chat_dice_info">{$info}</span>
                <span class="chat_dice_rolls">{$lanci}</span>
            </span>
        HTML
    );
}

/**
 * Ritorna la formattazione html per un messaggio di tipo "Tiro su Abilità" ( F ) in chat.
 *
 * @param array{
 *      tipo: string,
 *      mittente: string,
 *      destinatario: string,
 *      url_img_chat: string,
 *      ora: string,
 *      imgs: string,
 *      testo: string,
 * } $azione
 * @return string
 */
function gdrcd_chat_skill_format($azione)
{
    // formattazione orario del messaggio
    $chat_time = gdrcd_chat_time_component($azione);

    // formattazione mittente del messaggio in chat
    $chat_mittente = gdrcd_chat_name_component($azione);

    // decodifica il messaggio
    $body = json_decode($azione['testo'], true);

    // formattazione dettaglio dei valori coinvolti nel tiro abilità
    $chat_skill = gdrcd_chat_stats_component($body['skill']['name'], $body);

    // Tipologia di azione. Es: F
    $azione_tipo = $azione['tipo'];

    // Assemblo la formattazione HTML per la tipologia di messaggio
    return gdrcd_chat_message_component(
        $azione_tipo,
        <<<HTML
            {$chat_time}
            <span class="chat_stats_body">
                {$chat_mittente}
                {$chat_skill}
            </span>
        HTML
    );
}

/**
 * Ritorna la formattazione html per un messaggio di tipo "Tiro Caratteristica" ( C ) in chat.
 *
 * @param array{
 *      tipo: string,
 *      mittente: string,
 *      destinatario: string,
 *      url_img_chat: string,
 *      ora: string,
 *      imgs: string,
 *      testo: string,
 * } $azione
 * @return string
 */
function gdrcd_chat_stats_format($azione)
{
    // formattazione orario del messaggio
    $chat_time = gdrcd_chat_time_component($azione);

    // formattazione mittente del messaggio in chat
    $chat_mittente = gdrcd_chat_name_component($azione);

    // decodifica il messaggio
    $body = json_decode($azione['testo'], true);

    // formattazione dettaglio dei valori coinvolti nel tiro caratteristica
    $chat_stats = gdrcd_chat_stats_component($body['stats']['name'], $body);

    // Tipologia di azione. Es: C
    $azione_tipo = $azione['tipo'];

    // Assemblo la formattazione HTML per la tipologia di messaggio
    return gdrcd_chat_message_component(
        $azione_tipo,
        <<<HTML
            {$chat_time}
            <span class="chat_stats_body">
                {$chat_mittente}
                {$chat_stats}
            </span>
        HTML
    );
}

/**
 * Ritorna la formattazione html per un messaggio di tipo "Sussurro" ( S ) in chat.
 * Un utente può leggere il sussurro:
 *  - se è il mittente del messaggio
 *  - se è il destinatario del messaggio
 *  - se si è MODERATOR o superiore e $PARAMETERS['mode']['spyprivaterooms'] è abilitato
 *
 * @param array{
 *      tipo: string,
 *      mittente: string,
 *      destinatario: string,
 *      url_img_chat: string,
 *      ora: string,
 *      imgs: string,
 *      testo: string,
 * } $azione
 * @return null|string
 */
function gdrcd_chat_whisper_format($azione)
{
    $MESSAGE = $GLOBALS['MESSAGE'];
    $PARAMETERS = $GLOBALS['PARAMETERS'];

    if ($_SESSION['login'] == $azione['destinatario']) {

        // l'utente connesso riceve il sussurro
        $mittente_o_destinatario = $azione['mittente'] .' '. $MESSAGE['chat']['whisper']['by'];

    } elseif ($_SESSION['login'] == $azione['mittente']) {

        // l'utente connesso ha inviato il sussurro
        $mittente_o_destinatario = $MESSAGE['chat']['whisper']['to'] .' '. $azione['destinatario'];

    } elseif ($_SESSION['permessi'] >= MODERATOR && $PARAMETERS['mode']['spyprivaterooms'] == 'ON') {

        // l'utente connesso può leggere i sussurri di altri giocatori
        // se è almeno MODERATOR e spyprivaterooms è abilitato
        $mittente_o_destinatario = $azione['mittente']
            .' '. $MESSAGE['chat']['whisper']['from_to']
            .' '. $azione['destinatario'];

    } else {

        // l'utente connesso non è abilitato a leggere il sussurro
        return null;

    }

    // formattazione mittente del messaggio in chat
    $chat_mittente = gdrcd_chat_name_component($mittente_o_destinatario);

    $chat_body = gdrcd_chat_body_component($azione);

    // Tipologia di azione. Es: S
    $azione_tipo = $azione['tipo'];

    // Assemblo la formattazione HTML per la tipologia di messaggio
    return gdrcd_chat_message_component(
        $azione_tipo,
        <<<HTML
            {$chat_mittente}:
            {$chat_body}
        HTML
    );
}

/**
 * Ritorna la formattazione html per un messaggio di tipo "Azione" ( A ) in chat.
 *
 * @param array{
 *      tipo: string,
 *      mittente: string,
 *      destinatario: string,
 *      url_img_chat: string,
 *      ora: string,
 *      imgs: string,
 *      testo: string,
 * } $azione
 * @return string
 */
function gdrcd_chat_action_format($azione)
{
    $PARAMETERS = $GLOBALS['PARAMETERS'];

    // formattazione avatar di chat
    $chat_avatar = $PARAMETERS['mode']['chat_avatar'] == 'ON' && !empty($azione['url_img_chat'])
        ? gdrcd_chat_avatar_component($azione)
        : '';

    // formattazione orario del messaggio
    $chat_time = gdrcd_chat_time_component($azione);

    // formattazione icone
    $chat_icons = $PARAMETERS['mode']['chaticons'] == 'ON'
        ? gdrcd_chat_icons_component($azione)
        : '';

    // formattazione nome mittente
    $chat_sender = gdrcd_chat_sender_component($azione);

    // formattazione tag
    $chat_tag = !empty($azione['destinatario'])
        ? gdrcd_chat_tag_component($azione)
        : '';

    // formattazione mittente del messaggio in chat
    $chat_mittente_e_tag = gdrcd_chat_name_component($chat_sender . $chat_tag, false);

    // formattazione corpo messaggio
    $chat_body = gdrcd_chat_body_with_colors_component($azione);

    // Tipologia di azione. Es: A
    $azione_tipo = $azione['tipo'];

    // Assemblo la formattazione HTML per la tipologia di messaggio
    return gdrcd_chat_message_component(
        $azione_tipo,
        <<<HTML
            {$chat_avatar}
            {$chat_time}
            {$chat_icons}
            {$chat_mittente_e_tag}
            {$chat_body}
        HTML
    );
}

/**
 * Ritorna la formattazione html per un messaggio di tipo "Parlato" ( P ) in chat.
 *
 * @param array{
 *      tipo: string,
 *      mittente: string,
 *      destinatario: string,
 *      url_img_chat: string,
 *      ora: string,
 *      imgs: string,
 *      testo: string,
 * } $azione
 * @return string
 */
function gdrcd_chat_message_format($azione)
{
    $PARAMETERS = $GLOBALS['PARAMETERS'];

    // formattazione avatar di chat
    $chat_avatar = $PARAMETERS['mode']['chat_avatar'] == 'ON' && !empty($azione['url_img_chat'])
        ? gdrcd_chat_avatar_component($azione)
        : '';

    // formattazione orario del messaggio
    $chat_time = gdrcd_chat_time_component($azione);

    // formattazione icone
    $chat_icons = $PARAMETERS['mode']['chaticons'] == 'ON'
        ? gdrcd_chat_icons_component($azione)
        : '';

    // formattazione nome mittente
    $chat_sender = gdrcd_chat_sender_component($azione);

    // formattazione tag
    $chat_tag = !empty($azione['destinatario'])
        ? gdrcd_chat_tag_component($azione)
        : '';

    // formattazione mittente del messaggio in chat
    $chat_mittente_e_tag = gdrcd_chat_name_component($chat_sender . $chat_tag . ':', false);

    // formattazione corpo messaggio
    $chat_body = gdrcd_chat_body_with_colors_component($azione);

    // Tipologia di azione. Es: P
    $azione_tipo = $azione['tipo'];

    // Assemblo la formattazione HTML per la tipologia di messaggio
    return gdrcd_chat_message_component(
        $azione_tipo,
        <<<HTML
            {$chat_avatar}
            {$chat_time}
            {$chat_icons}
            {$chat_mittente_e_tag}
            {$chat_body}
        HTML
    );
}

/**
 * Ritorna la formattazione HTML per un messaggio di invito (X) in una chat privata.
 * Mostra l'orario e il nome dell'utente invitato.
 *
 * @param array{
 *      tipo: string,
 *      mittente: string,
 *      destinatario: string,
 *      url_img_chat: string,
 *      ora: string,
 *      imgs: string,
 *      testo: string,
 * } $azione
 * @return string
 */
function gdrcd_chat_private_invite_format($azione)
{
    $MESSAGE = $GLOBALS['MESSAGE'];

    // formattazione orario del messaggio
    $chat_time = gdrcd_chat_time_component($azione);

    // formattazione mittente del messaggio in chat
    $chat_mittente = gdrcd_chat_name_component($azione);

    // decodifica il messaggio
    $body = json_decode($azione['testo'], true);

    // nome utente cacciato dalla chat privata
    $invited = $body['invited'];

    // Tipologia di azione. Es: Z
    $azione_tipo = $azione['tipo'];

    // Assemblo la formattazione HTML per la tipologia di messaggio
    return gdrcd_chat_message_component(
        $azione_tipo,
        <<<HTML
            {$chat_time}
            {$chat_mittente}
            {$MESSAGE['chat']['warning']['invited']}:
            {$invited}
        HTML
    );
}

/**
 * Ritorna la formattazione HTML per un messaggio di espulsione (Y) in una chat privata.
 * Mostra l'orario e il nome dell'utente espulso.
 *
 * @param array{
 *      tipo: string,
 *      mittente: string,
 *      destinatario: string,
 *      url_img_chat: string,
 *      ora: string,
 *      imgs: string,
 *      testo: string,
 * } $azione
 * @return string
 */
function gdrcd_chat_private_kick_format($azione)
{
    $MESSAGE = $GLOBALS['MESSAGE'];

    // formattazione orario del messaggio
    $chat_time = gdrcd_chat_time_component($azione);

    // formattazione mittente del messaggio in chat
    $chat_mittente = gdrcd_chat_name_component($azione);

    // decodifica il messaggio
    $body = json_decode($azione['testo'], true);

    // nome utente cacciato dalla chat privata
    $kicked = $body['kicked'];

    // Tipologia di azione. Es: Z
    $azione_tipo = $azione['tipo'];

    // Assemblo la formattazione HTML per la tipologia di messaggio
    return gdrcd_chat_message_component(
        $azione_tipo,
        <<<HTML
            {$chat_time}
            {$chat_mittente}
            {$MESSAGE['chat']['warning']['expelled']}:
            {$kicked}
        HTML
    );
}

/**
 * Ritorna la formattazione HTML per un messaggio che mostra l'elenco degli invitati (Z) in una chat privata.
 * Mostra l'orario e la lista degli utenti invitati, oppure un messaggio se non ci sono invitati.
 *
 * @param array{
 *      tipo: string,
 *      mittente: string,
 *      destinatario: string,
 *      url_img_chat: string,
 *      ora: string,
 *      imgs: string,
 *      testo: string,
 * } $azione
 * @return string
 */
function gdrcd_chat_private_list_format($azione)
{
    $MESSAGE = $GLOBALS['MESSAGE'];

    // formattazione orario del messaggio
    $chat_time = gdrcd_chat_time_component($azione);

    // decodifica il messaggio
    $body = json_decode($azione['testo'], true);

    // Formatta l'elenco degli invitati in chat
    $chat_invitati = $MESSAGE['interface']['administration']['locations']['no_invited'];

    if (!empty($body['invited_list'])) {
        // Ordina alfabeticamente gli invitati
        sort($body['invited_list']);

        // Unisce gli invitati in una stringa separata da virgola
        $chat_invitati = implode(', ', $body['invited_list']);
    }

    // Tipologia di azione. Es: Z
    $azione_tipo = $azione['tipo'];

    // Assemblo la formattazione HTML per la tipologia di messaggio
    return gdrcd_chat_message_component(
        $azione_tipo,
        <<<HTML
            {$chat_time}
            {$MESSAGE['chat']['warning']['invited_list']}:
            {$chat_invitati}
        HTML
    );
}

/**
 * Restituisce la formattazione HTML di base per un messaggio di chat.
 * Avvolge il contenuto formattato ($azione_html) in un div con classe specifica per il tipo di messaggio.
 *
 * @param string $tipo Tipologia del messaggio (es: P, A, S, C, F, D, O, M, N, I, X, Y, Z)
 * @param string $azione_html HTML già formattato relativo al contenuto del messaggio
 * @return string HTML completo del messaggio pronto per la visualizzazione in chat
 */
function gdrcd_chat_message_component($tipo, $azione_html)
{
    return <<<HTML
        <div class="chat_row_{$tipo}">
            {$azione_html}
            <br style="clear:both;" />
        </div>
        HTML;
}

/**
 * Ritorna la formattazione html per l'avatar in chat del personaggio.
 *  - Se $PARAMETERS['settings']['chat_avatar']['link']['mode'] è abilitato, l'avatar sarà cliccabile
 *  - Se $PARAMETERS['settings']['chat_avatar']['link']['popup'] è abilitato, il link sarà aperto con modalWindow()
 *
 * @param array{
 *      tipo: string,
 *      mittente: string,
 *      destinatario: string,
 *      url_img_chat: string,
 *      ora: string,
 *      imgs: string,
 *      testo: string,
 * } $azione
 * @return string
 */
function gdrcd_chat_avatar_component($azione)
{
    $PARAMETERS = $GLOBALS['PARAMETERS'];

    $larghezza = $PARAMETERS['settings']['chat_avatar']['width'];
    $altezza = $PARAMETERS['settings']['chat_avatar']['height'];
    $avatar_url = $azione['url_img_chat'];

    $chat_avatar = <<<HTML
        <img
            src="{$avatar_url}"
            class="chat_avatar"
            style="width:{$larghezza}px; height:{$altezza}px;"
        />
        HTML;

    if(
        isset($PARAMETERS['settings']['chat_avatar']['link']['mode'])
        && $PARAMETERS['settings']['chat_avatar']['link']['mode'] == 'ON'
    ) {
        $isChatAvatarPoupLink = isset($PARAMETERS['settings']['chat_avatar']['link']['popup'])
            && $PARAMETERS['settings']['chat_avatar']['link']['popup'] == 'ON';

        $chat_avatar_url = $isChatAvatarPoupLink
            ? "javascript:modalWindow('scheda', 'Scheda di ". addslashes($azione['mittente']) ."', 'popup.php?page=scheda&pg=". urlencode($azione['mittente']) ."');"
            : "main.php?page=scheda&pg=". urlencode($azione['mittente']);

        $chat_avatar = <<<HTML
            <a href="{$chat_avatar_url}">{$chat_avatar}</a>
            HTML;
    }

    return $chat_avatar;
}

/**
 * Ritorna la formattazione html per l'orario del messaggio in chat.
 * Se $azione è un array, formatta il campo 'ora'; se è una stringa, la usa direttamente.
 *
 * @param array{
 *      tipo: string,
 *      mittente: string,
 *      destinatario: string,
 *      url_img_chat: string,
 *      ora: string,
 *      imgs: string,
 *      testo: string,
 * } $azione
 * @return string
 */
function gdrcd_chat_time_component($azione)
{
    $time = gdrcd_format_time(is_array($azione) ? $azione['ora'] : $azione);

    return <<<HTML
        <span class="chat_time">{$time}</span>
        HTML;
}

/**
 * Ritorna la formattazione html per le icone in chat del personaggio.
 * Le icone attualmente formattate da questo metodo sono quelle relative
 * a razza e genere del personaggio.
 *
 * @param array{
 *      tipo: string,
 *      mittente: string,
 *      destinatario: string,
 *      url_img_chat: string,
 *      ora: string,
 *      imgs: string,
 *      testo: string,
 * } $azione
 * @return string
 */
function gdrcd_chat_icons_component($azione)
{
    $PARAMETERS = $GLOBALS['PARAMETERS'];

    $icone = explode(';', $azione['imgs']);

    $icona_genere_url = sprintf(
        'imgs/icons/testamini%s.png',
        urlencode($icone[0])
    );

    $icona_razza_url = sprintf(
        'themes/%s/imgs/races/%s',
        urlencode($PARAMETERS['themes']['current_theme']),
        urlencode($icone[1]?? '')
    );

    return <<<HTML
        <span class="chat_icons">
            <img class="presenti_ico" src="{$icona_razza_url}">
            <img class="presenti_ico" src="{$icona_genere_url}">
        </span>
        HTML;
}

/**
 * Ritorna la formattazione html per il nome del mittente di un messaggio in chat.
 * Se $azione è un array, formatta il campo 'mittente'; se è una stringa, la usa direttamente.
 *
 * @param string|array{
 *      tipo: string,
 *      mittente: string,
 *      destinatario: string,
 *      url_img_chat: string,
 *      ora: string,
 *      imgs: string,
 *      testo: string,
 * } $azione
 * @param bool $use_filter Default true. Se posto su false esclude l'uso interno di gdrcd_filter 'out'
 * @return string HTML contenente il nome formattato del mittente
 */
function gdrcd_chat_name_component($azione, $use_filter = true)
{
    $message = is_array($azione)
        ? $azione['mittente'] // se $azione è un array formatta 'mittente'
        : $azione;            // se azione è una stringa la usa com'è

    if ($use_filter) {
        $message = gdrcd_filter('out', $message);
    }

    return <<<HTML
        <span class="chat_name">{$message}</span>
        HTML;
}

/**
 * Ritorna la formattazione html per il nome del mittente del messaggio in chat.
 * Se $azione è un array, formatta il campo 'mittente'; se è una stringa, la usa direttamente.
 *
 * @param array{
 *      tipo: string,
 *      mittente: string,
 *      destinatario: string,
 *      url_img_chat: string,
 *      ora: string,
 *      imgs: string,
 *      testo: string,
 * } $azione
 * @param bool $use_filter Default true. Se posto su false esclude l'uso interno di gdrcd_filter 'out'
 * @return string
 */
function gdrcd_chat_sender_component($azione, $use_filter = true)
{
    $mittente = is_array($azione)
        ? $azione['mittente'] // se $azione è un array formatta 'mittente'
        : $azione;            // se azione è una stringa la usa com'è

    if ($use_filter) {
        $mittente = gdrcd_filter('out', $mittente);
    }

    return <<<HTML
        <a
            class="chat_sender"
            href="#"
            onclick="
                javascript:document.getElementById('tag').value='{$mittente}';
                document.getElementById('tipo')[2].selected = '1';
                document.getElementById('message').focus();"
        >
            {$mittente}
        </a>
        HTML;
}

/**
 * Ritorna la formattazione html per il tag del messaggio in chat.
 * Se $azione è un array, formatta il campo 'destinatario'; se è una stringa, la usa direttamente.
 *
 * @param array{
 *      tipo: string,
 *      mittente: string,
 *      destinatario: string,
 *      url_img_chat: string,
 *      ora: string,
 *      imgs: string,
 *      testo: string,
 * } $azione
 * @param bool $use_filter Default true. Se posto su false esclude l'uso interno di gdrcd_filter 'out'
 * @return string
 */
function gdrcd_chat_tag_component($azione, $use_filter = true)
{
    $tag = is_array($azione)
        ? $azione['destinatario'] // se $azione è un array formatta 'destinatario'
        : $azione;                // se azione è una stringa la usa com'è

    if ($use_filter) {
        $tag = gdrcd_filter('out', $tag);
    }

    return <<<HTML
        <span class="chat_tag">[{$tag}]</span>
        HTML;
}

/**
 * Ritorna la formattazione html per il corpo dei messaggi in chat.
 * Se $azione è un array, formatta il campo 'testo'; se è una stringa, la usa direttamente.
 *
 * @param string|array{
 *      tipo: string,
 *      mittente: string,
 *      destinatario: string,
 *      url_img_chat: string,
 *      ora: string,
 *      imgs: string,
 *      testo: string,
 * } $azione
 * @param bool $use_filter Default true. Se posto su false esclude l'uso interno di gdrcd_filter 'out'
 * @return string
 */
function gdrcd_chat_body_component($azione, $use_filter = true)
{
    $message = is_array($azione)
        ? $azione['testo'] // se $azione è un array formatta 'testo'
        : $azione;         // se azione è una stringa la usa com'è

    if ($use_filter) {
        $message = gdrcd_filter('out', $message);
    }

    return <<<HTML
        <span class="chat_msg">{$message}</span>
        HTML;
}

/**
 * Ritorna la formattazione html per il corpo del messaggio in chat
 * con supporto alla colorazione interna alle parentesi.
 * Se $azione è un array, formatta il campo 'testo'; se è una stringa, la usa direttamente.
 *
 * @param array{
 *      tipo: string,
 *      mittente: string,
 *      destinatario: string,
 *      url_img_chat: string,
 *      ora: string,
 *      imgs: string,
 *      testo: string,
 * } $azione
 * @param null|string $utente Nome utente da evidenziare nel messaggio, se non indicato userà quello in sessione. Si può disattivare indicando esplicitamente null come valore.
 * @return string
 */
function gdrcd_chat_body_with_colors_component($azione, $utente = '')
{
    $message = is_array($azione)
        ? $azione['testo'] // se $azione è un array formatta 'testo'
        : $azione;         // se azione è una stringa la usa com'è

    $message = gdrcd_chat_add_colors(gdrcd_filter('out', $message));

    if ($utente === '') {
        $utente = $_SESSION['login'];
    }

    if ($utente !== null) {
        $message = gdrcd_chat_highlight_user($utente, $message);
    }

    return gdrcd_chat_body_component($message, false);
}

/**
 * Ritorna la formattazione HTML dei dettagli di un tiro su caratteristica o abilità in chat.
 * Mostra i valori coinvolti nel tiro, inclusi nome, bonus, oggetti, razza, dado e somma finale.
 *
 * @param string $stats_or_skill_name Nome della caratteristica o abilità principale coinvolta nel tiro
 * @param array{
 *     stats: array{name: string, value: int},
 *     skill?: array{name: string, value: int},
 *     race: array{name: string, value: int},
 *     items: array{value: int},
 *     dice?: array{name: string, value: int},
 *     sum: int
 * } $body Dati decodificati dal messaggio, contenenti i valori di stats, skill, race, items, dice e sum
 * @return string HTML formattato con i dettagli del tiro
 */
function gdrcd_chat_stats_component($stats_or_skill_name, $body)
{
    $MESSAGE = $GLOBALS['MESSAGE'];

    // formatta facoltativamente il messaggio sul rank abilità se presente
    $messaggio_skill = '';

    if (isset($body['skill'])) {
        $messaggio_skill = <<<HTML
            {$body['skill']['name']}
            {$MESSAGE['chat']['commands']['use_skills']['rank']}
            <span class="chat_skill_bonus_value">{$body['skill']['value']}</span>,
            HTML;
    }

    // formatta facoltativamente il messaggio sull'esito del lancio del dado
    $messaggio_dadi = '';

    if ($body['dice']) {
        $messaggio_dadi = <<<HTML
            {$MESSAGE['chat']['commands']['use_skills']['die']}
            <span class="chat_dice_faces">{$body['dice']['name']}</span>
            <span class="chat_skill_bonus_value">{$body['dice']['value']}</span>
            HTML;
    }

    return <<<HTML
        {$MESSAGE['chat']['commands']['use_skills']['uses']}
        {$stats_or_skill_name}:

        {$messaggio_skill}

        {$body['stats']['name']}
        <span class="chat_skill_bonus_value">{$body['stats']['value']}</span>,

        {$body['race']['name']}
        <span class="chat_skill_bonus_value">{$body['race']['value']}</span>,

        {$MESSAGE['chat']['commands']['use_skills']['items']}
        <span class="chat_skill_bonus_value">{$body['items']['value']}</span>,

        {$messaggio_dadi}

        {$MESSAGE['chat']['commands']['use_skills']['sum']}
        <span class="chat_skill_total_value">{$body['sum']}</span>.
        HTML;
}

/**
 * Sostituisce eventuali parentesi angolari in coppia in una stringa con parentesi quadre
 * @param string $str : la stringa da controllare
 * @return string $str con la coppie di parentesi angolari sostituite con parentesi quadre
 */
function gdrcd_chat_replace_angs($str)
{
    $search = [
        '#\&lt;(.+?)\&gt;#is',
        '#\<(.+?)>#is',
    ];

    $replace = [
        '[$1]',
        '[$1]',
    ];

    return preg_replace($search, $replace, $str);
}

/**
 * Colora in HTML le parti di testo comprese tra parentesi angolari o parentesi quadre
 *
 * @param string $str : la stringa da controllare
 * @return string|string[]|null $str con la parti colorate
 */
function gdrcd_chat_add_colors($str)
{
    if (!$str) {
        return $str;
    }

    $search = [
        '#\&lt;(.+?)\&gt;#is',
        '#\[(.+?)\]#is',
    ];

    $replace = [
        '<span class="color2">&lt;$1&gt;</span>',
        '<span class="color2">&lt;$1&gt;</span>',
    ];

    return preg_replace($search, $replace, $str);
}

/**
 * Sottolinea in HTML una stringa presente in un testo. Usata per sottolineare il proprio nome in chat
 * @param string $user : la stringa da sottolineare, in genere un nome utente
 * @param string $str : la stringa in cui cercare e sottolineare $user
 * @param bool $master : determino se ad inviare l'azione è un master o meno
 * @return string|string[]|null $str con tutte le occorrenze di $user sottolineate
 */
function gdrcd_chat_highlight_user($user, $str, $master = false)
{
    if (!$str) {
        return $str;
    }

    $search = "|\\b" . preg_quote($user, "|") . "\\b|si";

    if (!$master) {
        $replace = '<span class="chat_me">' . gdrcd_filter('out', $user) . '</span>';
    } else {
        $replace = '<span class="chat_me_master">' . gdrcd_filter('out', $user) . '</span>';
    }

    return preg_replace($search, $replace, $str);
}

/**
 * Salva in sessione l'id riferito all'ultimo messaggio letto dalla chat.
 *
 * @see gdrcd_chat_get_lastmessage_id
 * @param int $id
 * @return void
 */
function gdrcd_chat_set_lastmessage_id($id)
{
    $_SESSION['last_message'] = $id;
}

/**
 * Recupera dalla sessione l'id riferito all'ultimo messaggio letto dalla chat.
 * Alla primissima esecuzione ritornerà il valore zero.
 *
 * @see gdrcd_chat_set_lastmessage_id
 * @return int
 */
function gdrcd_chat_get_lastmessage_id()
{
    return empty($_SESSION['last_message'])? 0 : $_SESSION['last_message'];
}
