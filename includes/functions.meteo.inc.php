<?php
/**
 * Funzioni del meteo.
 *
 * Il file è diviso in due parti:
 *  - un nucleo di funzioni pure (nessuna variabile globale, nessun accesso al
 *    database, nessuna lettura dell'orologio): ricevono tutto come parametro e
 *    sono verificate da tests/meteo_test.php;
 *  - un sottile strato di funzioni che leggono il contesto (database,
 *    $PARAMETERS, $MESSAGE, configurazioni) e delegano le decisioni al nucleo.
 *
 * Modello dei dati: sia `mappa_click` (zona) sia `mappa` (chat) hanno le
 * colonne meteo_temperatura, meteo_condizione e meteo_scadenza. Una chat senza
 * valore eredita dalla zona, una zona senza valore usa il meteo automatico.
 * Un valore con scadenza passata viene semplicemente ignorato: non esiste
 * alcuno scheduler e nulla viene scritto alla scadenza.
 *
 * Il file definisce solo funzioni: può essere incluso senza effetti collaterali.
 */


/* ---------------------------------------------------------------------------
 * Nucleo puro
 * ------------------------------------------------------------------------- */

/**
 * Elenco ordinato degli slug delle condizioni meteo.
 *
 * È l'unica fonte di verità per gli slug. L'indice di ogni slug corrisponde
 * al vecchio ordine di $MESSAGE['interface']['meteo']['status'][0..4].
 *
 * @return string[] Slug delle condizioni, dall'indice 0 all'indice 4
 */
function gdrcd_meteo_condizioni(): array
{
    return ['sereno', 'variabile', 'nuvoloso', 'pioggia', 'neve'];
}

/**
 * Elenco ordinato delle fasi lunari, usato come nome delle immagini.
 *
 * L'indice restituito da gdrcd_meteo_fase_lunare() seleziona l'immagine
 * themes/<tema>/imgs/luna/<nome>.png; i titoli si trovano nel vocabolario,
 * in $MESSAGE['interface']['meteo']['moon'][0..7].
 *
 * @return string[] Nomi delle fasi, dall'indice 0 all'indice 7
 */
function gdrcd_meteo_fasi_lunari(): array
{
    return [
        'nuova',
        'crescente',
        'primo-quarto',
        'gibbosa-crescente',
        'piena',
        'gibbosa-calante',
        'ultimo-quarto',
        'calante',
    ];
}

/**
 * Calcola il meteo automatico per l'istante indicato.
 *
 * Porting fedele dell'algoritmo storico di pages/info_location.inc.php
 * (modalità auto_meteo = ON):
 *  - la temperatura minima del mese è la temperatura base più uno scostamento
 *    mensile; durante il giorno sale fino alle 13 e poi scende;
 *  - il "caso" (1 o 2) alterna l'escursione termica ogni tre giorni;
 *  - la condizione dipende da (giorno dell'anno + ora / 4) modulo 12; con
 *    minima inferiore a 4 gradi la pioggia diventa neve.
 *
 * Corrispondenze con il codice originale: strftime('%j') è 1-based, quindi
 * diventa format('z') + 1; '%H' diventa format('G'); '%m' diventa format('n').
 * Le divisioni con floor() e il modulo su float dell'originale, per valori non
 * negativi, coincidono con intdiv().
 *
 * @param DateTimeInterface $now Istante di riferimento (con il suo fuso orario)
 * @param int $baseTemperature Temperatura minima assoluta ($PARAMETERS['date']['base_temperature'])
 * @return array{temperatura:int, condizione:string}
 */
function gdrcd_meteo_auto(DateTimeInterface $now, int $baseTemperature): array
{
    $scostamentoMensile = [0, 4, 8, 14, 20, 28, 30, 27, 21, 15, 5, 1];

    $ore = (int)$now->format('G');
    $mese = (int)$now->format('n');
    $giorno = (int)$now->format('z') + 1;

    $minima = $baseTemperature + $scostamentoMensile[$mese - 1];
    $escursione = (intdiv($giorno, 3) % 2) + 1;

    if ($ore < 14) {
        $gradi = $minima + (intdiv($ore, 3) * $escursione);
    } else {
        $gradi = $minima + (4 * $escursione) - (intdiv($ore, 3) * $escursione) + (3 * $escursione);
    }

    $slug = gdrcd_meteo_condizioni();

    switch (($giorno + intdiv($ore, 4)) % 12) {
        case 0:
        case 1:
        case 6:
        case 10:
        case 11:
            $condizione = $slug[0];
            break;

        case 2:
        case 5:
        case 7:
            $condizione = $slug[1];
            break;

        case 3:
        case 9:
            $condizione = $slug[2];
            break;

        default: // 4 e 8
            $condizione = $minima < 4 ? $slug[4] : $slug[3];
            break;
    }

    return ['temperatura' => $gradi, 'condizione' => $condizione];
}

