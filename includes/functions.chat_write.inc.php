<?php
/**
 * Questo file contiene tutte le funzioni della chat
 * responsabili per la scrittura delle azioni e per
 * l'utilizzo dello skillsystem
 */

/**
 * Gestisce il salvataggio nel database di un messaggio di chat in base alla tipologia specificata o dedotta.
 * Se il tipo non è specificato, viene determinato automaticamente dal primo carattere del messaggio.
 * Supporta diversi tipi di messaggi: azione, sussurro, dadi, master, PNG e immagine.
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
 * Gestisce l'utilizzo del sistema di abilità tramite chat.
 *
 * @param int $id_ab ID dell'abilità da utilizzare.
 * @return array{code: int, message: string} $status
 */
function gdrcd_chat_use_skill($id_ab)
{
    return gdrcd_chat_write_message(GDRCD_CHAT_SKILL_SYMBOL . $id_ab);
}

/**
 * Gestisce l'utilizzo del sistema di caratteristiche tramite chat.
 *
 * @param int $id_stats ID della caratteristica da utilizzare.
 * @return array{code: int, message: string} $status
 */
function gdrcd_chat_use_stats($id_stats)
{
    return gdrcd_chat_write_message(GDRCD_CHAT_STATS_SYMBOL . $id_stats);
}

/**
 * Gestisce il lancio di dadi tramite chat.
 *
 * @param int $dice Numero di facce del dado da lanciare.
 * @return array{code: int, message: string} $status
 */
function gdrcd_chat_use_dice($dice, $number = 1, $modifier = 0, $threshold = 0)
{
    $diceNumber = (int)$number > 1 ? $number : '';
    $diceString = $diceNumber .'d'. $dice;

    if ($modifier) {
        $diceString .= ($modifier > 0 ? '+' : '') . $modifier;
    }

    if ($threshold) {
        $diceString .= ','. $threshold;
    }

    return gdrcd_chat_write_message(GDRCD_CHAT_DICE_SYMBOL . $diceString);
}

/**
 * Gestisce l'utilizzo di un oggetto tramite chat.
 *
 * @param int $id_item ID dell'oggetto da utilizzare.
 * @return array{code: int, message: string|array} $status
 */
