<?php
/**
 * Questo file contiene tutte le funzioni specifiche
 * per il funzionamento della chat di GDRCD
 */

/**
 * Converte il codice di esito delle funzioni di chat in un codice di stato HTTP appropriato.
 *
 * Mappa i valori di ritorno delle funzioni di salvataggio messaggi di chat
 * (gdrcd_chat_write_message, gdrcd_chat_use_skillsystem)
 * ai corrispondenti codici di stato HTTP standard per le risposte API.
 *
 * @param int $status Il codice di esito della funzione di chat. Valori supportati:
 *      - 1: operazione completata con successo
 *      - 0: errore nei dati forniti, operazione fallita
 *      - -1: permessi insufficienti per l'operazione
 * @return int Il codice di stato HTTP corrispondente:
 *      - 201 (Created): per $status = 1, messaggio salvato correttamente
 *      - 400 (Bad Request): per $status = 0, dati non validi
 *      - 403 (Forbidden): per $status = -1, permessi insufficienti
 */
function gdrcd_chat_status_to_http_code(int $status)
{
    return match ($status) {
        1 => 201,
        0 => 400,
        -1 => 403,
    };
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
 * @return int 1 se il messaggio viene inserito nel database, 0 se non può essere inserito per qualche errore nei dati forniti, -1 se non si dispone dei permessi per questa tipologia di messaggio
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

        // case GDRCD_CHAT_STATS_TYPE:
        //     return gdrcd_chat_stats_save($message);

        // case GDRCD_CHAT_SKILL_TYPE:
        //     return gdrcd_chat_skill_format($message);

        case GDRCD_CHAT_DICE_TYPE:
            return gdrcd_chat_dice_save($message);

        // case GDRCD_CHAT_ITEM_TYPE:
        //     return gdrcd_chat_item_format($message);

        case GDRCD_CHAT_MASTER_TYPE:
            return gdrcd_chat_master_save($message);

        case GDRCD_CHAT_PNG_TYPE:
            return gdrcd_chat_png_save($tag_o_destinatario, $message);

        case GDRCD_CHAT_IMAGE_TYPE:
            return gdrcd_chat_image_save($message);

        default:
            return 0;
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
 * @return int 1 se il messaggio viene inserito nel database, 0 se non può essere inserito per qualche errore nei dati forniti, -1 se non si dispone dei permessi per questa tipologia di messaggio
 */
function gdrcd_chat_image_save(
    $testo,
    $tipo = GDRCD_CHAT_IMAGE_TYPE,
    $symbol = GDRCD_CHAT_IMAGE_SYMBOL
) {
    // Se non si dispone dei permessi
    if ($_SESSION['permessi'] < GAMEMASTER) {
        return -1;
    }

    // Rimuove il primo carattere se il messaggio inizia col simbolo dedicato
    $testo = gdrcd_chat_strip_message_symbol($testo, $symbol);

    // Se il testo è vuoto l'inserimento fallisce
    if (empty($testo)) {
        return 0;
    }

    gdrcd_chat_db_insert_for_login(
        '',
        $tipo,
        $testo
    );

    return 1;
}

/**
 * Inserisce nella tabella `chat` del database un messaggio di tipo "PNG".
 *
 * @param string $nomepng il nome del png da impersonare
 * @param string $testo il messaggio da salvare
 * @param string $tipo Facoltativo. La tipologia interna con cui salvare il messaggio nel database
 * @param string $symbol Facoltativo. il simbolo da rimuovere se presente come primo carattere
 * @return int 1 se il messaggio viene inserito nel database, 0 se non può essere inserito per qualche errore nei dati forniti, -1 se non si dispone dei permessi per questa tipologia di messaggio
 */
function gdrcd_chat_png_save(
    $nomepng,
    $testo,
    $tipo = GDRCD_CHAT_PNG_TYPE,
    $symbol = GDRCD_CHAT_PNG_SYMBOL
) {
    // Se non si dispone dei permessi
    if ($_SESSION['permessi'] < GAMEMASTER) {
        return -1;
    }

    if (empty($nomepng)) {

        // Se non ho il nomepng prova a cercarlo nel testo del messaggio, in stile $nomepng$
        $escaped_symbol = preg_quote($symbol);

        if (preg_match("#^{$escaped_symbol}([^{$escaped_symbol}]+?){$escaped_symbol}#i", $testo, $match) !== 1) {
            // L'azione è formattata male (non ho il nomepng, il messaggio o entrambi), ritorno fallimento.
            return 0;
        }

        // ripulisce la parte iniziale del messaggio da $nomepng$
        $testo = trim(strtr($testo, [$match[0] => '']));
        $nomepng = $match[1];

    }

    // Se presente, rimuove il simbolo usato per indicare il png dal messaggio
    $testo = gdrcd_chat_strip_message_symbol($testo, $symbol);

    // Se il testo è vuoto l'inserimento fallisce
    if (empty($testo)) {
        return 0;
    }

    // formatta il nome del png per consistenza
    $nomepng = gdrcd_capital_letter($nomepng);

    // inserisco il sussurro in chat
    gdrcd_chat_db_insert_for_login(
        $nomepng,
        $tipo,
        $testo
    );

    return 1;
}

/**
 * Inserisce nella tabella `chat` del database un messaggio di tipo "Master".
 *
 * @param string $testo il messaggio da salvare
 * @param string $tipo Facoltativo. La tipologia interna con cui salvare il messaggio nel database
 * @param string $symbol Facoltativo. il simbolo da rimuovere se presente come primo carattere
 * @return int 1 se il messaggio viene inserito nel database, 0 se non può essere inserito per qualche errore nei dati forniti, -1 se non si dispone dei permessi per questa tipologia di messaggio
 */
function gdrcd_chat_master_save(
    $testo,
    $tipo = GDRCD_CHAT_MASTER_TYPE,
    $symbol = GDRCD_CHAT_MASTER_SYMBOL
) {
    // Se non si dispone dei permessi
    if ($_SESSION['permessi'] < GAMEMASTER) {
        return -1;
    }

    // Rimuove il primo carattere se il messaggio inizia col simbolo dedicato
    $testo = gdrcd_chat_strip_message_symbol($testo, $symbol);

    // Se il testo è vuoto l'inserimento fallisce
    if (empty($testo)) {
        return 0;
    }

    gdrcd_chat_db_insert_for_login(
        '',
        $tipo,
        $testo
    );

    return 1;
}

/**
 * Inserisce nella tabella `chat` del database un messaggio di tipo "Tiro di dado".
 * Il lancio dadi supporta espressioni nel formato: [numero]d[facce][modificatore],[soglia]
 * Esempi validi: d6, 2d20, 3d10+5, 4d8-2,6
 *
 * @param string $testo il messaggio da elaborare contenente l'espressione dei dadi
 * @param string $tipo Facoltativo. La tipologia interna con cui salvare il messaggio nel database
 * @param string $symbol Facoltativo. il simbolo da rimuovere se presente come primo carattere
 * @return int 1 se il messaggio viene inserito nel database, 0 se non può essere inserito per qualche errore nei dati forniti, -1 se non si dispone dei permessi per questa tipologia di messaggio
 */
function gdrcd_chat_dice_save(
    $testo,
    $tipo = GDRCD_CHAT_DICE_TYPE,
    $symbol = GDRCD_CHAT_DICE_SYMBOL
) {
    // Rimuove il primo carattere se il messaggio inizia col simbolo dedicato
    $testo = gdrcd_chat_strip_message_symbol($testo, $symbol);

    // Se il testo è vuoto l'inserimento fallisce
    if (empty($testo)) {
        return 0;
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
        return 0;
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

    return 1;
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
 * @return int 1 se il messaggio viene inserito nel database, 0 se non può essere inserito per qualche errore nei dati forniti, -1 se non si dispone dei permessi per questa tipologia di messaggio
 */
function gdrcd_chat_whisper_save(
    $destinatario,
    $testo,
    $tipo = GDRCD_CHAT_WHISPER_TYPE,
    $symbol = GDRCD_CHAT_WHISPER_SYMBOL
) {
    if (empty($destinatario)) {

        // Se non ho il destinatario prova a cercarlo nel testo del messaggio, in stile @nomeutente@
        $escaped_symbol = preg_quote($symbol);

        if (preg_match("#^{$escaped_symbol}([^{$escaped_symbol}]+?){$escaped_symbol}#i", $testo, $match) !== 1) {
            // Il sussurro è formattato male (non ho il destinatario, il messaggio o entrambi), ritorno fallimento.
            return 0;
        }

        // ripulisce la parte iniziale del messaggio da @nomeutente@
        $testo = trim(strtr($testo, [$match[0] => '']));
        $destinatario = $match[1];

    }

    // Se presente, rimuove il simbolo usato per indicare il sussurro dal messaggio
    $testo = gdrcd_chat_strip_message_symbol($testo, $symbol);

    // Se il testo è vuoto l'inserimento fallisce
    if (empty($testo)) {
        return 0;
    }

    // formatta il nome del destinatario. E' necessario per la ricerca nel database.
    $destinatario = gdrcd_capital_letter($destinatario);

    $stmt = gdrcd_stmt('SELECT nome FROM personaggio WHERE nome = ?', ['s', $destinatario]);

    // se destinatario non esiste nel database, ritorna fallimento
    if (gdrcd_query($stmt, 'num_rows') === 0) {
        return 0;
    }

    gdrcd_query($stmt, 'free');

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

    return 1;
}

/**
 * Inserisce nella tabella `chat` del database un messaggio di tipo "Azione".
 *
 * @param string $tag il tag di locazione
 * @param string $testo il messaggio da salvare
 * @param string $tipo Facoltativo. La tipologia interna con cui salvare il messaggio nel database
 * @param string $symbol Facoltativo. il simbolo da rimuovere se presente come primo carattere
 * @return int 1 se il messaggio viene inserito nel database, 0 se non può essere inserito per qualche errore nei dati forniti, -1 se non si dispone dei permessi per questa tipologia di messaggio
 */
function gdrcd_chat_action_save(
    $tag,
    $testo,
    $tipo = GDRCD_CHAT_ACTION_TYPE,
    $symbol = GDRCD_CHAT_ACTION_SYMBOL
) {
    // Rimuove il primo carattere se il messaggio inizia col simbolo dedicato
    $testo = gdrcd_chat_strip_message_symbol($testo, $symbol);

    // Se il testo è vuoto l'inserimento fallisce
    if (empty($testo)) {
        return 0;
    }

    gdrcd_chat_db_insert_for_login(
        $tag,
        $tipo,
        $testo
    );

    return 1;
}

/**
 * Inserisce nella tabella `chat` del database un messaggio di tipo "Parlato".
 *
 * @param string $tag il tag di locazione
 * @param string $testo il messaggio da salvare
 * @param string $tipo Facoltativo. La tipologia interna con cui salvare il messaggio nel database
 * @param string $symbol Facoltativo. il simbolo da rimuovere se presente come primo carattere
 * @return int 1 se il messaggio viene inserito nel database, 0 se non può essere inserito per qualche errore nei dati forniti, -1 se non si dispone dei permessi per questa tipologia di messaggio
 */
function gdrcd_chat_message_save(
    $tag,
    $testo,
    $tipo = GDRCD_CHAT_MESSAGE_TYPE,
    $symbol = GDRCD_CHAT_MESSAGE_SYMBOL
) {
    // Rimuove il primo carattere se il messaggio inizia col simbolo dedicato
    $testo = gdrcd_chat_strip_message_symbol($testo, $symbol);

    // Se il testo è vuoto l'inserimento fallisce
    if (empty($testo)) {
        return 0;
    }

    gdrcd_chat_db_insert_for_login(
        $tag,
        $tipo,
        $testo
    );

    return 1;
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

        case GDRCD_CHAT_DICE_SYMBOL:
            return GDRCD_CHAT_DICE_TYPE;

        case GDRCD_CHAT_MASTER_SYMBOL:
            return GDRCD_CHAT_MASTER_TYPE;

        case GDRCD_CHAT_IMAGE_SYMBOL:
            return GDRCD_CHAT_IMAGE_TYPE;

        default:
            return GDRCD_CHAT_DEFAULT_TYPE;

    }
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
 *  - nome: nome della chat
 *  - stanza_apparente: nome della chat nei presenti
 *  - invitati: lista nomi invitati separata da virgola
 *  - privata: 1 se la chat è privata, 0 altrimenti
 *  - proprietario: nome del proprietario della chat privata
 *  - scadenza: data scadenza chat privata
 *
 * @param int $luogo
 * @return null|array{
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
 * @return array<array{id_abilita: int, nome: string}> Array di abilità
 */
function gdrcd_chat_player_skills($nome)
{
    $skills = [];

    $stmt = gdrcd_stmt(
        'SELECT id_abilita, nome
        FROM abilita
        WHERE id_razza = -1
            OR id_razza IN(SELECT id_razza FROM personaggio WHERE nome = ?)
        ORDER BY nome',
        ['s', $nome]
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
 * @return array<array{id_oggetto: int, nome: string, cariche: int}> Ogni elemento contiene:
 *               - id_oggetto: ID univoco dell'oggetto
 *               - nome: Nome dell'oggetto
 *               - cariche: Numero di cariche disponibili per l'oggetto
 */
function gdrcd_chat_player_items($nome)
{
    $items = [];

    $stmt = gdrcd_stmt(
        'SELECT
            clgpersonaggiooggetto.id_oggetto,
            oggetto.nome,
            clgpersonaggiooggetto.cariche

        FROM clgpersonaggiooggetto
            JOIN oggetto ON clgpersonaggiooggetto.id_oggetto = oggetto.id_oggetto

        WHERE clgpersonaggiooggetto.nome = ?
            AND posizione > 0

        ORDER BY oggetto.nome',
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
            'facce' => $dice_value,
        ];
    }

    return $dice;
}

/**
 * Settaggio dei SESSION per tag e tipo
 */
function settaTag($tag)
{
    $_SESSION['tag'] = $tag;
}

function settaTipo($tipo)
{
    $_SESSION['tipo_azione']=$tipo;
}

/**
 * invio e lettura  Azione e parlato
  */
function inviaAzione($testo, $tag){
    settaTag($tag);
    settaTipo('A');
    gdrcd_query("INSERT INTO chat ( stanza, imgs, mittente, destinatario, ora, tipo, testo) VALUES (".$_SESSION['luogo'].", '".$_SESSION['sesso'].";".$_SESSION['img_razza']."', '".$_SESSION['login']."', '".gdrcd_capital_letter(gdrcd_filter('in', $tag))."', NOW(), 'A', '{$testo}')");
}

function inviaParlato($testo, $tag){
    settaTag($tag);
    settaTipo('P');
    gdrcd_query("INSERT INTO chat ( stanza, imgs, mittente, destinatario, ora, tipo, testo) VALUES (".$_SESSION['luogo'].", '".$_SESSION['sesso'].";".$_SESSION['img_razza']."', '".$_SESSION['login']."', '".gdrcd_capital_letter(gdrcd_filter('in', $tag))."', NOW(), 'P', '{$testo}')");
}

/**
 * Sussurri
 */
function inviaSussurro($testo, $tag)
{
    $tag=ucfirst($tag);
    switch ($tag){
        case 'Tutti':
            gdrcd_query("INSERT INTO chat ( stanza, imgs, mittente, destinatario, ora, tipo, testo) VALUES (".$_SESSION['luogo'].", '".$_SESSION['sesso'].";".$_SESSION['img_razza']."', '".$_SESSION['login']."', '{$tag}', NOW(), 'S', '{$testo}')");
            break;
        default:
            $r_check_dest = gdrcd_query("SELECT nome FROM personaggio WHERE DATE_ADD(ultimo_refresh, INTERVAL 30 MINUTE) > NOW() AND ultimo_luogo = ".$_SESSION['luogo']." AND nome = '".$tag."' LIMIT 1", 'result');
            if (gdrcd_query($r_check_dest, 'num_rows') < 1)
            {//se non c'è nessuno da notificare
                $testo=$tag.' non è presente ' ;
                $tag=$_SESSION['login'];
            }
            gdrcd_query("INSERT INTO chat ( stanza, imgs, mittente, destinatario, ora, tipo, testo) VALUES (".$_SESSION['luogo'].", '".$_SESSION['sesso'].";".$_SESSION['img_razza']."', '".$_SESSION['login']."', '{$tag}', NOW(), 'S', '{$testo}')");
            break;
    }
    settaTag('');
    settaTipo('');
}

/**
 * invio / lettura Master
 */
function inviaMaster($testo){

    settaTipo('M');
    gdrcd_query("INSERT INTO chat ( stanza, imgs, mittente, destinatario, ora, tipo, testo) VALUES (".$_SESSION['luogo'].", '".$_SESSION['sesso'].";".$_SESSION['img_razza']."', '".$_SESSION['login']."', '', NOW(), 'M', '{$testo}')");
}

/**
 * invio / lettura PNG
 */
function inviaPNG($testo, $tag){

    settaTipo('N');
    gdrcd_query("INSERT INTO chat ( stanza, imgs, mittente, destinatario, ora, tipo, testo) VALUES (".$_SESSION['luogo'].", '".$_SESSION['sesso'].";".$_SESSION['img_razza']."', '".$_SESSION['login']."', '{$tag}', NOW(), 'N', '{$testo}')");
}


/**
 * invio Abilità
 */
function inviaAbilita($abilita)
{
    $PARAMETERS = $GLOBALS['PARAMETERS'];
    $MESSAGE = $GLOBALS['MESSAGE'];
    $actual_healt = gdrcd_query("SELECT salute FROM personaggio WHERE nome = '".$_SESSION['login']."'");


    if($actual_healt['salute'] > 0) {

        $skill = gdrcd_query("SELECT nome, car FROM abilita WHERE id_abilita = " . gdrcd_filter('num', $abilita) . " LIMIT 1");
        $car = gdrcd_query("SELECT car" . gdrcd_filter('num', $skill['car']) . " AS car_now FROM personaggio WHERE nome = '" . $_SESSION['login'] . "' LIMIT 1");
        $bonus = gdrcd_query("SELECT SUM(oggetto.bonus_car" . gdrcd_filter('num', $skill['car']) . ") as bonus FROM oggetto JOIN clgpersonaggiooggetto ON clgpersonaggiooggetto.id_oggetto=oggetto.id_oggetto WHERE clgpersonaggiooggetto.nome='" . $_SESSION['login'] . "' AND clgpersonaggiooggetto.posizione > 1");
        $racial_bonus = gdrcd_query("SELECT bonus_car" . gdrcd_filter('num', $skill['car']) . " AS racial_bonus FROM razza WHERE id_razza IN (SELECT id_razza FROM personaggio WHERE nome='" . $_SESSION['login'] . "')");
        $rank = gdrcd_query("SELECT grado FROM clgpersonaggioabilita WHERE id_abilita=" . gdrcd_filter('num', $abilita) . " AND nome='" . $_SESSION['login'] . "' LIMIT 1");
        if ($PARAMETERS['mode']['dices'] == 'ON') {
            mt_srand((double)microtime() * 1000000);
            $dice = ($_POST['dice'] != 'no_dice') ? $_POST['dice'] : '1';

            $die = mt_rand(1, (int)$dice);

            $chat_dice_msg = gdrcd_filter('in', $MESSAGE['chat']['commands']['use_skills']['die']) . ' ' . gdrcd_filter('num', $die) . ',';
        } else {
            $chat_dice_msg = '';
            $die = 0;
        }
        $car_value = gdrcd_filter('num', $car['car_now']) + gdrcd_filter('num', $racial_bonus['racial_bonus']);
        $carr = gdrcd_filter('num', $car['car_now']) + gdrcd_filter('num', $racial_bonus['racial_bonus']) + gdrcd_filter('num', $die) + gdrcd_filter('num', $rank['grado']) + gdrcd_filter('num', $bonus['bonus']);

        $testo = "{$_SESSION['login']} " . gdrcd_filter('in', $MESSAGE['chat']['commands']['use_skills']['uses']) . " " . gdrcd_filter('in', $skill['nome']) . ": " . gdrcd_filter('in', $PARAMETERS['names']['stats']['car' . $skill['car'] . '']) . " {$car_value}, {$chat_dice_msg} " . gdrcd_filter('in', $MESSAGE['chat']['commands']['use_skills']['ramk']) . " " . gdrcd_filter('num', $rank['grado']) . ", " . gdrcd_filter('in', $MESSAGE['chat']['commands']['use_skills']['items']) . " " . gdrcd_filter('num', $bonus['bonus']) . ", " . gdrcd_filter('in', $MESSAGE['chat']['commands']['use_skills']['sum']) . " {$carr}";
        gdrcd_query("INSERT INTO chat ( stanza, imgs, mittente, destinatario, ora, tipo, testo ) VALUES (" . $_SESSION['luogo'] . ", '" . $_SESSION['sesso'] . ";" . $_SESSION['img_razza'] . "', '" . $_SESSION['login'] . "', '', NOW(), 'C', '{$testo}')");


        }
    else {
       gdrcd_query("INSERT INTO chat ( stanza, imgs, mittente, destinatario, ora, tipo, testo ) VALUES (".$_SESSION['luogo'].", '".$_SESSION['sesso'].";".$_SESSION['img_razza']."', '".$_SESSION['login']."', '".gdrcd_capital_letter(gdrcd_filter('in', $_SESSION['login'])."', NOW(), 'S', '".gdrcd_filter('in', $MESSAGE['status_pg']['exausted'])."')"));
    }

}

function inviaStatistica($statistica,$dado)
{
    $PARAMETERS = $GLOBALS['PARAMETERS'];
    $MESSAGE = $GLOBALS['MESSAGE'];
    mt_srand((double) microtime() * 1000000);
    $die = mt_rand(1, gdrcd_filter('num', (int) $dado));
    $id_stats = explode('_', $statistica);
    $car = gdrcd_query("SELECT car".gdrcd_filter('num', $id_stats[1])." AS car_now FROM personaggio WHERE nome = '".$_SESSION['login']."' LIMIT 1");
    $racial_bonus = gdrcd_query("SELECT bonus_car".gdrcd_filter('num', $id_stats[1])." AS racial_bonus FROM razza WHERE id_razza IN (SELECT id_razza FROM personaggio WHERE nome='".$_SESSION['login']."')");
    $car=gdrcd_filter('num', $car['car_now'] + $racial_bonus['racial_bonus']);
    $carr=gdrcd_filter('num', $car) + gdrcd_filter('num', $die) ;
    $testo="{$_SESSION['login']} ".gdrcd_filter('in', $MESSAGE['chat']['commands']['use_skills']['uses'])." ".gdrcd_filter('in', $PARAMETERS['names']['stats']['car'.$id_stats[1]]).": ".gdrcd_filter('in', $PARAMETERS['names']['stats']['car'.$id_stats[1].''])." {$car}, ".gdrcd_filter('in', $MESSAGE['chat']['commands']['use_skills']['die'])." " .gdrcd_filter('num', $die).", ".gdrcd_filter('in', $MESSAGE['chat']['commands']['use_skills']['sum'])."{$carr}";
    gdrcd_query("INSERT INTO chat ( stanza, imgs, mittente, destinatario, ora, tipo, testo ) VALUES (".$_SESSION['luogo'].", '".$_SESSION['sesso'].";".$_SESSION['img_razza']."', '".$_SESSION['login']."', '', NOW(), 'C', '{$testo}')");
}

function inviaDado($dado)
{
    $MESSAGE = $GLOBALS['MESSAGE'];
    mt_srand((double) microtime() * 1000000);
    $die = mt_rand(1, gdrcd_filter('num', $dado));
    gdrcd_query("INSERT INTO chat ( stanza, imgs, mittente, destinatario, ora, tipo, testo ) VALUES (".$_SESSION['luogo'].", '".$_SESSION['sesso'].";".$_SESSION['img_razza']."', '".$_SESSION['login']."', '', NOW(), 'D', '".$_SESSION['login'].' '.gdrcd_filter('in', $MESSAGE['chat']['commands']['die']['cast']).gdrcd_filter('num', $dado).': '.gdrcd_filter('in', $MESSAGE['chat']['commands']['die']['sum']).' '.gdrcd_filter('num', $die)."')");
}
function inviaOggetto($oggetto)
{
    $MESSAGE = $GLOBALS['MESSAGE'];
    $item = gdrcd_filter('num',$oggetto);
    $me = gdrcd_filter('in',$_SESSION['login']);
    $data = gdrcd_query("
                        SELECT oggetto.nome,oggetto.cariche AS new_cariche, clgpersonaggiooggetto.cariche,clgpersonaggiooggetto.numero
                        FROM  oggetto
                            LEFT JOIN clgpersonaggiooggetto
                        ON clgpersonaggiooggetto.id_oggetto = oggetto.id_oggetto
                        WHERE oggetto.id_oggetto='{$item}'
                          AND clgpersonaggiooggetto.nome='{$me}' LIMIT 1");
    // Informazioni dell'oggetto
    $nomeOggetto = gdrcd_filter_out($data['nome']);
    $cariche = gdrcd_filter('num',$data['cariche']);
    $numero = gdrcd_filter('num',$data['numero']);
    $new_cariche = gdrcd_filter('num',$data['new_cariche']);

    # Se ho meno di una carica
    if($cariche <= 1){

        # Se ho un solo oggetto
        if($numero == 1){

            # Cancello la riga
            $query = "DELETE FROM clgpersonaggiooggetto WHERE nome ='{$me}' AND id_oggetto='{$item}' LIMIT 1";
        }
        # Se ho piu' oggetti
        else{

            # Ricarico le cariche e scalo il numro di oggetti
            $query = "UPDATE clgpersonaggiooggetto
                                    SET cariche = '{$new_cariche}', numero = numero - 1
                                WHERE nome ='{$me}' AND id_oggetto='{$item}' LIMIT 1";
        }
    }
    # SE ho piu' di una sola carica
    else{
        $query = "UPDATE clgpersonaggiooggetto SET cariche = cariche -1 WHERE nome ='{$me}' AND id_oggetto='{$item}' LIMIT 1";
    }

    gdrcd_query($query);

    gdrcd_query("INSERT INTO chat ( stanza, imgs, mittente, destinatario, ora, tipo, testo ) VALUES (".$_SESSION['luogo'].", '".$_SESSION['sesso'].";".$_SESSION['img_razza']."', '".$_SESSION['login']."', '', NOW(), 'O', '".$_SESSION['login'].' '.gdrcd_filter('in', $MESSAGE['chat']['commands']['die']['item']).': '.gdrcd_filter('in', $nomeOggetto)."')");

}
function Invita($tag)
{
    $MESSAGE = $GLOBALS['MESSAGE'];
    $info = gdrcd_query("SELECT invitati, nome, proprietario FROM mappa WHERE id=".$_SESSION['luogo']."");
    $ok_command = false;
    if($info['proprietario'] == $_SESSION['login'] || strpos($_SESSION['gilda'], $info['proprietario']) != false) {
        $ok_command = true;
    }
    gdrcd_query("UPDATE mappa SET invitati = '".$info['invitati'].','.gdrcd_capital_letter(strtolower(gdrcd_filter('in', $tag)))."' WHERE id=".$_SESSION['luogo']." LIMIT 1");
    gdrcd_query("INSERT INTO chat ( stanza, mittente, destinatario, ora, tipo, testo ) VALUES (".$_SESSION['luogo'].", 'System message', '".$_SESSION['login']."', NOW(), 'S', '".gdrcd_capital_letter(gdrcd_filter('in', $tag)).' '.$MESSAGE['chat']['warning']['invited']."')");
    if(empty($_POST['tag']) === false) {
        gdrcd_query("INSERT INTO messaggi ( mittente, destinatario, spedito, letto, testo ) VALUES ('System message', '".gdrcd_capital_letter(gdrcd_filter('in', $tag))."', NOW(), 0,  '".$_SESSION['login'].' '.$MESSAGE['chat']['warning']['invited_message'].' '.$info['nome']."')");
    }
}

function Leave($tag_n_beyond)
{
    $MESSAGE = $GLOBALS['MESSAGE'];
    $info = gdrcd_query("SELECT invitati, nome, proprietario FROM mappa WHERE id=".$_SESSION['luogo']."");

    $ok_command = false;
    if($info['proprietario'] == $_SESSION['login'] || strpos($_SESSION['gilda'], $info['proprietario']) != false) {
        $ok_command = true;
    }
    if($ok_command === true){
        $scaccia = str_replace(','.gdrcd_capital_letter(gdrcd_filter('in', $tag_n_beyond)), '', $info['invitati']);
        gdrcd_query("UPDATE mappa SET invitati = '".$scaccia."' WHERE id=".$_SESSION['luogo']." LIMIT 1");
        gdrcd_query("INSERT INTO chat ( stanza, mittente, destinatario, ora, tipo, testo ) VALUES (".$_SESSION['luogo'].", 'System message', '".$_SESSION['login']."', NOW(), 'S', '".gdrcd_capital_letter(gdrcd_filter('in', $tag_n_beyond)).' '.$MESSAGE['chat']['warning']['expelled']."')");
    }

}
function Elenco()
{
    $MESSAGE = $GLOBALS['MESSAGE'];
    $info = gdrcd_query("SELECT invitati, nome, proprietario FROM mappa WHERE id=".$_SESSION['luogo']."");
    $ok_command = false;
    if($info['proprietario'] == $_SESSION['login'] || strpos($_SESSION['gilda'], $info['proprietario']) != false) {
        $ok_command = true;
    }
    if($ok_command === true){
        $ospiti = str_replace(',', '', $info['invitati']);
        gdrcd_query("INSERT INTO chat ( stanza, mittente, destinatario, ora, tipo, testo ) VALUES (".$_SESSION['luogo'].", 'System message', '".$_SESSION['login']."', NOW(), 'S', '".$MESSAGE['chat']['warning']['list'].': '.$ospiti."')");
    }

}
