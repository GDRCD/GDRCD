<?php

class GruppiStipendiExtra extends Gruppi
{
    private bool
        $active_extra_earn;

    /**
     * @fn __construct
     * @note Costruttore della classe
     */
    protected function __construct()
    {
        parent::__construct();
        $this->active_extra_earn = Functions::get_constant('GROUPS_EXTRA_EARNS');
    }

    /*** CONFIG ***/

    /**
     * @fn activeExtraEarns
     * @note Ritorna se il sistema di gestione stipendi extra è attivo
     * @return bool
     */
    public function activeExtraEarn(): bool
    {
        return $this->active_extra_earn;
    }

    /**** TABLES HELPERS ***/

    /**
     * @fn getAllExtraEarn
     * @note Estrae tutti gli stipendi extra
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllExtraEarn(string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt(
            "SELECT {$val} FROM gruppi_stipendi_extra  WHERE 1",
            []
        );
    }

    /**
     * @fn getExtraEarn
     * @note Estrae il singolo stipendio extra
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getExtraEarn(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt(
            "SELECT {$val} FROM gruppi_stipendi_extra WHERE id = :id LIMIT 1",
            [ 'id' => $id ]
        );
    }

    /**
     * @fn getPgExtraEarns
     * @note Estrae gli stipendi extra di un personaggio
     * @param int $pg
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getPgExtraEarns(int $pg, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt(
            "SELECT {$val} FROM gruppi_stipendi_extra 
                   LEFT JOIN gruppi ON (gruppi.id = gruppi_stipendi_extra.gruppo)
                   WHERE gruppi_stipendi_extra.personaggio = :pg ",
            [ 'pg' => $pg ]
        );
    }

    /**
     * @fn getGroupExtraEarns
     * @note Estrae gli stipendi extra dati da un gruppo
     * @param array $groups
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getGroupExtraEarnsByIds(array $groups, string $val = '*'): DBQueryInterface
    {
        $toSearch = implode(',', $groups);
        return DB::queryStmt(
            "SELECT {$val} FROM gruppi_stipendi_extra 
                  WHERE gruppi_stipendi_extra.gruppo IN ({$toSearch}) ",
            []
        );
    }


    /*** AJAX ***/

    /**
     * @fn ajaxExtraEarnData
     * @note Estrae i dati di uno stipendio extra in modo dinamico
     * @param array $post
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function ajaxExtraEarnData(array $post): DBQueryInterface
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
    public function permissionMangeExtraEarn(): bool
    {
        return Permissions::permission('MANAGE_GROUPS_EXTRA_EARN');
    }

    /**
     * @fn permissionMangeExtraEarn
     * @note Controlla che si abbiano i permessi per gestire gli stipendi extra
     * @param int $id
     * @return bool
     * @throws Throwable
     */
    public function permissionMangeSpecificEarn(int $id): bool
    {
        $earn_data = $this->getExtraEarn($id);
        $group_id = Filters::int($earn_data['gruppo']);
        return $this->haveGroupPower($group_id);
    }

    /*** LISTS ***/