function gdrcd_chat_use_item($id_item)
{
    return gdrcd_chat_write_message(GDRCD_CHAT_ITEM_SYMBOL . $id_item);
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

    // Tutto a buon fine: status "created" è il modo per indicare che l'operazione ha creato dati nel db
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

    // Tutto a buon fine: status "created" è il modo per indicare che l'operazione ha creato dati nel db
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

    // Assegna esperienza
    gdrcd_chat_assign_experience($testo);

    // Tutto a buon fine: status "created" è il modo per indicare che l'operazione ha creato dati nel db
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
    $PARAMETERS = $GLOBALS['PARAMETERS'];
    $MESSAGE = $GLOBALS['MESSAGE'];

    // Rimuove il primo carattere se il messaggio inizia col simbolo dedicato
    $testo = gdrcd_chat_strip_message_symbol($testo, $symbol);

    // Se il testo è vuoto l'inserimento fallisce
    if (empty($testo)) {
        return gdrcd_chat_status_invalid($MESSAGE['chat']['error']['empty_message']);
    }

    // Le seguenti espressioni regolari servono ad individuare le diverse
    // componenti di una stringa di testo così formata: 5d10+6,7
    // - Il 5 iniziale è il numero di dadi da lanciare. Facoltativo. ($dice_number_regex)
    // - Il 10 dopo la "d" rappresenta il numero di facce dei dadi da lanciare. ($dice_faces_regex)
    // - Il +6 è un modificatore che si somma al totale, può essere anche negativo. Facoltativo. ($dice_modifier_regexp)
    // - Il 7 seguito da una virgola permette di evidenziare i dadi che hanno raggiunto o superato quel valore. Facoltativo. ($dice_threshold_regex)

    $dice_number_regex = '[0-9]+';
    $dice_faces_regex = '[0-9]+';
    $dice_modifier_regexp = '(?:\+|-)[0-9]+';
    $dice_threshold_regex = '[0-9]+';

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

    // Verifica che il numero di facce selezionato sia consentito
    $allowed_dice_faces = array_values($PARAMETERS['settings']['skills_dices']['faces']);

    if (!in_array($dice_faces, $allowed_dice_faces)) {
        return gdrcd_chat_status_invalid(
            gdrcd_filter_out(
                $MESSAGE['chat']['error']['invalid_dice_faces']
                . ' '
                . $MESSAGE['chat']['error']['dice_allowed_values']
                . ' '
                . implode(', ', $allowed_dice_faces)
            )
        );
    }

    // Verifica che il numero di dadi da lanciare sia consentito@return string|int|false
    if ($dice_number < 1 || $dice_number > $PARAMETERS['settings']['skills_dices']['max_number']) {
        return gdrcd_chat_status_invalid(
            gdrcd_filter_out(
                $MESSAGE['chat']['error']['invalid_dice_number']
                . ' '
                . $MESSAGE['chat']['error']['dice_allowed_values']
                . ' 1 ... '
                . $PARAMETERS['settings']['skills_dices']['max_number']
            )
        );
    }

    // Verifica che il valore del modificatore sia consentito
    if (
        $dice_modifier !== null
        && (
            (int)$dice_modifier < $PARAMETERS['settings']['skills_dices']['min_modifier']
            || (int)$dice_modifier > $PARAMETERS['settings']['skills_dices']['max_modifier']
        )
    ) {
        return gdrcd_chat_status_invalid(
            gdrcd_filter_out(
                $MESSAGE['chat']['error']['invalid_dice_modifier']
                . ' '
                . $MESSAGE['chat']['error']['dice_allowed_values']
                . ' '
                . $PARAMETERS['settings']['skills_dices']['min_modifier']
                . ' ... '
                . $PARAMETERS['settings']['skills_dices']['max_modifier']
            )
        );
    }

    // Verifica che il valore della soglia successi sia consentito
    if (
        $dice_threshold !== null
        && (
            $dice_threshold < 0
            || $dice_threshold > $dice_faces
        )
    ) {
        return gdrcd_chat_status_invalid(
            gdrcd_filter_out(
                $MESSAGE['chat']['error']['invalid_dice_threshold']
                . ' '
                . $MESSAGE['chat']['error']['dice_allowed_values']
                . ' 1 ... '
                . $dice_faces
            )
        );
    }

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

    // Somma il valore di tutti i dadi lanciati
    $sum = array_sum($rolls);

    if ($dice_modifier) {
        // Aggiunge il modificatore alla somma dei dadi
        $sum += (int)$dice_modifier;
    }

    // salva tutti i dati relativi al lancio dei dadi in un array
    $result = [
        'expression' => $testo,
        'number' => $dice_number,
        'faces' => $dice_faces,
        'modifier' => $dice_modifier,
        'threshold' => $dice_threshold,
        'successes' => $dice_threshold !== null? $successes : null,
        'sum' => $sum,
        'rolls' => $rolls
    ];

    // inserisce nel database l'array convertito in json con tutti i dati sul risultato dei lanci
    gdrcd_chat_db_insert_for_login(
        '',
        $tipo,
        json_encode($result)
    );

    // Tutto a buon fine: status "created" è il modo per indicare che l'operazione ha creato dati nel db
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

    $personaggio = gdrcd_chat_player_info($_SESSION['login']);
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

    // Tutto a buon fine: status "created" è il modo per indicare che l'operazione ha creato dati nel db
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
    $personaggio = gdrcd_chat_player_info($_SESSION['login']);

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

    $skillDice = $PARAMETERS['settings']['skill_dice'];

    // Se i dadi sono abilitati e l'abilità ha un tipo di dado associato
    if ($PARAMETERS['mode']['dices'] == 'ON' && !empty($skillDice)) {

        // lancio effettivo del dado associato alla skill
        $die = random_int(1, (int) $skillDice);

        // recupera il nome del dado filtrando da gdrcd_chat_dice_list
        // il record che ha un valore di "facce" pari al dado configurato per la skill
        $dice = array_filter(
            gdrcd_chat_dice_list(),
            fn($dice) => $dice['facce'] === (int) $skillDice
        );

        // se ho trovato qualcosa allora assegno il nome rilevato
        // nel caso la definizione non esista assegno al numero di facce il prefisso 'd' come nome di default
        $die_name = !empty($dice)
            ? current($dice)['nome']
            : 'd'. (int) $skillDice;

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

    // Tutto a buon fine: status "created" è il modo per indicare che l'operazione ha creato dati nel db
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
 * @return array{code: int, message: string|array} $status Array associativo proveniente da gdrcd_chat_status()
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

    // Tutto a buon fine: status "created" è il modo per indicare che l'operazione ha creato dati nel db
    // In più inviamo nel messaggio in uscita l'elenco aggiornato degli oggetti del giocatore
    return gdrcd_chat_status_created(
        ['items' => gdrcd_chat_player_items($_SESSION['login'])]
    );
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
    $personaggio = gdrcd_chat_player_info($destinatario);

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

    // Tutto a buon fine: status "created" è il modo per indicare che l'operazione ha creato dati nel db
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

    // Assegna esperienza
    gdrcd_chat_assign_experience($testo);

    // Tutto a buon fine: status "created" è il modo per indicare che l'operazione ha creato dati nel db
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
    $info = gdrcd_chat_room_info($_SESSION['luogo']);

    // Se l'utente connesso non ha i permessi per procedere, usciamo subito
    if (!gdrcd_chat_room_is_login_owner($info)) {
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
    $personaggio = gdrcd_chat_player_info($destinatario);

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
        'invited' => $destinatario,
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
    $info = gdrcd_chat_room_info($_SESSION['luogo']);

    // Se l'utente connesso non ha i permessi per procedere, usciamo subito
    if (!gdrcd_chat_room_is_login_owner($info)) {
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
        'kicked' => $destinatario,
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
    $info = gdrcd_chat_room_info($_SESSION['luogo']);

    // Se l'utente connesso non ha i permessi per procedere, usciamo subito
    if (!gdrcd_chat_room_is_login_owner($info)) {
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
 * Assegna esperienza al personaggio in base al messaggio inviato in chat.
 *
 * La quantità di esperienza assegnata dipende dalla lunghezza del messaggio e dalla configurazione
 * dei parametri globali. Se la funzionalità è disattivata o se il messaggio è inviato in una chat privata
 * quando il flag $PARAMETERS['mode']['exp_in_private'] è disattivato, la funzione non esegue alcuna operazione.
 * L'esperienza viene salvata direttamente nel database del personaggio loggato.
 *
 * @param string $message Il messaggio inviato in chat su cui calcolare l'esperienza da assegnare
 * @return void
 */
function gdrcd_chat_assign_experience($message)
{
    $PARAMETERS = $GLOBALS['PARAMETERS'];

    // Se da config la funzionalità dell'esperienza in chat è disattivata, usciamo senza far nulla
    if($PARAMETERS['mode']['exp_by_chat'] != 'ON') {
        return;
    }

    $msg_length = strlen($message);
    $char_needed = gdrcd_filter('num', $PARAMETERS['settings']['exp_by_chat']['number']);
    $exp_assign = gdrcd_filter('num', $PARAMETERS['settings']['exp_by_chat']['value']);

    if ($char_needed == 0) {

        // A zero caratteri necessari, l'esperienza viene sempre assegnata
        $exp_bonus = $exp_assign;

    } else {

        // Se non ho un valore fisso di esperienza da assegnare, lo calcolo come lunghezza/caratteri_necessari
        // Altrimenti assegna il bonus fisso se la lunghezza sel messaggio raggiunge il numero di caratteri
        $exp_bonus = $exp_assign <= 0
            ? $msg_length / $char_needed
            : ( $msg_length >= $char_needed ? $exp_assign : 0);

    }

    // Recupero le informazioni sulla chat corrente
    $info = gdrcd_chat_room_info($_SESSION['luogo']);
    $chat_privata = $info['privata'] == 1;
    $exp_in_chat_privata_disattivata = $PARAMETERS['mode']['exp_in_private'] != 'ON';

    // Se non è permesso assegnare esperienza nelle chat private esco
    if ( $chat_privata && $exp_in_chat_privata_disattivata ) {
        return;
    }

    // Salva a database l'esperienza assegnata
    gdrcd_stmt(
        'UPDATE personaggio SET esperienza = esperienza + ? WHERE nome = ? LIMIT 1',
        [
            'ds',
            $exp_bonus,
            $_SESSION['login'],
        ]
    );
}
