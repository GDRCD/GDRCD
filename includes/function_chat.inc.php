<?php
/**
 * Questo file contiene tutte le funzioni specifiche
 * per il funzionamento della chat di GDRCD
 */

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
function gdrcd_chat_read_message($azione)
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
 * Gestisce il salvataggio nel database di un messaggio di chat in base alla tipologia specificata o dedotta.
 * Se il tipo non è specificato, viene determinato automaticamente dal primo carattere del messaggio.
 * Supporta diversi tipi di messaggi: parlato, azione, sussurro, dadi, master, PNG e immagine.
 *
 * @param string $message il messaggio da salvare
 * @param string $tag_o_destinatario Facoltativo. Il tag di locazione o il destinatario appropriato per la tipologia di messaggio
 * @param string|null $type Facoltativo. La tipologia del messaggio. Se null, viene dedotta dal primo carattere del messaggio
 * @return array{code: int, message: string} $status Array associativo proveniente da gdrcd_chat_status()
 */
function gdrcd_chat_write_message(
    $message,
    $tag_o_destinatario = '',
    $type = null
) {
    if (empty($type)) {
        // Se "empty", la tipologia viene dedotta dal primo carattere nel messaggio.
        $type = gdrcd_chat_get_type_from_message($message);
    }

    switch ($type) {
        case GDRCD_CHAT_MESSAGE_TYPE:
            return gdrcd_chat_message_save($tag_o_destinatario, $message);

        case GDRCD_CHAT_ACTION_TYPE:
            return gdrcd_chat_action_save($tag_o_destinatario, $message);

        case GDRCD_CHAT_WHISPER_TYPE:
            return gdrcd_chat_whisper_save($tag_o_destinatario, $message);

        case GDRCD_CHAT_STATS_TYPE:
            return gdrcd_chat_stats_save($message);

        case GDRCD_CHAT_SKILL_TYPE:
            return gdrcd_chat_skill_save($message);

        case GDRCD_CHAT_DICE_TYPE:
            return gdrcd_chat_dice_save($message);

        case GDRCD_CHAT_ITEM_TYPE:
            return gdrcd_chat_item_save($message);

        case GDRCD_CHAT_MASTER_TYPE:
            return gdrcd_chat_master_save($message);

        case GDRCD_CHAT_PNG_TYPE:
            return gdrcd_chat_png_save($tag_o_destinatario, $message);

        case GDRCD_CHAT_IMAGE_TYPE:
            return gdrcd_chat_image_save($message);

        case GDRCD_CHAT_PRIVATE_INVITE_TYPE:
            return gdrcd_chat_private_invite_save($tag_o_destinatario, $message);

        case GDRCD_CHAT_PRIVATE_KICK_TYPE:
            return gdrcd_chat_private_kick_save($tag_o_destinatario, $message);

        case GDRCD_CHAT_PRIVATE_LIST_TYPE:
            return gdrcd_chat_private_list_save($message);

        default:
            $MESSAGE = $GLOBALS['MESSAGE'];
            return gdrcd_chat_status_invalid($MESSAGE['chat']['error']['invalid_message_type'] .': '. $type);
    }
}

/**
 * Ritorna il codice interno della tipologia di azione determinato
 * in base al primo carattere presente in $message.
 * Se il primo carattere non permette di determinare la tipologia di azione,
 * viene ritornato il valore di GDRCD_CHAT_DEFAULT_TYPE
 *
 * @param string $message
 * @return string
 */
function gdrcd_chat_get_type_from_message($message)
{
    $first_char = substr($message, 0, 1);

    switch ($first_char) {

        case GDRCD_CHAT_MESSAGE_SYMBOL:
            return GDRCD_CHAT_MESSAGE_TYPE;

        case GDRCD_CHAT_ACTION_SYMBOL:
            return GDRCD_CHAT_ACTION_TYPE;

        case GDRCD_CHAT_WHISPER_SYMBOL:
            return GDRCD_CHAT_WHISPER_TYPE;

        case GDRCD_CHAT_STATS_SYMBOL:
            return GDRCD_CHAT_STATS_TYPE;

        case GDRCD_CHAT_SKILL_SYMBOL:
            return GDRCD_CHAT_SKILL_TYPE;

        case GDRCD_CHAT_DICE_SYMBOL:
            return GDRCD_CHAT_DICE_TYPE;

        case GDRCD_CHAT_ITEM_SYMBOL:
            return GDRCD_CHAT_ITEM_TYPE;

        case GDRCD_CHAT_MASTER_SYMBOL:
            return GDRCD_CHAT_MASTER_TYPE;

        case GDRCD_CHAT_PNG_SYMBOL:
            return GDRCD_CHAT_PNG_TYPE;

        case GDRCD_CHAT_IMAGE_SYMBOL:
            return GDRCD_CHAT_IMAGE_TYPE;

        case GDRCD_CHAT_PRIVATE_INVITE_SYMBOL:
            return GDRCD_CHAT_PRIVATE_INVITE_TYPE;

        case GDRCD_CHAT_PRIVATE_KICK_SYMBOL:
            return GDRCD_CHAT_PRIVATE_KICK_TYPE;

        case GDRCD_CHAT_PRIVATE_LIST_SYMBOL:
            return GDRCD_CHAT_PRIVATE_LIST_TYPE;

        default:
            return GDRCD_CHAT_DEFAULT_TYPE;

    }
}

/**
 * Gestisce l'utilizzo del sistema di abilità/caratteristiche/dadi/oggetti tramite chat.
 *
 * Questa funzione consente di inviare automaticamente messaggi in chat per l'utilizzo
 * di diversi elementi del sistema di gioco. Accetta uno solo dei parametri alla volta
 * e genera il messaggio appropriato in chat per la tipologia scelta.
 *
 * Utilizza internamente gdrcd_chat_write_message() per l'effettivo salvataggio del messaggio.
 *
 * @param int|null $id_ab Facoltativo. ID dell'abilità da utilizzare. Se fornito, genera un messaggio di tipo "Tiro su Abilità"
 * @param int|null $id_stats Facoltativo. ID della caratteristica da utilizzare. Se fornito, genera un messaggio di tipo "Tiro Caratteristica"
 * @param int|null $dice Facoltativo. Numero di facce del dado da lanciare. Se fornito, genera un messaggio di tipo "Tiro Dado"
 * @param int|null $id_item Facoltativo. ID dell'oggetto da utilizzare. Se fornito, genera un messaggio di tipo "Utilizzo Oggetto"
 * @return array{code: int, message: string} $status Array associativo proveniente da gdrcd_chat_status()
 */