/**
 * Indica se un valore meteo manuale (di zona o di chat) è attivo.
 *
 * Il valore è attivo quando ha una condizione nota, una temperatura e la
 * scadenza è assente oppure strettamente successiva a $now. Una scadenza
 * uguale a $now è già scaduta; una scadenza illeggibile è considerata scaduta.
 *
 * @param array|null $riga Riga con meteo_temperatura, meteo_condizione, meteo_scadenza
 * @param DateTimeInterface $now Istante di riferimento
 * @return bool
 */
function gdrcd_meteo_valore_attivo(?array $riga, DateTimeInterface $now): bool
{
    if ($riga === null
        || !isset($riga['meteo_condizione'], $riga['meteo_temperatura'])
        || !in_array($riga['meteo_condizione'], gdrcd_meteo_condizioni(), true)
    ) {
        return false;
    }

    $scadenza = $riga['meteo_scadenza'] ?? null;

    if ($scadenza === null) {
        return true;
    }

    $scadenza = (string)$scadenza;
    $istante = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $scadenza, $now->getTimezone());

    if ($istante === false || $istante->format('Y-m-d H:i:s') !== $scadenza) {
        return false;
    }

    return $istante > $now;
}

/**
 * Risolve il meteo da mostrare secondo la regola zona + override di chat.
 *
 * Ordine di precedenza: override della chat attivo, poi valore della zona
 * attivo, infine il meteo automatico. La modalità auto_meteo = ON va gestita
 * dal chiamante prima di invocare questa funzione.
 *
 * @param array|null $luogo Riga della chat (mappa) oppure null
 * @param array|null $zona Riga della zona (mappa_click) oppure null
 * @param array $auto Risultato di gdrcd_meteo_auto()
 * @param DateTimeInterface $now Istante di riferimento
 * @return array{temperatura:int, condizione:string, fonte:string, scadenza:?string}
 *         fonte vale 'luogo', 'zona' oppure 'auto'
 */
function gdrcd_meteo_risolvi(?array $luogo, ?array $zona, array $auto, DateTimeInterface $now): array
{
    foreach (['luogo' => $luogo, 'zona' => $zona] as $fonte => $riga) {
        if (gdrcd_meteo_valore_attivo($riga, $now)) {
            return [
                'temperatura' => (int)$riga['meteo_temperatura'],
                'condizione' => (string)$riga['meteo_condizione'],
                'fonte' => $fonte,
                'scadenza' => isset($riga['meteo_scadenza']) ? (string)$riga['meteo_scadenza'] : null,
            ];
        }
    }

    return [
        'temperatura' => (int)$auto['temperatura'],
        'condizione' => (string)$auto['condizione'],
        'fonte' => 'auto',
        'scadenza' => null,
    ];
}

/**
 * Valida i dati meteo inviati da un form.
 *
 * La temperatura è accettata solo se intera (int oppure stringa di sole cifre
 * con segno opzionale, di lunghezza ragionevole) e viene limitata
 * all'intervallo [$min, $max]. La condizione deve essere uno degli slug ammessi.
 *
 * @param mixed $temp Temperatura ricevuta
 * @param mixed $cond Condizione ricevuta
 * @param int $min Temperatura minima ammessa
 * @param int $max Temperatura massima ammessa
 * @param string[] $slugs Slug ammessi (di norma gdrcd_meteo_condizioni())
 * @return array{temperatura:int, condizione:string}|null Dati normalizzati, oppure null se non validi
 */
