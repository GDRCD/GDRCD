<?php

/**
 * Funzioni dedicate ai Forum
 * @author Kasa
 */

/**
 * Determina se l'Utente possiede i permessi di visualizzazione di un Forum
 * @param int $tipoForum // Categoria del Forum
 * @param string $proprietariForum // Proprietari associati al Forum
 * @return bool
 */
function gdrcd_forum_controllo_permessi($tipoForum, $proprietariForum) {
    return (
        // Bacheca Pubblica
        ($tipoForum <= PERTUTTI)
        ||
        // Bacheca Razza
        (($tipoForum == SOLORAZZA) && ($_SESSION['id_razza'] == $proprietariForum))
        ||
        // Bacheca Gilda
        (($tipoForum == SOLOGILDA) && gdrcd_forum_controllo_appartenenza_gilda($proprietariForum) != false)
        ||
        // Bacheca Master
        (($tipoForum == SOLOMASTERS) && ($_SESSION['permessi'] >= GAMEMASTER))
        ||
        // Bacheca Moderatori / Admin
        ($_SESSION['permessi'] >= MODERATOR)
    );
}

/**
 * Determina se l'Utente appartiene ad una o più Gilde legate ad un Forum
 * @param string $proprietari // Proprietari associati al Forum
 * @return bool
 */
function gdrcd_forum_controllo_appartenenza_gilda($proprietari) {
    // Inizializzo il controllo
    $result = FALSE;

    // Metto i proprietari in un array
    $arrayProprietari = explode(',', $proprietari);

    foreach($arrayProprietari AS $proprietario) {
        // Se ho già ottenuto un controllo positivo, proseguo
        if($result === TRUE) continue;
        // Controllo che il proprietario sia presente nelle Gilde del Personaggio
        $result = (bool)strpos($_SESSION['gilda'], '*' . $proprietario . '*');
    }

    return $result;
}

