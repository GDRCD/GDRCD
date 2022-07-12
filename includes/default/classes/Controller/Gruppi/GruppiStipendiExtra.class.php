<?php


class GruppiStipendiExtra extends Gruppi
{
    private
        $active_extra_earn;

    protected function __construct()
    {
        parent::__construct();
        $this->active_extra_earn = Functions::get_constant('GROUPS_EXTRA_EARNS');
    }


    /*** CONFIG ***/

    public function activeExtraEarn()
    {
        return $this->active_extra_earn;
    }

    /**** TABLES HELPERS ***/

    /**
     * @fn getAllExtraEarn
     * @note Estrae tutti gli stipendi extra
     * @param string $val
     * @return bool|int|mixed|string
     */
    public static function getAllExtraEarn(string $val = '*')
    {
        return DB::query("SELECT {$val}
                                FROM gruppi_stipendi_extra 
                                WHERE 1", 'result');
    }

    /**
     * @fn getExtraEarn
     * @note Estrae il singolo stipendio extra
     * @param int $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    public static function getExtraEarn(int $id, string $val = '*')
    {
        return DB::query("SELECT {$val}
                                FROM gruppi_stipendi_extra 
                                WHERE id = '{$id}' LIMIT 1");
    }

    /**
     * @fn getPgExtraEarns
     * @note Estrae gli stipendi extra di un personaggio
     * @param int $pg
     * @param string $val
     * @return bool|int|mixed|string
     */
    public static function getPgExtraEarns(int $pg, string $val = '*')
    {
        return DB::query("SELECT {$val}
                                FROM gruppi_stipendi_extra 
                                LEFT JOIN gruppi ON (gruppi.id = gruppi_stipendi_extra.gruppo)
                                WHERE gruppi_stipendi_extra.personaggio = '{$pg}' ", 'result');
    }

    /**
     * @fn getGroupExtraEarns
     * @note Estrae gli stipendi extra dati da un gruppo
     * @param int $group
     * @param string $val
     * @return bool|int|mixed|string
     */
    public static function getGroupExtraEarns(int $group, string $val = '*')
    {
        return DB::query("SELECT {$val}
                                FROM gruppi_stipendi_extra 
                                WHERE personaggio_statistiche.gruppo = '{$group}' ", 'result');
    }


    /*** AJAX ***/

    /**
     * @fn ajaxExtraEarnData
     * @note Estrae i dati di uno stipendio extra in modo dinamico
     * @param array $post
     * @return array|bool|int|string
     */
    public function ajaxExtraEarnData(array $post): array
    {
        $id = Filters::int($post['id']);
        return $this->getExtraEarn($id);
    }

    /*** PERMESSI ***/

    /**
     * @fn permissionMangeExtraEarn
     * @note Controlla che si abbiano i permessi per gestire gli stipendi extra
     * @return bool
     */
    public static function permissionMangeExtraEarn(): bool
    {
        return Permissions::permission('MANAGE_GROUPS_EXTRA_EARN');
    }

    /*** LISTS ***/

    /**
     * @fn listTypes
     * @note Genera gli option per gli stipendi extra
     * @return string
     */
    public function listExtraEarns(): string
    {
        $earns = $this->getAllExtraEarn();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $earns);
    }


    /** GESTIONE */

    /**
     * @fn NewExtraEarn
     * @note Inserisce un nuovo stipendio extra
     * @param array $post
     * @return array
     */
    public function NewExtraEarn(array $post): array
    {
        if ($this->permissionMangeExtraEarn()) {

            $nome = Filters::text($post['nome']);
            $pg = Filters::int($post['personaggio']);
            $group = Filters::int($post['gruppo']);
            $valore = Filters::int($post['denaro']);
            $interval = Filters::int($post['interval']);
            $interval_type = Filters::in($post['interval_type']);
            $last_exec = Filters::in($post['last_exec']);


            DB::query("INSERT INTO gruppi_stipendi_extra (`nome`, `personaggio`, `gruppo`, `valore`, `interval`, `interval_type`, `last_exec`)
                            VALUES ('{$nome}', '{$pg}', '{$group}', '{$valore}', '{$interval}', '{$interval_type}', '{$last_exec}')");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Stipendio extra assegnato.',
                'swal_type' => 'success',
                'earns_list' => $this->listExtraEarns()
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error'
            ];
        }
    }

    /**
     * @fn ModExtraEarn
     * @note Aggiorna uno stipendio extra
     * @param array $post
     * @return array
     */
    public function ModExtraEarn(array $post): array
    {
        if ($this->permissionMangeExtraEarn()) {

            $id = Filters::in($post['id']);
            $nome = Filters::text($post['nome']);
            $pg = Filters::int($post['personaggio']);
            $group = Filters::int($post['gruppo']);
            $valore = Filters::int($post['denaro']);
            $interval = Filters::int($post['interval']);
            $interval_type = Filters::in($post['interval_type']);


            DB::query("UPDATE gruppi_stipendi_extra 
                            SET `nome` = '{$nome}', `personaggio` = '{$pg}', `gruppo` = '{$group}', 
                                `valore` = '{$valore}', `interval` = '{$interval}', `interval_type` = '{$interval_type}'
                            WHERE id = '{$id}'");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Stipendio extra modificato.',
                'swal_type' => 'success',
                'earns_list' => $this->listExtraEarns()
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error'
            ];
        }
    }

    /**
     * @fn DelExtraEarn
     * @note Cancella uno stipendio extra
     * @param array $post
     * @return array
     */
    public function DelExtraEarn(array $post): array
    {
        if ($this->permissionMangeExtraEarn()) {

            $id = Filters::in($post['id']);

            DB::query("DELETE FROM gruppi_stipendi_extra WHERE id = '{$id}'");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Tipo gruppo eliminato.',
                'swal_type' => 'success',
                'earns_list' => $this->listExtraEarns()
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error'
            ];
        }
    }
}