function gdrcd_meteo_valida(mixed $temp, mixed $cond, int $min, int $max, array $slugs): ?array
{
    if (is_int($temp)) {
        $temperatura = $temp;
    } elseif (is_string($temp) && strlen($temp) <= 6 && preg_match('/^-?\d+$/D', $temp) === 1) {
        $temperatura = (int)$temp;
    } else {
        return null;
    }

    if (!is_string($cond) || !in_array($cond, $slugs, true)) {
        return null;
    }

    return [
        'temperatura' => max($min, min($max, $temperatura)),
        'condizione' => $cond,
    ];
}

/**
 * Calcola la scadenza di un valore meteo manuale.
 *
 * @param DateTimeInterface $now Istante di riferimento
 * @param int $ore Durata in ore; 0 o negativa significa nessuna scadenza
 * @return string|null Scadenza nel formato 'Y-m-d H:i:s', oppure null
 */
function gdrcd_meteo_scadenza(DateTimeInterface $now, int $ore): ?string
{
    if ($ore <= 0) {
        return null;
    }

    // Somma tempo trascorso effettivo, così anche a cavallo dell'ora legale
    return DateTimeImmutable::createFromInterface($now)
        ->setTimestamp($now->getTimestamp() + $ore * 3600)
        ->format('Y-m-d H:i:s');
}

/**
 * Interpreta il vecchio formato testuale di mappa_click.meteo.
 *
 * Accetta stringhe come '20°c - sereno' o '-3°C - Pioggia'; al posto di '°'
 * sono tollerati anche 'º', '&deg;' e '&ordm;' (usato dal vecchio form).
 * L'etichetta viene confrontata, senza distinzione di maiuscole, con una
 * mappa italiana fissa: non dipende dal vocabolario attivo.
 * Usata dalla migrazione per il backfill delle nuove colonne.
 *
 * @param string $s Valore legacy
 * @return array{temperatura:int, condizione:string}|null Dati interpretati, oppure null
 */
function gdrcd_meteo_parse_legacy(string $s): ?array
{
    $etichette = [
        'sereno' => 'sereno',
        'variabile' => 'variabile',
        'nuvoloso' => 'nuvoloso',
        'pioggia' => 'pioggia',
        'neve' => 'neve',
    ];

    if (strlen($s) > 255
        || preg_match('/^\s*(-?\d{1,6})\s*(?:°|º|&deg;|&ordm;)\s*c\s*-\s*(.+)$/iu', $s, $match) !== 1
    ) {
        return null;
    }

    $etichetta = trim(mb_strtolower($match[2], 'UTF-8'));

    if (!isset($etichette[$etichetta])) {
        return null;
    }

    return [
        'temperatura' => (int)$match[1],
        'condizione' => $etichette[$etichetta],
    ];
}

/**
 * Calcola la fase lunare per la data indicata.
 *
 * Porting di gdrcd_lunar_phase() dichiarata in pages/info_location.inc.php,
 * che verrà rimossa dalla pagina nella Fase 2; fino ad allora le due funzioni
 * convivono con nomi diversi. Restituisce solo l'indice: il nome
 * dell'immagine è in gdrcd_meteo_fasi_lunari() e il titolo nel vocabolario
 * ($MESSAGE['interface']['meteo']['moon'][indice]).
 *
 * 0 nuova, 1 crescente, 2 primo-quarto, 3 gibbosa-crescente, 4 piena,
 * 5 gibbosa-calante, 6 ultimo-quarto, 7 calante.
 *
 * @param DateTimeInterface $now Data di riferimento (conta solo il giorno)
 * @return int Indice della fase, da 0 a 7
 */
function gdrcd_meteo_fase_lunare(DateTimeInterface $now): int
{
    $anno = (int)$now->format('Y');
    $mese = (int)$now->format('n');
    $giorno = (int)$now->format('j');

    // Prima di aprile si conta dall'anno precedente
    if ($mese < 4) {
        $anno = $anno - 1;
        $mese = $mese + 12;
    }

    $plenilunio = (365.25 * $anno) + (30.42 * $mese) + $giorno - 694039.09;
    $plenilunio = $plenilunio / 29.53;
    $plenilunio = $plenilunio - intval($plenilunio);
    $fase = (int)round($plenilunio * 8 + 0.5);

    return $fase === 8 ? 0 : $fase;
}

/**
 * Compone il testo del meteo nel formato unico "<Condizione> - <N>°C".
 *
 * Non applica alcun escape: l'etichetta va passata già filtrata per l'output.
 *
 * @param string $etichetta Etichetta della condizione
 * @param int $temperatura Temperatura in gradi
 * @return string
 */
