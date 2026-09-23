<?php

/**
 * Eventi applicativi di GDRCD.
 *
 * Queste funzioni costituiscono il punto di ingresso per registrare gli
 * eventi dalle pagine. Recuperano i dati necessari al contesto e delegano
 * la scrittura al sistema di log, evitando che le pagine debbano conoscere
 * descrizioni, livelli e struttura JSON dei singoli eventi.
 */


/**
 * Risolve l'autore di un evento, usando il personaggio in sessione come default.
 *
 * @param int|null $idAutore Identificativo esplicito dell'autore
 * @return int Identificativo dell'autore, oppure 0 se non disponibile
 */
function gdrcd_event_default_actor_id($idAutore = null)
{
    return $idAutore !== null ? (int)$idAutore : (int)($_SESSION['id_personaggio'] ?? 0);
}

/**
 * Registra un tentativo di login respinto perché l'indirizzo IP è in blacklist.
 *
 * @param string $nomeUtente Nome inserito nel form di login
 * @param string $ip Indirizzo IP della postazione
 * @return void
 */
function gdrcd_event_auth_login_blacklisted($nomeUtente, $ip)
{
    gdrcd_log_warning(
        'Tentativo di login bloccato',
        ['evento' => 'auth.login.bloccato.blacklist', 'ip' => $ip, 'autore' => $nomeUtente]
    );
}

/**
 * Registra un tentativo di accesso a un personaggio esiliato.
 *
 * @param int $idPersonaggio Identificativo del personaggio
 * @param string $nomePersonaggio Nome del personaggio
 * @param string $ip Indirizzo IP della postazione
 * @return void
 */
function gdrcd_event_auth_login_exiled($idPersonaggio, $nomePersonaggio, $ip)
{
    $contesto = gdrcd_log_context_make(
        ['ip' => $ip],
        (int)$idPersonaggio,
        $nomePersonaggio
    );
    gdrcd_log_warning(
        'Tentativo di login su account in esilio',
        ['evento' => 'auth.login.bloccato.esilio'] + $contesto,
        (int)$idPersonaggio
    );
}

/**
 * Registra una possibile correlazione tra account rilevata dal cookie di login.
 *
 * @param int $idPersonaggio Personaggio che sta effettuando il login
 * @param int $altroIdPersonaggio Personaggio memorizzato nel cookie
 * @param string $ip Indirizzo IP della postazione
 * @return void
 */
function gdrcd_event_auth_multiaccount_cookie($idPersonaggio, $altroIdPersonaggio, $ip)
{
    $idPersonaggio = (int)$idPersonaggio;
    $contesto = gdrcd_log_context_make(
        ['ip' => $ip],
        (int)$altroIdPersonaggio,
        gdrcd_character_name($altroIdPersonaggio),
        $idPersonaggio,
        gdrcd_character_name($idPersonaggio)
    );
    gdrcd_log_warning(
        'Rilevato possibile account multiplo tramite cookie attivo',
        ['evento' => 'auth.multiaccount.cookie'] + $contesto,
        $idPersonaggio
    );
}

/**
 * Registra una possibile correlazione tra account rilevata dall'indirizzo IP.
 *
 * @param int $idPersonaggio Personaggio che sta effettuando il login
 * @param int $altroIdPersonaggio Identificativo dell'account correlato
 * @param string $altroNomePersonaggio Nome dell'account correlato
 * @param string $ip Indirizzo IP condiviso
 * @return void
 */
function gdrcd_event_auth_multiaccount_ip($idPersonaggio, $altroIdPersonaggio, $altroNomePersonaggio, $ip)
{
    $idPersonaggio = (int)$idPersonaggio;
    $contesto = gdrcd_log_context_make(
        ['ip' => $ip],
        $idPersonaggio,
        gdrcd_character_name($idPersonaggio),
        (int)$altroIdPersonaggio,
        $altroNomePersonaggio
    );
    gdrcd_log_warning(
        'Possibile correlazione tra account tramite IP',
        ['evento' => 'auth.multiaccount.ip'] + $contesto,
        $idPersonaggio
    );
}

