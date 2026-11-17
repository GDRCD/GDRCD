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


/* ------------------------------------------------------------------------- */

$falliti = $GLOBALS['falliti'];

foreach ($falliti as $fallito) {
    echo 'FAIL ' . $fallito . PHP_EOL;
}

printf("%d verifiche superate, %d fallite\n", $GLOBALS['passati'], count($falliti));

exit(count($falliti) === 0 ? 0 : 1);
