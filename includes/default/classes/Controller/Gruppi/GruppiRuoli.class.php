<?php

/**
 * @class GruppiRuoli
 * @note Classe che gestisce i ruoli dei gruppi
 */
class GruppiRuoli extends Gruppi
{

    protected $groups_max_roles;


    protected function __construct()
    {
        parent::__construct();
        $this->groups_max_roles = Functions::get_constant('GROUPS_MAX_ROLES');
    }

    /**** PERMESSI ****/

    /**
     * @fn permissionManageRoles
     * @note Controlla se si hanno i permessi per gestire i ruoli
     * @return bool
     */
    public function permissionManageRoles(): bool
    {
        return Permissions::permission('MANAGE_GROUPS');
    }

    /**** TABLE HELPERS ****/

    /**
     * @fn getRole
     * @note Estrae un ruolo
     * @param int $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getRole(int $id, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM gruppi_ruoli WHERE id='{$id}' LIMIT 1");
    }

    /**
     * @fn getAllRoles
     * @note Estrae tutti i ruoli
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllRoles(string $val = '*')
    {
        return DB::query("SELECT {$val} FROM gruppi_ruoli WHERE 1", 'result');
    }

    /**
     * @fn getAllRolesByIds
     * @note Estrae tutti i ruoli di piu' gruppi
     * @param array $ids
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllRolesByIds(array $ids, string $val = '*')
    {
        $toSearch = implode(',', $ids);
        return DB::query("SELECT {$val} FROM gruppi_ruoli WHERE gruppo IN ({$toSearch})", 'result');
    }

    /**
     * @fn getAllGroupRoles
     * @note Estrae i ruoli di un gruppo preciso
     * @param int $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllGroupRoles(int $id, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM gruppi_ruoli WHERE gruppo='{$id}'", 'result');
    }

    /**
     * @fn getAllGroupMembers
     * @note Estrae tutti i membri di un gruppo preciso
     * @param int $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllGroupMembers(int $id, string $val = 'personaggio.nome,gruppi_ruoli.nome as role,gruppi_ruoli.immagine')
    {
        return DB::query("
                SELECT {$val} FROM gruppi_ruoli 
                    LEFT JOIN personaggio_ruolo ON personaggio_ruolo.ruolo = gruppi_ruoli.id 
                    LEFT JOIN personaggio ON personaggio_ruolo.personaggio = personaggio.id 
                WHERE gruppi_ruoli.gruppo='{$id}' AND personaggio_ruolo.id IS NOT NULL", 'result');
    }

    /**
     * @fn getAvailableRoles
     * @note Estrae i ruoli che un personaggio puo' gestire
     * @return bool|int|mixed|string
     */
    public function getAvailableRoles()
    {
        $groups = DB::query("
                SELECT gruppi_ruoli.gruppo FROM gruppi_ruoli 
                    LEFT JOIN personaggio_ruolo ON personaggio_ruolo.ruolo = gruppi_ruoli.id AND personaggio_ruolo.personaggio ='{$this->me_id}'
                    WHERE gruppi_ruoli.poteri = 1 AND personaggio_ruolo.id IS NOT NULL", 'result');

        $groups_list = [];

        foreach ($groups as $group) {
            array_push($groups_list, $group['gruppo']);
        }

        return $this->getAllRolesByIds($groups_list);
    }

    /**
     * @fn getCharacterRolesNumbers
     * @note Conta quanti ruoli ha un personaggio
     * @param int $pg
     * @return int
     */
    public function getCharacterRolesNumbers(int $pg): int
    {

        $groups = DB::query("
                SELECT COUNT(personaggio_ruolo.id) AS 'TOT' FROM personaggio_ruolo 
                WHERE personaggio_ruolo.personaggio ='{$pg}'");

        return Filters::int($groups['TOT']);
    }


    /**
     * @fn getCharacterSalaries
     * @note Ottiene tutti gli stipendi dei ruoli di un personaggio
     * @param int $pg
     * @return bool|int|mixed|string
     */
    public function getCharacterRolesSalaries(int $pg){
        return DB::query(
            "SELECT gruppi_ruoli.stipendio FROM personaggio_ruolo 
                    LEFT JOIN gruppi_ruoli ON (personaggio_ruolo.ruolo = gruppi_ruoli.id)
                    WHERE personaggio_ruolo.personaggio ='{$pg}'",'result');
    }

    /**** LIST ****/

    /**
     * @fn listGroups
     * @note Genera gli option per i gruppi
     * @return string
     */
    public function listAvailableRoles(): string
    {
        if ($this->permissionManageGroups()) {
            $roles = $this->getAllRoles();
        } else {
            $roles = $this->getAvailableRoles();
        }
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $roles);
    }

    /**
     * @fn listGroups
     * @note Genera gli option per i gruppi
     * @return string
     */
    public function listRoles(): string
    {
        $roles = $this->getAllRoles();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $roles);
    }

    /**
     * @fn listGroups
     * @note Genera gli option per i gruppi
     * @return string
     */
    public function listServiceAvailableRoles(): string
    {
        $roles = $this->getAllRoles();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $roles);
    }

