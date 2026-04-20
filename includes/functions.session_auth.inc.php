<?php

/**
 * Funzioni di alto livello per la gestione delle sessioni utente in GDRCD.
 *
 * Questo modulo implementa i flussi di controllo e validazione delle sessioni,
 * utilizzando la tabella `sessions` come unica fonte di verità.
 */

/**
 * Avvia o riprende la sessione per un utente non autenticato (guest).
 * Da utilizzare nelle pagine che non richiedono autenticazione
 * ma necessitano di una sessione PHP (es: pagina di login).
 *
 * @return void
 */
function gdrcd_session_guest(): void
{
    gdrcd_session_init();
    gdrcd_session_commit();
}

/**
 * Avvia o riprende la sessione per un utente autenticato.
 * Esegue la validazione completa della sessione tramite la tabella sessions
 * e gestisce tutti i controlli di sicurezza (scadenza, fingerprint, IP, refresh).
 *
 * Da utilizzare nelle pagine che richiedono l'autenticazione (es: main.php).
 *
 * @return void
 */
function gdrcd_session_auth(): void
{
    gdrcd_session_init();

    // Se la sessione PHP è vuota, è scaduta
    if (empty($_SESSION) || empty($_SESSION['id_personaggio'])) {
        gdrcd_session_destroy();
        gdrcd_session_expired_error();
        return;
    }

    $id_sessione = gdrcd_session_id();
    $metadata = gdrcd_session_metadata($id_sessione);

    // 1. Esistenza della sessione
    if ($metadata === null) {
        gdrcd_session_destroy();
        gdrcd_session_expired_error();
        return;
    }

    // 2. Verifica che la sessione non sia stata revocata
    $status = $metadata['status'];

    if ($status === GDRCD_SESSION_STATUS_REVOKED) {
        gdrcd_session_destroy();
        gdrcd_session_expired_error();
        return;
    }

    // 3. Verifica scadenza della sessione
    $now = time();

    // 3a. Scadenza temporale
    $data_scadenza = strtotime($metadata['data_scadenza']);

    if (!$data_scadenza || $now >= $data_scadenza) {
        gdrcd_session_invalidate($id_sessione);
        gdrcd_session_destroy();
        gdrcd_session_expired_error();
        return;
    }

    // 3b. Hard limit: data_login + GDRCD_SESSION_MAX_TTL
    $data_login = strtotime($metadata['data_login']);

    if (!$data_login || $now > ($data_login + GDRCD_SESSION_MAX_TTL)) {
        gdrcd_session_invalidate($id_sessione);
        gdrcd_session_destroy();
        gdrcd_session_expired_error();
        return;
    }

    // 4. Sessione Refreshed
    $fingerprint = gdrcd_session_fingerprint($metadata);

    if ($status === GDRCD_SESSION_STATUS_REFRESHED) {
        $data_refreshed_at = strtotime($metadata['data_refreshed_at']);
        $grace_expired = !$data_refreshed_at || ($now > ($data_refreshed_at + GDRCD_SESSION_GRACE_PERIOD));

        if (!$grace_expired) {
            // Fingerprint valido e in periodo di grazia
            if ($fingerprint >= GDRCD_FINGERPRINT_CONFIDENT) {
                gdrcd_session_commit();
                return;
            }

            // Fingerprint incerto e in periodo di grazia
            if ($fingerprint === GDRCD_FINGERPRINT_UNSURE) {
                gdrcd_session_log($metadata['id_personaggio'], 'Sessione refreshed con fingerprint incerto rilevata.');
                gdrcd_session_commit();
                return;
            }
        }

        // Fingerprint non corrispondente
        if ($fingerprint === GDRCD_FINGERPRINT_WRONG) {
            gdrcd_session_destroy();
            gdrcd_session_log($metadata['id_personaggio'], 'Sessione refreshed con fingerprint non corrispondente, distrutta.');
            gdrcd_session_expired_error();
            return;
        }

        // Oltre il periodo di grazia
        gdrcd_session_destroy();
        gdrcd_session_log($metadata['id_personaggio'], 'Sessione refreshed, periodo di grazia scaduto.');
        gdrcd_session_expired_error();
        return;
    }

    // 5. Verifica IP e fingerprint (solo per sessioni active)
    $stored_ip = $metadata['ip'];
    $current_ip = gdrcd_session_client_ip();

    if ($current_ip !== $stored_ip) {
        if ($fingerprint < GDRCD_FINGERPRINT_CONFIDENT) {
            gdrcd_session_destroy();
            gdrcd_session_log($metadata['id_personaggio'], 'Cambio IP rilevato con fingerprint insufficiente, sessione distrutta.');
            gdrcd_session_expired_error();
            return;
        }
    }

    // 6. Refresh della sessione
    $data_refresh = strtotime($metadata['data_refresh']);

    if (!$data_refresh || $now >= $data_refresh) {
        gdrcd_session_refresh($metadata);
        gdrcd_session_commit();
        return;
    }

    // 7. Aggiornamento attività
    if ($status === GDRCD_SESSION_STATUS_ACTIVE) {
        $data_ultimavisita = strtotime($metadata['data_ultimavisita']);

        if (!$data_ultimavisita || ($now - $data_ultimavisita) >= GDRCD_SESSION_ACTIVITY_DEBOUNCE) {
            gdrcd_stmt(
                'UPDATE `sessions` SET `data_ultimavisita` = NOW() WHERE `id_sessione` = ? AND `status` = ?',
                [$id_sessione, GDRCD_SESSION_STATUS_ACTIVE]
            );
        }
    }

    // 8. Rilascio lock: tutto ok!
    gdrcd_session_commit();
}