/**
 * Registra il completamento corretto di un login.
 *
 * @param int $idPersonaggio Identificativo del personaggio autenticato
 * @param string $ip Indirizzo IP della postazione
 * @return void
 */
function gdrcd_event_auth_login_success($idPersonaggio, $ip)
{
    $idPersonaggio = (int)$idPersonaggio;
    $contesto = gdrcd_log_context_make(
        ['ip' => $ip],
        $idPersonaggio,
        gdrcd_character_name($idPersonaggio),
        $idPersonaggio,
        gdrcd_character_name($idPersonaggio)
    );
    gdrcd_log_info(
        'Login effettuato con successo',
        ['evento' => 'auth.login.successo'] + $contesto,
        $idPersonaggio
    );
}

/**
 * Registra un login rifiutato perché il personaggio ha già una sessione attiva.
 *
 * @param int $idPersonaggio Identificativo del personaggio
 * @param string $nomePersonaggio Nome del personaggio
 * @param string $ip Indirizzo IP della postazione
 * @return void
 */
function gdrcd_event_auth_login_active($idPersonaggio, $nomePersonaggio, $ip)
{
    gdrcd_log_warning(
        'Tentativo di connessione da postazione ancora attiva',
        [
            'evento' => 'auth.login.bloccato',
            'ip' => $ip,
            'id_autore' => (int)$idPersonaggio,
            'autore' => $nomePersonaggio,
        ],
        (int)$idPersonaggio
    );
}

/**
 * Registra un tentativo di login con credenziali non valide.
 *
 * @param string $nomeUtente Nome inserito nel form di login
 * @param string $ip Indirizzo IP della postazione
 * @param int|null $idPersonaggio Identificativo del personaggio, se riconosciuto
 * @return void
 */
function gdrcd_event_auth_login_failed($nomeUtente, $ip, $idPersonaggio = null)
{
    gdrcd_log_warning(
        'Tentativo di login non riuscito',
        ['evento' => 'auth.login.fallito', 'ip' => $ip, 'autore' => $nomeUtente],
        $idPersonaggio !== null ? (int)$idPersonaggio : null
    );
}

/**
 * Registra la modifica del livello di permessi di un personaggio.
 *
 * @param int $idPersonaggio Personaggio interessato dalla modifica
 * @param string $nuovoRuolo Descrizione del nuovo livello di permessi
 * @param int|null $idAutore Personaggio che esegue la modifica; usa la sessione se omesso
 * @return void
 */
function gdrcd_event_character_permission_change($idPersonaggio, $nuovoRuolo, $idAutore = null)
{
    $idAutore = gdrcd_event_default_actor_id($idAutore);
    $contesto = gdrcd_log_context_make(
        ['nuovo_ruolo' => $nuovoRuolo],
        (int)$idPersonaggio,
        gdrcd_character_name($idPersonaggio),
        $idAutore,
        gdrcd_character_name($idAutore)
    );

    gdrcd_log_notice(
        'Cambio permesso del personaggio',
        ['evento' => 'personaggio.permessi.cambio'] + $contesto,
        $idAutore
    );
}

/**
 * Registra l'abbandono di una quantità di oggetti.
 *
 * Produce anche un log per lo staffer quando l'autore è diverso dal
 * proprietario dell'oggetto.
 *
 * @param int $idPersonaggio Proprietario dal cui inventario viene rimosso l'oggetto
 * @param int $idOggetto Identificativo dell'oggetto
 * @param int $quantita Quantità rimossa
 * @param int|null $idAutore Personaggio che esegue l'operazione; usa la sessione se omesso
 * @return void
 */
function gdrcd_event_item_discard($idPersonaggio, $idOggetto, $quantita = 1, $idAutore = null)
{
    $idPersonaggio = (int)$idPersonaggio;
    $idAutore = gdrcd_event_default_actor_id($idAutore);
    $contesto = gdrcd_log_context_make(
        [
            'id_oggetto' => (int)$idOggetto,
            'oggetto' => gdrcd_item_name($idOggetto),
            'quantita_rimossa' => (int)$quantita,
        ],
        $idPersonaggio,
        gdrcd_character_name($idPersonaggio),
        $idAutore,
        gdrcd_character_name($idAutore)
    );

    gdrcd_log_info(
        'Oggetto abbandonato dal personaggio',
        ['evento' => 'personaggio.abbandona_oggetto'] + $contesto,
        $idPersonaggio
    );

    if ($idAutore !== $idPersonaggio) {
        gdrcd_log_notice(
            'Oggetto abbandonato dal personaggio',
            ['evento' => 'personaggio.abbandona_oggetto'] + $contesto,
            $idAutore
        );
    }
}