function gdrcd_chat_use_skillsystem(
    $id_ab = null,
    $id_stats = null,
    $dice = null,
    $id_item = null
) {
    if ($id_ab != null && $id_ab !== '') {
        return gdrcd_chat_write_message(GDRCD_CHAT_SKILL_SYMBOL . $id_ab);
    }

    if ($id_stats != null && $id_stats !== '') {
        return gdrcd_chat_write_message(GDRCD_CHAT_STATS_SYMBOL . $id_stats);
    }

    if ($dice != null && $dice !== '') {
        return gdrcd_chat_write_message(GDRCD_CHAT_DICE_SYMBOL . "d{$dice}");
    }

    if ($id_item != null && $id_item !== '') {
        return gdrcd_chat_write_message(GDRCD_CHAT_ITEM_SYMBOL . $id_item);
    }

    $MESSAGE = $GLOBALS['MESSAGE'];
    return gdrcd_chat_status_invalid($MESSAGE['chat']['error']['invalid_skillsystem_type']);
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
    return <<<HTML
        <div class="chat_row_{$azione_tipo}">
            <img class="chat_img" src="{$image_url}" />
        </div>
        HTML;
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
    $chat_time = gdrcd_chat_time_format($azione);

    // formattazione corpo messaggio
    $chat_body = gdrcd_chat_body_with_colors_format($azione, null);

    $png_name = $azione['destinatario'];

    // Tipologia di azione. Es: N
    $azione_tipo = $azione['tipo'];

    // Assemblo la formattazione HTML per la tipologia di messaggio
    return <<<HTML
        <div class="chat_row_{$azione_tipo}">
            {$chat_time}
            <span class="chat_name">{$png_name}</span>
            {$chat_body}
            <br style="clear:both;" />
        </div>
        HTML;
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
    $testo = gdrcd_chatme(
        $_SESSION['login'],
        gdrcd_bbcoder(gdrcd_filter('out', $azione['testo'])),
        true
    );

    // Tipologia di azione. Es: M
    $azione_tipo = $azione['tipo'];

    // Assemblo la formattazione HTML per la tipologia di messaggio
    return <<<HTML
        <div class="chat_row_{$azione_tipo}">
            <div class="ora_ms">{$azione_time} Master Screen</div>
            <span class="chat_master">{$testo}</span>
            <br style="clear:both;" />
        </div>
        HTML;
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
    // Di default la formattazione html per questa tipologia di azione
    // è identica a quella per il tiro su caratteristica. Qualora si voglia
    // personalizzare in modo differente l'html basterà sostituire il codice
    // interno a questa funzione con una copia di quello presente nella funzione
    // gdrcd_chat_stats_format() e modificare a piacere dove necessario.
    return gdrcd_chat_stats_format($azione);
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
    // FIXME: va rifattorizzata perché adesso questo tipo di azione contiene un json
    return gdrcd_chat_stats_format($azione);
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
    // Di default la formattazione html per questa tipologia di azione
    // è identica a quella per il tiro su caratteristica. Qualora si voglia
    // personalizzare in modo differente l'html basterà sostituire il codice
    // interno a questa funzione con una copia di quello presente nella funzione
    // gdrcd_chat_stats_format() e modificare a piacere dove necessario.
    return gdrcd_chat_stats_format($azione);
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
    $chat_time = gdrcd_chat_time_format($azione);

    // formattazione corpo messaggio
    $chat_body = gdrcd_chat_body_format($azione);

    // Tipologia di azione. Es: C
    $azione_tipo = $azione['tipo'];

    // Assemblo la formattazione HTML per la tipologia di messaggio
    return <<<HTML
        <div class="chat_row_{$azione_tipo}">
            {$chat_time}
            {$chat_body}
            <br style="clear:both;" />
        </div>
        HTML;
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
        $mittente_o_destinatario = gdrcd_filter('out', $azione['mittente']) .' '. $MESSAGE['chat']['whisper']['by'];

    } elseif ($_SESSION['login'] == $azione['mittente']) {

        // l'utente connesso ha inviato il sussurro
        $mittente_o_destinatario = $MESSAGE['chat']['whisper']['to'] .' '. gdrcd_filter('out', $azione['destinatario']);

    } elseif ($_SESSION['permessi'] >= MODERATOR && $PARAMETERS['mode']['spyprivaterooms'] == 'ON') {

        // l'utente connesso può leggere i sussurri di altri giocatori
        // se è almeno MODERATOR e spyprivaterooms è abilitato
        $mittente_o_destinatario = gdrcd_filter('out', $azione['mittente'])
            .' '. $MESSAGE['chat']['whisper']['from_to']
            .' '. gdrcd_filter('out', $azione['destinatario']);

    } else {

        // l'utente connesso non è abilitato a leggere il sussurro
        return null;

    }

    $chat_body = gdrcd_chat_body_format($azione);

    // Tipologia di azione. Es: S
    $azione_tipo = $azione['tipo'];

    // Assemblo la formattazione HTML per la tipologia di messaggio
    return <<<HTML
        <div class="chat_row_{$azione_tipo}">
            <span class="chat_name">{$mittente_o_destinatario}:</span>
            {$chat_body}
            <br style="clear:both;" />
        </div>
        HTML;
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
        ? gdrcd_chat_avatar_format($azione)
        : '';

    // formattazione orario del messaggio
    $chat_time = gdrcd_chat_time_format($azione);

    // formattazione icone
    $chat_icons = $PARAMETERS['mode']['chaticons'] == 'ON'
        ? gdrcd_chat_icons_format($azione)
        : '';

    // formattazione nome mittente
    $chat_sender = gdrcd_chat_sender_format($azione);

    // formattazione tag
    $chat_tag = !empty($azione['destinatario'])
        ? gdrcd_chat_tag_format($azione)
        : '';

    // formattazione corpo messaggio
    $chat_body = gdrcd_chat_body_with_colors_format($azione);

    // Tipologia di azione. Es: A
    $azione_tipo = $azione['tipo'];

    // Assemblo la formattazione HTML per la tipologia di messaggio
    return <<<HTML
        <div class="chat_row_{$azione_tipo}">
            {$chat_avatar}
            {$chat_time}
            {$chat_icons}

            <span class="chat_name">
                {$chat_sender}
                {$chat_tag}
            </span>

            {$chat_body}

            <br style="clear:both;" />
        </div>
        HTML;
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
        ? gdrcd_chat_avatar_format($azione)
        : '';

    // formattazione orario del messaggio
    $chat_time = gdrcd_chat_time_format($azione);

    // formattazione icone
    $chat_icons = $PARAMETERS['mode']['chaticons'] == 'ON'
        ? gdrcd_chat_icons_format($azione)
        : '';

    // formattazione nome mittente
    $chat_sender = gdrcd_chat_sender_format($azione);

    // formattazione tag
    $chat_tag = !empty($azione['destinatario'])
        ? gdrcd_chat_tag_format($azione)
        : '';

    // formattazione corpo messaggio
    $chat_body = gdrcd_chat_body_with_colors_format($azione);

    // Tipologia di azione. Es: P
    $azione_tipo = $azione['tipo'];

    // Assemblo la formattazione HTML per la tipologia di messaggio
    return <<<HTML
        <div class="chat_row_{$azione_tipo}">
            {$chat_avatar}
            {$chat_time}
            {$chat_icons}

            <span class="chat_name">
                {$chat_sender}
                {$chat_tag}
                :
            </span>

            {$chat_body}

            <br style="clear:both;" />
        </div>
        HTML;
}

function gdrcd_chat_private_invite_format($azione)
{
    // FIXME: va rifattorizzata perché adesso questo tipo di azione contiene un json
    return gdrcd_chat_stats_format($azione);
}

function gdrcd_chat_private_kick_format($azione)
{
    // FIXME: va rifattorizzata perché adesso questo tipo di azione contiene un json
    return gdrcd_chat_stats_format($azione);
}

function gdrcd_chat_private_list_format($azione)
{
    // FIXME: va rifattorizzata perché adesso questo tipo di azione contiene un json
    return gdrcd_chat_stats_format($azione);
}

/**
 * Ritorna la formattazione html per il corpo dei messaggi in chat.
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
function gdrcd_chat_body_format($azione)
{
    $message = gdrcd_filter('out', $azione['testo']);

    return <<<HTML
        <span class="chat_msg">{$message}</span>
        HTML;
}

/**
 * Ritorna la formattazione html per il corpo del messaggio in chat
 * con supporto alla colorazione interna alle parentesi.
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
function gdrcd_chat_body_with_colors_format($azione, $utente = '')
{
    // TODO: refactor gdrcd_chatcolor and gdrcd_chatme and move it to this file
    $message = gdrcd_chatcolor(gdrcd_filter('out', $azione['testo']));

    if ($utente === '') {
        $utente = $_SESSION['login'];
    }

    if ($utente !== null) {
        $message = gdrcd_chatme($utente, $message);
    }

    return <<<HTML
        <span class="chat_msg">{$message}</span>
        HTML;
}

/**
 * Ritorna la formattazione html per il tag del messaggio in chat.
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
function gdrcd_chat_tag_format($azione)
{
    $tag = gdrcd_filter('out', $azione['destinatario']);

    return <<<HTML
        <span class="chat_tag">[{$tag}]</span>
        HTML;
}

/**
 * Ritorna la formattazione html per il nome del mittente del messaggio in chat.
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
function gdrcd_chat_sender_format($azione)
{
    $mittente = gdrcd_filter('out', $azione['mittente']);

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
 * Ritorna la formattazione html per l'orario del messaggio in chat.
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
function gdrcd_chat_time_format($azione)
{
    $time = gdrcd_format_time($azione['ora']);

    return <<<HTML
        <span class="chat_time">{$time}</span>
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
function gdrcd_chat_avatar_format($azione)
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
function gdrcd_chat_icons_format($azione)
{
    $PARAMETERS = $GLOBALS['PARAMETERS'];

    $icone = explode(';', $azione['imgs']);

    $icona_genere_url = sprintf(
        'imgs/icons/testamini%s.png',
        urlencode($icone[0])
    );

    // TODO: create a core function gdrcd_current_theme() to fetch the current theme for the user
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
 * Inserisce nella tabella `chat` del database un messaggio di tipo "Immagine".
 *
 * @param string $testo il messaggio da salvare
 * @param string $tipo Facoltativo. La tipologia interna con cui salvare il messaggio nel database
 * @param string $symbol Facoltativo. il simbolo da rimuovere se presente come primo carattere
 * @return array{code: int, message: string} $status Array associativo proveniente da gdrcd_chat_status()
 */
function gdrcd_chat_image_save(
    $testo,
    $tipo = GDRCD_CHAT_IMAGE_TYPE,
    $symbol = GDRCD_CHAT_IMAGE_SYMBOL
) {
    $MESSAGE = $GLOBALS['MESSAGE'];

    // Se non si dispone dei permessi
    if ($_SESSION['permessi'] < GAMEMASTER) {
        return gdrcd_chat_status_forbidden($MESSAGE['chat']['error']['permissions']);
    }

    // Rimuove il primo carattere se il messaggio inizia col simbolo dedicato
    $testo = gdrcd_chat_strip_message_symbol($testo, $symbol);

    // Se il testo è vuoto l'inseriment$MESSAGE['chat']['error']['empty_message']o fallisce
    if (empty($testo)) {
        return gdrcd_chat_status_invalid($MESSAGE['chat']['error']['empty_message']);
    }

    gdrcd_chat_db_insert_for_login(
        '',
        $tipo,
        $testo
    );

    return gdrcd_chat_status_created();
}

/**
 * Inserisce nella tabella `chat` del database un messaggio di tipo "PNG".
 *
 * @param string $nomepng il nome del png da impersonare
 * @param string $testo il messaggio da salvare
 * @param string $tipo Facoltativo. La tipologia interna con cui salvare il messaggio nel database
 * @param string $symbol Facoltativo. il simbolo da rimuovere se presente come primo carattere
 * @return array{code: int, message: string} $status Array associativo proveniente da gdrcd_chat_status()
 */
function gdrcd_chat_png_save(
    $nomepng,
    $testo,
    $tipo = GDRCD_CHAT_PNG_TYPE,
    $symbol = GDRCD_CHAT_PNG_SYMBOL
) {
    $MESSAGE = $GLOBALS['MESSAGE'];

    // Se non si dispone dei permessi
    if ($_SESSION['permessi'] < GAMEMASTER) {
        return gdrcd_chat_status_forbidden($MESSAGE['chat']['error']['permissions']);
    }

    if (empty($nomepng)) {
        $nomepng = gdrcd_chat_extract_recipient_from_message($testo, $symbol);

        if (!$nomepng) {
            // L'azione è formattata male (non ho il nomepng, il messaggio o entrambi), ritorno fallimento.
            return gdrcd_chat_status_invalid($MESSAGE['chat']['error']['invalid_recipient']);
        }
    }

    // Se presente, rimuove il simbolo usato per indicare il png dal messaggio
    $testo = gdrcd_chat_strip_message_symbol($testo, $symbol);

    // Se il testo è vuoto l'inserimento fallisce
    if (empty($testo)) {
        return gdrcd_chat_status_invalid($MESSAGE['chat']['error']['empty_message']);
    }

    // formatta il nome del png per consistenza
    $nomepng = gdrcd_capital_letter($nomepng);

    // inserisco il sussurro in chat
    gdrcd_chat_db_insert_for_login(
        $nomepng,
        $tipo,
        $testo
    );

    return gdrcd_chat_status_created();
}