    /**** AJAX ****/

    /**
     * @param array $post
     * @return array
     */
    public function ajaxRoleData(array $post): array
    {
        $id = Filters::int($post['id']);
        return $this->getRole($id);
    }

    /**** RENDER ****/

    /**
     * @fn rolesList
     * @note Render html della lista dei ruoli
     * @param int $corp
     * @return string
     */
    public function rolesList(int $corp): string
    {
        $template = Template::getInstance()->startTemplate();
        $roles = $this->getAllGroupRoles($corp);
        return $template->renderTable(
            'servizi/roles_list',
            $this->renderRolesList($roles)
        );
    }

    /**
     * @fn renderRolesList
     * @note Render html lista ruoli
     * @param object $list
     * @return array
     */
    public function renderRolesList(object $list): array
    {
        $row_data = [];

        foreach ($list as $row) {

            $array = [
                'id' => Filters::int($row['id']),
                'name' => Filters::out($row['nome']),
                'logo' => Router::getThemeDir() . 'imgs/groups/' . Filters::out($row['immagine']),
                'stipendio' => Filters::int($row['stipendio']),
                'poteri' => Filters::bool($row['poteri']),
            ];

            $row_data[] = $array;
        }

        $cells = [
            'Nome',
            'Logo',
            'Stipendio',
            'Poteri',
        ];
        return [
            'body_rows' => $row_data,
            'cells' => $cells,
        ];
    }

    /**
     * @fn membersList
     * @note Render html della lista dei membri
     * @param int $corp
     * @return string
     */
    public function membersList(int $corp): string
    {
        $template = Template::getInstance()->startTemplate();
        $members = $this->getAllGroupMembers($corp);
        return $template->renderTable(
            'servizi/members_list',
            $this->renderMembersList($members)
        );
    }

    /**
     * @fn renderMembersList
     * @note Render html lista membri
     * @param object $list
     * @return array
     */
    public function renderMembersList(object $list): array
    {
        $row_data = [];

        foreach ($list as $row) {

            $array = [
                'id' => Filters::int($row['id']),
                'personaggio' => Filters::out($row['nome']),
                'logo' => Router::getThemeDir() . 'imgs/groups/' . Filters::out($row['immagine']),
                'name' => Filters::out($row['role']),
            ];

            $row_data[] = $array;
        }

        $cells = [
            'Personaggio',
            'Ruolo',
            'Logo'
        ];
        return [
            'body_rows' => $row_data,
            'cells' => $cells,
        ];
    }

    /** GESTIONE */

