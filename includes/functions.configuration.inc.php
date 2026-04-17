<?php

/**
 * Restituisce una versione leggibile di un parametro o valore di configurazione.
 *
 * Converte il dato passato sostituendo gli underscore con spazi,
 * capitalizzando ogni parola e filtrando l'output per la visualizzazione.
 *
 * @param string $parametro il parametro o il valore di configurazione da formattare
 * @return string
 */
function gdrcd_configuration_label($parametro) {
    return gdrcd_filter('out', ucwords(strtr($parametro, ['_' => ' '])));
}

/**
 * Recupera un valore di configurazione dal database
 *
 * @param string $parametro Il parametro nel formato "categoria.parametro"
 * @return string|null Il valore della configurazione o null se non trovato
 */
function gdrcd_configuration_get($parametro)
{
    // Recupera il valore tramite query
    // Se il valore nel db non esiste, ritorna esplicitamente null
    [$categoria, $parametro] = explode('.', $parametro, 2);

    $result = gdrcd_stmt(
        "SELECT valore, `default` FROM configurazioni WHERE categoria = ? AND parametro = ?",
        [
            $categoria,
            $parametro
        ]
    );

    if (gdrcd_query($result, 'num_rows') === 0) {
        return null;
    }

    $value = gdrcd_query($result, 'assoc');

    return !is_null($value['valore']) && $value['valore'] !== ''
        ? $value['valore']
        : $value['default'];
}

/**
 * Imposta un valore di configurazione nel database
 *
 * @param string $parametro Il parametro nel formato "categoria.parametro"
 * @param string $value Il valore da salvare
 * @return void
 */
function gdrcd_configuration_set($parametro, $value)
{
    [$categoria, $parametro] = explode('.', $parametro, 2);

    gdrcd_stmt(
        "UPDATE configurazioni
            SET valore = ?
        WHERE categoria = ?
            AND parametro = ?",
        [
            $value,
            $categoria,
            $parametro
        ]
    );
}
