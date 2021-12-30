<?php

class Statistiche extends BaseClass{


    /**
     * @fn __construct
     * @note Class constructor
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /*** OGGETTO TABLE HELPERS ***/

    /**
     * @fn getStat
     * @note Estrae i dati di una singola statistica
     * @param int $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getStat(int $id, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM statistiche WHERE id='{$id}' LIMIT 1");
    }

    /**
     * @fn getStats
     * @note Estrae le statistiche
     * @param int $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getStats(string $val = '*')
    {
        return DB::query("SELECT {$val} FROM statistiche WHERE 1",'result');
    }

    /*** AJAX ***/


    /**
     * @fn ajaxStatData
     * @note Estrae i dati di una statistica alla modifica
     * @param array $post
     * @return array|void
     */
    public function ajaxStatData(array $post)
    {

        if ($this->permissionManageStatistics()) {

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
     */
    public function existStat(int $id): bool
    {
        $data = DB::query("SELECT * FROM statistiche WHERE id='{$id}' LIMIT 1");
        return (DB::rowsNumber($data) > 0);
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
     */
    public function listStats(int $selected = 0): string
    {
        $html = '';
        $list = $this->getStats('id,nome');

        foreach ($list as $row) {
            $id = Filters::int($row['id']);
            $nome = Filters::in($row['nome']);
            $sel = ($selected == $id) ? 'selected' : '';

            $html .= "<option value='{$id}' {$sel}>{$nome}</option>";
        }

        return $html;
    }


    /*** MANAGEMENT FUNCTIONS - STATISTIC **/

    /**
     * @fn insertStat
     * @note Inserimento statistica
     * @param array $post
     * @return array
     */
    public function insertStat(array $post): array
    {

        if ($this->permissionManageStatistics()) {

            $nome = Filters::in($post['nome']);
            $descrizione = Filters::in($post['descr']);
            $max = Filters::int($post['max_val']);
            $min = Filters::int($post['min_val']);
            $iscr = Filters::checkbox($post['iscrizione']);
            $creato_da = Filters::int($this->me_id);


            DB::query("INSERT INTO statistiche(nome, max_val, min_val, descrizione,iscrizione,creato_da) 
                            VALUES('{$nome}','{$max}','{$min}','{$descrizione}','{$iscr}','{$creato_da}')");

            $resp = ['response' => true, 'mex' => 'Statistica inserita correttamente.'];
        } else {
            $resp = ['response' => false, 'mex' => 'Permesso negato.'];
        }

        return $resp;
    }

    /**
     * @fn editStat
     * @note Modifica oggetto
     * @param array $post
     * @return array
     */
    public function editStat(array $post): array
    {

        if ($this->permissionManageStatistics()) {

            $id = Filters::int($post['stat']);
            $nome = Filters::in($post['nome']);
            $descrizione = Filters::in($post['descr']);
            $max = Filters::int($post['max_val']);
            $min = Filters::int($post['min_val']);
            $iscr = Filters::checkbox($post['iscrizione']);
            $creato_da = Filters::int($this->me_id);

            DB::query("UPDATE statistiche 
                            SET nome='{$nome}',descrizione='{$descrizione}',max_val='{$max}',
                                min_val='{$min}',creato_da='{$creato_da}',iscrizione='{$iscr}'
                            WHERE id='{$id}' LIMIT 1");

            $resp = ['response' => true, 'mex' => 'Statistica modificata correttamente.'];
        } else {
            $resp = ['response' => false, 'mex' => 'Permesso negato.'];
        }

        return $resp;
    }

    /**
     * @fn deleteStat
     * @note Eliminazione statistica
     * @param array $post
     * @return array
     */
    public function deleteStat(array $post): array
    {

        if ($this->permissionManageStatistics()) {

            $id = Filters::int($post['stat']);

            DB::query("DELETE FROM statistiche WHERE id='{$id}'");

            $resp = ['response' => true, 'mex' => 'Statistica eliminata correttamente.'];
        } else {
            $resp = ['response' => false, 'mex' => 'Permesso negato.'];
        }

        return $resp;
    }
}