/**
 * Registra il trasferimento di un oggetto tra due personaggi.
 *
 * Produce un log per mittente e destinatario e, se necessario, un terzo log
 * per lo staffer che ha eseguito l'operazione.
 *
 * @param int $mittenteId Personaggio che cede l'oggetto
 * @param int $destinatarioId Personaggio che riceve l'oggetto
 * @param int $idOggetto Identificativo dell'oggetto
 * @param int $quantita Quantità trasferita
 * @param int $cariche Cariche associate all'oggetto
 * @param int|null $idAutore Personaggio che esegue l'operazione; usa la sessione se omesso
 * @return void
 */
function gdrcd_event_item_transfer($mittenteId, $destinatarioId, $idOggetto, $quantita = 1, $cariche = 0, $idAutore = null)
{
    $mittenteId = (int)$mittenteId;
    $destinatarioId = (int)$destinatarioId;
    $idAutore = gdrcd_event_default_actor_id($idAutore);
    $contesto = gdrcd_log_context_make(
        [
            'id_oggetto' => (int)$idOggetto,
            'id_destinatario' => $destinatarioId,
            'destinatario' => gdrcd_character_name($destinatarioId),
            'oggetto' => gdrcd_item_name($idOggetto),
            'quantita' => (int)$quantita,
            'cariche' => (int)$cariche,
        ],
        $mittenteId,
        gdrcd_character_name($mittenteId),
        $idAutore,
        gdrcd_character_name($idAutore)
    );

    gdrcd_log_info(
        'Oggetto ceduto a un altro personaggio',
        ['evento' => 'personaggio.cedi_oggetto'] + $contesto,
        $mittenteId
    );
    gdrcd_log_info(
        'Oggetto ricevuto da un altro personaggio',
        ['evento' => 'personaggio.ricevi_oggetto'] + $contesto,
        $destinatarioId
    );

    if ($idAutore !== $mittenteId) {
        gdrcd_log_notice(
            'Oggetto ceduto a un altro personaggio',
            ['evento' => 'personaggio.cedi_oggetto'] + $contesto,
            $idAutore
        );
    }
}

/**
 * Registra l'equipaggiamento di un oggetto in una posizione del personaggio.
 *
 * @param int $idPersonaggio Personaggio che indossa l'oggetto
 * @param int $idOggetto Identificativo dell'oggetto
 * @param int $posizione Posizione di equipaggiamento
 * @return void
 */
function gdrcd_event_item_equip($idPersonaggio, $idOggetto, $posizione)
{
    $idPersonaggio = (int)$idPersonaggio;
    $contesto = gdrcd_log_context_make(
        [
            'id_oggetto' => (int)$idOggetto,
            'oggetto' => gdrcd_item_name($idOggetto),
            'posizione' => (int)$posizione,
        ],
        $idPersonaggio,
        gdrcd_character_name($idPersonaggio)
    );

    gdrcd_log_info(
        'Oggetto indossato dal personaggio',
        ['evento' => 'personaggio.indossa_oggetto'] + $contesto,
        $idPersonaggio
    );
}

/**
 * Registra lo spostamento di un oggetto dall'inventario allo zaino.
 *
 * @param int $idPersonaggio Proprietario dell'oggetto
 * @param int $idOggetto Identificativo dell'oggetto
 * @return void
 */
function gdrcd_event_item_move_to_backpack($idPersonaggio, $idOggetto)
{
    $idPersonaggio = (int)$idPersonaggio;
    $contesto = gdrcd_log_context_make(
        [
            'id_oggetto' => (int)$idOggetto,
            'oggetto' => gdrcd_item_name($idOggetto),
            'posizione' => ZAINO,
        ],
        $idPersonaggio,
        gdrcd_character_name($idPersonaggio)
    );

    gdrcd_log_info(
        'Oggetto spostato nello zaino dal personaggio',
        ['evento' => 'personaggio.sposta_oggetto_inventario'] + $contesto,
        $idPersonaggio
    );
}

