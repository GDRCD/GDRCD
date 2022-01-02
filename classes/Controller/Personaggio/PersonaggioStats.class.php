<?php


class PersonaggioStats extends Personaggio
{

    /**** TABLES HELPERS ***/

    /**
     * @fn getPgAllStats
     * @note Estrae tutte le statistiche di un personaggio
     * @param int $id_pg
     * @param string $val
     * @return bool|int|mixed|string
     */
    public static function getPgAllStats(int $id_pg, string $val = 'statistiche.*,personaggio_statistiche.valore')
    {
        return DB::query("SELECT {$val}
                                FROM statistiche 
                                LEFT JOIN personaggio_statistiche ON 
                                    (statistiche.id = personaggio_statistiche.statistica AND personaggio_statistiche.personaggio ='{$id_pg}')
                                WHERE 1 ORDER BY statistiche.nome", 'result');
    }

    /**
     * @fn getPgObject
     * @note Estrae i dati di una statistica di un personaggio
     * @param int $id
     * @param int $pg
     * @param string $val
     * @return bool|int|mixed|string
     */
    public static function getPgStat(int $id, int $pg, string $val = 'statistiche.*,personaggio_statistiche.*')
    {
        return DB::query("SELECT {$val}
                                FROM personaggio_statistiche 
                                LEFT JOIN statistiche ON (statistiche.id = personaggio_statistiche.statistica)
                                  WHERE personaggio_statistiche.statistica = '{$id}' AND personaggio_statistiche.personaggio ='{$pg}' LIMIT 1");
    }

    /**
     * @fn getPgObject
     * @note Estrae i dati di una statistica di un personaggio
     * @param int $id
     * @param int $pg
     * @param string $val
     * @return bool|int|mixed|string
     */
    public static function getPgStatByStatId(int $id, int $pg, string $val = '*')
    {
        return DB::query("SELECT {$val}
                                FROM personaggio_statistiche 
                                  WHERE personaggio_statistiche.statistica = '{$id}' AND personaggio_statistiche.personaggio = '{$pg}'
                                LIMIT 1");
    }

    /*** PERMESSI ***/

    /**
     * @fn permissionUpgradeStats
     * @note Controlla che si abbiano i permessi per aumentare le statistiche
     * @return bool
     */
    public static function permissionUpgradeStats(): bool
    {
        return Permissions::permission('UPGRADE_SCHEDA_STATS');
    }

    /**
     * @fn permissionDowngradeStats
     * @note Controlla che si abbiano i permessi per diminuire le statistiche
     * @return bool
     */
    public static function permissionDowngradeStats(): bool
    {
        return Permissions::permission('DOWNGRADE_SCHEDA_STATS');
    }

    /*** CONTROLS ***/

    /**
     * @fn isPgStatUpgradable
     * @note Controlla se la statistica e' aumentabile
     * @param int $id
     * @param int $pg
     * @return bool
     */
    public static function isPgStatUpgradable(int $id, int $pg): bool
    {

        $stat_class = Statistiche::getInstance();
        $res = false;

        if (self::permissionUpgradeStats()) {

            $stat_data = $stat_class->getStat($id, 'max_val');
            $max_val = Filters::int($stat_data['max_val']);

            $pg_stat_data = self::getPgStatByStatId($id, $pg);
            $pg_val = !empty($pg_stat_data) ? Filters::int($pg_stat_data['valore']) : 0;

            if ($pg_val < $max_val) {
                return true;
            }


        }

        return $res;

    }

    /**
     * @fn isPgStatDowngradable
     * @note Controlla se la statistica e' aumentabile
     * @param int $id
     * @param int $pg
     * @return bool
     */
    public static function isPgStatDowngradable(int $id, int $pg): bool
    {

        $stat_class = Statistiche::getInstance();
        $res = false;

        if (self::permissionUpgradeStats()) {

            $stat_data = $stat_class->getStat($id, 'min_val');
            $min_val = Filters::int($stat_data['min_val']);

            $pg_stat_data = self::getPgStatByStatId($id, $pg);
            $pg_val = !empty($pg_stat_data) ? Filters::int($pg_stat_data['valore']) : 0;

            if ($pg_val > $min_val) {
                return true;
            }


        }

        return $res;

    }

    /**
     * @fn existPgStat
     * @note Controlla se una statistica esiste per un pg
     * @param int $id
     * @param int $pg
     * @return bool
     */
    public static function existPgStat(int $id, int $pg): bool
    {
        $data = self::getPgStat($id, $pg);
        $count = DB::rowsNumber($data);

        return ($count > 0);
    }

    /*** FUNCTIONS ***/

    /**
     * @fn upgradePgStat
     * @note Aumenta una statistica di un personaggio
     * @param int $id
     * @param int $pg
     * @return array
     */
    public static function upgradePgStat(int $id, int $pg): array
    {

        $response = [
            'mex' => Filters::out('Permesso negato.'),
            'response' => false
        ];

        if (self::isPgStatUpgradable($id, $pg)) {


            if (self::existPgStat($id, $pg)) {
                DB::query("UPDATE personaggio_statistiche SET valore= valore+1 
                            WHERE personaggio_statistiche.statistica='{$id}' AND personaggio_statistiche.personaggio='{$pg}'");
            } else {
                if(Statistiche::existStat($id)) {
                    DB::query("INSERT INTO personaggio_statistiche(personaggio, statistica, valore) VALUES('{$pg}','{$id}',1)");
                }
            }
            $response = [
                'mex' => Filters::out('Stastistica aumentata con successo.'),
                'response' => true
            ];
        }

        return $response;
    }

    /**
     * @fn downgradePgStat
     * @note Diminuisce una statistica di un personaggio
     * @param int $id
     * @param int $pg
     * @return array
     */
    public static function downgradePgStat(int $id, int $pg): array
    {
        $response = [
            'mex' => Filters::out('Permesso negato.'),
            'response' => false
        ];

        if (self::isPgStatDowngradable($id, $pg) && Statistiche::existStat($id)) {


            DB::query("UPDATE personaggio_statistiche SET valore= valore-1 
                            WHERE personaggio_statistiche.statistica='{$id}' AND personaggio_statistiche.personaggio='{$pg}'");

            $response = [
                'mex' => Filters::out('Stastistica diminuita con successo.'),
                'response' => true
            ];
        }

        return $response;
    }
}