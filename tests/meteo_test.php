<?php
/**
 * Test del nucleo puro del meteo (includes/functions.meteo.inc.php).
 *
 * Nessuna dipendenza: si esegue con `php tests/meteo_test.php`.
 * Termina con codice diverso da zero se almeno una verifica fallisce.
 *
 * I valori attesi di gdrcd_meteo_auto() e gdrcd_meteo_fase_lunare() sono
 * stati catturati eseguendo l'algoritmo storico di pages/info_location.inc.php
 * (strftime/date sulle stesse date, fuso Europe/Rome) prima del porting.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

require_once __DIR__ . '/../includes/functions.meteo.inc.php';

$GLOBALS['passati'] = 0;
$GLOBALS['falliti'] = [];

/**
 * Confronta in modo stretto valore atteso e ottenuto.
 */
function verifica($nome, $atteso, $ottenuto)
{
    if ($atteso === $ottenuto) {
        $GLOBALS['passati']++;
        return;
    }

    $GLOBALS['falliti'][] = $nome
        . "\n    atteso:   " . var_export($atteso, true)
        . "\n    ottenuto: " . var_export($ottenuto, true);
}

/**
 * Esegue un gruppo di verifiche, trasformando eccezioni e warning in fallimenti.
 */
function gruppo($nome, callable $verifiche)
{
    try {
        $verifiche();
    } catch (Throwable $e) {
        $GLOBALS['falliti'][] = $nome . ': ' . get_class($e) . ': ' . $e->getMessage()
            . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')';
    }
}

function istante($s)
{
    return new DateTimeImmutable($s, new DateTimeZone('Europe/Rome'));
}


/* ------------------------------------------------------------------------- */

gruppo('gdrcd_meteo_condizioni', function () {
    verifica('condizioni: ordine degli slug', ['sereno', 'variabile', 'nuvoloso', 'pioggia', 'neve'], gdrcd_meteo_condizioni());
});