    /**
     * @fn listTypes
     * @note Genera gli option per gli stipendi extra
     * @return string
     * @throws Throwable
     */
    public function listExtraEarns(): string
    {
        $earns = $this->getAllExtraEarn();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $earns);
    }

    /**
     * @fn listAvailableExtraEarns
     * @note Genera gli option per gli stipendi extra disponibili
     * @return string
     * @throws Throwable
     */
    public function listAvailableExtraEarns(): string
    {
        $groups = $this->getAvailableGroups();
        $earns = $this->getGroupExtraEarnsByIds($groups);
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $earns);
    }


    /**** GESTIONE ****/

    /**
     * @fn NewExtraEarn
     * @note Inserisce un nuovo stipendio extra
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function NewExtraEarn(array $post): array
    {
        if ( $this->permissionMangeExtraEarn() ) {

            $nome = Filters::text($post['nome']);
            $pg = Filters::int($post['personaggio']);
            $group = Filters::int($post['gruppo']);
            $valore = Filters::int($post['denaro']);
            $interval = Filters::int($post['interval']);
            $interval_type = Filters::in($post['interval_type']);
            $last_exec = Filters::in($post['last_exec']);

            DB::queryStmt(
                "INSERT INTO gruppi_stipendi_extra (nome, personaggio, gruppo, denaro, intervallo, intervallo_tipo, last_exec) 
                    VALUES (:nome, :pg, :group, :valore, :interval, :interval_type, :last_exec)",
                [
                    'nome' => $nome,
                    'pg' => $pg,
                    'group' => $group,
                    'valore' => $valore,
                    'interval' => $interval,
                    'interval_type' => $interval_type,
                    'last_exec' => $last_exec
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Stipendio extra assegnato.',
                'swal_type' => 'success',
                'earns_list' => $this->listExtraEarns(),
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
     * @fn ModExtraEarn
     * @note Aggiorna uno stipendio extra
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function ModExtraEarn(array $post): array
    {
        if ( $this->permissionMangeExtraEarn() ) {

            $id = Filters::in($post['id']);
            $nome = Filters::text($post['nome']);
            $pg = Filters::int($post['personaggio']);
            $group = Filters::int($post['gruppo']);
            $valore = Filters::int($post['denaro']);
            $interval = Filters::int($post['interval']);
            $interval_type = Filters::in($post['interval_type']);

            DB::queryStmt(
                "UPDATE gruppi_stipendi_extra SET nome = :nome, personaggio = :pg, gruppo = :group, denaro = :valore, 
                    intervallo = :interval, intervallo_tipo = :interval_type WHERE id = :id",
                [
                    'id' => $id,
                    'nome' => $nome,
                    'pg' => $pg,
                    'group' => $group,
                    'valore' => $valore,
                    'interval' => $interval,
                    'interval_type' => $interval_type
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Stipendio extra modificato.',
                'swal_type' => 'success',
                'earns_list' => $this->listExtraEarns(),
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
     * @fn DelExtraEarn
     * @note Cancella uno stipendio extra
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function DelExtraEarn(array $post): array
    {
        if ( $this->permissionMangeExtraEarn() ) {

            $id = Filters::in($post['id']);

            DB::queryStmt(
                "DELETE FROM gruppi_stipendi_extra WHERE id = :id",
                [
                    'id' => $id
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Stipendio extra eliminato.',
                'swal_type' => 'success',
                'earns_list' => $this->listExtraEarns(),
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

    /*** AMMINISTRAZIONE GILDE ***/

    /**
     * @fn NewExtraEarnByBoss
     * @note Inserisce un nuovo stipendio extra se sei il capo
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function NewExtraEarnByBoss(array $post): array
    {
        $group = Filters::int($post['gruppo']);

        if ( $this->haveGroupPower($group) ) {

            $nome = Filters::text($post['nome']);
            $pg = Filters::int($post['personaggio']);
            $group = Filters::int($post['gruppo']);
            $valore = Filters::int($post['denaro']);
            $interval = Filters::int($post['interval']);
            $interval_type = Filters::in($post['interval_type']);
            $last_exec = Filters::in($post['last_exec']);

            DB::queryStmt(
                "INSERT INTO gruppi_stipendi_extra (nome, personaggio, gruppo, denaro, intervallo, intervallo_tipo, last_exec) 
                    VALUES (:nome, :pg, :group, :valore, :interval, :interval_type, :last_exec)",
                [
                    'nome' => $nome,
                    'pg' => $pg,
                    'group' => $group,
                    'valore' => $valore,
                    'interval' => $interval,
                    'interval_type' => $interval_type,
                    'last_exec' => $last_exec
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Stipendio extra creato.',
                'swal_type' => 'success',
                'earns_list' => $this->listAvailableExtraEarns(),
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
     * @fn ModExtraEarnByBoss
     * @note Modifica un stipendio extra se sei il capo
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function ModExtraEarnByBoss(array $post): array
    {
        $id = Filters::in($post['id']);
        $group = Filters::int($post['gruppo']);

        if ( $this->permissionMangeSpecificEarn($id) && $this->haveGroupPower($group) ) {

            $nome = Filters::text($post['nome']);
            $pg = Filters::int($post['personaggio']);
            $valore = Filters::int($post['denaro']);
            $interval = Filters::int($post['interval']);
            $interval_type = Filters::in($post['interval_type']);

            DB::queryStmt(
                "UPDATE gruppi_stipendi_extra SET nome = :nome, personaggio = :pg, denaro = :valore, 
                    intervallo = :interval, intervallo_tipo = :interval_type WHERE id = :id",
                [
                    'id' => $id,
                    'nome' => $nome,
                    'pg' => $pg,
                    'valore' => $valore,
                    'interval' => $interval,
                    'interval_type' => $interval_type
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Stipendio extra modificato.',
                'swal_type' => 'success',
                'earns_list' => $this->listAvailableExtraEarns(),
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
     * @fn RemoveExtraEarnByBoss
     * @note Elimina uno stipendio extra se sei il capo
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function RemoveExtraEarnByBoss(array $post): array
    {
        $id = Filters::in($post['id']);

        if ( $this->permissionMangeSpecificEarn($id) ) {

            DB::queryStmt(
                "DELETE FROM gruppi_stipendi_extra WHERE id = :id",
                [
                    'id' => $id
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Stipendio extra eliminato.',
                'swal_type' => 'success',
                'earns_list' => $this->listAvailableExtraEarns(),
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