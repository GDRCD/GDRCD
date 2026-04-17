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
    if (!gdrcd_configuration_cache_exist()) {
        gdrcd_configuration_cache_create();
    }

    $configuration = gdrcd_configuration_cache_get();

    return $configuration[$parametro] ?? null;
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

function gdrcd_configuration_cache_filename()
{
    return GDRCD_PATH . 'cache/configuration.cache.php';
}

function gdrcd_configuration_cache_exist()
{
    return file_exists(gdrcd_configuration_cache_filename());
}

function gdrcd_configuration_cache_create()
{
    $configurations = gdrcd_stmt_all('SELECT categoria, parametro, valore, `default` FROM configurazioni');

    $configMap = [];

    foreach ($configurations as $config) {
        $key = implode('.', [$config['categoria'], $config['parametro']]);

        $configMap[$key] = !is_null($config['valore']) && $config['valore'] !== ''
            ? $config['valore']
            : $config['default'];
    }

    $cacheContent = '<?php return '. var_export($configMap, true) .';';

    $cacheFilename = gdrcd_configuration_cache_filename();
    $cacheFolder = dirname($cacheFilename);

    if (!file_exists($cacheFolder) && !mkdir($cacheFolder, 0777, true)) {
        throw new Exception('Errore! Non posso creare la cartella in: '. $cacheFolder);
    }

    if (file_put_contents($cacheFilename, $cacheContent, LOCK_EX) === false) {
        throw new Exception('Errore! Non posso creare il file in: '. $cacheFilename);
    }
}

function gdrcd_configuration_cache_get()
{
    require gdrcd_configuration_cache_filename();
}
