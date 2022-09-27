<?php

class Permissions extends BaseClass
{

    /*** GENERICO **/

    /**
     * @fn extractPermissionGroups
     * @note Estrae i gruppi di permesso del personaggio specificato
     * @param int $pg
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function extractPermissionGroups(int $pg): DBQueryInterface
    {
        $pg = Filters::int($pg);
        return DB::queryStmt("SELECT group_id FROM permessi_group_personaggio WHERE personaggio=:pg ", [
            'pg' => $pg,
        ]);
    }

    /**
     * @fn permissionExist
     * @note Controlla se il permesso specificato esiste
     * @param string $val
     * @return bool
     * @throws Throwable
     */
    public static function permissionExist(string $val): bool
    {
        $contr = DB::queryStmt("SELECT * FROM permessi_custom WHERE permission_name=:val LIMIT 1", [
            'val' => $val,
        ]);
        return ($contr->getNumRows() > 0);
    }

    /**
     * @fn permissionExistInGroup
     * @note Controlla che il permesso esista nel gruppo specificato
     * @param int $permission
     * @param int $group
     * @return bool
     * @throws Throwable
     */
    public function permissionExistInGroup(int $permission, int $group): bool
    {
        $contr = DB::queryStmt("SELECT * FROM permessi_group_assignment WHERE permission=:permission AND group_id=:group LIMIT 1",
            [
                'permission' => $permission,
                'group' => $group,
            ]);
        return ($contr->getNumRows() > 0);
    }

    /**
     * @fn permissionExistInCharacter
     * @note Controlla se il permesso esiste per quel personaggio
     * @param int $pg
     * @param int $permission
     * @return bool
     * @throws Throwable
     */
    public function permissionExistInCharacter(int $pg, int $permission): bool
    {
        $contr = DB::queryStmt("SELECT * FROM permessi_personaggio WHERE permission=:permission AND personaggio=:pg LIMIT 1", [
            'permission' => $permission,
            'pg' => $pg,
        ]);
        return ($contr->getNumRows() > 0);
    }

    /**
     * @fn permissionId
     * @note Estrae l'id del permesso testuale
     * @param string $val
     * @return int
     * @throws Throwable
     */
    public function permissionId(string $val): int
    {
        $contr = DB::queryStmt("SELECT id FROM permessi_custom WHERE permission_name=:val LIMIT 1", [
            'val' => $val,
        ]);
        return Filters::int($contr['id']);
    }

    /*** FUNZIONI ***/

    /**
     * @fn permission
     * @note Controlla se il personaggio attuale ha il permesso specificato, il controllo viene effettuato:
     * - Nei permessi di gruppo
     * - Nei permessi specifici del pg
     * - Se è un superuser, ogni controllo è true
     * @param string $permission
     * @param int $pg
     * @return bool
     * @throws Throwable
     */
    public static function permission(string $permission, int $pg = 0): bool
    {
        # EDIT_CONSTANTS
        $permission = Filters::out($permission);

        if ( self::permissionExist($permission) ) {

            $pg_id = ($pg == 0) ? Functions::getInstance()->getMyId() : $pg;

            # Estraggo i gruppi del pg collegato
            $perm_groups = self::getInstance()->extractPermissionGroups($pg_id);
            $perm_id = self::getInstance()->permissionId($permission);

            return (
                self::getInstance()->permissionInGroups($perm_groups, $perm_id) ||
                self::getInstance()->permissionExistInCharacter($pg_id, $perm_id) ||
                self::getInstance()->isSuperUser($perm_groups)
            );

        } else {
            die("Permesso {$permission} inesistente.");
        }
    }

    /**
     * @fn permissionInGroups
     * @note Controlla che un permesso sia disponibile nei gruppi specificati
     * @param array|DBQueryInterface $groups
     * @param string $permission
     * @return bool
     * @throws Throwable
     */
    public function permissionInGroups(array|DBQueryInterface $groups, string $permission): bool
    {

        $resp = false;
        $permission = Filters::int($permission);

        foreach ( $groups as $group ) {
            $group_id = Filters::int($group['group_id']);

            if ( $this->permissionExistInGroup($permission, $group_id) ) {
                $resp = true;
                break;
            }
        }

        return $resp;
    }

    /**
     * @fn isSuperUser
     * @note Controlla se uno dei gruppi del pg bypassa qualsiasi blocco o permesso
     * @param array|DBQueryInterface $groups
     * @return bool
     * @throws Throwable
     */
    private function isSuperUser(array|DBQueryInterface $groups): bool
    {

        $resp = false;
        foreach ( $groups as $group ) {
            $group_id = Filters::int($group['group_id']);

            $data = DB::queryStmt("SELECT superuser FROM permessi_group WHERE id=:group LIMIT 1",[
                'group' => $group_id,
            ]);

            if ( Filters::int($data['superuser']) ) {
                $resp = true;
                break;
            }
        }

        return $resp;
    }

    /**
     * @fn getPgListPermissions
     * @note Estrae tutti i personaggi che sono compresi nei permessi indicati, che siano di gruppo, persona o superuser
     * @param array $permissions
     * @return array
     * @throws Throwable
     */
    public static function getPgListPermissions(array $permissions): array
    {

        $pg_list = Functions::getAllPgs();
        $pg_array = [];

        foreach ( $pg_list as $pg ) {
            $pg_id = Filters::int($pg['id']);
            $pg_name = Filters::out($pg['nome']);

            if ( !in_array($pg_id, $pg_array) ) {
                foreach ( $permissions as $permission ) {

                    $permission = Filters::in($permission);

                    if ( self::permission($permission, $pg_id) ) {
                        $pg_array[] = ['id' => $pg_id, 'nome' => $pg_name];
                    }
                }
            }
        }

        return $pg_array;
    }
}