gruppo('gdrcd_meteo_auto', function () {
    $ore = [0, 3, 7, 13, 14, 18, 23];

    // [data => [[temperatura, condizione] per ciascuna ora di $ore]], temperatura base -4
    $griglia = [
        '2025-01-01' => [[-4, 'sereno'], [-3, 'sereno'], [-2, 'variabile'], [0, 'neve'], [-1, 'neve'], [-3, 'variabile'], [-4, 'sereno']],
        '2025-02-14' => [[0, 'nuvoloso'], [2, 'nuvoloso'], [4, 'sereno'], [8, 'sereno'], [6, 'sereno'], [2, 'sereno'], [0, 'variabile']],
        '2025-03-03' => [[4, 'variabile'], [5, 'variabile'], [6, 'nuvoloso'], [8, 'variabile'], [7, 'variabile'], [5, 'sereno'], [4, 'variabile']],
        '2025-04-10' => [[10, 'pioggia'], [12, 'pioggia'], [14, 'variabile'], [18, 'variabile'], [16, 'variabile'], [12, 'pioggia'], [10, 'nuvoloso']],
        '2025-05-21' => [[16, 'nuvoloso'], [18, 'nuvoloso'], [20, 'sereno'], [24, 'sereno'], [22, 'sereno'], [18, 'sereno'], [16, 'variabile']],
        '2025-06-06' => [[24, 'sereno'], [25, 'sereno'], [26, 'variabile'], [28, 'pioggia'], [27, 'pioggia'], [25, 'variabile'], [24, 'sereno']],
        '2025-07-31' => [[26, 'pioggia'], [27, 'pioggia'], [28, 'nuvoloso'], [30, 'sereno'], [29, 'sereno'], [27, 'sereno'], [26, 'sereno']],
        '2025-08-15' => [[23, 'sereno'], [25, 'sereno'], [27, 'sereno'], [31, 'variabile'], [29, 'variabile'], [25, 'nuvoloso'], [23, 'pioggia']],
        '2025-09-09' => [[17, 'sereno'], [18, 'sereno'], [19, 'sereno'], [21, 'nuvoloso'], [20, 'nuvoloso'], [18, 'pioggia'], [17, 'variabile']],
        '2025-10-27' => [[11, 'sereno'], [12, 'sereno'], [13, 'sereno'], [15, 'nuvoloso'], [14, 'nuvoloso'], [12, 'pioggia'], [11, 'variabile']],
        '2025-11-02' => [[1, 'sereno'], [2, 'sereno'], [3, 'variabile'], [5, 'nuvoloso'], [4, 'nuvoloso'], [2, 'sereno'], [1, 'sereno']],
        '2025-12-31' => [[-3, 'variabile'], [-1, 'variabile'], [1, 'sereno'], [5, 'neve'], [3, 'neve'], [-1, 'nuvoloso'], [-3, 'sereno']],
        '2024-01-01' => [[-4, 'sereno'], [-3, 'sereno'], [-2, 'variabile'], [0, 'neve'], [-1, 'neve'], [-3, 'variabile'], [-4, 'sereno']],
        '2024-02-29' => [[0, 'sereno'], [1, 'sereno'], [2, 'sereno'], [4, 'nuvoloso'], [3, 'nuvoloso'], [1, 'neve'], [0, 'variabile']],
        '2024-03-01' => [[4, 'sereno'], [5, 'sereno'], [6, 'variabile'], [8, 'pioggia'], [7, 'pioggia'], [5, 'variabile'], [4, 'sereno']],
        '2024-12-31' => [[-3, 'sereno'], [-2, 'sereno'], [-1, 'variabile'], [1, 'nuvoloso'], [0, 'nuvoloso'], [-2, 'sereno'], [-3, 'sereno']],
    ];

    foreach ($griglia as $data => $attesi) {
        foreach ($ore as $i => $ora) {
            $now = istante(sprintf('%s %02d:37:00', $data, $ora));
            verifica(
                sprintf('auto %s %02d:37 base -4', $data, $ora),
                ['temperatura' => $attesi[$i][0], 'condizione' => $attesi[$i][1]],
                gdrcd_meteo_auto($now, -4)
            );
        }
    }

    // Altre temperature base: la soglia neve/pioggia dipende dalla minima del mese
    $altre = [
        [7, '2025-01-01', [[7, 'sereno'], [8, 'sereno'], [9, 'variabile'], [11, 'pioggia'], [10, 'pioggia'], [8, 'variabile'], [7, 'sereno']]],
        [7, '2025-12-31', [[8, 'variabile'], [10, 'variabile'], [12, 'sereno'], [16, 'pioggia'], [14, 'pioggia'], [10, 'nuvoloso'], [8, 'sereno']]],
        [-20, '2025-06-06', [[8, 'sereno'], [9, 'sereno'], [10, 'variabile'], [12, 'pioggia'], [11, 'pioggia'], [9, 'variabile'], [8, 'sereno']]],
        [-30, '2025-07-31', [[0, 'neve'], [1, 'neve'], [2, 'nuvoloso'], [4, 'sereno'], [3, 'sereno'], [1, 'sereno'], [0, 'sereno']]],
    ];

    foreach ($altre as [$base, $data, $attesi]) {
        foreach ($ore as $i => $ora) {
            $now = istante(sprintf('%s %02d:37:00', $data, $ora));
            verifica(
                sprintf('auto %s %02d:37 base %d', $data, $ora, $base),
                ['temperatura' => $attesi[$i][0], 'condizione' => $attesi[$i][1]],
                gdrcd_meteo_auto($now, $base)
            );
        }
    }
});

