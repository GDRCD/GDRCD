<?php

/**
 * Impostazioni del meteo nella tabella configurazioni (categoria `meteo`), modificabili da Gestione > Configurazioni.
 * Si leggono con gdrcd_configuration_get('meteo.<parametro>').
 *
 * Gli INSERT sono idempotenti grazie alla chiave unica (categoria, parametro): se il parametro esiste già il suo
 * valore non viene sovrascritto.
 * Le migrazioni non aggiornano da sole cache/configuration.cache.php, per cui up() e down() la rigenerano.
 */
class GDRCD57_Insert_Meteo_In_Configurazioni_Table extends DbMigration
{
    /** Parametri gestiti dalla migrazione: [ordinamento, opzioni, default, parametro, descrizione, input] */
    const PARAMETRI = [
        [1, 'si,no', 'si', 'fasi_lunari', 'Mostra la fase lunare nel riquadro delle condizioni meteo (sostituisce la costante MOON)', 'select'],
        [2, '', '0', 'durata_default', 'Durata predefinita, in ore, del meteo impostato dai master. 0 = nessuna scadenza', 'input'],
        [3, '', '-45', 'temperatura_min', 'Temperatura minima (in °C) accettata quando si imposta il meteo', 'input'],
        [4, '', '45', 'temperatura_max', 'Temperatura massima (in °C) accettata quando si imposta il meteo', 'input'],
    ];

    public function up()
    {
        // Inserimento parametri su tabella configurazioni
        foreach (self::PARAMETRI as [$ordinamento, $opzioni, $default, $parametro, $descrizione, $input]) {
            gdrcd_stmt(
                "INSERT INTO `configurazioni` (`tipo`, `categoria`, `ordinamento`, `opzioni`, `default`, `parametro`, `valore`, `descrizione`, `input`)
                 VALUES ('string', 'meteo', ?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE `id` = `id`",
                [$ordinamento, $opzioni, $default, $parametro, $default, $descrizione, $input]
            );
        }

        $this->rebuildConfigurationCache();
    }

    public function down()
    {
        // Rimozione parametri inseriti in up()
        $parametri = array_column(self::PARAMETRI, 3);
        $segnaposto = implode(', ', array_fill(0, count($parametri), '?'));

        gdrcd_stmt(
            "DELETE FROM `configurazioni` WHERE categoria = ? AND parametro IN ({$segnaposto})",
            array_merge(['meteo'], $parametri)
        );

        $this->rebuildConfigurationCache();
    }

    /**
     * Rigenera la cache delle configurazioni. Se non è possibile scriverla, la elimina: verrà ricreata dalla prossima
     * chiamata a gdrcd_configuration_get(). Una cache non aggiornata invece non verrebbe mai rigenerata.
     * @throws Exception se la cache non può essere né rigenerata né eliminata
     */
    private function rebuildConfigurationCache()
    {
        try {
            gdrcd_configuration_cache_create();
        } catch (Exception $e) {
            $cacheFilename = gdrcd_configuration_cache_filename();

            if (file_exists($cacheFilename) && !@unlink($cacheFilename)) {
                throw new Exception('Impossibile aggiornare o eliminare la cache delle configurazioni: ' . $cacheFilename, 0, $e);
            }
        }
    }
}
