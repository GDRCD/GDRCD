<?php

class PersonaggioStats extends Personaggio
{

    /**** TABLES HELPERS ***/

    /**
     * @fn getPgAllStats
     * @note Estrae tutte le statistiche di un personaggio
     * @param int $id_pg
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public static function getPgAllStats(int $id_pg, string $val = 'statistiche.*,personaggio_statistiche.valore'): DBQueryInterface
    {
        return DB::queryStmt(
            "SELECT {$val} FROM statistiche 
                  LEFT JOIN personaggio_statistiche ON (statistiche.id = personaggio_statistiche.statistica AND personaggio_statistiche.personaggio =:pg)
                  WHERE 1 ORDER BY statistiche.nome",
            ['pg' => $id_pg]
        );
    }

    /**
     * @fn getPgObject
     * @note Estrae i dati di una statistica di un personaggio
     * @param int $id
     * @param int $pg
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public static function getPgStat(int $id, int $pg, string $val = 'statistiche.*,personaggio_statistiche.*'): DBQueryInterface
    {
        return DB::queryStmt(
            "SELECT {$val} FROM personaggio_statistiche 
                  LEFT JOIN statistiche ON (statistiche.id = personaggio_statistiche.statistica)
                  WHERE personaggio_statistiche.statistica = :id AND personaggio_statistiche.personaggio =:pg LIMIT 1",
            ['id' => $id, 'pg' => $pg]
        );
    }

    /**
     * @fn getPgObject
     * @note Estrae i dati di una statistica di un personaggio
     * @param int $id
     * @param int $pg
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public static function getPgStatByStatId(int $id, int $pg, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt(
            "SELECT {$val} FROM personaggio_statistiche 
                  WHERE personaggio_statistiche.statistica = :id AND personaggio_statistiche.personaggio = :pg
                  LIMIT 1",
            ['id' => $id, 'pg' => $pg]
        );
    }

    /*** PERMESSI ***/

    /**
     * @fn permissionUpgradeStats
     * @note Controlla che si abbiano i permessi per aumentare le statistiche
     * @return bool
     * @throws Throwable
     */
    public static function permissionUpgradeStats(): bool
    {
        return Permissions::permission('UPGRADE_SCHEDA_STATS');
    }

    /**
     * @fn permissionDowngradeStats
     * @note Controlla che si abbiano i permessi per diminuire le statistiche
     * @return bool
     * @throws Throwable
     */
    public static function permissionDowngradeStats(): bool
    {
        return Permissions::permission('DOWNGRADE_SCHEDA_STATS');
    }

    /*** CONTROLS ***/

    /**
     * @fn isPgStatUpgradable
     * @note Controlla se la statistica è aumentabile
     * @param int $id
     * @param int $pg
     * @return bool
     * @throws Throwable
     */
    public static function isPgStatUpgradable(int $id, int $pg): bool
    {

        $stat_class = Statistiche::getInstance();

        if ( self::permissionUpgradeStats() ) {

            $stat_data = $stat_class->getStat($id, 'max_val');
            $max_val = Filters::int($stat_data['max_val']);

            $pg_stat_data = self::getPgStatByStatId($id, $pg);
            $pg_val = !empty($pg_stat_data) ? Filters::int($pg_stat_data['valore']) : 0;

            if ( $pg_val < $max_val ) {
                return true;
            }

        }

        return false;

    }

    /**
     * @fn isPgStatDowngradable
     * @note Controlla se la statistica è aumentabile
     * @param int $id
     * @param int $pg
     * @return bool
     * @throws Throwable
     */
    public static function isPgStatDowngradable(int $id, int $pg): bool
    {

        $stat_class = Statistiche::getInstance();

        if ( self::permissionUpgradeStats() ) {

            $stat_data = $stat_class->getStat($id, 'min_val');
            $min_val = Filters::int($stat_data['min_val']);

            $pg_stat_data = self::getPgStatByStatId($id, $pg);
            $pg_val = !empty($pg_stat_data) ? Filters::int($pg_stat_data['valore']) : 0;

            if ( $pg_val > $min_val ) {
                return true;
            }

        }

        return false;
    }

    /**
     * @fn existPgStat
     * @note Controlla se una statistica esiste per un pg
     * @param int $id
     * @param int $pg
     * @return bool
     * @throws Throwable
     */
    public static function existPgStat(int $id, int $pg): bool
    {
        $data = self::getPgStat($id, $pg);
        return (DB::rowsNumber($data) > 0);
    }

    /*** FUNCTIONS ***/

    /**
     * @fn upgradePgStat
     * @note Aumenta una statistica di un personaggio
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public static function upgradePgStat(array $post): array
    {
        $id = Filters::int($post['stat']);
        $pg = Filters::int($post['pg']);

        if ( self::isPgStatUpgradable($id, $pg) ) {
            if ( self::existPgStat($id, $pg) ) {
                DB::queryStmt(
                    "UPDATE personaggio_statistiche SET valore= valore+1 
                            WHERE personaggio_statistiche.statistica=:id AND personaggio_statistiche.personaggio=:pg",
                    ['id' => $id, 'pg' => $pg]
                );
            } else {
                if ( Statistiche::existStat($id) ) {
                    DB::queryStmt(
                        "INSERT INTO personaggio_statistiche(personaggio, statistica, valore) VALUES(:pg,:id,1)",
                        ['id' => $id, 'pg' => $pg]
                    );
                }
            }
            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Statistica aumentata con successo.',
                'swal_type' => 'success',
                'new_template' => SchedaStats::getInstance()->statsPage($pg),
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione negata!',
                'swal_message' => 'Statistica non aumentabile ulteriormente.',
                'swal_type' => 'error',
            ];
        }
    }

    /**
     * @fn downgradePgStat
     * @note Diminuisce una statistica di un personaggio
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public static function downgradePgStat(array $post): array
    {
        $id = Filters::int($post['stat']);
        $pg = Filters::int($post['pg']);

        if ( self::isPgStatDowngradable($id, $pg) && Statistiche::existStat($id) ) {

            DB::queryStmt(
                "UPDATE personaggio_statistiche SET valore= valore-1 
                            WHERE personaggio_statistiche.statistica=:id AND personaggio_statistiche.personaggio=:pg",
                [
                    'id' => $id,
                    'pg' => $pg,
                ]
            );

            return ['response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Statistica diminuita con successo.',
                'swal_type' => 'success',
                'new_template' => SchedaStats::getInstance()->statsPage($pg),
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione negata!',
                'swal_message' => 'Statistica non aumentabile ulteriormente.',
                'swal_type' => 'error',
            ];
        }

    }
}