/**
 * Inserisce nella tabella `chat` del database un messaggio di tipo "Master".
 *
 * @param string $testo il messaggio da salvare
 * @param string $tipo Facoltativo. La tipologia interna con cui salvare il messaggio nel database
 * @param string $symbol Facoltativo. il simbolo da rimuovere se presente come primo carattere
 * @return array{code: int, message: string} $status Array associativo proveniente da gdrcd_chat_status()
 */
function gdrcd_chat_master_save(
    $testo,
    $tipo = GDRCD_CHAT_MASTER_TYPE,
    $symbol = GDRCD_CHAT_MASTER_SYMBOL
) {
    $MESSAGE = $GLOBALS['MESSAGE'];

    // Se non si dispone dei permessi
    if ($_SESSION['permessi'] < GAMEMASTER) {
        return gdrcd_chat_status_forbidden($MESSAGE['chat']['error']['permissions']);
    }

    // Rimuove il primo carattere se il messaggio inizia col simbolo dedicato
    $testo = gdrcd_chat_strip_message_symbol($testo, $symbol);

    // Se il testo è vuoto l'inserimento fallisce
    if (empty($testo)) {
        return gdrcd_chat_status_invalid($MESSAGE['chat']['error']['empty_message']);
    }

    gdrcd_chat_db_insert_for_login(
        '',
        $tipo,
        $testo
    );

    return gdrcd_chat_status_created();
}

/**
 * Inserisce nella tabella `chat` del database un messaggio di tipo "Tiro di dado".
 * Il lancio dadi supporta espressioni nel formato: [numero]d[facce][modificatore],[soglia]
 * Esempi validi: d6, 2d20, 3d10+5, 4d8-2,6
 *
 * @param string $testo il messaggio da elaborare contenente l'espressione dei dadi
 * @param string $tipo Facoltativo. La tipologia interna con cui salvare il messaggio nel database
 * @param string $symbol Facoltativo. il simbolo da rimuovere se presente come primo carattere
 * @return array{code: int, message: string} $status Array associativo proveniente da gdrcd_chat_status()
 */
function gdrcd_chat_dice_save(
    $testo,
    $tipo = GDRCD_CHAT_DICE_TYPE,
    $symbol = GDRCD_CHAT_DICE_SYMBOL
) {
    $MESSAGE = $GLOBALS['MESSAGE'];

    // Rimuove il primo carattere se il messaggio inizia col simbolo dedicato
    $testo = gdrcd_chat_strip_message_symbol($testo, $symbol);

    // Se il testo è vuoto l'inserimento fallisce
    if (empty($testo)) {
        return gdrcd_chat_status_invalid($MESSAGE['chat']['error']['empty_message']);
    }

    // Le seguenti espressioni regolari servono ad individuare le diverse
    // componenti di una stringa di testo così formata: 5d10+6,7
    // * Il 5 iniziale è il numero di dadi da lanciare. Facoltativo. Valori possibili: 1...100 ($dice_number_regex)
    // * Il 10 dopo la "d" rappresenta il numero di facce dei dadi da lanciare. Valori possibili: 2, 4, 6, 8, 10, 12, 20, 100 ($dice_faces_regex)
    // * Il +6 è un modificatore che si somma al totale, può essere anche negativo. Facoltativo. Valori possibili: -100...+100 ($dice_modifier_regexp)
    // * Il 7 seguito da una virgola permette di evidenziare i dadi che hanno raggiunto o superato quel valore. Facoltativo. Valori possibili: 0...100  ($dice_threshold_regex)

    $dice_number_regex = '(?:[1-9][0-9]?|100)';
    $dice_faces_regex = implode('|', array_column(gdrcd_chat_dice_list(), 'facce'));
    $dice_modifier_regexp = '(?:\+|-)(?:[1-9][0-9]?|100|0)';
    $dice_threshold_regex = '(?:[1-9][0-9]?|100|0)';

    $dice_regex = "($dice_number_regex)?"
        . "d($dice_faces_regex)"
        . "($dice_modifier_regexp)?"
        . "(?:,($dice_threshold_regex))?";

    if (preg_match("#^{$dice_regex}$#i", $testo, $match) !== 1) {
        // se l'espressione è invalida ritorna fallimento
        return gdrcd_chat_status_invalid($MESSAGE['chat']['error']['invalid_dice']);
    }

    // Recupera il valore effettivo rilevato nel testo inviato in chat
    // per le varie parti dell'espressione descritta in precedenza
    $dice_number = empty($match[1])? 1 : (int)$match[1];
    $dice_faces = (int)$match[2];
    $dice_modifier = empty($match[3])? null : $match[3];
    $dice_threshold = empty($match[4])? null : (int)$match[4];

    $successes = 0;
    $rolls = [];

    for ($i = 0; $i < $dice_number; ++$i) {
        // lancia il dado
        $roll = random_int(1, $dice_faces);

        // se è stata definita una soglia, verifico se il lancio la soddisfa
        if ($dice_threshold !== null && $roll >= $dice_threshold) {
            ++$successes;
        }

        // conserva il risultato del lancio in $rolls
        $rolls[] = $roll;
    }

    // Determina se il modificatore sia positivo o negativo per aggiungerlo correttamente alla somma
    $modifier_sign = str_starts_with($dice_modifier, '+')? 1 : -1;
    $modifier_value = $modifier_sign * (int)substr($dice_modifier, 1);

    // salva tutti i dati relativi al lancio dei dadi in un array
    $result = [
        'expression' => $testo,
        'number' => $dice_number,
        'faces' => $dice_faces,
        'modifier' => $dice_modifier,
        'threshold' => $dice_threshold,
        'successes' => $dice_threshold !== null? $successes : null,
        'sum' => array_sum($rolls) + $modifier_value,
        'rolls' => $rolls
    ];

    // inserisce nel database l'array convertito in json con tutti i dati sul risultato dei lanci
    gdrcd_chat_db_insert_for_login(
        '',
        $tipo,
        json_encode($result)
    );

    return gdrcd_chat_status_created();
}

/**
 * Inserisce nella tabella `chat` del database un messaggio di tipo "Tiro Caratteristica".
 *
 * Questa funzione gestisce il salvataggio di un tiro su caratteristica, calcolando il totale
 * sulla base del valore della caratteristica, bonus razziali, bonus da oggetti equipaggiati
 * e, se abilitato, il lancio di un dado associato. Se il personaggio ha salute pari o inferiore a zero,
 * viene inserito un messaggio di avviso come sussurro. I dati del tiro vengono salvati in formato JSON
 * nel campo testo della tabella chat.
 *
 * @param string $testo Il messaggio contenente l'ID della caratteristica da utilizzare (es: %2)
 * @param string $tipo Facoltativo. La tipologia interna con cui salvare il messaggio nel database
 * @param string $symbol Facoltativo. Il simbolo da rimuovere se presente come primo carattere
 * @return array{code: int, message: string} $status Array associativo proveniente da gdrcd_chat_status()
 */
function gdrcd_chat_stats_save(
    $testo,
    $tipo = GDRCD_CHAT_STATS_TYPE,
    $symbol = GDRCD_CHAT_STATS_SYMBOL
) {
    $MESSAGE = $GLOBALS['MESSAGE'];
    $PARAMETERS = $GLOBALS['PARAMETERS'];

    // Rimuove il primo carattere se il messaggio inizia col simbolo dedicato
    $statsId = gdrcd_chat_strip_message_symbol($testo, $symbol);

    if (!is_numeric($statsId)) {
        return gdrcd_chat_status_invalid($MESSAGE['chat']['error']['invalid_stats']);
    }

    $carId = 'car' . $statsId;
    $bonusCarId = 'bonus_car' . $statsId;

    $personaggio = gdrcd_chat_character_info($_SESSION['login']);
    $stats = $personaggio[$carId] ?? null;

    if ( $stats === null ) {
        // la caratteristica non esiste
        return gdrcd_chat_status_error($MESSAGE['chat']['error']['unknown_stats'] .': '. $carId);
    }

    if ($personaggio['salute'] <= 0) {
        // se l'utente non ha salute residua non può compiere l'azione
        return gdrcd_chat_status_forbidden($MESSAGE['status_pg']['exausted']);
    }

    $items = [];
    $items_bonus = 0;

    // Recupero eventuali bonus dati da oggetti equipaggiati
    $player_items = gdrcd_chat_player_items($_SESSION['login']);

    foreach ($player_items as $row) {
        $bonus = $row[$bonusCarId];

        if ($bonus === 0) {
            // Se l'oggetto non da un bonus rilevante per la skill passo al successivo
            continue;
        }

        // salvo i dati per lo specifico oggetto
        $items[] = [
            'id' => $row['id_oggetto'],
            'name' => $row['nome'],
            'value' => $bonus,
            'position' => $row['posizione'],
        ];

        // cumulo il bonus fornito dallo specifico oggetto
        $items_bonus += $bonus;
    }

    // Bonus razziali alla caratteristica
    $racial_record = gdrcd_chat_get_race($personaggio['id_razza']);

    if (empty($racial_record)) {
        return gdrcd_chat_status_error($MESSAGE['chat']['error']['unknown_race'] .': '. $personaggio['id_razza']);
    }

    $racial_bonus = $racial_record[$bonusCarId]?? 0;

    $die = null;
    $die_name = null;

    $statsDice = $PARAMETERS['settings']['stats_dice'];

    // Se i dadi sono abilitati e l'abilità ha un tipo di dado associato
    if ($PARAMETERS['mode']['dices'] == 'ON' && !empty($statsDice)) {

        // lancio effettivo del dado associato alla skill
        $die = random_int(1, (int) $statsDice);

        // recupera il nome del dado filtrando da gdrcd_chat_dice_list
        // il record che ha un valore di "facce" pari al dado configurato per la skill
        $dice = array_filter(
            gdrcd_chat_dice_list(),
            fn($dice) => $dice['facce'] === (int) $statsDice
        );

        // se ho trovato qualcosa allora assegno il nome rilevato
        // nel caso la definizione non esista assegno al numero di facce il prefisso 'd' come nome di default
        $die_name = !empty($dice)
            ? current($dice)['nome']
            : 'd'. (int) $statsDice;

    }

    // Calcoliamo il totale
    $total = $stats
        + $racial_bonus
        + $items_bonus
        + ($die? $die : 0);

    // salva tutti i dati relativi al tiro abilità
    $result = [

        // valore totale
        'sum' => $total,

        // caratteristica usata
        'stats' => [
            'id' => $carId,
            'name' => $PARAMETERS['names']['stats'][$carId],
            'value' => $stats,
        ],

        // bonus razziale applicato
        'race' => [
            'id' => $personaggio['id_razza'],
            'name' => $racial_record['nome'],
            'value' => $racial_bonus,
        ],

        // dado utilizzato
        'dice' => $die
            ? [
                'id' => (int) $statsDice,
                'name' => $die_name,
                'value' => $die,
            ]
            : null,

        // valore del bonus ed elenco oggetti equipaggiati che contribuiscono al calcolo
        'items' => [
            'value' => $items_bonus,
            'equip' => $items,
        ],

    ];

    // inserisce nel database l'array convertito in json con tutti i dati sul tiro abilità
    gdrcd_chat_db_insert_for_login(
        '',
        $tipo,
        json_encode($result)
    );

    return gdrcd_chat_status_created();
}