    /**
     * @fn NewRole
     * @note Inserisce un ruolo gruppo
     * @param array $post
     * @return array
     */
    public function NewRole(array $post): array
    {
        if ($this->permissionManageRoles()) {

            $group = Filters::int($post['gruppo']);
            $nome = Filters::in($post['nome']);
            $img = Filters::in($post['immagine']);
            $stipendio = Filters::int($post['stipendio']);
            $poteri = Filters::checkbox($post['poteri']);


            DB::query("INSERT INTO gruppi_ruoli (gruppo,nome,immagine,stipendio,poteri )  
                        VALUES ('{$group}','{$nome}','{$img}','{$stipendio}','{$poteri}') ");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Ruolo gruppo creato.',
                'swal_type' => 'success',
                'roles_list' => $this->listRoles()
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
     * @fn ModRole
     * @note Aggiorna un ruolo gruppo
     * @param array $post
     * @return array
     */
    public function ModRole(array $post): array
    {
        if ($this->permissionManageRoles()) {
            $id = Filters::in($post['id']);
            $group = Filters::int($post['gruppo']);
            $nome = Filters::in($post['nome']);
            $img = Filters::in($post['immagine']);
            $stipendio = Filters::int($post['stipendio']);
            $poteri = Filters::checkbox($post['poteri']);


            DB::query("UPDATE  gruppi_ruoli 
                SET gruppo='{$group}',nome = '{$nome}', 
                    immagine='{$img}',stipendio='{$stipendio}',
                    poteri='{$poteri}' WHERE id='{$id}'");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Ruolo gruppo modificato.',
                'swal_type' => 'success',
                'roles_list' => $this->listRoles()
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
     * @fn DelRole
     * @note Cancella un ruolo gruppo
     * @param array $post
     * @return array
     */
    public function DelRole(array $post): array
    {
        if ($this->permissionManageRoles()) {

            $id = Filters::in($post['id']);

            DB::query("DELETE FROM gruppi_ruoli WHERE id='{$id}'");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Gruppo eliminato.',
                'swal_type' => 'success',
                'roles_list' => $this->listRoles()
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


    /*** SERVICES ***/

    /**
     * @fn AssignRole
     * @note Assegna un ruolo a un personaggio
     * @param array $post
     * @return array
     */
    public function AssignRole(array $post): array
    {

        $personaggio = Filters::int($post['personaggio']);
        $ruolo = Filters::int($post['ruolo']);
        $ruolo_data = $this->getRole($ruolo);
        $group = Filters::int($ruolo_data['gruppo']);

        if ($this->permissionServiceGroups($group)) {

            $roles_number = $this->getCharacterRolesNumbers($personaggio);

            if ($roles_number < $this->groups_max_roles) {

                DB::query("INSERT INTO personaggio_ruolo(ruolo,personaggio) VALUES('{$ruolo}','{$personaggio}')");

                return [
                    'response' => true,
                    'swal_title' => 'Successo!',
                    'swal_message' => 'Ruolo assegnato correttamente!',
                    'swal_type' => 'success'
                ];
            } else {
                return [
                    'response' => false,
                    'swal_title' => 'Operazione fallita!',
                    'swal_message' => 'Il personaggio ha raggiunto il numero massimo di ruoli disponibili.',
                    'swal_type' => 'error'
                ];
            }


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
     * @fn RemoveRole
     * @note Rimuovi un ruolo a un personaggio
     * @param array $post
     * @return array
     */
    public function RemoveRole(array $post): array
    {

        $personaggio = Filters::int($post['personaggio']);
        $ruolo = Filters::int($post['ruolo']);
        $ruolo_data = $this->getRole($ruolo);
        $group = Filters::int($ruolo_data['gruppo']);

        if ($this->permissionServiceGroups($group)) {

            DB::query("DELETE FROM personaggio_ruolo WHERE ruolo='{$ruolo}' AND personaggio='{$personaggio}'");

            return [
                'response' => true,
                'swal_title' => 'Successo!',
                'swal_message' => 'Ruolo assegnato correttamente!',
                'swal_type' => 'success'
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