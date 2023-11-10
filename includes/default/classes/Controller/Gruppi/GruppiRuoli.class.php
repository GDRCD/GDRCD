<?php

/**
 * @class GruppiRuoli
 * @note Classe che gestisce i ruoli dei gruppi
 */
class GruppiRuoli extends Gruppi
{

    protected int $groups_max_roles;

    /**
     * @fn __construct
     * @note Costruttore della classe
     * @throws Throwable
     */
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
     * @throws Throwable
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
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getRole(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT $val FROM gruppi_ruoli WHERE id = :id LIMIT 1", ['id' => $id]);
    }

    /**
     * @fn getAllRoles
     * @note Estrae tutti i ruoli
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllRoles(string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM gruppi_ruoli WHERE 1", []);
    }

    /**
     * @fn getAllRolesByIds
     * @note Estrae tutti i ruoli di piu' gruppi
     * @param array $ids
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllRolesByIds(array $ids, string $val = '*'): DBQueryInterface
    {
        $toSearch = implode(',', $ids);
        return DB::queryStmt("SELECT {$val} FROM gruppi_ruoli WHERE gruppo IN ({$toSearch})", []);
    }

    /**
     * @fn getAllGroupRoles
     * @note Estrae i ruoli di un gruppo preciso
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllGroupRoles(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM gruppi_ruoli WHERE gruppo = :id", ['id' => $id]);
    }

    /**
     * @fn getAllGroupMembers
     * @note Estrae tutti i membri di un gruppo preciso
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllGroupMembers(int $id, string $val = 'personaggio.nome,gruppi_ruoli.nome as role,gruppi_ruoli.immagine'): DBQueryInterface
    {
        return DB::queryStmt("
                SELECT {$val} FROM gruppi_ruoli 
                    LEFT JOIN personaggio_ruolo ON personaggio_ruolo.ruolo = gruppi_ruoli.id 
                    LEFT JOIN personaggio ON personaggio_ruolo.personaggio = personaggio.id 
                WHERE gruppi_ruoli.gruppo=:id AND personaggio_ruolo.id IS NOT NULL", ['id' => $id]);
    }

    /**
     * @fn getAllGroupBoss
     * @note Estrae tutti i membri di un gruppo preciso
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllGroupBoss(int $id, string $val = 'personaggio.id,personaggio.nome,gruppi_ruoli.nome as role,gruppi_ruoli.immagine'): DBQueryInterface
    {

        return DB::queryStmt("
                SELECT {$val} FROM gruppi_ruoli 
                    LEFT JOIN personaggio_ruolo ON personaggio_ruolo.ruolo = gruppi_ruoli.id 
                    LEFT JOIN personaggio ON personaggio_ruolo.personaggio = personaggio.id 
                WHERE gruppi_ruoli.gruppo=:id AND personaggio_ruolo.id IS NOT NULL AND gruppi_ruoli.poteri = 1",
            ['id' => $id]
        );
    }

    /**
     * @fn getAvailableRoles
     * @note Estrae i ruoli che un personaggio puo' gestire
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAvailableRoles(): DBQueryInterface
    {
        $groups = DB::queryStmt("
                SELECT gruppi_ruoli.gruppo FROM gruppi_ruoli 
                    LEFT JOIN personaggio_ruolo ON personaggio_ruolo.ruolo = gruppi_ruoli.id AND personaggio_ruolo.personaggio =:pg
                    WHERE gruppi_ruoli.poteri = 1 AND personaggio_ruolo.id IS NOT NULL",
            ['pg' => $this->me_id]
        );

        $groups_list = [];

        foreach ( $groups as $group ) {
            $groups_list[] = $group['gruppo'];
        }

        return $this->getAllRolesByIds($groups_list);
    }

    /**
     * @fn getCharacterSalaries
     * @note Ottiene tutti gli stipendi dei ruoli di un personaggio
     * @param int $pg
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getCharacterRolesSalaries(int $pg): DBQueryInterface
    {
        return DB::queryStmt(
            "SELECT gruppi.id,gruppi.nome,gruppi_ruoli.stipendio,gruppi.denaro FROM personaggio_ruolo 
                    LEFT JOIN gruppi_ruoli ON (personaggio_ruolo.ruolo = gruppi_ruoli.id)
                    LEFT JOIN gruppi ON (gruppi_ruoli.gruppo = gruppi.id)
                    WHERE personaggio_ruolo.personaggio =:pg",
            ['pg' => $pg]
        );
    }

    /**** LIST ****/