/**
 * Inserisce nella tabella `chat` del database un messaggio di tipo "Tiro su Abilità".
 *
 * Questa funzione gestisce il salvataggio di un tiro abilità, calcolando il totale
 * sulla base della caratteristica di riferimento, bonus razziali, grado abilità,
 * bonus da oggetti equipaggiati e, se abilitato, il lancio di un dado associato all'abilità.
 * Se il personaggio ha salute pari o inferiore a zero, viene inserito un messaggio di avviso come sussurro.
 * I dati del tiro vengono salvati in formato JSON nel campo testo della tabella chat.
 *
 * @param string $testo Il messaggio contenente l'ID dell'abilità da utilizzare ( es: ^12 )
 * @param string $tipo Facoltativo. La tipologia interna con cui salvare il messaggio nel database
 * @param string $symbol Facoltativo. Il simbolo da rimuovere se presente come primo carattere
 * @return array{code: int, message: string} $status Array associativo proveniente da gdrcd_chat_status()
 */
function gdrcd_chat_skill_save(
    $testo,
    $tipo = GDRCD_CHAT_SKILL_TYPE,
    $symbol = GDRCD_CHAT_SKILL_SYMBOL
) {
    $MESSAGE = $GLOBALS['MESSAGE'];
    $PARAMETERS = $GLOBALS['PARAMETERS'];

    // Rimuove il primo carattere se il messaggio inizia col simbolo dedicato
    $skillId = (int) gdrcd_chat_strip_message_symbol($testo, $symbol);

    if (empty($skillId)) {
        return gdrcd_chat_status_invalid($MESSAGE['chat']['error']['invalid_skill']);
    }

    // Recupero informazioni sull'abilità
    $skill_record = gdrcd_chat_player_skill($_SESSION['login'], $skillId);

    if (empty($skill_record)) {
        return gdrcd_chat_status_error($MESSAGE['chat']['error']['unknown_skill']);
    }

    // Valore della skill del personaggio.
    // Se un personaggio non ha mai speso punti per la skill, allora
    // grado risulterà nullo, in quel caso assumiamo zero come default.
    $skill_rank = $skill_record['grado'] ?? 0;

    // Cerca le informazioni sull'utilizzatore della skill nel database
    $personaggio = gdrcd_chat_character_info($_SESSION['login']);

    if ($personaggio['salute'] <= 0) {
        // se l'utente non ha salute residua non può compiere l'azione
        return gdrcd_chat_status_forbidden($MESSAGE['status_pg']['exausted']);
    }

    // Definisce gli identificativi di car e bonus_car (es: car1 e bonus_car1)
    $carId = 'car' . $skill_record['car'];
    $bonusCarId = 'bonus_car' . $skill_record['car'];

    // determina il valore della caratteristica di riferimento
    $stats = $personaggio[$carId] ?? null;

    if ( $stats === null ) {
        // la caratteristica non esiste
        return gdrcd_chat_status_error($MESSAGE['chat']['error']['unknown_stats'] .': '. $carId);
    }

    $items = [];
    $items_bonus = 0;

    // Recupero eventuali bonus dati da oggetti equipaggiati
    $player_items = gdrcd_chat_player_items($_SESSION['login']);

    foreach ($player_items as $row) {
        $bonus = $row[$bonusCarId];

        if ($bonus === 0) {
            // Se l'oggetto non da un bonus rilevante per la skill passo al successivo
            continue;
        }

        // salvo i dati per lo specifico oggetto
        $items[] = [
            'id' => $row['id_oggetto'],
            'name' => $row['nome'],
            'value' => $bonus,
            'position' => $row['posizione'],
        ];

        // cumulo il bonus fornito dallo specifico oggetto
        $items_bonus += $bonus;
    }

    // Bonus razziali alla caratteristica
    $racial_record = gdrcd_chat_get_race($personaggio['id_razza']);

    if (empty($racial_record)) {
        return gdrcd_chat_status_error($MESSAGE['chat']['error']['unknown_race'] .': '. $personaggio['id_razza']);
    }

    $racial_bonus = $racial_record[$bonusCarId]?? 0;

    $die = null;
    $die_name = null;

    // Se i dadi sono abilitati e l'abilità ha un tipo di dado associato
    if ($PARAMETERS['mode']['dices'] == 'ON' && !empty($skill_record['dice'])) {

        // lancio effettivo del dado associato alla skill
        $die = random_int(1, (int)$skill_record['dice']);

        // recupera il nome del dado filtrando da gdrcd_chat_dice_list
        // il record che ha un valore di "facce" pari al dado configurato per la skill
        $dice = array_filter(
            gdrcd_chat_dice_list(),
            fn($dice) => $dice['facce'] === (int)$skill_record['dice']
        );

        // se ho trovato qualcosa allora assegno il nome rilevato
        // nel caso la definizione non esista assegno al numero di facce il prefisso 'd' come nome di default
        $die_name = !empty($dice)
            ? current($dice)['nome']
            : 'd'. (int)$skill_record['dice'];

    }

    // Calcoliamo il totale
    $total = $stats
        + $racial_bonus
        + $skill_rank
        + $items_bonus
        + ($die? $die : 0);

    // salva tutti i dati relativi al tiro abilità
    $result = [

        // valore totale
        'sum' => $total,

        // caratteristica usata
        'stats' => [
            'id' => $carId,
            'name' => $PARAMETERS['names']['stats'][$carId],
            'value' => $stats,
        ],

        // abilità usata
        'skill' => [
            'id' => $skillId,
            'name' => $skill_record['nome'],
            'value' => $skill_rank,
        ],

        // bonus razziale applicato
        'race' => [
            'id' => $personaggio['id_razza'],
            'name' => $racial_record['nome'],
            'value' => $racial_bonus,
        ],

        // dado utilizzato
        'dice' => $die
            ? [
                'id' => (int)$skill_record['dice'],
                'name' => $die_name,
                'value' => $die,
            ]
            : null,

        // valore del bonus ed elenco oggetti equipaggiati che contribuiscono al calcolo
        'items' => [
            'value' => $items_bonus,
            'equip' => $items,
        ],

    ];

    // inserisce nel database l'array convertito in json con tutti i dati sul tiro abilità
    gdrcd_chat_db_insert_for_login(
        '',
        $tipo,
        json_encode($result)
    );

    return gdrcd_chat_status_created();
}

/**
 * Inserisce nella tabella `chat` del database un messaggio di tipo "Utilizzo Oggetto".
 *
 * Questa funzione gestisce il salvataggio dell'utilizzo di un oggetto da parte del personaggio,
 * decrementando le cariche o il numero dell'oggetto tramite gdrcd_chat_player_item_consume.
 * Se l'oggetto non esiste o non è utilizzabile, l'operazione fallisce.
 * I dati dell'oggetto utilizzato vengono salvati in formato JSON nel campo testo della tabella chat.
 *
 * @param string $testo Il messaggio contenente l'ID dell'oggetto da utilizzare (es: =12)
 * @param string $tipo Facoltativo. La tipologia interna con cui salvare il messaggio nel database
 * @param string $symbol Facoltativo. Il simbolo da rimuovere se presente come primo carattere
 * @return array{code: int, message: string} $status Array associativo proveniente da gdrcd_chat_status()
 */
function gdrcd_chat_item_save(
    $testo,
    $tipo = GDRCD_CHAT_ITEM_TYPE,
    $symbol = GDRCD_CHAT_ITEM_SYMBOL
) {
    $MESSAGE = $GLOBALS['MESSAGE'];

    // Rimuove il primo carattere se il messaggio inizia col simbolo dedicato
    $id_oggetto = (int) gdrcd_chat_strip_message_symbol($testo, $symbol);

    // Se il testo è vuoto l'inserimento fallisce
    if (empty($id_oggetto)) {
        return gdrcd_chat_status_invalid($MESSAGE['chat']['error']['empty_message']);
    }

    $item = gdrcd_chat_player_item($_SESSION['login'], $id_oggetto);

    // Se l'oggetto non esiste l'operazione fallisce
    if (empty($item)) {
        return gdrcd_chat_status_invalid($MESSAGE['chat']['error']['invalid_item'] .': '. $id_oggetto);
    }

    $item = gdrcd_chat_player_item_consume($_SESSION['login'], $item);

    // Informazioni dell'oggetto usato
    $result = [
        'id' => $item['id_oggetto'],
        'name' => $item['nome'],
        'number' => $item['numero'],
        'charges' => $item['cariche'],
        'max_charges' => $item['max_cariche'],
    ];

    // inserisce nel database l'array convertito in json con tutti i dati sul tiro abilità
    gdrcd_chat_db_insert_for_login(
        '',
        $tipo,
        json_encode($result)
    );

    return gdrcd_chat_status_created();
}