function gdrcd_meteo_formatta(string $etichetta, int $temperatura): string
{
    return $etichetta . ' - ' . $temperatura . '°C';
}


/* ---------------------------------------------------------------------------
 * Accesso al contesto (database, configurazione, vocabolario)
 * ------------------------------------------------------------------------- */

/**
 * Restituisce l'orologio unico del meteo.
 *
 * Usa il fuso orario predefinito di PHP, lo stesso di date() e del vecchio
 * strftime(), così il meteo automatico resta identico al precedente. Tutti i
 * confronti con le scadenze vanno fatti con questo valore, mai con NOW() SQL.
 *
 * @return DateTimeImmutable
 */
function gdrcd_meteo_now(): DateTimeImmutable
{
    return new DateTimeImmutable('now', new DateTimeZone(date_default_timezone_get()));
}

/**
 * Legge dalla configurazione i limiti di temperatura ammessi.
 *
 * @return array{min:int, max:int}
 */
function gdrcd_meteo_limiti(): array
{
    $min = gdrcd_configuration_get('meteo.temperatura_min') ?? -45;
    $max = gdrcd_configuration_get('meteo.temperatura_max') ?? 45;

    $min = is_numeric($min) ? (int)$min : -45;
    $max = is_numeric($max) ? (int)$max : 45;

    return $min <= $max
        ? ['min' => $min, 'max' => $max]
        : ['min' => $max, 'max' => $min];
}

/**
 * Restituisce tabella e chiave primaria per un bersaglio meteo.
 *
 * @param string $target 'zona' (mappa_click) oppure 'luogo' (mappa)
 * @return array{tabella:string, chiave:string}|null null se il bersaglio non è ammesso
 */
function gdrcd_meteo_target(string $target): ?array
{
    $targets = [
        'zona' => ['tabella' => 'mappa_click', 'chiave' => 'id_click'],
        'luogo' => ['tabella' => 'mappa', 'chiave' => 'id'],
    ];

    return $targets[$target] ?? null;
}

/**
 * Risolve la zona (mappa_click) a cui appartiene la posizione corrente.
 *
 * In una chat ($luogo > 0) usa la zona della chat letta dal database. Sulla
 * mappa ($luogo == -1), o per una chat senza zona o con una zona inesistente,
 * usa $mappa se esiste, altrimenti la mappa principale. Stessa logica di
 * pages/mappaclick.inc.php.
 *
 * @param int $luogo Id della chat, oppure -1 sulla mappa
 * @param int $mappa Id della mappa in sessione
 * @return int|null Id della zona, oppure null se non esiste alcuna mappa
 */
function gdrcd_meteo_resolve_click_id(int $luogo, int $mappa): ?int
{
    if ($luogo > 0) {
        $riga = gdrcd_stmt_one(
            'SELECT mappa_click.id_click
                FROM mappa
                INNER JOIN mappa_click ON mappa_click.id_click = mappa.id_mappa
            WHERE mappa.id = ?
            LIMIT 1',
            [(int)$luogo]
        );

        if (!empty($riga)) {
            return (int)$riga['id_click'];
        }
    }

    if ($mappa > 0) {
        $riga = gdrcd_stmt_one(
            'SELECT id_click FROM mappa_click WHERE id_click = ? LIMIT 1',
            [(int)$mappa]
        );

        if (!empty($riga)) {
            return (int)$riga['id_click'];
        }
    }

    $riga = gdrcd_stmt_one('SELECT id_click FROM mappa_click WHERE principale = 1 ORDER BY id_click LIMIT 1');

    return !empty($riga) ? (int)$riga['id_click'] : null;
}

/**
 * Calcola il meteo corrente per la posizione indicata.
 *
 * Con auto_meteo = ON restituisce sempre il meteo automatico; altrimenti
 * applica gdrcd_meteo_risolvi() a chat e zona.
 *
 * @param int $luogo Id della chat, oppure -1 sulla mappa
 * @param int $mappa Id della mappa in sessione
 * @return array{
 *  temperatura:int,
 *  condizione:string,
 *  fonte:string,
 *  scadenza:?string,
 *  testo:string,
 *  id_click:?int,
 *  id_luogo:?int
 * } testo è già filtrato per l'output HTML
 */
