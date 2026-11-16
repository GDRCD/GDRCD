<?php

/**
 * Recupera il nome di un oggetto e lo memorizza per la durata della richiesta.
 *
 * @param int $idOggetto Identificativo dell'oggetto
 * @return string Nome dell'oggetto, oppure "-" se non trovato
 */
function gdrcd_item_name($idOggetto)
{
    static $names = [];
    $idOggetto = (int)$idOggetto;

    if (array_key_exists($idOggetto, $names)) {
        return $names[$idOggetto];
    }

    $oggetto = gdrcd_stmt_one(
        'SELECT nome FROM oggetto WHERE id_oggetto = ?',
        [$idOggetto]
    );

    $names[$idOggetto] = $oggetto['nome'] ?? '-';

    return $names[$idOggetto];
}