/**
 * Inserisce nella tabella `chat` del database un messaggio di tipo "Sussurro".
 * Il sussurro può essere inviato in 3 modi:
 *  1. Selezionando dalla tendina il tipo "Sussurro", scrivendo nel tag il destinatario e il messaggio nell'input di testo principale
 *  2. Scrivendo nel tag il nome del destinatario e nell'input di testo il simbolo del sussurro unicamente come primo carattere
 *  3. Scrivendo nell'input di testo principale il nome utente contornato dal carattere del sussurro. Es: @nomeutente@ Ciao come va?
 *
 * @param string $destinatario il destinatario del sussurro
 * @param string $testo il messaggio da salvare
 * @param string $tipo Facoltativo. La tipologia interna con cui salvare il messaggio nel database
 * @param string $symbol Facoltativo. il simbolo da rimuovere se presente come primo carattere o se usato come delimitatore del destinatario
 * @return array{code: int, message: string} $status Array associativo proveniente da gdrcd_chat_status()
 */
function gdrcd_chat_whisper_save(
    $destinatario,
    $testo,
    $tipo = GDRCD_CHAT_WHISPER_TYPE,
    $symbol = GDRCD_CHAT_WHISPER_SYMBOL
) {
    $MESSAGE = $GLOBALS['MESSAGE'];

    if (empty($destinatario)) {
        $destinatario = gdrcd_chat_extract_recipient_from_message($testo, $symbol);

        if (!$destinatario) {
            return gdrcd_chat_status_invalid($MESSAGE['chat']['error']['invalid_recipient']);
        }
    }

    // Se presente, rimuove il simbolo usato per indicare il sussurro dal messaggio
    $testo = gdrcd_chat_strip_message_symbol($testo, $symbol);

    // Se il testo è vuoto l'inserimento fallisce
    if (empty($testo)) {
        return gdrcd_chat_status_invalid($MESSAGE['chat']['error']['empty_message']);
    }

    // formatta il nome del destinatario. E' necessario per la ricerca nel database.
    $destinatario = gdrcd_capital_letter($destinatario);

    // Cerca le informazioni sul destinatario nel database
    $personaggio = gdrcd_chat_character_info($destinatario);

    // se destinatario non esiste nel database, ritorna fallimento
    if ($personaggio === null) {
        return gdrcd_chat_status_invalid($MESSAGE['chat']['error']['unknown_recipient'] .': '. $destinatario);
    }

    // Giunti a questo punto abbiamo:
    //  - verificato che il sussurro sia formattato correttamente
    //  - verificato che il destinatario sia un nome utente valido e realmente esistente
    //  - ripulito il messaggio di chat da eventuali simboli

    // inserisco il sussurro in chat
    gdrcd_chat_db_insert_for_login(
        $destinatario,
        $tipo,
        $testo
    );

    return gdrcd_chat_status_created();
}

/**
 * Inserisce nella tabella `chat` del database un messaggio di tipo "Azione".
 *
 * @param string $tag il tag di locazione
 * @param string $testo il messaggio da salvare
 * @param string $tipo Facoltativo. La tipologia interna con cui salvare il messaggio nel database
 * @param string $symbol Facoltativo. il simbolo da rimuovere se presente come primo carattere
 * @return array{code: int, message: string} $status Array associativo proveniente da gdrcd_chat_status()
 */
function gdrcd_chat_action_save(
    $tag,
    $testo,
    $tipo = GDRCD_CHAT_ACTION_TYPE,
    $symbol = GDRCD_CHAT_ACTION_SYMBOL
) {
    $MESSAGE = $GLOBALS['MESSAGE'];

    // Rimuove il primo carattere se il messaggio inizia col simbolo dedicato
    $testo = gdrcd_chat_strip_message_symbol($testo, $symbol);

    // Se il testo è vuoto l'inserimento fallisce
    if (empty($testo)) {
        return gdrcd_chat_status_invalid($MESSAGE['chat']['error']['empty_message']);
    }

    // Salva il tag in sessione
    gdrcd_chat_set_tag($tag);

    // Salva l'azione nel database
    gdrcd_chat_db_insert_for_login(
        $tag,
        $tipo,
        $testo
    );

    return gdrcd_chat_status_created();
}

/**
 * Inserisce nella tabella `chat` del database un messaggio di tipo "Parlato".
 *
 * @param string $tag il tag di locazione
 * @param string $testo il messaggio da salvare
 * @param string $tipo Facoltativo. La tipologia interna con cui salvare il messaggio nel database
 * @param string $symbol Facoltativo. il simbolo da rimuovere se presente come primo carattere
 * @return array{code: int, message: string} $status Array associativo proveniente da gdrcd_chat_status()
 */
function gdrcd_chat_message_save(
    $tag,
    $testo,
    $tipo = GDRCD_CHAT_MESSAGE_TYPE,
    $symbol = GDRCD_CHAT_MESSAGE_SYMBOL
) {
    $MESSAGE = $GLOBALS['MESSAGE'];

    // TODO: calcolo esperienza in chat

    // Rimuove il primo carattere se il messaggio inizia col simbolo dedicato
    $testo = gdrcd_chat_strip_message_symbol($testo, $symbol);

    // Se il testo è vuoto l'inserimento fallisce
    if (empty($testo)) {
        return gdrcd_chat_status_invalid($MESSAGE['chat']['error']['empty_message']);
    }

    // Salva il tag in sessione
    gdrcd_chat_set_tag($tag);

    // Salva l'azione nel database
    gdrcd_chat_db_insert_for_login(
        $tag,
        $tipo,
        $testo
    );

    return gdrcd_chat_status_created();
}

/**
 * Gestisce l'invito di un personaggio a una chat privata.
 *
 * Questa funzione permette al proprietario della chat privata di invitare un altro personaggio.
 * Verifica i permessi, controlla che il destinatario sia valido e non già invitato,
 * aggiorna la lista degli invitati nel database e invia una notifica al destinatario.
 * Inserisce inoltre un messaggio in chat relativo all'invito.
 *
 * @param string $destinatario Il nome del personaggio da invitare. Se vuoto, viene estratto dal testo.
 * @param string $testo Il messaggio di accompagnamento per l'invito.
 * @param string $tipo Facoltativo. La tipologia interna con cui salvare il messaggio nel database.
 * @param string $symbol Facoltativo. Il simbolo da rimuovere se presente come primo carattere o delimitatore del destinatario.
 * @return array{code: int, message: string} $status Array associativo proveniente da gdrcd_chat_status()
 */
function gdrcd_chat_private_invite_save(
    $destinatario,
    $testo,
    $tipo = GDRCD_CHAT_PRIVATE_INVITE_TYPE,
    $symbol = GDRCD_CHAT_PRIVATE_INVITE_SYMBOL
) {
    $MESSAGE = $GLOBALS['MESSAGE'];

    // Recupero le informazioni sulla chat corrente
    $info = gdrcd_chat_info($_SESSION['luogo']);

    // Se l'utente connesso non ha i permessi per procedere, usciamo subito
    if (!gdrcd_chat_is_room_owner($info)) {
        return gdrcd_chat_status_forbidden($MESSAGE['chat']['error']['permissions']);
    }

    if (empty($destinatario)) {
        $destinatario = gdrcd_chat_extract_recipient_from_message($testo, $symbol);

        if (!$destinatario) {
            return gdrcd_chat_status_invalid($MESSAGE['chat']['error']['invalid_recipient']);
        }
    }

    // Se presente, rimuove il simbolo usato per il messaggio
    $testo = trim(gdrcd_chat_strip_message_symbol($testo, $symbol));

    // formatta il nome del destinatario. E' necessario per la ricerca nel database.
    $destinatario = gdrcd_capital_letter($destinatario);

    // Cerca le informazioni sul destinatario nel database
    $personaggio = gdrcd_chat_character_info($destinatario);

    // se destinatario non esiste nel database, ritorna fallimento
    if ($personaggio === null) {
        return gdrcd_chat_status_invalid($MESSAGE['chat']['error']['unknown_recipient'] .': '. $destinatario);
    }

    // Converte la stringa invitati in un array
    $invitati = !empty($info['invitati'])
        ? explode(',', $info['invitati'])
        : [];

    // Se il personaggio è già invitato, esce con un errore
    if (in_array($destinatario, $invitati)) {
        return gdrcd_chat_status_invalid($MESSAGE['chat']['error']['already_invited'] .': '. $destinatario);
    }

    // Giunti a questo punto abbiamo:
    //  - verificato che login abbia i permessi per gestire la chat privata
    //  - verificato che il destinatario sia un nome utente valido e realmente esistente
    //  - verificato che il destinatario non risulti già invitato in chat

    // Aggiunge il nome del personaggio all'array degli invitati
    $invitati[] = $destinatario;

    // Aggiorna la lista invitati sul database
    gdrcd_stmt(
        "UPDATE mappa
            SET invitati = ?
        WHERE id = ?
        LIMIT 1",
        [
            'si',
            implode(',', $invitati),
            $info['id'],
        ]
    );

    // Invia un messaggio di posta al personaggio invitato
    gdrcd_stmt(
        'INSERT INTO messaggi ( mittente, destinatario, spedito, letto, testo )
        VALUES (?, ?, NOW(), 0, ?)',
        [
            'sss',
            'System message',
            $destinatario,
            $_SESSION['login']
                .' '. $MESSAGE['chat']['warning']['invited_message']
                .' '. $info['nome']
                ."\n" . $testo
        ]
    );

    $result = [
        'message' => $testo,
        'invited_list' => $invitati,
    ];

    // inserisco il messaggio in chat
    gdrcd_chat_db_insert_for_login(
        $destinatario,
        $tipo,
        json_encode($result)
    );

    return gdrcd_chat_status_created();
}

