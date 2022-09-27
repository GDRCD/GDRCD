<?php

class Statistiche extends BaseClass
{
    /*** OGGETTO TABLE HELPERS ***/

    /**
     * @fn getStat
     * @note Estrae i dati di una singola statistica
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getStat(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM statistiche WHERE id=:id LIMIT 1",[
            'id' => $id,
        ]);
    }

    /**
     * @fn getAllStats
     * @note Estrae le statistiche
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllStats(string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM statistiche WHERE 1 ORDER BY nome", []);
    }

    /*** AJAX ***/

    /**
     * @fn ajaxStatData
     * @note Estrae i dati di una statistica alla modifica
     * @param array $post
     * @return array|void
     * @throws Throwable
     */
    public function ajaxStatData(array $post)
    {
        if ( $this->permissionManageStatistics() ) {
            $id = Filters::int($post['id']);
            $data = $this->getStat($id);

            return [
                'nome' => Filters::out($data['nome']),
                'descrizione' => Filters::out($data['descrizione']),
                'max_val' => Filters::int($data['max_val']),
                'min_val' => Filters::int($data['min_val']),
                'iscrizione' => Filters::int($data['iscrizione']),
            ];
        }
    }

    /*** TABLES CONTROLS ***/

    /**
     * @fn existStat
     * @note Controlla se una statistica esiste
     * @param int $id
     * @return bool
     * @throws Throwable
     */
    public static function existStat(int $id): bool
    {
        $data = self::getInstance()->getStat($id, 'id');
        return ($data->getNumRows() > 0);
    }

    /*** PERMISSION ***/

    /**
     * @fn permissionManageObjects
     * @note Controlla se si hanno i permessi per la gestione degli oggetti
     * @return bool
     */
    public function permissionManageStatistics(): bool
    {
        return Permissions::permission('MANAGE_STATS');
    }

    /*** LISTS ***/

    /**
     * @fn listStats
     * @note Crea le select delle statistiche
     * @param int $selected
     * @return string
     * @throws Throwable
     */
    public function listStats(int $selected = 0): string
    {
        $list = $this->getAllStats('id,nome');
        return Template::getInstance()->startTemplate()->renderSelect('id','nome',$selected,$list);
    }

    /*** MANAGEMENT FUNCTIONS - STATISTIC **/

    /**
     * @fn insertStat
     * @note Inserimento statistica
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function insertStat(array $post): array
    {
        if ( $this->permissionManageStatistics() ) {
            $nome = Filters::in($post['nome']);
            $descrizione = Filters::in($post['descr']);
            $max = Filters::int($post['max_val']);
            $min = Filters::int($post['min_val']);
            $iscr = Filters::checkbox($post['iscrizione']);
            $creato_da = Filters::int($this->me_id);

            DB::queryStmt("INSERT INTO statistiche (nome,descrizione,max_val,min_val,iscrizione,creato_da) VALUES (:nome,:descrizione,:max_val,:min_val,:iscrizione,:creato_da)",[
                'nome' => $nome,
                'descrizione' => $descrizione,
                'max_val' => $max,
                'min_val' => $min,
                'iscrizione' => $iscr,
                'creato_da' => $creato_da,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Statistica inserita correttamente.',
                'swal_type' => 'success',
                'stat_list' => $this->listStats(),
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

    /**
     * @fn editStat
     * @note Modifica oggetto
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function editStat(array $post): array
    {
        if ( $this->permissionManageStatistics() ) {
            $id = Filters::int($post['stat']);
            $nome = Filters::in($post['nome']);
            $descrizione = Filters::in($post['descr']);
            $max = Filters::int($post['max_val']);
            $min = Filters::int($post['min_val']);
            $iscr = Filters::checkbox($post['iscrizione']);
            $creato_da = Filters::int($this->me_id);

            DB::queryStmt("UPDATE statistiche SET nome=:nome,descrizione=:descrizione,max_val=:max_val,min_val=:min_val,iscrizione=:iscrizione,creato_da=:creato_da WHERE id=:id",[
                'id' => $id,
                'nome' => $nome,
                'descrizione' => $descrizione,
                'max_val' => $max,
                'min_val' => $min,
                'iscrizione' => $iscr,
                'creato_da' => $creato_da,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Statistica modificata correttamente.',
                'swal_type' => 'success',
                'stat_list' => $this->listStats(),
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

    /**
     * @fn deleteStat
     * @note Eliminazione statistica
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function deleteStat(array $post): array
    {
        if ( $this->permissionManageStatistics() ) {
            $id = Filters::int($post['stat']);

            DB::queryStmt("DELETE FROM statistiche WHERE id=:id",[
                'id' => $id,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Statistica eliminata correttamente.',
                'swal_type' => 'success',
                'stat_list' => $this->listStats(),
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