/**
 * Gestisce il refresh della sessione corrente.
 * Marca la vecchia sessione come refreshed, genera un nuovo id
 * e crea un nuovo record nella tabella sessions.
 *
 * Utilizza affected_rows per gestire la concorrenza: solo una richiesta
 * effettuerà il refresh, le altre non eseguiranno alcuna azione.
 *
 * @param array $old_metadata I metadati della sessione corrente da refreshare.
 * @return void
 */
function gdrcd_session_refresh(array $old_metadata): void
{
    $old_id = gdrcd_session_id();

    gdrcd_database_transaction(function() use($old_metadata, $old_id) {

        // Tenta di marcare la sessione come refreshed (idempotente tramite affected_rows)
        $result = gdrcd_stmt(
            'UPDATE `sessions`
                SET `status` = ?, `data_refreshed_at` = NOW()
            WHERE `id_sessione` = ? AND `status` = ?',
            [GDRCD_SESSION_STATUS_REFRESHED, $old_id, GDRCD_SESSION_STATUS_ACTIVE]
        );

        // Se affected_rows === 0, un'altra richiesta ha già gestito il refresh
        if (($result['affected'] ?? 0) === 0) {
            return;
        }

        // Genera un nuovo id di sessione
        $new_id = gdrcd_session_regenerate();

        // Aggiorna il riferimento alla sessione successiva nella vecchia
        gdrcd_stmt(
            'UPDATE `sessions` SET `id_sessione_next` = ? WHERE `id_sessione` = ?',
            [$new_id, $old_id]
        );

        // Crea il nuovo record preservando la data_login originale
        gdrcd_session_metadata_create(
            $new_id,
            (int) $old_metadata['id_personaggio'],
            $old_metadata['data_login']
        );

    });

    // Rinfresca i dati della sessione PHP
    $session_data = gdrcd_session_data((int) $old_metadata['id_personaggio']);
    gdrcd_session_write($session_data);
}

/**
 * Crea una nuova sessione per un utente appena autenticato.
 * Genera un nuovo id di sessione, crea il record nella tabella sessions
 * e popola la sessione PHP con i dati dell'utente.
 *
 * @param int $id_personaggio L'id del personaggio per cui creare la sessione.
 * @return void
 */
function gdrcd_session_create(int $id_personaggio): void
{
    $new_id = gdrcd_session_regenerate();
    $now = date('Y-m-d H:i:s');

    gdrcd_session_metadata_create($new_id, $id_personaggio, $now);

    $session_data = gdrcd_session_data($id_personaggio);
    gdrcd_session_write($session_data);
}