/**
 * Registra lo spostamento di un oggetto dallo zaino all'inventario.
 *
 * @param int $idPersonaggio Proprietario dell'oggetto
 * @param int $idOggetto Identificativo dell'oggetto
 * @return void
 */
function gdrcd_event_item_move_to_inventory($idPersonaggio, $idOggetto)
{
    $idPersonaggio = (int)$idPersonaggio;
    $contesto = gdrcd_log_context_make(
        [
            'id_oggetto' => (int)$idOggetto,
            'oggetto' => gdrcd_item_name($idOggetto),
            'posizione' => INVENTARIO,
        ],
        $idPersonaggio,
        gdrcd_character_name($idPersonaggio)
    );

    gdrcd_log_info(
        'Oggetto spostato nell\'inventario dal personaggio',
        ['evento' => 'personaggio.sposta_oggetto_zaino'] + $contesto,
        $idPersonaggio
    );
}

/**
 * Registra un'assegnazione di punti esperienza.
 *
 * Produce un log per il personaggio che riceve i punti e uno per l'autore
 * dell'assegnazione.
 *
 * @param int $idPersonaggio Personaggio che riceve i punti esperienza
 * @param int $px Quantità di punti, positiva o negativa
 * @param string $causale Motivazione dell'assegnazione
 * @param int|null $idAutore Personaggio che assegna i punti; usa la sessione se omesso
 * @return void
 */
function gdrcd_event_experience_assign($idPersonaggio, $px, $causale, $idAutore = null)
{
    $idAutore = gdrcd_event_default_actor_id($idAutore);
    $contesto = gdrcd_log_context_make(
        ['px' => (int)$px, 'causale' => $causale],
        (int)$idPersonaggio,
        gdrcd_character_name($idPersonaggio),
        $idAutore,
        gdrcd_character_name($idAutore)
    );

    gdrcd_log_notice('Ricevuti punti esperienza', ['evento' => 'personaggio.riceve_px'] + $contesto, (int)$idPersonaggio);
    gdrcd_log_notice('Assegnazione punti esperienza al personaggio', ['evento' => 'personaggio.assegna_px'] + $contesto, $idAutore);
}

/**
 * Registra un bonifico per entrambe le parti coinvolte.
 *
 * @param int $mittenteId Personaggio che invia il denaro
 * @param int $destinatarioId Personaggio che riceve il denaro
 * @param int|float|string $ammontare Importo trasferito
 * @param string $valuta Nome della valuta
 * @param string $causale Causale del bonifico
 * @return void
 */
function gdrcd_event_bank_transfer($mittenteId, $destinatarioId, $ammontare, $valuta, $causale)
{
    $contesto = gdrcd_log_context_make([
        'id_destinatario' => (int)$destinatarioId,
        'destinatario' => gdrcd_character_name($destinatarioId),
        'ammontare' => $ammontare,
        'valuta' => $valuta,
        'causale' => $causale,
    ]);

    gdrcd_log_notice('Bonifico inviato a un altro personaggio', ['evento' => 'banca.invio_bonifico', 'direzione' => 'uscita'] + $contesto, (int)$mittenteId);
    gdrcd_log_notice('Bonifico ricevuto dal personaggio', ['evento' => 'banca.ricezione_bonifico', 'direzione' => 'entrata'] + $contesto, (int)$destinatarioId);
}

/**
 * Registra l'inizio di un nuovo lavoro autonomo.
 *
 * @param int $idPersonaggio Personaggio che inizia il lavoro
 * @param int $idLavoro Identificativo del lavoro
 * @param string $nomeLavoro Nome del lavoro
 * @return void
 */
function gdrcd_event_job_start($idPersonaggio, $idLavoro, $nomeLavoro)
{
    $contesto = gdrcd_log_context_make(['lavoro' => $nomeLavoro, 'id_lavoro' => (int)$idLavoro]);
    gdrcd_log_info('Il personaggio ha iniziato un nuovo lavoro', ['evento' => 'personaggio.nuovo_lavoro'] + $contesto, (int)$idPersonaggio);
}

