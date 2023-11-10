<?php

class OggettiStatistiche extends Oggetti
{

    /*** TABLE HELPERS ***/

    /**
     * @fn getObjectStat
     * @note Estrae i dati della statistica di un oggetto
     * @param int $object
     * @param int $stat
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getObjectStat(int $object, int $stat, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM oggetto_statistiche WHERE oggetto=:object AND statistica=:stat LIMIT 1", [
            'object' => $object,
            'stat' => $stat,
        ]);
    }

    /**
     * @fn getObjectStats
     * @note Estrae i dati delle statistiche di un oggetto
     * @param int $object
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getObjectStats(int $object, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM oggetto_statistiche WHERE oggetto=:object", [
            'object' => $object,
        ]);
    }


    /*** PERMISSION ***/

    /**
     * @fn permissionManageObjectsStats
     * @note Controlla se si hanno i permessi per gestire le statistiche degli oggetti
     * @return bool
     * @throws Throwable
     */
    public function permissionManageObjectsStats(): bool
    {
        return Permissions::permission('MANAGE_OBJECTS_STATS');
    }

    /*** CONTROLS ***/

    /**
     * @fn existObjectStat
     * @note Controlla se esiste una statistica per un oggetto
     * @param int $object
     * @param int $stat
     * @return bool
     * @throws Throwable
     */
    public function existObjectStat(int $object, int $stat): bool
    {
        return $this->getObjectStat($object, $stat, 'id')->getNumRows() > 0;
    }

    /*** AJAX ***/

    /**
     * @fn ajaxObjectStatData
     * @note Estrae i dati di una statistica di un oggetto
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function ajaxObjectStatData(array $post): array
    {

        $object = Filters::int($post['oggetto']);
        $stat = Filters::int($post['stat']);

        if ( $this->permissionManageObjectsStats() && $this->existObjectStat($object, $stat) ) {
            return $this->getObjectStat($object, $stat)->getData()[0];
        } else {
            return [];
        }
    }

    /*** MANAGEMENT FUNCTIONS - OBJECT TYPES **/

    /**
     * @fn newObjectStat
     * @note Crea una nuova statistica per un oggetto
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function newObjectStat(array $post): array
    {

        if ( $this->permissionManageObjectsStats() ) {

            $object = Filters::int($post['oggetto']);
            $stat = Filters::int($post['stat']);
            $value = Filters::int($post['valore']);

            if ( !$this->existObjectStat($object, $stat) ) {
                DB::queryStmt("INSERT INTO oggetto_statistiche (oggetto,statistica,valore,creato_da) VALUES (:object,:stat,:value,:created_by)", [
                    'object' => $object,
                    'stat' => $stat,
                    'value' => $value,
                    'created_by' => $this->me_id,
                ]);

                return [
                    'response' => true,
                    'swal_title' => 'Operazione riuscita!',
                    'swal_message' => 'Statistica Oggetto inserita correttamente.',
                    'swal_type' => 'success',
                ];
            } else {
                return [
                    'response' => false,
                    'swal_title' => 'Operazione fallita!',
                    'swal_message' => 'La statistica per questo oggetto esiste già.',
                    'swal_type' => 'error',
                ];
            }
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error',
            ];
        }
    }

    /**
     * @fn editObjectStat
     * @note Modifica una statistica di un oggetto
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function editObjectStat(array $post): array
    {

        if ( $this->permissionManageObjectsStats() ) {

            $object = Filters::int($post['oggetto']);
            $stat = Filters::int($post['stat']);
            $value = Filters::int($post['valore']);

            if ( $this->existObjectStat($object, $stat) ) {

                DB::queryStmt("UPDATE oggetto_statistiche SET valore=:value WHERE oggetto=:object AND statistica=:stat", [
                    'object' => $object,
                    'stat' => $stat,
                    'value' => $value,
                ]);

                return [
                    'response' => true,
                    'swal_title' => 'Operazione riuscita!',
                    'swal_message' => 'Statistica Oggetto modificata correttamente.',
                    'swal_type' => 'success',
                ];
            } else {
                return [
                    'response' => false,
                    'swal_title' => 'Operazione fallita!',
                    'swal_message' => 'La statistica per questo oggetto non esiste.',
                    'swal_type' => 'error',
                ];
            }
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error',
            ];
        }
    }

    /**
     * @fn deleteObjectStat
     * @note Eliminazione di una statistica di un oggetto
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function deleteObjectStat(array $post): array
    {

        if ( $this->permissionManageObjectsStats() ) {

            $object = Filters::int($post['oggetto']);
            $stat = Filters::int($post['stat']);

            DB::queryStmt("DELETE FROM oggetto_statistiche WHERE oggetto=:object AND statistica=:stat", [
                'object' => $object,
                'stat' => $stat,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Statistica Oggetto eliminata correttamente.',
                'swal_type' => 'success',
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error',
            ];
        }
    }

}