/**
 * Recupera i metadati di una sessione dalla tabella sessions.
 *
 * @param string $id_sessione L'id della sessione da cercare.
 * @return array|null I metadati della sessione, oppure null se non trovata.
 */
function gdrcd_session_metadata(string $id_sessione): array|null
{
    return gdrcd_stmt_one(
        'SELECT * FROM `sessions` WHERE `id_sessione` = ?',
        [$id_sessione]
    );
}

/**
 * Crea un nuovo record nella tabella sessions con i metadati della sessione.
 * Calcola automaticamente le date di refresh, scadenza e le informazioni del client.
 *
 * @param string $id_sessione L'id della sessione da creare.
 * @param int $id_personaggio L'id del personaggio associato.
 * @param string $data_login La data di login da preservare (formato Y-m-d H:i:s).
 * @return void
 */
function gdrcd_session_metadata_create(string $id_sessione, int $id_personaggio, string $data_login): void
{
    $now = time();
    $data_creazione = date('Y-m-d H:i:s', $now);
    $data_refresh = date('Y-m-d H:i:s', $now + GDRCD_SESSION_REFRESH_INTERVAL);
    $data_scadenza = date('Y-m-d H:i:s', $now + GDRCD_SESSION_EXPIRY);
    $ip = gdrcd_session_client_ip();
    $client = json_encode(gdrcd_session_client_data(), JSON_UNESCAPED_UNICODE);

    gdrcd_stmt(
        'INSERT INTO `sessions` (
            `id_sessione`,
            `id_personaggio`,
            `status`,
            `data_creazione`,
            `data_refresh`,
            `data_scadenza`,
            `data_ultimavisita`,
            `data_login`,
            `ip`,
            `client`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
        [
            $id_sessione,
            $id_personaggio,
            GDRCD_SESSION_STATUS_ACTIVE,
            $data_creazione,
            $data_refresh,
            $data_scadenza,
            $data_creazione,
            $data_login,
            $ip,
            $client,
        ]
    );
}

/**
 * Gestisce il flusso di Session Takeover Protection.
 * Verifica il token OTP e, se valido, revoca tutte le sessioni attive
 * del personaggio e ne crea una nuova.
 *
 * @param int $id_personaggio L'id del personaggio che tenta il takeover.
 * @param string $token Il token OTP fornito dall'utente.
 * @return array|null I dati del personaggio se il takeover ha successo, null altrimenti.
 */
