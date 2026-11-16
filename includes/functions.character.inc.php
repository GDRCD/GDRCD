<?php

/**
 * Recupera il nome di un personaggio e lo memorizza per la durata della richiesta.
 *
 * @param int $idPersonaggio Identificativo del personaggio
 * @return string Nome del personaggio, oppure "-" se non trovato
 */
function gdrcd_character_name($idPersonaggio)
{
    static $names = [];
    $idPersonaggio = (int)$idPersonaggio;

    if (array_key_exists($idPersonaggio, $names)) {
        return $names[$idPersonaggio];
    }

    $personaggio = gdrcd_stmt_one(
        'SELECT nome FROM personaggio WHERE id_personaggio = ?',
        [$idPersonaggio]
    );

    $names[$idPersonaggio] = $personaggio['nome'] ?? '-';

    return $names[$idPersonaggio];
}