    /**
     * @fn listAvailableRoles
     * @note Genera gli option per i ruoli disponibili
     * @return string
     * @throws Throwable
     */
    public function listAvailableRoles(): string
    {
        if ( $this->permissionManageGroups() ) {
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
     * @throws Throwable
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
     * @throws Throwable
     */
    public function listServiceAvailableRoles(): string
    {
        $roles = $this->getAllRoles();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $roles);
    }

    /**** AJAX ****/

    /**
     * @param array $post
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function ajaxRoleData(array $post): DBQueryInterface
    {
        $id = Filters::int($post['id']);
        return $this->getRole($id)->getData()[0];
    }

    /**** RENDER ****/

    /**
     * @fn rolesList
     * @note Render html della lista dei ruoli
     * @param int $corp
     * @return string
     * @throws Throwable
     */
    public function rolesList(int $corp): string
    {
        $roles = $this->getAllGroupRoles($corp);
        return Template::getInstance()->startTemplate()->renderTable(
            'servizi/roles_list',
            $this->renderRolesList($roles)
        );
    }

    /**
     * @fn renderRolesList
     * @note Render html lista ruoli
     * @param object $list
     * @return array
     * @throws Throwable
     */
    public function renderRolesList(object $list): array
    {
        $row_data = [];

        foreach ( $list as $row ) {

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
     * @throws Throwable
     */
    public function membersList(int $corp): string
    {
        $members = $this->getAllGroupMembers($corp);
        return Template::getInstance()->startTemplate()->renderTable(
            'servizi/members_list',
            $this->renderMembersList($members)
        );
    }

    /**
     * @fn renderMembersList
     * @note Render html lista membri
     * @param object $list
     * @return array
     * @throws Throwable
     */
    public function renderMembersList(object $list): array
    {
        $row_data = [];

        foreach ( $list as $row ) {

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
            'Logo',
        ];
        return [
            'body_rows' => $row_data,
            'cells' => $cells,
        ];
    }

    /**** GESTIONE ****/

    /**
     * @fn NewRole
     * @note Inserisce un ruolo gruppo
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function NewRole(array $post): array
    {
        if ( $this->permissionManageRoles() ) {

            $group = Filters::int($post['gruppo']);
            $nome = Filters::in($post['nome']);
            $img = Filters::in($post['immagine']);
            $stipendio = Filters::int($post['stipendio']);
            $poteri = Filters::checkbox($post['poteri']);

            DB::queryStmt(
                "INSERT INTO gruppi_ruoli (gruppo,nome,immagine,stipendio,poteri) VALUES (:gruppo,:nome,:immagine,:stipendio,:poteri)",
                [
                    'gruppo' => $group,
                    'nome' => $nome,
                    'immagine' => $img,
                    'stipendio' => $stipendio,
                    'poteri' => $poteri,
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Ruolo gruppo creato.',
                'swal_type' => 'success',
                'roles_list' => $this->listRoles(),
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
     * @fn ModRole
     * @note Aggiorna un ruolo gruppo
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function ModRole(array $post): array
    {
        if ( $this->permissionManageRoles() ) {
            $id = Filters::in($post['id']);
            $group = Filters::int($post['gruppo']);
            $nome = Filters::in($post['nome']);
            $img = Filters::in($post['immagine']);
            $stipendio = Filters::int($post['stipendio']);
            $poteri = Filters::checkbox($post['poteri']);

            DB::queryStmt(
                "UPDATE gruppi_ruoli SET gruppo=:gruppo,nome=:nome,immagine=:immagine,stipendio=:stipendio,poteri=:poteri WHERE id=:id",
                [
                    'id' => $id,
                    'gruppo' => $group,
                    'nome' => $nome,
                    'immagine' => $img,
                    'stipendio' => $stipendio,
                    'poteri' => $poteri,
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Ruolo gruppo modificato.',
                'swal_type' => 'success',
                'roles_list' => $this->listRoles(),
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
     * @fn DelRole
     * @note Cancella un ruolo gruppo
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function DelRole(array $post): array
    {
        if ( $this->permissionManageRoles() ) {

            $id = Filters::in($post['id']);

            DB::queryStmt("DELETE FROM gruppi_ruoli WHERE id=:id", ['id' => $id]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Gruppo eliminato.',
                'swal_type' => 'success',
                'roles_list' => $this->listRoles(),
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


    /*** SERVICES ***/

    /**
     * @fn AssignRole
     * @note Assegna un ruolo a un personaggio
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function AssignRole(array $post): array
    {

        $personaggio = Filters::int($post['personaggio']);
        $ruolo = Filters::int($post['ruolo']);
        $ruolo_data = $this->getRole($ruolo);
        $group = Filters::int($ruolo_data['gruppo']);

        if ( $this->permissionServiceGroups($group) ) {

            $roles_number = PersonaggioRuolo::getInstance()->getCharacterRolesNumbers($personaggio);

            if ( $roles_number < $this->groups_max_roles ) {

                DB::queryStmt(
                    "INSERT INTO personaggio_ruolo(ruolo,personaggio) VALUES(:ruolo,:pg)",
                    ['ruolo' => $ruolo, 'pg' => $personaggio]
                );

                return [
                    'response' => true,
                    'swal_title' => 'Successo!',
                    'swal_message' => 'Ruolo assegnato correttamente!',
                    'swal_type' => 'success',
                ];
            } else {
                return [
                    'response' => false,
                    'swal_title' => 'Operazione fallita!',
                    'swal_message' => 'Il personaggio ha raggiunto il numero massimo di ruoli disponibili.',
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
     * @fn RemoveRole
     * @note Rimuovi un ruolo a un personaggio
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function RemoveRole(array $post): array
    {

        $personaggio = Filters::int($post['personaggio']);
        $ruolo = Filters::int($post['ruolo']);
        $ruolo_data = $this->getRole($ruolo);
        $group = Filters::int($ruolo_data['gruppo']);

        if ( $this->permissionServiceGroups($group) ) {

            DB::queryStmt(
                "DELETE FROM personaggio_ruolo WHERE ruolo=:ruolo AND personaggio=:pg",
                ['ruolo' => $ruolo, 'pg' => $personaggio]
            );

            return [
                'response' => true,
                'swal_title' => 'Successo!',
                'swal_message' => 'Ruolo assegnato correttamente!',
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