gruppo('gdrcd_meteo_risolvi', function () {
    $now = istante('2025-06-01 12:00:00');
    $auto = ['temperatura' => 21, 'condizione' => 'sereno'];
    $riga = function ($temperatura, $condizione, $scadenza) {
        return ['meteo_temperatura' => $temperatura, 'meteo_condizione' => $condizione, 'meteo_scadenza' => $scadenza];
    };
    $zona = $riga(10, 'pioggia', '2025-06-01 15:00:00');

    verifica(
        'risolvi: vince la chat',
        ['temperatura' => 5, 'condizione' => 'neve', 'fonte' => 'luogo', 'scadenza' => '2025-06-01 13:00:00'],
        gdrcd_meteo_risolvi($riga(5, 'neve', '2025-06-01 13:00:00'), $zona, $auto, $now)
    );
    verifica(
        'risolvi: chat scaduta, si usa la zona',
        ['temperatura' => 10, 'condizione' => 'pioggia', 'fonte' => 'zona', 'scadenza' => '2025-06-01 15:00:00'],
        gdrcd_meteo_risolvi($riga(5, 'neve', '2025-06-01 11:59:59'), $zona, $auto, $now)
    );
    verifica(
        'risolvi: chat senza condizione eredita dalla zona',
        ['temperatura' => 10, 'condizione' => 'pioggia', 'fonte' => 'zona', 'scadenza' => '2025-06-01 15:00:00'],
        gdrcd_meteo_risolvi($riga(null, null, null), $zona, $auto, $now)
    );
    verifica(
        'risolvi: zona scaduta, si usa auto',
        ['temperatura' => 21, 'condizione' => 'sereno', 'fonte' => 'auto', 'scadenza' => null],
        gdrcd_meteo_risolvi(null, $riga(10, 'pioggia', '2025-05-31 12:00:00'), $auto, $now)
    );
    verifica(
        'risolvi: scadenza uguale a now è scaduta',
        ['temperatura' => 21, 'condizione' => 'sereno', 'fonte' => 'auto', 'scadenza' => null],
        gdrcd_meteo_risolvi($riga(5, 'neve', '2025-06-01 12:00:00'), $riga(10, 'pioggia', '2025-06-01 12:00:00'), $auto, $now)
    );
    verifica(
        'risolvi: scadenza un secondo nel futuro è applicata',
        ['temperatura' => 5, 'condizione' => 'neve', 'fonte' => 'luogo', 'scadenza' => '2025-06-01 12:00:01'],
        gdrcd_meteo_risolvi($riga(5, 'neve', '2025-06-01 12:00:01'), $zona, $auto, $now)
    );
    verifica(
        'risolvi: scadenza null non scade mai',
        ['temperatura' => -2, 'condizione' => 'nuvoloso', 'fonte' => 'zona', 'scadenza' => null],
        gdrcd_meteo_risolvi(null, $riga(-2, 'nuvoloso', null), $auto, istante('2099-12-31 23:59:59'))
    );
    verifica(
        'risolvi: chat e zona null, si usa auto',
        ['temperatura' => 21, 'condizione' => 'sereno', 'fonte' => 'auto', 'scadenza' => null],
        gdrcd_meteo_risolvi(null, null, $auto, $now)
    );
    verifica(
        'risolvi: righe vuote (colonne null), si usa auto',
        ['temperatura' => 21, 'condizione' => 'sereno', 'fonte' => 'auto', 'scadenza' => null],
        gdrcd_meteo_risolvi($riga(null, null, null), $riga(null, null, null), $auto, $now)
    );
    verifica(
        'risolvi: temperatura da database come stringa',
        ['temperatura' => -7, 'condizione' => 'neve', 'fonte' => 'zona', 'scadenza' => null],
        gdrcd_meteo_risolvi(null, $riga('-7', 'neve', null), $auto, $now)
    );
    verifica(
        'risolvi: condizione sconosciuta ignorata',
        ['temperatura' => 21, 'condizione' => 'sereno', 'fonte' => 'auto', 'scadenza' => null],
        gdrcd_meteo_risolvi($riga(5, 'grandine', null), null, $auto, $now)
    );
    verifica(
        'risolvi: scadenza illeggibile considerata scaduta',
        ['temperatura' => 10, 'condizione' => 'pioggia', 'fonte' => 'zona', 'scadenza' => '2025-06-01 15:00:00'],
        gdrcd_meteo_risolvi($riga(5, 'neve', '0000-00-00 00:00:00'), $zona, $auto, $now)
    );
    verifica(
        'risolvi: scadenza confrontata nel fuso di now',
        ['temperatura' => 21, 'condizione' => 'sereno', 'fonte' => 'auto', 'scadenza' => null],
        // 11:30 UTC = 13:30 a Roma: la scadenza 13:00 (ora di Roma) è già passata
        gdrcd_meteo_risolvi($riga(5, 'neve', '2025-06-01 13:00:00'), null, $auto,
            (new DateTimeImmutable('2025-06-01 11:30:00', new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Europe/Rome')))
    );
});

gruppo('gdrcd_meteo_valida', function () {
    $slugs = gdrcd_meteo_condizioni();

    verifica('valida: intero nel range', ['temperatura' => 12, 'condizione' => 'sereno'], gdrcd_meteo_valida(12, 'sereno', -45, 45, $slugs));
    verifica('valida: limite inferiore', ['temperatura' => -45, 'condizione' => 'neve'], gdrcd_meteo_valida(-80, 'neve', -45, 45, $slugs));
    verifica('valida: limite superiore', ['temperatura' => 45, 'condizione' => 'sereno'], gdrcd_meteo_valida('99', 'sereno', -45, 45, $slugs));
    verifica('valida: stringa numerica', ['temperatura' => -3, 'condizione' => 'pioggia'], gdrcd_meteo_valida('-3', 'pioggia', -45, 45, $slugs));
    verifica('valida: stringa non numerica', null, gdrcd_meteo_valida('abc', 'sereno', -45, 45, $slugs));
    verifica('valida: stringa float', null, gdrcd_meteo_valida('12.5', 'sereno', -45, 45, $slugs));
    verifica('valida: float', null, gdrcd_meteo_valida(12.0, 'sereno', -45, 45, $slugs));
    verifica('valida: stringa vuota', null, gdrcd_meteo_valida('', 'sereno', -45, 45, $slugs));
    verifica('valida: spazi e newline', null, gdrcd_meteo_valida("12\n", 'sereno', -45, 45, $slugs));
    verifica('valida: null', null, gdrcd_meteo_valida(null, 'sereno', -45, 45, $slugs));
    verifica('valida: booleano', null, gdrcd_meteo_valida(true, 'sereno', -45, 45, $slugs));
    verifica('valida: temperatura array', null, gdrcd_meteo_valida(['12'], 'sereno', -45, 45, $slugs));
    verifica('valida: condizione array', null, gdrcd_meteo_valida(12, ['sereno'], -45, 45, $slugs));
    verifica('valida: slug sconosciuto', null, gdrcd_meteo_valida(12, 'grandine', -45, 45, $slugs));
    verifica('valida: etichetta al posto dello slug', null, gdrcd_meteo_valida(12, 'Sereno', -45, 45, $slugs));
    verifica('valida: condizione vuota', null, gdrcd_meteo_valida(12, '', -45, 45, $slugs));
    verifica('valida: temperatura da 10KB', null, gdrcd_meteo_valida(str_repeat('9', 10240), 'sereno', -45, 45, $slugs));
    verifica('valida: condizione da 10KB', null, gdrcd_meteo_valida(12, str_repeat('a', 10240), -45, 45, $slugs));
});

gruppo('gdrcd_meteo_scadenza', function () {
    $now = istante('2025-06-01 12:34:56');

    verifica('scadenza: 0 ore', null, gdrcd_meteo_scadenza($now, 0));
    verifica('scadenza: ore negative', null, gdrcd_meteo_scadenza($now, -3));
    verifica('scadenza: 3 ore', '2025-06-01 15:34:56', gdrcd_meteo_scadenza($now, 3));
    verifica('scadenza: 24 ore a cavallo del mese', '2025-06-02 12:34:56', gdrcd_meteo_scadenza($now, 24));
    // 30 marzo 2025, 01:30: alle 02:00 si passa all'ora legale, 3 ore effettive portano alle 05:30
    verifica('scadenza: cambio ora legale', '2025-03-30 05:30:00', gdrcd_meteo_scadenza(istante('2025-03-30 01:30:00'), 3));
});

gruppo('gdrcd_meteo_parse_legacy', function () {
    verifica('legacy: valore predefinito', ['temperatura' => 20, 'condizione' => 'sereno'], gdrcd_meteo_parse_legacy('20°c - sereno'));
    verifica('legacy: maiuscole', ['temperatura' => 12, 'condizione' => 'neve'], gdrcd_meteo_parse_legacy('12°C - Neve'));
    verifica('legacy: temperatura negativa', ['temperatura' => -3, 'condizione' => 'pioggia'], gdrcd_meteo_parse_legacy('-3°C - Pioggia'));
    verifica('legacy: &ordm;', ['temperatura' => 5, 'condizione' => 'nuvoloso'], gdrcd_meteo_parse_legacy('5&ordm;C - Nuvoloso'));
    verifica('legacy: &deg;', ['temperatura' => 18, 'condizione' => 'nuvoloso'], gdrcd_meteo_parse_legacy('18&deg;c - nuvoloso'));
    verifica('legacy: º', ['temperatura' => 0, 'condizione' => 'variabile'], gdrcd_meteo_parse_legacy('0ºC - VARIABILE'));
    verifica('legacy: spazi', ['temperatura' => 7, 'condizione' => 'variabile'], gdrcd_meteo_parse_legacy('  7 ° C -  variabile '));
    verifica('legacy: testo libero', null, gdrcd_meteo_parse_legacy('bel tempo oggi'));
    verifica('legacy: etichetta sconosciuta', null, gdrcd_meteo_parse_legacy('20°C - Grandine'));
    verifica('legacy: ordine invertito', null, gdrcd_meteo_parse_legacy('Sereno - 12°C'));
    verifica('legacy: senza unità', null, gdrcd_meteo_parse_legacy('20 - sereno'));
    verifica('legacy: vuoto', null, gdrcd_meteo_parse_legacy(''));
    verifica('legacy: UTF-8 non valido', null, gdrcd_meteo_parse_legacy("20\xB0C - sereno"));
});

gruppo('gdrcd_meteo_fase_lunare', function () {
    // Valori dell'algoritmo storico gdrcd_lunar_phase()
    $attese = [
        '2024-01-11' => 0,
        '2024-01-25' => 4,
        '2024-02-09' => 0,
        '2024-04-08' => 0,
        '2024-06-22' => 4,
        '2024-12-30' => 0,
        '2025-03-14' => 4,
        '2025-03-29' => 0,
        '2025-09-07' => 4,
        '2025-11-17' => 7,
        '2026-01-03' => 4,
        '2026-11-17' => 2,
    ];

    foreach ($attese as $data => $fase) {
        verifica('fase lunare ' . $data, $fase, gdrcd_meteo_fase_lunare(istante($data . ' 12:00:00')));
    }

    verifica('fasi lunari: nomi immagini', [
        'nuova', 'crescente', 'primo-quarto', 'gibbosa-crescente',
        'piena', 'gibbosa-calante', 'ultimo-quarto', 'calante',
    ], gdrcd_meteo_fasi_lunari());
});

gruppo('gdrcd_meteo_formatta', function () {
    verifica('formatta: testo unificato', 'Sereno - 12°C', gdrcd_meteo_formatta('Sereno', 12));
    verifica('formatta: temperatura negativa', 'Neve - -3°C', gdrcd_meteo_formatta('Neve', -3));
});

gruppo('gdrcd_meteo_durata_predefinita', function () {
    $durate = [0, 1, 3, 6, 12, 24];

    verifica('durata predefinita: stringa ammessa', 6, gdrcd_meteo_durata_predefinita('6', $durate));
    verifica('durata predefinita: intero ammesso', 12, gdrcd_meteo_durata_predefinita(12, $durate));
    verifica('durata predefinita: spazi ignorati', 3, gdrcd_meteo_durata_predefinita(' 3 ', $durate));
    verifica('durata predefinita: non in elenco', 0, gdrcd_meteo_durata_predefinita('5', $durate));
    verifica('durata predefinita: negativa', 0, gdrcd_meteo_durata_predefinita('-1', $durate));
    verifica('durata predefinita: decimale', 0, gdrcd_meteo_durata_predefinita('1.5', $durate));
    verifica('durata predefinita: vuota', 0, gdrcd_meteo_durata_predefinita('', $durate));
    verifica('durata predefinita: null', 0, gdrcd_meteo_durata_predefinita(null, $durate));
});

gruppo('gdrcd_meteo_etichetta_durata', function () {
    verifica('etichetta durata: nessuna scadenza', 'Nessuna scadenza', gdrcd_meteo_etichetta_durata(0, 'Nessuna scadenza', '1 ora', '%d ore'));
    verifica('etichetta durata: un\'ora', '1 ora', gdrcd_meteo_etichetta_durata(1, 'Nessuna scadenza', '1 ora', '%d ore'));
    verifica('etichetta durata: più ore', '24 ore', gdrcd_meteo_etichetta_durata(24, 'Nessuna scadenza', '1 ora', '%d ore'));
    verifica('etichetta durata: negativa', 'Nessuna scadenza', gdrcd_meteo_etichetta_durata(-3, 'Nessuna scadenza', '1 ora', '%d ore'));
});

gruppo('gdrcd_meteo_orario_scadenza', function () {
    $now = istante('2025-06-01 12:00:00');

    verifica('orario scadenza: stesso giorno', '15:30', gdrcd_meteo_orario_scadenza('2025-06-01 15:30:00', $now));
    verifica('orario scadenza: fine giornata', '23:59', gdrcd_meteo_orario_scadenza('2025-06-01 23:59:59', $now));
    verifica('orario scadenza: giorno dopo', '02/06 00:00', gdrcd_meteo_orario_scadenza('2025-06-02 00:00:00', $now));
    verifica('orario scadenza: anno dopo, stesso giorno e mese', '01/06 15:30', gdrcd_meteo_orario_scadenza('2026-06-01 15:30:00', $now));
    verifica('orario scadenza: passata nello stesso giorno', '08:05', gdrcd_meteo_orario_scadenza('2025-06-01 08:05:00', $now));
    verifica('orario scadenza: null', null, gdrcd_meteo_orario_scadenza(null, $now));
    verifica('orario scadenza: data zero', null, gdrcd_meteo_orario_scadenza('0000-00-00 00:00:00', $now));
    verifica('orario scadenza: formato errato', null, gdrcd_meteo_orario_scadenza('2025-06-01 15:30', $now));
    verifica('orario scadenza: testo', null, gdrcd_meteo_orario_scadenza('domani', $now));
    verifica('orario scadenza: vuota', null, gdrcd_meteo_orario_scadenza('', $now));
    // 23:30 UTC del 31 maggio = 01:30 del 1 giugno a Roma: il giorno è quello di now
    verifica(
        'orario scadenza: giorno confrontato nel fuso di now',
        '15:30',
        gdrcd_meteo_orario_scadenza('2025-06-01 15:30:00',
            (new DateTimeImmutable('2025-05-31 23:30:00', new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Europe/Rome')))
    );
});

gruppo('gdrcd_meteo_valida_richiesta', function () {
    $now = istante('2025-06-01 12:34:56');
    $limiti = ['min' => -45, 'max' => 45];
    $condizioni = ['sereno', 'variabile', 'nuvoloso', 'pioggia', 'neve'];
    $durate = [0, 1, 3, 6, 12, 24];
    $valida = function (array $input) use ($limiti, $condizioni, $durate, $now) {
        return gdrcd_meteo_valida_richiesta($input, $limiti, $condizioni, $durate, $now);
    };

    verifica(
        'richiesta: senza scadenza',
        ['temperatura' => 12, 'condizione' => 'sereno', 'scadenza' => null],
        $valida(['temperatura' => '12', 'condizione' => 'sereno', 'durata' => '0'])
    );
    verifica(
        'richiesta: con scadenza',
        ['temperatura' => -3, 'condizione' => 'neve', 'scadenza' => '2025-06-01 15:34:56'],
        $valida(['temperatura' => '-3', 'condizione' => 'neve', 'durata' => '3'])
    );
    verifica(
        'richiesta: 24 ore',
        ['temperatura' => 20, 'condizione' => 'pioggia', 'scadenza' => '2025-06-02 12:34:56'],
        $valida(['temperatura' => '20', 'condizione' => 'pioggia', 'durata' => '24'])
    );
    verifica(
        'richiesta: temperatura limitata',
        ['temperatura' => 45, 'condizione' => 'sereno', 'scadenza' => null],
        $valida(['temperatura' => '999', 'condizione' => 'sereno', 'durata' => '0'])
    );
    verifica('richiesta: temperatura non intera', 'invalid_temperature', $valida(['temperatura' => '12.5', 'condizione' => 'sereno', 'durata' => '0']));
    verifica('richiesta: temperatura mancante', 'invalid_temperature', $valida(['condizione' => 'sereno', 'durata' => '0']));
    verifica('richiesta: temperatura array', 'invalid_temperature', $valida(['temperatura' => ['12'], 'condizione' => 'sereno', 'durata' => '0']));
    verifica('richiesta: temperatura da 10KB', 'invalid_temperature', $valida(['temperatura' => str_repeat('9', 10240), 'condizione' => 'sereno', 'durata' => '0']));
    verifica('richiesta: condizione sconosciuta', 'invalid_condition', $valida(['temperatura' => '12', 'condizione' => 'grandine', 'durata' => '0']));
    verifica('richiesta: condizione mancante', 'invalid_condition', $valida(['temperatura' => '12', 'durata' => '0']));
    verifica('richiesta: condizione array', 'invalid_condition', $valida(['temperatura' => '12', 'condizione' => ['sereno'], 'durata' => '0']));
    verifica('richiesta: condizione e temperatura errate', 'invalid_condition', $valida(['temperatura' => 'x', 'condizione' => 'x', 'durata' => '0']));
    verifica('richiesta: durata non in elenco', 'invalid_duration', $valida(['temperatura' => '12', 'condizione' => 'sereno', 'durata' => '5']));
    verifica('richiesta: durata con zero iniziale', 'invalid_duration', $valida(['temperatura' => '12', 'condizione' => 'sereno', 'durata' => '03']));
    verifica('richiesta: durata con spazi', 'invalid_duration', $valida(['temperatura' => '12', 'condizione' => 'sereno', 'durata' => ' 3']));
    verifica('richiesta: durata intera', 'invalid_duration', $valida(['temperatura' => '12', 'condizione' => 'sereno', 'durata' => 3]));
    verifica('richiesta: durata mancante', 'invalid_duration', $valida(['temperatura' => '12', 'condizione' => 'sereno']));
    verifica('richiesta: durata array', 'invalid_duration', $valida(['temperatura' => '12', 'condizione' => 'sereno', 'durata' => ['3']]));
    verifica('richiesta: durata da 10KB', 'invalid_duration', $valida(['temperatura' => '12', 'condizione' => 'sereno', 'durata' => str_repeat('1', 10240)]));
});

gruppo('gdrcd_meteo_id_richiesta', function () {
    verifica('id richiesta: cifre', 42, gdrcd_meteo_id_richiesta('42'));
    verifica('id richiesta: zero iniziale', 7, gdrcd_meteo_id_richiesta('007'));
    verifica('id richiesta: zero', null, gdrcd_meteo_id_richiesta('0'));
    verifica('id richiesta: negativo', null, gdrcd_meteo_id_richiesta('-1'));
    verifica('id richiesta: con spazi', null, gdrcd_meteo_id_richiesta(' 1'));
    verifica('id richiesta: newline finale', null, gdrcd_meteo_id_richiesta("1\n"));
    verifica('id richiesta: vuoto', null, gdrcd_meteo_id_richiesta(''));
    verifica('id richiesta: intero', null, gdrcd_meteo_id_richiesta(1));
    verifica('id richiesta: array', null, gdrcd_meteo_id_richiesta(['1']));
    verifica('id richiesta: null', null, gdrcd_meteo_id_richiesta(null));
    verifica('id richiesta: troppo lungo', null, gdrcd_meteo_id_richiesta(str_repeat('1', 11)));
});

gruppo('gdrcd_meteo_pannello_modello', function () {
    $now = istante('2025-06-01 12:00:00');
    $auto = ['temperatura' => 21, 'condizione' => 'sereno'];
    $etichette = ['sereno' => 'Sereno', 'pioggia' => 'Pioggia', 'neve' => 'Neve', 'nuvoloso' => 'Nuvoloso &amp; co'];
    $zona = function ($id, $nome, $principale, $t, $c, $s) {
        return ['id_click' => $id, 'nome' => $nome, 'principale' => $principale,
            'meteo_temperatura' => $t, 'meteo_condizione' => $c, 'meteo_scadenza' => $s];
    };
    $luogo = function ($id, $nome, $privata, $t, $c, $s) {
        return ['luogo_id' => $id, 'luogo_nome' => $nome, 'luogo_privata' => $privata,
            'luogo_meteo_temperatura' => $t, 'luogo_meteo_condizione' => $c, 'luogo_meteo_scadenza' => $s];
    };
    $senzaLuogo = $luogo(null, null, null, null, null, null);
    $meteo = function ($t, $c, $fonte, $s, $testo, $sTesto) {
        return ['temperatura' => $t, 'condizione' => $c, 'fonte' => $fonte, 'scadenza' => $s, 'testo' => $testo, 'scadenza_testo' => $sTesto];
    };

    // Zona 7 (principale) con valore fino a domani e tre chat; zona 3 scaduta con una chat; zona 9 senza chat
    $righe = [
        $zona(7, 'Città <alta>', 1, '10', 'pioggia', '2025-06-02 08:00:00') + $luogo(20, 'Taverna', 0, '-5', 'neve', '2025-06-01 13:00:00'),
        $zona(7, 'Città <alta>', 1, '10', 'pioggia', '2025-06-02 08:00:00') + $luogo(4, 'Alcova', 1, null, null, null),
        $zona(7, 'Città <alta>', 1, '10', 'pioggia', '2025-06-02 08:00:00') + $luogo(5, 'Cripta', 0, 2, 'neve', '2025-06-01 11:00:00'),
        $zona(3, 'Bosco', 0, 18, 'nuvoloso', '2025-05-31 12:00:00') + $luogo(8, 'Radura', 0, 30, 'sereno', null),
        $zona(9, null, 0, null, null, null) + $senzaLuogo,
    ];

    $atteso = [
        'auto_mode' => false,
        'zone' => [
            [
                'id_click' => 7,
                'nome' => 'Città <alta>',
                'principale' => true,
                'meteo' => $meteo(10, 'pioggia', 'zona', '2025-06-02 08:00:00', 'Pioggia - 10°C', '02/06 08:00'),
                'luoghi' => [
                    ['id' => 20, 'nome' => 'Taverna', 'privata' => false,
                        'meteo' => $meteo(-5, 'neve', 'luogo', '2025-06-01 13:00:00', 'Neve - -5°C', '13:00')],
                    ['id' => 4, 'nome' => 'Alcova', 'privata' => true,
                        'meteo' => $meteo(10, 'pioggia', 'zona', '2025-06-02 08:00:00', 'Pioggia - 10°C', '02/06 08:00')],
                    // Override scaduto: eredita dalla zona
                    ['id' => 5, 'nome' => 'Cripta', 'privata' => false,
                        'meteo' => $meteo(10, 'pioggia', 'zona', '2025-06-02 08:00:00', 'Pioggia - 10°C', '02/06 08:00')],
                ],
            ],
            [
                'id_click' => 3,
                'nome' => 'Bosco',
                'principale' => false,
                // Zona scaduta: automatico
                'meteo' => $meteo(21, 'sereno', 'auto', null, 'Sereno - 21°C', null),
                'luoghi' => [
                    ['id' => 8, 'nome' => 'Radura', 'privata' => false,
                        'meteo' => $meteo(30, 'sereno', 'luogo', null, 'Sereno - 30°C', null)],
                ],
            ],
            [
                'id_click' => 9,
                'nome' => '',
                'principale' => false,
                'meteo' => $meteo(21, 'sereno', 'auto', null, 'Sereno - 21°C', null),
                'luoghi' => [],
            ],
        ],
    ];

    verifica('pannello: raggruppamento, ordine e risoluzione', $atteso, gdrcd_meteo_pannello_modello($righe, false, $auto, $now, $etichette));

    // Modalità ON: tutto automatico, anche dove esiste un valore manuale attivo
    $autoRiga = $meteo(21, 'sereno', 'auto', null, 'Sereno - 21°C', null);
    $modello = gdrcd_meteo_pannello_modello($righe, true, $auto, $now, $etichette);

    verifica('pannello ON: auto_mode', true, $modello['auto_mode']);
    verifica('pannello ON: zone nello stesso ordine', [7, 3, 9], array_column($modello['zone'], 'id_click'));
    verifica('pannello ON: chat nello stesso ordine', [20, 4, 5], array_column($modello['zone'][0]['luoghi'], 'id'));

    foreach ($modello['zone'] as $z) {
        verifica('pannello ON: zona ' . $z['id_click'] . ' automatica', $autoRiga, $z['meteo']);

        foreach ($z['luoghi'] as $l) {
            verifica('pannello ON: chat ' . $l['id'] . ' automatica', $autoRiga, $l['meteo']);
        }
    }

    // Etichetta già filtrata passata così com'è, slug senza etichetta in ucfirst
    $modello = gdrcd_meteo_pannello_modello([
        $zona(1, 'A', 0, 4, 'nuvoloso', null) + $luogo(2, 'B', 0, 1, 'variabile', null),
    ], false, $auto, $now, $etichette);

    verifica('pannello: etichetta già filtrata', 'Nuvoloso &amp; co - 4°C', $modello['zone'][0]['meteo']['testo']);
    verifica('pannello: etichetta mancante', 'Variabile - 1°C', $modello['zone'][0]['luoghi'][0]['meteo']['testo']);

    verifica('pannello: nessuna zona', ['auto_mode' => false, 'zone' => []], gdrcd_meteo_pannello_modello([], false, $auto, $now, $etichette));
});


/* ------------------------------------------------------------------------- */

$falliti = $GLOBALS['falliti'];

foreach ($falliti as $fallito) {
    echo 'FAIL ' . $fallito . PHP_EOL;
}

printf("%d verifiche superate, %d fallite\n", $GLOBALS['passati'], count($falliti));

exit(count($falliti) === 0 ? 0 : 1);