/**
 * Gestisce la rimozione (kick) di un personaggio da una chat privata.
 *
 * Questa funzione permette al proprietario della chat privata di espellere un personaggio invitato.
 * Verifica i permessi, controlla che il destinatario sia valido e presente tra gli invitati,
 * aggiorna la lista degli invitati nel database e invia una notifica al destinatario espulso.
 * Inserisce inoltre un messaggio in chat relativo all'espulsione.
 *
 * @param string $destinatario Il nome del personaggio da espellere. Se vuoto, viene estratto dal testo.
 * @param string $testo Il messaggio di accompagnamento per l'espulsione.
 * @param string $tipo Facoltativo. La tipologia interna con cui salvare il messaggio nel database.
 * @param string $symbol Facoltativo. Il simbolo da rimuovere se presente come primo carattere o delimitatore del destinatario.
 * @return array{code: int, message: string} $status Array associativo proveniente da gdrcd_chat_status()
 */
function gdrcd_chat_private_kick_save(
    $destinatario,
    $testo,
    $tipo = GDRCD_CHAT_PRIVATE_KICK_TYPE,
    $symbol = GDRCD_CHAT_PRIVATE_KICK_SYMBOL
) {
    $MESSAGE = $GLOBALS['MESSAGE'];

    // Recupero le informazioni sulla chat corrente
    $info = gdrcd_chat_info($_SESSION['luogo']);

    // Se l'utente connesso non ha i permessi per procedere, usciamo subito
    if (!gdrcd_chat_is_room_owner($info)) {
        return gdrcd_chat_status_forbidden($MESSAGE['chat']['error']['permissions']);
    }

    if (empty($destinatario)) {
        $destinatario = gdrcd_chat_extract_recipient_from_message($testo, $symbol);

        if (!$destinatario) {
            return gdrcd_chat_status_invalid($MESSAGE['chat']['error']['invalid_recipient']);
        }
    }

    // Se presente, rimuove il simbolo usato per il messaggio
    $testo = trim(gdrcd_chat_strip_message_symbol($testo, $symbol));

    // formatta il nome del destinatario.
    $destinatario = gdrcd_capital_letter($destinatario);

    // Converte la stringa invitati in un array
    $invitati = !empty($info['invitati'])
        ? explode(',', $info['invitati'])
        : [];

    // Se il personaggio non è in elenco, esce con un errore
    if (!in_array($destinatario, $invitati)) {
        return gdrcd_chat_status_invalid($MESSAGE['chat']['error']['already_kicked'] .': '. $destinatario);
    }

    // Giunti a questo punto abbiamo:
    //  - verificato che login abbia i permessi per gestire la chat privata
    //  - verificato che il destinatario risulti invitato in chat

    // Rimuove il nome del personaggio dalla lista invitati
    $invitati = array_filter(
        $invitati,
        fn($invitato) => $invitato !== $destinatario
    );

    // Aggiorna la lista invitati sul database
    gdrcd_stmt(
        "UPDATE mappa
            SET invitati = ?
        WHERE id = ?
        LIMIT 1",
        [
            'si',
            implode(',', $invitati),
            $info['id'],
        ]
    );

    // Invia un messaggio di posta al personaggio cacciato
    gdrcd_stmt(
        'INSERT INTO messaggi ( mittente, destinatario, spedito, letto, testo )
        VALUES (?, ?, NOW(), 0, ?)',
        [
            'sss',
            'System message',
            $destinatario,
            $_SESSION['login']
                .' '. $MESSAGE['chat']['warning']['expelled_message']
                .' '. $info['nome']
                ."\n" . $testo
        ]
    );

    $result = [
        'message' => $testo,
        'invited_list' => $invitati,
    ];

    // inserisco il messaggio in chat
    gdrcd_chat_db_insert_for_login(
        $destinatario,
        $tipo,
        json_encode($result)
    );

    return gdrcd_chat_status_created();
}

/**
 * Inserisce nella tabella `chat` un messaggio contenente la lista aggiornata degli invitati alla chat privata.
 *
 * Questa funzione può essere utilizzata dal proprietario della chat privata per comunicare agli utenti
 * la lista attuale degli invitati. Verifica i permessi e salva in chat la lista in formato JSON.
 *
 * @param string $testo Il messaggio inviato in chat
 * @param string $tipo Facoltativo. La tipologia interna con cui salvare il messaggio nel database.
 * @param string $symbol Facoltativo. Il simbolo da rimuovere se presente come primo carattere.
 * @return array{code: int, message: string} $status Array associativo proveniente da gdrcd_chat_status()
 */
function gdrcd_chat_private_list_save(
    $testo,
    $tipo = GDRCD_CHAT_PRIVATE_LIST_TYPE,
    $symbol = GDRCD_CHAT_PRIVATE_LIST_SYMBOL
) {
    $MESSAGE = $GLOBALS['MESSAGE'];

    // Recupero le informazioni sulla chat corrente
    $info = gdrcd_chat_info($_SESSION['luogo']);

    // Se l'utente connesso non ha i permessi per procedere, usciamo subito
    if (!gdrcd_chat_is_room_owner($info)) {
        return gdrcd_chat_status_forbidden($MESSAGE['chat']['error']['permissions']);
    }

    // Converte la stringa invitati in un array
    $invitati = !empty($info['invitati'])
        ? explode(',', $info['invitati'])
        : [];

    $result = [
        'invited_list' => $invitati,
    ];

    // inserisco il messaggio in chat
    gdrcd_chat_db_insert_for_login(
        '',
        $tipo,
        json_encode($result)
    );

    return gdrcd_chat_status_created();
}

/**
 * Inserisce nel database una riga nella tabella `chat` da parte dell'utente connesso al sito.
 *
 * @param string $tag_o_destinatario il tag o il destinatario appropriati per la tipologia di messaggio
 * @param string $tipo il tipo interno dei messaggi di chat (es: A, M, P etc.)
 * @param string $testo il messaggio da salvare
 * @return void
 */
function gdrcd_chat_db_insert_for_login(
    $tag_o_destinatario,
    $tipo,
    $testo
) {
    gdrcd_chat_db_insert(
        $_SESSION['luogo'],
        [$_SESSION['sesso'], $_SESSION['img_razza']],
        $_SESSION['login'],
        $tag_o_destinatario,
        $tipo,
        $testo
    );
}

/**
 * Inserisce nel database una riga nella tabella `chat`.
 *
 * @param int $stanza Id della stanza della chat
 * @param string[] $imgs array di iconcine da utilizzare
 * @param string $mittente
 * @param string $tag_o_destinatario il tag o il destinatario appropriati per la tipologia di messaggio
 * @param string $tipo il tipo interno dei messaggi di chat (es: A, M, P etc.)
 * @param string $testo il messaggio da salvare
 * @return void
 */
function gdrcd_chat_db_insert(
    $stanza,
    $imgs,
    $mittente,
    $tag_o_destinatario,
    $tipo,
    $testo
) {
    gdrcd_stmt(
        'INSERT INTO chat (stanza, imgs, mittente, destinatario, ora, tipo, testo)
        VALUES (?, ?, ?, ?, NOW(), ?, ?)',
        [
            'isssss',
            $stanza,
            implode(';', $imgs),
            $mittente,
            $tag_o_destinatario,
            $tipo,
            $testo
        ]
    );
}

/**
 * Estrae il nome del destinatario da un messaggio delimitato da un simbolo specifico.
 *
 * Cerca nel messaggio la prima occorrenza di una sottostringa delimitata dal simbolo fornito
 * (es: @nomeutente@, !nomeutente!, $nomepng$) e la restituisce come nome destinatario.
 * Rimuove inoltre la sottostringa trovata dal messaggio originale tramite riferimento.
 * Se il destinatario non viene trovato, ritorna null.
 *
 * @param string $message Il messaggio da cui estrarre il destinatario (passato per riferimento, viene modificato)
 * @param string $symbol Il simbolo delimitatore da cercare (es: @, !, $)
 * @return null|string Il nome del destinatario estratto, oppure null se non trovato
 */
function gdrcd_chat_extract_recipient_from_message(&$message, $symbol)
{
    $escaped_symbol = preg_quote($symbol);

    // Se il destinatario non è stato trovato ritorna null
    if (preg_match("#^{$escaped_symbol}([^{$escaped_symbol}]+?){$escaped_symbol}#i", $message, $match) !== 1) {
        return null;
    }

    // ripulisce la parte iniziale del messaggio da @nomeutente@
    $message = trim(strtr($message, [$match[0] => '']));

    // ritorna il nome del destinatario trovato
    return $match[1];
}

/**
 * Rimuove il simbolo indicato dal messaggio se questo è presente come primo carattere.
 *
 * @param string $message il testo inviato in chat
 * @param string $symbol il simbolo da rimuovere se presente come primo carattere
 * @return string il messaggio inviato in chat senza il primo carattere se trovato
 */
function gdrcd_chat_strip_message_symbol($message, $symbol)
{
    if (str_starts_with($message, $symbol)) {
        return trim(substr($message, 1));
    }

    return $message;
}

/**
 * Indica se la chat con id $luogo è accessibile per l'utente connesso.
 * Le chat pubbliche sono sempre accessibili a chiunque.
 * Le chat private sono accessibili:
 *  - se si è il proprietario
 *  - se si è invitati
 *  - se si è MODERATOR o superiore
 *
 * @param null|int|array{
 *  invitati: string,
 *  privata: int,
 *  proprietario: ?string,
 *  scadenza: ?string,
 * } $luogo
 * @return bool true se l'utente può accedervi, false altrimenti
 */
function gdrcd_chat_is_accessible($luogo)
{
    if ($luogo === null) {
        return false;
    }

    $info = is_int($luogo)
        ? gdrcd_chat_info($luogo) ?? []
        : $luogo;

    if (($info['privata'] ?? 0) != 1) {
        // chat pubblica: sempre accessibile
        return true;
    }

    $invitati = explode(',', $info['invitati']);
    $login_moderator_o_superiore = $_SESSION['permessi'] >= MODERATOR;
    $login_proprietario_chat = $info['proprietario'] == $_SESSION['login'];
    $login_inviato_chat = in_array($_SESSION['login'], $invitati);
    $chat_scaduta = time() >= strtotime($info['scadenza']);

    // chat privata scaduta: se non sei moderator o maggiore, non sei abilitato
    if ($chat_scaduta && !$login_moderator_o_superiore) {
        return false;
    }

    // chat privata ancora valida: accessibile solo nei seguenti casi
    return $login_moderator_o_superiore
        || $login_proprietario_chat
        || $login_inviato_chat;
}