function gdrcd_meteo_corrente(int $luogo, int $mappa): array
{
    global $PARAMETERS, $MESSAGE;

    $now = gdrcd_meteo_now();
    $auto = gdrcd_meteo_auto($now, (int)($PARAMETERS['date']['base_temperature'] ?? 0));
    $idClick = gdrcd_meteo_resolve_click_id($luogo, $mappa);
    $idLuogo = $luogo > 0 ? $luogo : null;

    if (($PARAMETERS['mode']['auto_meteo'] ?? 'ON') == 'ON') {
        $meteo = $auto + ['fonte' => 'auto', 'scadenza' => null];
    } else {
        $rigaLuogo = null;
        $rigaZona = null;

        if ($idLuogo !== null) {
            $rigaLuogo = gdrcd_stmt_one(
                'SELECT meteo_temperatura, meteo_condizione, meteo_scadenza FROM mappa WHERE id = ? LIMIT 1',
                [(int)$idLuogo]
            ) ?: null;
        }

        if ($idClick !== null) {
            $rigaZona = gdrcd_stmt_one(
                'SELECT meteo_temperatura, meteo_condizione, meteo_scadenza FROM mappa_click WHERE id_click = ? LIMIT 1',
                [(int)$idClick]
            ) ?: null;
        }

        $meteo = gdrcd_meteo_risolvi($rigaLuogo, $rigaZona, $auto, $now);
    }

    $etichetta = $MESSAGE['interface']['meteo']['status'][$meteo['condizione']] ?? ucfirst($meteo['condizione']);

    $meteo['testo'] = gdrcd_meteo_formatta(gdrcd_filter('out', $etichetta), $meteo['temperatura']);
    $meteo['id_click'] = $idClick;
    $meteo['id_luogo'] = $idLuogo;

    return $meteo;
}

/**
 * Imposta un meteo manuale su una zona o su una chat e registra l'evento.
 *
 * I valori vanno validati prima con gdrcd_meteo_valida(); qui viene
 * ricontrollato solo che bersaglio, condizione e scadenza siano ammessi.
 *
 * @param string $target 'zona' (mappa_click) oppure 'luogo' (mappa)
 * @param int $id Id della zona o della chat
 * @param int $temp Temperatura in gradi
 * @param string $cond Slug della condizione
 * @param string|null $scadenza Scadenza 'Y-m-d H:i:s', oppure null per nessuna scadenza
 * @return bool false se i parametri non sono ammessi
 */
function gdrcd_meteo_imposta(string $target, int $id, int $temp, string $cond, ?string $scadenza): bool
{
    $bersaglio = gdrcd_meteo_target($target);

    if ($bersaglio === null
        || $id <= 0
        || !in_array($cond, gdrcd_meteo_condizioni(), true)
        || ($scadenza !== null && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/D', $scadenza) !== 1)
    ) {
        return false;
    }

    gdrcd_stmt(
        "UPDATE {$bersaglio['tabella']}
            SET meteo_temperatura = ?,
                meteo_condizione = ?,
                meteo_scadenza = ?
        WHERE {$bersaglio['chiave']} = ?
        LIMIT 1",
        [(int)$temp, $cond, $scadenza, (int)$id]
    );

    gdrcd_event_meteo_change($target, $id, (int)$temp, $cond, $scadenza);

    return true;
}

/**
 * Rimuove il meteo manuale da una zona o da una chat e registra l'evento.
 *
 * La chat torna a ereditare dalla zona, la zona torna al meteo automatico.
 *
 * @param string $target 'zona' (mappa_click) oppure 'luogo' (mappa)
 * @param int $id Id della zona o della chat
 * @return bool false se i parametri non sono ammessi
 */
function gdrcd_meteo_azzera(string $target, int $id): bool
{
    $bersaglio = gdrcd_meteo_target($target);

    if ($bersaglio === null || $id <= 0) {
        return false;
    }

    gdrcd_stmt(
        "UPDATE {$bersaglio['tabella']}
            SET meteo_temperatura = NULL,
                meteo_condizione = NULL,
                meteo_scadenza = NULL
        WHERE {$bersaglio['chiave']} = ?
        LIMIT 1",
        [(int)$id]
    );

    gdrcd_event_meteo_change($target, $id);

    return true;
}