/**
 * Registra le dimissioni da un lavoro autonomo.
 *
 * @param int $idPersonaggio Personaggio che lascia il lavoro
 * @param int $idLavoro Identificativo del lavoro
 * @param string $nomeLavoro Nome del lavoro
 * @return void
 */
function gdrcd_event_job_resign($idPersonaggio, $idLavoro, $nomeLavoro)
{
    $contesto = gdrcd_log_context_make(['lavoro' => $nomeLavoro, 'id_lavoro' => (int)$idLavoro]);
    gdrcd_log_info('Il personaggio si è dimesso dal lavoro', ['evento' => 'personaggio.dimissione_lavoro'] + $contesto, (int)$idPersonaggio);
}

/**
 * Registra l'assegnazione di un ruolo di gilda.
 *
 * Produce un log per l'autore dell'assegnazione e uno per il personaggio che
 * riceve il ruolo.
 *
 * @param int $idPersonaggio Personaggio che riceve il ruolo
 * @param int $idRuolo Identificativo del ruolo
 * @param string $nomeRuolo Nome del ruolo
 * @param int|null $idAutore Personaggio che assegna il ruolo; usa la sessione se omesso
 * @return void
 */
function gdrcd_event_guild_role_assign($idPersonaggio, $idRuolo, $nomeRuolo, $idAutore = null)
{
    $idAutore = gdrcd_event_default_actor_id($idAutore);
    $contesto = gdrcd_log_context_make(
        ['id_lavoro' => (int)$idRuolo, 'lavoro' => $nomeRuolo],
        (int)$idPersonaggio,
        gdrcd_character_name($idPersonaggio),
        $idAutore,
        gdrcd_character_name($idAutore)
    );
    gdrcd_log_notice('Ha assegnato nuovo ruolo al personaggio', ['evento' => 'personaggio.assegna_lavoro'] + $contesto, $idAutore);
    gdrcd_log_notice('Assegnato nuovo ruolo al personaggio', ['evento' => 'personaggio.assegna_lavoro'] + $contesto, (int)$idPersonaggio);
}

/**
 * Registra la rimozione di un ruolo di gilda.
 *
 * Produce un log per il personaggio rimosso e uno per l'autore
 * dell'operazione.
 *
 * @param int $idPersonaggio Personaggio a cui viene rimosso il ruolo
 * @param int $idRuolo Identificativo del ruolo
 * @param string $nomeRuolo Nome del ruolo
 * @param int|null $idAutore Personaggio che rimuove il ruolo; usa la sessione se omesso
 * @return void
 */
function gdrcd_event_guild_role_remove($idPersonaggio, $idRuolo, $nomeRuolo, $idAutore = null)
{
    $idAutore = gdrcd_event_default_actor_id($idAutore);
    $contesto = gdrcd_log_context_make(
        ['id_lavoro' => (int)$idRuolo, 'lavoro' => $nomeRuolo],
        (int)$idPersonaggio,
        gdrcd_character_name($idPersonaggio),
        $idAutore,
        gdrcd_character_name($idAutore)
    );
    gdrcd_log_notice('Dimissione dal ruolo del personaggio', ['evento' => 'personaggio.dimissione_lavoro'] + $contesto, (int)$idPersonaggio);
    gdrcd_log_notice('Ha dimesso il ruolo del personaggio', ['evento' => 'personaggio.dimissione_lavoro'] + $contesto, $idAutore);
}

/**
 * Registra il cambio di nome di un personaggio.
 *
 * Se la modifica è eseguita da un altro personaggio, produce un secondo log
 * associato all'autore dell'operazione.
 *
 * @param int $idPersonaggio Personaggio rinominato
 * @param string $nomePrecedente Nome precedente
 * @param string $nomeNuovo Nuovo nome
 * @param int|null $idAutore Personaggio che esegue la modifica; usa la sessione se omesso
 * @return void
 */