/**
 * Verifica se il personaggio attualmente connesso è il proprietario della chat privata.
 *
 * Una chat privata può essere di proprietà di un personaggio (proprietario) o di una gilda (proprietario numerico presente nella stringa gilda dell'utente).
 * La funzione ritorna true solo se la chat è privata, non è scaduta e il proprietario corrisponde all'utente loggato o alla sua gilda.
 *
 * @param int|array{
 *  id: int,
 *  nome: ?string,
 *  stanza_apparente: ?string,
 *  invitati: string,
 *  privata: int,
 *  proprietario: ?string,
 *  scadenza: ?string,
 * } $luogo ID della chat o un array con le info della stanza corrente
 * @return bool true se l'utente corrente è il proprietario della chat privata, false altrimenti
 */
function gdrcd_chat_is_room_owner($luogo)
{
    // Recupero le informazioni sulla chat corrente
    $info = is_array($luogo) ? $luogo : gdrcd_chat_info($luogo);
    $chat_scaduta = time() >= strtotime($info['scadenza']);

    // Se la chat non è privata oppure è scaduta nessuno può esserne il proprietario
    if ($info['privata'] != 1 || $chat_scaduta) {
        return false;
    }

    $login_proprietario_chat = $info['proprietario'] == $_SESSION['login'];
    $gilda_proprietaria_chat = is_numeric($info['proprietario'])
        && str_contains($_SESSION['gilda'], (string)$info['proprietario']);

    // Altrimenti si può essere proprietari se la chat è associata allo specifico personaggio
    // oppure se la chat appartiene alla gilda di cui fa parte il personaggio
    return $login_proprietario_chat || $gilda_proprietaria_chat;
}

/**
 * Definisce la costante GDRCD_ENABLE_CHAT_OP valorizzata a true.
 * Questa funzione, assieme alla sua gemella gdrcd_chat_op_require_enable(),
 * serve ad implementare un controllo di sicurezza per garantire che
 * le operazioni di funzionamento della chat possano essere utilizzate
 * unicamente nei files previsti.
 *
 * @see gdrcd_chat_op_require_enable
 * @return void
 */
function gdrcd_chat_op_set_enable()
{
    if ( !defined('GDRCD_ENABLE_CHAT_OP') ) {
        define('GDRCD_ENABLE_CHAT_OP', true);
    }
}

/**
 * Garantisce che il funzionamento delle operazioni della chat siano consentite.
 *
 * @return void|never termina lo script se le operazioni di chat non sono consentite
 */