function gdrcd_session_takeover(int $id_personaggio, #[SensitiveParameter] string $token): ?array
{
    gdrcd_session_init();

    // TODO: da svolgere in transazione mysql

    // Recupera il token valido per il personaggio
    $token_record = gdrcd_stmt_one(
        'SELECT `token`, `data_scadenza`
        FROM `sessions_protection_token`
        WHERE `id_personaggio` = ? AND `data_utilizzo` IS NULL AND `data_scadenza` > NOW()
        ORDER BY `data_creazione` DESC LIMIT 1',
        [$id_personaggio]
    );

    if ($token_record === null) {
        gdrcd_session_commit();
        return null;
    }

    // Verifica il token tramite hash
    if (!gdrcd_password_check($token, $token_record['token'])) {
        gdrcd_session_commit();
        return null;
    }

    // Verifica fingerprint delle sessioni attive per avvisare di possibile compromissione
    $active_session = gdrcd_stmt_one(
        'SELECT * FROM `sessions` WHERE `id_personaggio` = ? AND `status` = ?',
        [$id_personaggio, GDRCD_SESSION_STATUS_ACTIVE]
    );

    if (gdrcd_session_fingerprint($active_session) < GDRCD_FINGERPRINT_CONFIDENT) {
        // TODO: logga l'evento
    }

    // Revoca tutte le sessioni attive
    gdrcd_session_disconnect($id_personaggio);

    // Crea la nuova sessione
    gdrcd_session_create($id_personaggio);

    // Marca il token come utilizzato
    $new_session_id = gdrcd_session_id();

    gdrcd_stmt(
        'UPDATE `sessions_protection_token`
        SET `data_utilizzo` = NOW(), `id_sessione` = ?
        WHERE `id_personaggio` = ? AND `token` = ?',
        [$new_session_id, $id_personaggio, $token_record['token']]
    );

    gdrcd_session_commit();

    // Recupera i dati del personaggio
    $personaggio = gdrcd_session_data($id_personaggio);

    return $personaggio;
}

/**
 * Genera e invia un token OTP per il flusso di Session Takeover Protection.
 * Il token viene hashato prima di essere salvato nel database.
 * Il token in chiaro viene inviato via email all'utente.
 *
 * @param int $id_personaggio L'id del personaggio per cui generare il token.
 * @return void
 */
function gdrcd_session_takeover_begin(int $id_personaggio): void
{
    // Genera un token casuale di 6 caratteri numerici
    $token_plain = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    // Hash del token per il salvataggio
    $token_hash = gdrcd_encript($token_plain);

    $now = date('Y-m-d H:i:s');
    $scadenza = date('Y-m-d H:i:s', time() + GDRCD_SESSION_TAKEOVER_TOKEN_TTL);

    gdrcd_stmt(
        'INSERT INTO `sessions_protection_token` (`id_personaggio`, `token`, `data_creazione`, `data_scadenza`)
        VALUES (?, ?, ?, ?)',
        [$id_personaggio, $token_hash, $now, $scadenza]
    );

    // Recupera l'email del personaggio
    $pg = gdrcd_stmt_one(
        'SELECT `email`, `nome` FROM `personaggio` WHERE `id_personaggio` = ?',
        [$id_personaggio]
    );

    if ($pg !== null && !empty($pg['email'])) {
        $subject = 'Codice di verifica accesso - ' . $GLOBALS['PARAMETERS']['info']['site_name'];
        $body = "Ciao {$pg['nome']},\n\n";
        $body .= "E' stato rilevato un tentativo di accesso al tuo account mentre risulta gia' attiva una sessione.\n\n";
        $body .= "Il tuo codice di verifica e': {$token_plain}\n\n";
        $body .= "Il codice scade tra ". intval(GDRCD_SESSION_TAKEOVER_TOKEN_TTL / 60) ." minuti.\n\n";
        $body .= "Se non hai richiesto tu questo accesso, ti consigliamo di cambiare la tua password.";

        @mail($pg['email'], $subject, $body);
    }
}

/**
 * Gestisce il flusso completo di login.
 * Verifica le credenziali, controlla sessioni attive esistenti
 * e decide se procedere con il login diretto, il takeover protection
 * o il rifiuto.
 *
 * @param string $username Il nome utente (nome del personaggio).
 * @param string $password La password in chiaro.
 * @return array|null Array con id del personaggio e stato dell'operazione, null se credenziali errate.
 *  Possibili chiavi di ritorno:
 *  - 'result': 'success' | 'takeover_required' | 'takeover_veryconfident'
 *  - 'id_personaggio': true se rilevata discrepanza fingerprint
 */
function gdrcd_session_login(string $username, #[SensitiveParameter] string $password): ?array
{
    gdrcd_session_init();

    // Recupera il personaggio
    $record = gdrcd_stmt_one(
        'SELECT `id_personaggio`, `pass`, `nome`, `permessi`
        FROM `personaggio`
        WHERE `nome` = ?
        LIMIT 1',
        [$username]
    );

    // Credenziali errate: utente non trovato
    if ($record === null) {
        gdrcd_session_commit();
        return null;
    }

    // Credenziali errate: password non corrisponde
    if (!gdrcd_password_check($password, $record['pass'])) {
        gdrcd_session_commit();
        return null;
    }

    // Credenziali errate: account disabilitato
    if ((int) $record['permessi'] <= DELETED) {
        gdrcd_session_commit();
        return null;
    }

    $id_personaggio = (int) $record['id_personaggio'];

    // Controlla sessioni attive esistenti per questo personaggio
    $active_session = gdrcd_stmt_one(
        'SELECT * FROM `sessions` WHERE `id_personaggio` = ? AND `status` = ?',
        [$id_personaggio, GDRCD_SESSION_STATUS_ACTIVE]
    );

    if ($active_session === null) {
        // Nessuna sessione attiva: procedi con il login diretto
        gdrcd_session_create($id_personaggio);
        gdrcd_session_commit();

        return [
            'result' => GDRCD_LOGIN_SUCCESS,
            'id_personaggio' => $id_personaggio,
        ];
    }

    // Sessioni attive presenti: controlla fingerprint
    $skip_takeover = gdrcd_session_fingerprint($active_session) === GDRCD_FINGERPRINT_VERYCONFIDENT;

    if ($skip_takeover) {
        // Stesso device: invalida sessioni precedenti e procedi
        gdrcd_session_disconnect($id_personaggio);
        gdrcd_session_create($id_personaggio);
        gdrcd_session_commit();

        return [
            'result' => GDRCD_LOGIN_SUCCESS,
            'id_personaggio' => $id_personaggio,
        ];
    }

    // Device diverso o incerto: richiedi verifica tramite token
    gdrcd_session_takeover_begin($id_personaggio);
    gdrcd_session_commit();

    $result = [
        'result' => GDRCD_LOGIN_TAKEOVER,
        'id_personaggio' => $id_personaggio,
    ];

    return $result;
}

/**
 * Gestisce il logout dell'utente.
 * Rimuove i dati dalla sessione PHP, invalida il record nella tabella sessions
 * e distrugge la sessione.
 *
 * @return void
 */
function gdrcd_session_logout(): void
{
    gdrcd_session_init();

    $id_sessione = gdrcd_session_id();

    gdrcd_session_clear();
    gdrcd_session_invalidate($id_sessione);
    gdrcd_session_destroy();
}

/**
 * Invalida una specifica sessione marcandola come revoked
 * e aggiornando la data di scadenza e logout a NOW().
 *
 * @param string $id_sessione L'id della sessione da invalidare.
 * @return void
 */
function gdrcd_session_invalidate(string $id_sessione): void
{
    gdrcd_stmt(
        'UPDATE `sessions`
            SET `status` = ?, `data_scadenza` = NOW()
        WHERE `id_sessione` = ?',
        [GDRCD_SESSION_STATUS_REVOKED, $id_sessione]
    );
}

/**
 * Revoca tutte le sessioni attive per un determinato personaggio.
 * Utilizzata durante il takeover protection e la disconnessione forzata.
 *
 * @param int $id_personaggio L'id del personaggio di cui revocare le sessioni.
 * @return void
 */
function gdrcd_session_disconnect(int $id_personaggio): void
{
    gdrcd_stmt(
        'UPDATE `sessions`
            SET `status` = ?, `data_scadenza` = NOW(), `data_logout` = NOW()
        WHERE `id_personaggio` = ? AND `status` = ?',
        [GDRCD_SESSION_STATUS_REVOKED, $id_personaggio, GDRCD_SESSION_STATUS_ACTIVE]
    );
}

/**
 * Aggiorna la data di refresh a NOW() per tutte le sessioni attive di un personaggio.
 * Utilizzata per forzare la rigenerazione della sessione alla prossima richiesta,
 * utile quando i dati del personaggio vengono modificati a database.
 *
 * @param int $id_personaggio L'id del personaggio di cui aggiornare le sessioni.
 * @return void
 */
function gdrcd_session_mark_refresh(int $id_personaggio): void
{
    gdrcd_stmt(
        'UPDATE `sessions`
            SET `data_refresh` = NOW()
        WHERE `id_personaggio` = ? AND `status` = ?',
        [$id_personaggio, GDRCD_SESSION_STATUS_ACTIVE]
    );
}

/**
 * Recupera dal database tutti i dati necessari per popolare la sessione PHP
 * per un determinato personaggio. Include informazioni dal profilo, razza e gilda.
 *
 * @param int $id_personaggio L'id del personaggio di cui recuperare i dati.
 * @return array Array associativo con tutti i valori da salvare in sessione.
 */
function gdrcd_session_data(int $id_personaggio): array
{
    $record = gdrcd_stmt_one(
        'SELECT
            personaggio.id_personaggio,
            personaggio.nome,
            personaggio.cognome,
            personaggio.permessi,
            personaggio.sesso,
            personaggio.blocca_media,
            personaggio.ora_uscita,
            personaggio.id_razza,
            personaggio.ultima_mappa,
            personaggio.ultimo_luogo,
            razza.sing_m,
            razza.sing_f,
            razza.icon AS url_img_razza
        FROM personaggio
        LEFT JOIN razza ON personaggio.id_razza = razza.id_razza
        WHERE personaggio.id_personaggio = ?
        LIMIT 1',
        [$id_personaggio]
    );

    if ($record === null) {
        return [];
    }

    $data = [
        'id_personaggio' => $record['id_personaggio'],
        'login'          => $record['nome'],
        'cognome'        => $record['cognome'],
        'permessi'       => $record['permessi'],
        'sesso'          => $record['sesso'],
        'blocca_media'   => $record['blocca_media'],
        'ultima_uscita'  => $record['ora_uscita'],
        'razza'          => ($record['sesso'] === 'f') ? $record['sing_f'] : $record['sing_m'],
        'img_razza'      => $record['url_img_razza'],
        'id_razza'       => $record['id_razza'],
        'mappa'          => empty($record['ultima_mappa']) ? 1 : $record['ultima_mappa'],
        'luogo'          => empty($record['ultimo_luogo']) ? -1 : $record['ultimo_luogo'],
        'tag'            => '',
        'last_message'   => 0,
    ];

    // Carica informazioni sulle gilde
    $gilda = '';
    $img_gilda = '';

    $personaggio_ruoli = gdrcd_stmt_all(
        'SELECT ruolo.gilda, ruolo.immagine
        FROM ruolo
        JOIN clgpersonaggioruolo ON clgpersonaggioruolo.id_ruolo = ruolo.id_ruolo
        WHERE clgpersonaggioruolo.id_personaggio = ?',
        [$id_personaggio]
    );

    foreach ($personaggio_ruoli as $ruolo) {
        $gilda .= ',*' . $ruolo['gilda'] . '*';
        $img_gilda .= $ruolo['immagine'] . ',';
    }

    $data['gilda'] = $gilda;
    $data['img_gilda'] = $img_gilda;

    return $data;
}

/**
 * Pulisce dal database le sessioni non più attive scadute da un determinato numero di giorni.
 * Utile per la manutenzione periodica della tabella sessions.
 *
 * @param int $days Numero di giorni oltre i quali le sessioni scadute vengono eliminate.
 * @return void
 */
function gdrcd_session_prune(int $days): void
{
    gdrcd_stmt(
        'DELETE FROM `sessions`
        WHERE `status` != ? AND `data_scadenza` < DATE_SUB(NOW(), INTERVAL ? DAY)',
        [GDRCD_SESSION_STATUS_ACTIVE, $days]
    );
}

/**
 * Registra un evento di sessione nel log del sistema.
 *
 * @param int $id_personaggio L'id del personaggio coinvolto.
 * @param string $descrizione La descrizione dell'evento.
 * @return void
 */
function gdrcd_session_log(int $id_personaggio, string $descrizione): void
{
    gdrcd_stmt(
        'INSERT INTO `log` (
            `id_personaggio`,
            `nome_interessato`,
            `autore`,
            `data_evento`,
            `codice_evento`,
            `descrizione_evento`
        ) VALUES (?, ?, ?, NOW(), ?, ?)',
        [
            $id_personaggio,
            '',
            gdrcd_client_ip(),
            BLOCKED,
            $descrizione,
        ]
    );
}

/**
 * Mostra il messaggio di sessione scaduta e termina lo script.
 * Utilizzata internamente quando la validazione della sessione fallisce.
 *
 * @return void
 */
function gdrcd_session_expired_error(): void
{
    $message = $GLOBALS['MESSAGE']['error']['session_expired'] ?? 'Sessione scaduta.';
    gdrcd_error($message);
}
