<?php
/**
 * Questo file contiene tutte le funzioni utili per le richieste xhr
 */

/**
 * Imposta il codice di risposta HTTP e stampa un messaggio di output in formato JSON.
 *
 * @param array{code: int, message: mixed} $status Array associativo proveniente da gdrcd_api_status()
 * @return void
 */
function gdrcd_api_output($status)
{
    if (!is_array($status)) {
        $status = gdrcd_api_status_error((string)$status);
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
function gdrcd_api_status($code, $message)
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
function gdrcd_api_status_ok($message)
{
    return gdrcd_api_status(200, $message);
}

/**
 * Indica un operazione andata a buon fine e che ha creato una risorsa ( come un record nel db ).
 * Restituisce uno stato di risposta HTTP 201 (Created) per le API della chat.
 *
 * @param string $message Facoltativo. Messaggio descrittivo dello stato (default: 'Created')
 * @return array{code: int, message: mixed} Array associativo con codice e messaggio
 */
function gdrcd_api_status_created($message = 'Created')
{
    return gdrcd_api_status(201, $message);
}

/**
 * Indica un operazione fallita a causa di dati incorretti forniti dall'utente.
 * Restituisce uno stato di risposta HTTP 400 (Bad Request) per le API della chat.
 *
 * @param string $message Facoltativo. Messaggio descrittivo dello stato (default: 'Invalid')
 * @return array{code: int, message: mixed} Array associativo con codice e messaggio
 */
function gdrcd_api_status_invalid($message = 'Invalid')
{
    return gdrcd_api_status(400, $message);
}

/**
 * Indica un operazione fallita a causa della mancanza dei permessi necessari.
 * Restituisce uno stato di risposta HTTP 403 (Forbidden) per le API della chat.
 *
 * @param string $message Facoltativo. Messaggio descrittivo dello stato (default: 'Forbidden')
 * @return array{code: int, message: mixed} Array associativo con codice e messaggio
 */
function gdrcd_api_status_forbidden($message = 'Forbidden')
{
    return gdrcd_api_status(403, $message);
}

/**
 * Indica che la risorsa richiesta non è stata trovata.
 * Restituisce uno stato di risposta HTTP 404 (Not Found) per le API della chat.
 *
 * @param string $message Facoltativo. Messaggio descrittivo dello stato (default: 'Not Found')
 * @return array{code: int, message: mixed} Array associativo con codice e messaggio
 */
function gdrcd_api_status_notfound($message = 'Not Found')
{
    return gdrcd_api_status(404, $message);
}

/**
 * Indica un operazione fallita a causa di problemi interni (come dati inconsistenti nel db).
 * Restituisce uno stato di risposta HTTP 500 (Internal Error) per le API della chat.
 *
 * @param string $message Facoltativo. Messaggio descrittivo dello stato (default: 'Internal Error')
 * @return array{code: int, message: mixed} Array associativo con codice e messaggio
 */
function gdrcd_api_status_error($message = 'Internal Error')
{
    return gdrcd_api_status(500, $message);
}

/**
 * Indica che la funzionalità richiesta non è implementata.
 * Restituisce uno stato di risposta HTTP 501 (Not Implemented) per le API della chat.
 *
 * @param string $message Facoltativo. Messaggio descrittivo dello stato (default: 'Not Implemented')
 * @return array{code: int, message: mixed} Array associativo con codice e messaggio
 */
function gdrcd_api_status_notimplemented($message = 'Not Implemented')
{
    return gdrcd_api_status(501, $message);
}