function gdrcd_chat_op_require_enabled()
{
    if ( !defined('GDRCD_ENABLE_CHAT_OP') || GDRCD_ENABLE_CHAT_OP !== true ) {
        http_response_code(403);
        die();
    }
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

/**
 * Salva in sessione il tag di locazione da associare ai messaggi di chat inviati.
 *
 * Il tag rappresenta una stringa identificativa della posizione o stanza
 * e viene utilizzato per associare i messaggi di chat a una determinata area.
 *
 * @param string $tag Il tag di locazione da salvare in sessione
 * @return void
 */
function gdrcd_chat_set_tag($tag)
{
    $_SESSION['tag'] = $tag;
}

/**
 * Recupera il tag di locazione attualmente salvato in sessione.
 * Il tag rappresenta la posizione del personaggio nell'ambiente definito dalla chat.
 *
 * @return string|null Il tag di locazione, oppure null se non impostato
 */
function gdrcd_chat_get_tag()
{
    return $_SESSION['tag'];
}

/**
 * Ritorna il nome della chat
 *
 * @param null|int|array{nome: ?string} $luogo
 * @return null|string
 */
function gdrcd_chat_name($luogo)
{
    if ($luogo === null) {
        return false;
    }

    $info = is_int($luogo)
        ? gdrcd_chat_info($luogo) ?? []
        : $luogo;

    return $info['nome']?? null;
}

/**
 * Ritorna le seguenti informazioni della chat identificata dall'id fornito:
 *  - id: ID del luogo
 *  - nome: nome della chat
 *  - stanza_apparente: nome della chat nei presenti
 *  - invitati: lista nomi invitati separata da virgola
 *  - privata: 1 se la chat è privata, 0 altrimenti
 *  - proprietario: nome del proprietario della chat privata
 *  - scadenza: data scadenza chat privata
 *
 * @param int $luogo
 * @return null|array{
 *  id: int,
 *  nome: ?string,
 *  stanza_apparente: ?string,
 *  invitati: string,
 *  privata: int,
 *  proprietario: ?string,
 *  scadenza: ?string,
 * }
 */
function gdrcd_chat_info($luogo)
{
    $stmt = gdrcd_stmt(
        'SELECT
            id,
            nome,
            stanza_apparente,
            invitati,
            privata,
            proprietario,
            scadenza

        FROM mappa

        WHERE id = ?',
        ['i', $luogo]
    );

    if (gdrcd_query($stmt, 'num_rows') === 0) {
        return null;
    }

    $record = gdrcd_query($stmt, 'fetch');
    gdrcd_query($stmt, 'free');
    return $record;
}

/**
 * Recupera tutte le abilità disponibili per un giocatore specifico.
 * Include le abilità universali (id_razza = -1) e quelle specifiche
 * della razza del personaggio.
 *
 * @param string $nome Nome del personaggio per cui recuperare le abilità
 * @return array<array{
 *  id_abilita: int,
 *  nome: string,
 *  car: int,
 *  dice: null|int,
 *  grado: null|int
 * }> Array di abilità
 */
function gdrcd_chat_player_skills($nome)
{
    $skills = [];

    $stmt = gdrcd_stmt(
        'SELECT abilita.id_abilita,
                abilita.nome,
                abilita.car,
                abilita.dice,
                clgpersonaggioabilita.grado

        FROM abilita
            LEFT JOIN clgpersonaggioabilita
                ON (
                    clgpersonaggioabilita.id_abilita = abilita.id_abilita
                    AND clgpersonaggioabilita.nome = ?
                )

        WHERE abilita.id_razza = -1
            OR abilita.id_razza = (SELECT id_razza FROM personaggio WHERE nome = ?)

        ORDER BY abilita.nome',
        ['ss', $nome, $nome]
    );

    if (gdrcd_query($stmt, 'num_rows') > 0) {
        while ($row = gdrcd_query($stmt, 'fetch')) {
            $skills[] = $row;
        }

        gdrcd_query($stmt, 'free');
    }

    return $skills;
}

/**
 * Recupera le informazioni di una specifica abilità dal database.
 *
 * Questa funzione chiama internamente gdrcd_chat_player_skills e ritorna
 * l'abilità identificata dallo $skillId fornito
 *
 * @param string $nome Nome del personaggio per cui recuperare le abilità
 * @param int $skillId L'ID dell'abilità da recuperare
 * @return null|array{
 *  id_abilita: int,
 *  nome: string,
 *  car: int,
 *  dice: null|int,
 *  grado: null|int
 * } Array associativo con i dati dell'abilità, oppure null se non trovata
 */
function gdrcd_chat_player_skill($nome, $skillId)
{
    $skills = gdrcd_chat_player_skills($nome);

    foreach ($skills as $skill) {
        if ($skill['id_abilita'] == $skillId) {
            return $skill;
        }
    }

    return null;
}

/**
 * Recupera tutte le caratteristiche disponibili per il personaggio.
 * Estrae le statistiche dai parametri globali filtrando solo quelle con ID numerico.
 *
 * @return array<array{id_stats: int, nome: string}> Array di caratteristiche
 */
function gdrcd_chat_player_stats()
{
    $PARAMETERS = $GLOBALS['PARAMETERS'];

    $stats = [];

    foreach($PARAMETERS['names']['stats'] as $id_stats => $name_stats) {
        $id_stats = substr($id_stats, 3);

        if(!is_numeric($id_stats)) {
            continue;
        }

        $stats[] = [
            'id_stats' => (int) $id_stats,
            'nome' => $name_stats,
        ];
    }

    return $stats;
}

/**
 * Recupera tutti gli oggetti utilizzabili in chat posseduti da un giocatore specifico.
 * Include solo gli oggetti in posizioni > 0 (equipaggiati o nell'inventario).
 *
 * @param string $nome Nome del personaggio per cui recuperare gli oggetti
 * @return array<array{
 *  id_oggetto: int,
 *  nome: string,
 *  bonus_car0: int,
 *  bonus_car1: int,
 *  bonus_car2: int,
 *  bonus_car3: int,
 *  bonus_car4: int,
 *  bonus_car5: int,
 *  posizione: int,
 *  numero: int,
 *  cariche: int,
 *  max_cariche: int,
 * }> Ogni elemento contiene:
 *      - id_oggetto: ID univoco dell'oggetto
 *      - nome: Nome dell'oggetto
 *      - bonus_car0: valore bonus fornito ai tiri di dado sulla caratteristica 0
 *      - bonus_car1: valore bonus fornito ai tiri di dado sulla caratteristica 1
 *      - bonus_car2: valore bonus fornito ai tiri di dado sulla caratteristica 2
 *      - bonus_car3: valore bonus fornito ai tiri di dado sulla caratteristica 3
 *      - bonus_car4: valore bonus fornito ai tiri di dado sulla caratteristica 4
 *      - bonus_car5: valore bonus fornito ai tiri di dado sulla caratteristica 5
 *      - posizione: Posizione oggetto equipaggiato
 *      - numero: Quantitativo di oggetti posseduti
 *      - cariche: Numero di cariche disponibili per l'oggetto
 *      - max_cariche: Numero massimo di cariche per l'oggetto
 */
function gdrcd_chat_player_items($nome)
{
    $items = [];

    $stmt = gdrcd_stmt(
        'SELECT oggetto.id_oggetto,
                oggetto.nome,
                oggetto.bonus_car0,
                oggetto.bonus_car1,
                oggetto.bonus_car2,
                oggetto.bonus_car3,
                oggetto.bonus_car4,
                oggetto.bonus_car5,
                clgpersonaggiooggetto.posizione,
                clgpersonaggiooggetto.numero,
                clgpersonaggiooggetto.cariche,
                oggetto.cariche AS max_cariche

        FROM clgpersonaggiooggetto
            JOIN oggetto USING(id_oggetto)

        WHERE clgpersonaggiooggetto.nome = ?
            AND clgpersonaggiooggetto.posizione > 1

        ORDER BY clgpersonaggiooggetto.posizione',
        ['s', $nome]
    );

    if (gdrcd_query($stmt, 'num_rows') > 0) {
        while ($row = gdrcd_query($stmt, 'fetch')) {
            $items[] = $row;
        }

        gdrcd_query($stmt, 'free');
    }

    return $items;
}

/**
 * Recupera le informazioni di un oggetto specifico posseduto da un giocatore.
 *
 * Questa funzione chiama internamente gdrcd_chat_player_items e ritorna
 * l'oggetto identificato da $itemId tra quelli posseduti dal personaggio $nome.
 *
 * @param string $nome Nome del personaggio per cui recuperare l'oggetto
 * @param int $itemId L'ID dell'oggetto da recuperare
 * @return null|array{
 *  id_oggetto: int,
 *  nome: string,
 *  bonus_car0: int,
 *  bonus_car1: int,
 *  bonus_car2: int,
 *  bonus_car3: int,
 *  bonus_car4: int,
 *  bonus_car5: int,
 *  posizione: int,
 *  numero: int,
 *  cariche: int,
 *  max_cariche: int
 * } Array associativo con i dati dell'oggetto, oppure null se non trovato
 */
function gdrcd_chat_player_item($nome, $itemId)
{
    $items = gdrcd_chat_player_items($nome);

    foreach ($items as $item) {
        if ($item['id_oggetto'] == $itemId) {
            return $item;
        }
    }

    return null;
}

/**
 * Consuma una carica di un oggetto posseduto da un personaggio.
 *
 * Questa funzione decrementa il numero di cariche disponibili per un oggetto equipaggiato.
 * Se le cariche terminano, decrementa il numero di oggetti o elimina la riga se era l'ultimo.
 * Aggiorna il database di conseguenza.
 *
 * La funzione ritorna i dati dello stesso oggetto con i valori di numero e cariche aggiornati.
 *
 * @param string $nome Nome del personaggio che consuma l'oggetto
 * @param array{
 *  id_oggetto: int,
 *  nome: string,
 *  bonus_car0: int,
 *  bonus_car1: int,
 *  bonus_car2: int,
 *  bonus_car3: int,
 *  bonus_car4: int,
 *  bonus_car5: int,
 *  posizione: int,
 *  numero: int,
 *  cariche: int,
 *  max_cariche: int
 * } $item oggetto equipaggiato dal personaggio recuperato in precedenza
 * @return array{
 *  id_oggetto: int,
 *  nome: string,
 *  bonus_car0: int,
 *  bonus_car1: int,
 *  bonus_car2: int,
 *  bonus_car3: int,
 *  bonus_car4: int,
 *  bonus_car5: int,
 *  posizione: int,
 *  numero: int,
 *  cariche: int,
 *  max_cariche: int
 * }
 */
function gdrcd_chat_player_item_consume($nome, $item)
{
    // Decremento cariche oggetto
    if ($item['cariche'] > 1) {
        gdrcd_stmt(
            'UPDATE clgpersonaggiooggetto
                SET cariche = cariche -1

            WHERE nome = ?
                AND id_oggetto = ?

            LIMIT 1',
            [
                'si',
                $nome,
                $item['id_oggetto']
            ]
        );

        --$item['cariche'];

        return $item;
    }

    // Decremento numero di oggetti
    if ($item['numero'] > 1) {
        gdrcd_stmt(
            'UPDATE clgpersonaggiooggetto
                SET cariche = ?,
                    numero = numero - 1

            WHERE nome = ?
                AND id_oggetto = ?

            LIMIT 1',
            [
                'isi',
                $item['max_cariche'],
                $nome,
                $item['id_oggetto']
            ]
        );

        --$item['numero'];
        $item['cariche'] = $item['max_cariche'];

        return $item;
    }

    // Elimino la riga
    gdrcd_stmt(
        'DELETE FROM clgpersonaggiooggetto WHERE nome = ? AND id_oggetto = ? LIMIT 1',
        [
            'si',
            $nome,
            $item['id_oggetto']
        ]
    );

    --$item['numero'];

    return $item;
}

/**
 * Recupera le informazioni di una specifica razza dal database.
 *
 * Questa funzione esegue una query sulla tabella `razza` per ottenere
 * i dati relativi alla razza identificata dall'id fornito.
 * Restituisce il nome della razza e i bonus alle caratteristiche.
 *
 * @param int $raceId L'ID della razza da recuperare
 * @return null|array{
 *     nome: string,
 *     bonus_car0: int,
 *     bonus_car1: int,
 *     bonus_car2: int,
 *     bonus_car3: int,
 *     bonus_car4: int,
 *     bonus_car5: int
 * } Array associativo con i dati della razza, oppure null se non trovata
 */
function gdrcd_chat_get_race($raceId)
{
    $racial_stmt = gdrcd_stmt(
        'SELECT nome_razza AS nome,
                bonus_car0,
                bonus_car1,
                bonus_car2,
                bonus_car3,
                bonus_car4,
                bonus_car5

        FROM razza

        WHERE id_razza = ?',
        ['i', $raceId]
    );

    if (gdrcd_query($racial_stmt, 'num_rows') === 0) {
        return null;
    }

    $racial_record = gdrcd_query($racial_stmt, 'fetch');
    gdrcd_query($racial_stmt, 'free');

    return $racial_record;

}

/**
 * Recupera tutti i dadi disponibili per la chat.
 *
 * @return array<array{nome: string, facce: int}> Array di dadi
 */
function gdrcd_chat_dice_list()
{
    $PARAMETERS = $GLOBALS['PARAMETERS'];

    $dice = [];

    foreach($PARAMETERS['settings']['skills_dices'] as $dice_name => $dice_value) {
        $dice[] = [
            'nome' => $dice_name,
            'facce' => (int) $dice_value,
        ];
    }

    return $dice;
}

/**
 * Recupera le informazioni di base di un personaggio dal database.
 *
 * Questa funzione esegue una query per ottenere i dati essenziali
 * di un personaggio specifico dalla tabella `personaggio`.
 * Utile per verificare l'esistenza del personaggio e recuperare
 * informazioni come lo stato di salute per controlli di validazione.
 *
 * @param string $nome Il nome del personaggio di cui recuperare le informazioni
 * @return null|array<string, string|int|float> Null se il personaggio non esiste,
 * altrimenti ritorna tutti i dati del personaggio
 */
function gdrcd_chat_character_info($nome)
{
    $stmt = gdrcd_stmt(
        'SELECT * FROM personaggio WHERE nome = ?',
        ['s', $nome]
    );

    if (gdrcd_query($stmt, 'num_rows') === 0) {
        return null;
    }

    $record = gdrcd_query($stmt, 'fetch');
    gdrcd_query($stmt, 'free');
    return $record;
}

/**
 * Imposta il codice di risposta HTTP e stampa un messaggio di errore in formato JSON.
 *
 * @param array{code: int, message: string} $status Array associativo proveniente da gdrcd_chat_status()
 * @return void
 */
function gdrcd_chat_status_output($status)
{
    if (!is_array($status)) {
        $status = gdrcd_chat_status_error((string)$status);
    }

    http_response_code($status['code']);
    echo json_encode([
        'error' => gdrcd_filter_out($status['message'])
    ]);
}

/**
 * Crea un array associativo che rappresenta uno stato di risposta per le API della chat.
 *
 * @param int $code Codice di stato HTTP (es: 201, 400, 403, 500)
 * @param string $message Messaggio descrittivo dello stato
 * @return array{code: int, message: string} Array associativo con codice e messaggio
 */
function gdrcd_chat_status($code, $message)
{
    return [
        'code' => $code,
        'message' => gdrcd_filter_out($message)
    ];
}

/**
 * Indica un operazione andata a buon fine e che ha creato una risorsa ( come un record nel db ).
 * Restituisce uno stato di risposta HTTP 201 (Created) per le API della chat.
 *
 * @param string $message Facoltativo. Messaggio descrittivo dello stato (default: 'Created')
 * @return array{code: int, message: string} Array associativo con codice e messaggio
 */
function gdrcd_chat_status_created($message = 'Created')
{
    return gdrcd_chat_status(201, $message);
}

/**
 * Indica un operazione fallita a causa di dati incorretti forniti dall'utente.
 * Restituisce uno stato di risposta HTTP 400 (Bad Request) per le API della chat.
 *
 * @param string $message Facoltativo. Messaggio descrittivo dello stato (default: 'Invalid')
 * @return array{code: int, message: string} Array associativo con codice e messaggio
 */
function gdrcd_chat_status_invalid($message = 'Invalid')
{
    return gdrcd_chat_status(400, $message);
}

/**
 * Indica un operazione fallita a causa della mancanza dei permessi necessari.
 * Restituisce uno stato di risposta HTTP 403 (Forbidden) per le API della chat.
 *
 * @param string $message Facoltativo. Messaggio descrittivo dello stato (default: 'Forbidden')
 * @return array{code: int, message: string} Array associativo con codice e messaggio
 */
function gdrcd_chat_status_forbidden($message = 'Forbidden')
{
    return gdrcd_chat_status(403, $message);
}

/**
 * Indica un operazione fallita a causa di problemi interni ( come dati inconsistenti nel db).
 * Restituisce uno stato di risposta HTTP 500 (Internal Error) per le API della chat.
 *
 * @param string $message Facoltativo. Messaggio descrittivo dello stato (default: 'Internal Error')
 * @return array{code: int, message: string} Array associativo con codice e messaggio
 */
function gdrcd_chat_status_error($message = 'Internal Error')
{
    return gdrcd_chat_status(500, $message);
}
