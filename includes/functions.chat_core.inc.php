<?php
/**
 * Questo file contiene tutte le funzioni della chat
 * comuni a lettura e scrittura
 */

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
function gdrcd_chat_room_is_login_allowed($luogo)
{
    if ($luogo === null) {
        return false;
    }

    $info = is_array($luogo)
        ? $luogo
        : (gdrcd_chat_room_info($luogo) ?? []);

    if (($info['privata'] ?? 0) != 1) {
        // chat pubblica: sempre accessibile
        return true;
    }

    $invitati = explode(',', $info['invitati']);
    $login_moderator_o_superiore = $_SESSION['permessi'] >= MODERATOR;
    $login_proprietario_chat = $info['proprietario'] == $_SESSION['id_personaggio'];
    $login_invitato_chat = in_array($_SESSION['id_personaggio'], $invitati);
    $chat_scaduta = time() >= strtotime($info['scadenza']);

    // chat privata scaduta: se non sei moderator o maggiore, non sei abilitato
    if ($chat_scaduta && !$login_moderator_o_superiore) {
        return false;
    }

    // chat privata ancora valida: accessibile solo nei seguenti casi
    return $login_moderator_o_superiore
        || $login_proprietario_chat
        || $login_invitato_chat;
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
function gdrcd_chat_room_is_login_owner($luogo)
{
    // Recupero le informazioni sulla chat corrente
    $info = is_array($luogo) ? $luogo : gdrcd_chat_room_info($luogo);
    $chat_scaduta = time() >= strtotime($info['scadenza']);

    // Se la chat non è privata oppure è scaduta nessuno può esserne il proprietario
    if ($info['privata'] != 1 || $chat_scaduta) {
        return false;
    }

    $login_proprietario_chat = $info['proprietario'] == $_SESSION['id_personaggio'];
    $gilda_proprietaria_chat = is_numeric($info['proprietario'])
        && str_contains($_SESSION['gilda'], (string)$info['proprietario']);

    // Altrimenti si può essere proprietari se la chat è associata allo specifico personaggio
    // oppure se la chat appartiene alla gilda di cui fa parte il personaggio
    return $login_proprietario_chat || $gilda_proprietaria_chat;
}

/**
 * Ritorna il nome della chat
 *
 * @param null|int|array{
 *  id: int,
 *  nome: ?string,
 *  stanza_apparente: ?string,
 *  invitati: string,
 *  privata: int,
 *  proprietario: ?string,
 *  scadenza: ?string,
 * } $luogo
 * @return null|string
 */
function gdrcd_chat_room_name($luogo)
{
    if ($luogo === null) {
        return false;
    }

    $info = is_int($luogo)
        ? gdrcd_chat_room_info($luogo) ?? []
        : $luogo;

    return $info['nome'] ?? null;
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
function gdrcd_chat_room_info($luogo)
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

    $record = gdrcd_query($stmt, 'assoc');
    gdrcd_query($stmt, 'free');
    return $record;
}

/**
 * Recupera tutte le abilità disponibili per un giocatore specifico.
 * Include le abilità universali (id_razza = -1) e quelle specifiche
 * della razza del personaggio.
 *
 * @param int $id_personaggio ID del personaggio per cui recuperare le abilità
 * @return array<array{
 *  id_abilita: int,
 *  nome: string,
 *  car: int,
 *  grado: null|int
 * }> Array di abilità
 */
function gdrcd_chat_player_skills($id_personaggio)
{
    $skills = [];

    $stmt = gdrcd_stmt(
        'SELECT abilita.id_abilita,
                abilita.nome,
                abilita.car,
                clgpersonaggioabilita.grado

        FROM abilita
            LEFT JOIN clgpersonaggioabilita
                ON (
                    clgpersonaggioabilita.id_abilita = abilita.id_abilita
                    AND clgpersonaggioabilita.id_personaggio = ?
                )

        WHERE abilita.id_razza = -1
            OR abilita.id_razza = (SELECT id_razza FROM personaggio WHERE id_personaggio = ?)

        ORDER BY abilita.nome',
        ['ii', $id_personaggio, $id_personaggio]
    );

    if (gdrcd_query($stmt, 'num_rows') > 0) {
        while ($row = gdrcd_query($stmt, 'assoc')) {
            $skills[] = $row;
        }

        gdrcd_query($stmt, 'free');
    }

    return $skills;
}

/**
 * Recupera le informazioni di un abilità di un personaggio specifico dal database.
 *
 * Questa funzione chiama internamente gdrcd_chat_player_skills e ritorna
 * l'abilità identificata dallo $skillId fornito
 *
 * @param int $id_personaggio ID del personaggio per cui recuperare le abilità
 * @param int $skillId L'ID dell'abilità da recuperare
 * @return null|array{
 *  id_abilita: int,
 *  nome: string,
 *  car: int,
 *  grado: null|int
 * } Array associativo con i dati dell'abilità, oppure null se non trovata
 */
function gdrcd_chat_player_skill($id_personaggio, $skillId)
{
    $skills = gdrcd_chat_player_skills($id_personaggio);

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
 * @param int $id_personaggio ID del personaggio per cui recuperare gli oggetti
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
function gdrcd_chat_player_items($id_personaggio)
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

        WHERE clgpersonaggiooggetto.id_personaggio = ?
            AND clgpersonaggiooggetto.posizione > 1

        ORDER BY clgpersonaggiooggetto.posizione',
        ['i', $id_personaggio]
    );

    if (gdrcd_query($stmt, 'num_rows') > 0) {
        while ($row = gdrcd_query($stmt, 'assoc')) {
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
 * @param int $id_personaggio Nome del personaggio per cui recuperare l'oggetto
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
function gdrcd_chat_player_item($id_personaggio, $itemId)
{
    $items = gdrcd_chat_player_items($id_personaggio);

    foreach ($items as $item) {
        if ($item['id_oggetto'] == $itemId) {
            return $item;
        }
    }

    return null;
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

    $racial_record = gdrcd_query($racial_stmt, 'assoc');
    gdrcd_query($racial_stmt, 'free');

    return $racial_record;
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
function gdrcd_chat_player_info($id_personaggio)
{
    $stmt = gdrcd_stmt(
        'SELECT * FROM personaggio WHERE id_personaggio = ?',
        ['i', $id_personaggio]
    );

    if (gdrcd_query($stmt, 'num_rows') === 0) {
        return null;
    }

    $record = gdrcd_query($stmt, 'assoc');
    gdrcd_query($stmt, 'free');
    return $record;
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

    foreach($PARAMETERS['settings']['skills_dices']['faces'] as $dice_name => $dice_value) {
        $dice[] = [
            'nome' => $dice_name,
            'facce' => (int) $dice_value,
        ];
    }

    return $dice;
}

/**
 * Imposta il codice di risposta HTTP e stampa un messaggio di output in formato JSON.
 *
 * @param array{code: int, message: mixed} $status Array associativo proveniente da gdrcd_chat_status()
 * @return void
 */
function gdrcd_chat_output($status)
{
    if (!is_array($status)) {
        $status = gdrcd_chat_status_error((string)$status);
    }

    $code = $status['code'] ?? 500;
    $message = $status['message'] ?? $GLOBALS['MESSAGE']['chat']['error']['unknown_error'];

    header('Content-type: application/json;charset=utf-8');
    http_response_code($code);
    echo json_encode([
        'code' => $code,
        'message' => $message,
    ]);
}

/**
 * Crea un array associativo che rappresenta uno stato di risposta per le API della chat.
 *
 * @param int $code Codice di stato HTTP (es: 201, 400, 403, 500)
 * @param string|array $message Messaggio descrittivo dello stato o dati di output
 * @return array{code: int, message: mixed} Array associativo con codice e messaggio
 */
function gdrcd_chat_status($code, $message)
{
    return [
        'code' => $code,
        'message' => $message
    ];
}

/**
 * Indica un'operazione completata con successo.
 * Restituisce uno stato di risposta HTTP 200 (OK) per le API della chat.
 *
 * @param string $message Messaggio descrittivo dello stato
 * @return array{code: int, message: mixed} Array associativo con codice e risposta
 */
function gdrcd_chat_status_ok($message)
{
    return gdrcd_chat_status(200, $message);
}

/**
 * Indica un operazione andata a buon fine e che ha creato una risorsa ( come un record nel db ).
 * Restituisce uno stato di risposta HTTP 201 (Created) per le API della chat.
 *
 * @param string $message Facoltativo. Messaggio descrittivo dello stato (default: 'Created')
 * @return array{code: int, message: mixed} Array associativo con codice e messaggio
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
 * @return array{code: int, message: mixed} Array associativo con codice e messaggio
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
 * @return array{code: int, message: mixed} Array associativo con codice e messaggio
 */
function gdrcd_chat_status_forbidden($message = 'Forbidden')
{
    return gdrcd_chat_status(403, $message);
}

/**
 * Indica che la risorsa richiesta non è stata trovata.
 * Restituisce uno stato di risposta HTTP 404 (Not Found) per le API della chat.
 *
 * @param string $message Facoltativo. Messaggio descrittivo dello stato (default: 'Not Found')
 * @return array{code: int, message: mixed} Array associativo con codice e messaggio
 */
function gdrcd_chat_status_notfound($message = 'Not Found')
{
    return gdrcd_chat_status(403, $message);
}

/**
 * Indica un operazione fallita a causa di problemi interni (come dati inconsistenti nel db).
 * Restituisce uno stato di risposta HTTP 500 (Internal Error) per le API della chat.
 *
 * @param string $message Facoltativo. Messaggio descrittivo dello stato (default: 'Internal Error')
 * @return array{code: int, message: mixed} Array associativo con codice e messaggio
 */
function gdrcd_chat_status_error($message = 'Internal Error')
{
    return gdrcd_chat_status(500, $message);
}

/**
 * Indica che la funzionalità richiesta non è implementata.
 * Restituisce uno stato di risposta HTTP 501 (Not Implemented) per le API della chat.
 *
 * @param string $message Facoltativo. Messaggio descrittivo dello stato (default: 'Not Implemented')
 * @return array{code: int, message: mixed} Array associativo con codice e messaggio
 */
function gdrcd_chat_status_notimplemented($message = 'Not Implemented')
{
    return gdrcd_chat_status(501, $message);
}
