<?php

/**
 * Meteo strutturato: aggiunge temperatura, condizione (slug) e scadenza a zone (mappa_click) e luoghi (mappa).
 *
 * - condizione NULL: nessun valore manuale (il luogo eredita dalla zona, la zona usa il meteo automatico)
 * - scadenza NULL: il valore manuale non scade
 *
 * La colonna legacy mappa_click.meteo non viene toccata: resta per una release e viene convertita nelle nuove
 * colonne dal backfill in up().
 *
 * La migrazione si può rieseguire senza errori: le colonne vengono aggiunte/rimosse solo se (non) esistono e il
 * backfill aggiorna solo le zone ancora prive di condizione. Serve perché le DDL fanno commit implicito: se up()
 * fallisce a metà la migrazione non viene registrata, ma le colonne già aggiunte restano.
 */
class GDRCD57_Add_Meteo_Columns_In_Mappa_And_Mappa_Click_Tables extends DbMigration
{
    /** Valore di default della colonna mappa_click.meteo (vedi 2020072500_GDRCD551.php) */
    const METEO_DEFAULT = '20°c - sereno';

    public function up()
    {
        // Colonne su mappa_click (zone), posizionate dopo la colonna legacy `meteo`
        $this->addMissingColumns('mappa_click', [
            'meteo_temperatura' => "SMALLINT    NULL DEFAULT NULL AFTER `meteo`",
            'meteo_condizione' => "VARCHAR(20) NULL DEFAULT NULL AFTER `meteo_temperatura`",
            'meteo_scadenza' => "DATETIME    NULL DEFAULT NULL AFTER `meteo_condizione`",
        ]);

        // Colonne su mappa (luoghi/chat), in coda alla tabella
        $this->addMissingColumns('mappa', [
            'meteo_temperatura' => "SMALLINT    NULL DEFAULT NULL",
            'meteo_condizione' => "VARCHAR(20) NULL DEFAULT NULL",
            'meteo_scadenza' => "DATETIME    NULL DEFAULT NULL",
        ]);

        $this->backfillZone();
    }

    public function down()
    {
        $columns = ['meteo_temperatura', 'meteo_condizione', 'meteo_scadenza'];

        $this->dropExistingColumns('mappa_click', $columns);
        $this->dropExistingColumns('mappa', $columns);
    }

    /**
     * Converte il meteo legacy delle zone (es. '12°C - Neve') nelle nuove colonne.
     * Le zone rimaste al valore di default della colonna non vengono convertite, così passano al meteo automatico
     * invece di restare ferme a 20°C. Le stringhe non riconosciute restano NULL (meteo automatico).
     * meteo_scadenza resta NULL: il valore importato non scade.
     */
    private function backfillZone()
    {
        if (!function_exists('gdrcd_meteo_parse_legacy')) {
            require_once __DIR__ . '/../includes/functions.meteo.inc.php';
        }

        // Carico tutte le righe prima di aggiornare, per non intrecciare lettura e scrittura
        $zone = iterator_to_array(gdrcd_stmt_all(
            "SELECT id_click, meteo FROM mappa_click WHERE meteo_condizione IS NULL"
        ), false);

        foreach ($zone as $zona) {
            $meteo = trim((string)$zona['meteo']);

            if ($meteo === '' || mb_strtolower($meteo, 'UTF-8') === self::METEO_DEFAULT) {
                continue;
            }

            $parsed = gdrcd_meteo_parse_legacy($meteo);

            if ($parsed === null) {
                continue;
            }

            gdrcd_stmt(
                "UPDATE mappa_click
                    SET meteo_temperatura = ?, meteo_condizione = ?
                  WHERE id_click = ? AND meteo_condizione IS NULL",
                [
                    (int)$parsed['temperatura'],
                    (string)$parsed['condizione'],
                    (int)$zona['id_click'],
                ]
            );
        }
    }

    /**
     * Aggiunge con un unico ALTER le colonne che non esistono ancora sulla tabella.
     * Una sola ALTER per tabella: con MyISAM le DDL non sono transazionali, così la modifica resta tutto-o-niente.
     * @param string $table
     * @param array<string, string> $definitions nome colonna => definizione, nell'ordine di creazione
     */
    private function addMissingColumns($table, array $definitions)
    {
        $clauses = [];

        foreach ($definitions as $column => $definition) {
            if (!$this->columnExists($table, $column)) {
                $clauses[] = "ADD COLUMN `{$column}` {$definition}";
            }
        }

        if (!empty($clauses)) {
            $this->alterTable("ALTER TABLE `{$table}`\n  " . implode(",\n  ", $clauses));
        }
    }

    /**
     * Rimuove con un unico ALTER le colonne indicate che esistono sulla tabella.
     * @param string $table
     * @param string[] $columns
     */
    private function dropExistingColumns($table, array $columns)
    {
        $clauses = [];

        foreach ($columns as $column) {
            if ($this->columnExists($table, $column)) {
                $clauses[] = "DROP COLUMN `{$column}`";
            }
        }

        if (!empty($clauses)) {
            $this->alterTable("ALTER TABLE `{$table}` " . implode(', ', $clauses));
        }
    }

    /**
     * Esegue un ALTER TABLE disattivando per la sola sessione NO_ZERO_DATE e NO_ZERO_IN_DATE.
     * Le installazioni storiche hanno date '0000-00-00 00:00:00' in mappa (ora_prenotazione, scadenza): con MyISAM
     * l'ALTER ricopia la tabella e, con la modalità strict di MySQL 8, fallirebbe con l'errore 1292.
     * La sql_mode originale viene sempre ripristinata.
     * @param string $sql
     */
    private function alterTable($sql)
    {
        $row = gdrcd_stmt_one("SELECT @@SESSION.sql_mode AS sql_mode");
        $sqlMode = (string)($row['sql_mode'] ?? '');
        $relaxedMode = implode(',', array_diff(explode(',', $sqlMode), ['NO_ZERO_DATE', 'NO_ZERO_IN_DATE']));

        gdrcd_stmt("SET SESSION sql_mode = ?", [$relaxedMode]);

        try {
            gdrcd_query($sql);
        } finally {
            gdrcd_stmt("SET SESSION sql_mode = ?", [$sqlMode]);
        }
    }

    /**
     * @param string $table
     * @param string $column
     * @return bool
     */
    private function columnExists($table, $column)
    {
        $row = gdrcd_stmt_one(
            "SELECT COUNT(*) AS n
               FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
            [$table, $column]
        );

        return (int)($row['n'] ?? 0) > 0;
    }
}