function gdrcd_event_character_name_change($idPersonaggio, $nomePrecedente, $nomeNuovo, $idAutore = null)
{
    $idPersonaggio = (int)$idPersonaggio;
    $idAutore = gdrcd_event_default_actor_id($idAutore);
    $contesto = gdrcd_log_context_make(
        ['nome_precedente' => $nomePrecedente, 'nome_nuovo' => $nomeNuovo],
        $idPersonaggio,
        $nomePrecedente,
        $idAutore,
        gdrcd_character_name($idAutore)
    );
    gdrcd_log_notice('Cambio nome del personaggio', ['evento' => 'personaggio.cambio_nome'] + $contesto, $idPersonaggio);
    if ($idAutore !== $idPersonaggio) {
        gdrcd_log_notice('Cambia il nome del personaggio', ['evento' => 'personaggio.cambio_nome'] + $contesto, $idAutore);
    }
}

/**
 * Registra il cambio di password di un personaggio senza salvare la password.
 *
 * Se la modifica è amministrativa, produce un log anche per l'autore.
 *
 * @param int $idPersonaggio Personaggio la cui password viene modificata
 * @param int|null $idAutore Personaggio che esegue la modifica; usa la sessione se omesso
 * @param string|null $ip Indirizzo IP; usa quello della richiesta se omesso
 * @return void
 */
function gdrcd_event_character_password_change($idPersonaggio, $idAutore = null, $ip = null)
{
    $idPersonaggio = (int)$idPersonaggio;
    $idAutore = gdrcd_event_default_actor_id($idAutore);
    $contesto = gdrcd_log_context_make(
        ['ip' => $ip ?? ($_SERVER['REMOTE_ADDR'] ?? '')],
        $idPersonaggio,
        gdrcd_character_name($idPersonaggio),
        $idAutore,
        gdrcd_character_name($idAutore)
    );
    gdrcd_log_notice('Cambio password del personaggio', ['evento' => 'personaggio.cambio_password'] + $contesto, $idPersonaggio);
    if ($idAutore !== $idPersonaggio) {
        gdrcd_log_notice('Cambio password del personaggio', ['evento' => 'personaggio.cambio_password'] + $contesto, $idAutore);
    }
}

/**
 * Registra la cancellazione volontaria di un account.
 *
 * @param int $idPersonaggio Personaggio che cancella il proprio account
 * @param string|null $ip Indirizzo IP; usa quello della richiesta se omesso
 * @return void
 */
function gdrcd_event_character_account_delete($idPersonaggio, $ip = null)
{
    $contesto = gdrcd_log_context_make(['ip' => $ip ?? ($_SERVER['REMOTE_ADDR'] ?? '')]);
    gdrcd_log_notice('Cancella account del personaggio', ['evento' => 'personaggio.cancella_account'] + $contesto, (int)$idPersonaggio);
}

/**
 * Registra la disabilitazione o il ripristino amministrativo di un account.
 *
 * Produce un log per il personaggio interessato e, quando differente, uno per
 * l'autore dell'operazione.
 *
 * @param int $idPersonaggio Account interessato
 * @param bool $abilitato True per il ripristino, false per la disabilitazione
 * @param int|null $idAutore Personaggio che esegue l'operazione; usa la sessione se omesso
 * @param string|null $ip Indirizzo IP; usa quello della richiesta se omesso
 * @return void
 */
function gdrcd_event_character_account_status($idPersonaggio, $abilitato, $idAutore = null, $ip = null)
{
    $idPersonaggio = (int)$idPersonaggio;
    $idAutore = gdrcd_event_default_actor_id($idAutore);
    $evento = $abilitato ? 'personaggio.ripristina_account' : 'personaggio.disabilita_account';
    $descrizione = $abilitato ? 'Ripristina account del personaggio' : 'Disabilita account del personaggio';
    $contesto = gdrcd_log_context_make(
        ['ip' => $ip ?? ($_SERVER['REMOTE_ADDR'] ?? '')],
        $idPersonaggio,
        gdrcd_character_name($idPersonaggio),
        $idAutore,
        gdrcd_character_name($idAutore)
    );
    gdrcd_log_notice($descrizione, ['evento' => $evento] + $contesto, $idPersonaggio);
    if ($idAutore !== $idPersonaggio) {
        gdrcd_log_notice($descrizione, ['evento' => $evento] + $contesto, $idAutore);
    }
}
