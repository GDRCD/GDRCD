<?php

class Permissions extends BaseClass
{

    /*** GENERICO **/

    /**
     * @fn extractPermissionGroups
     * @note Estrae i gruppi di permesso del personaggio specificato
     * @param int $pg
     * @return bool|int|mixed|string
     */
    public function extractPermissionGroups(int $pg)
    {
        $pg = Filters::int($pg);
        return DB::query("SELECT group_id FROM permessi_group_personaggio WHERE personaggio='{$pg}' ", 'result');
    }

    /**
     * @fn permissionExist
     * @note Controlla se il permesso specificato esiste
     * @param string $val
     * @return bool
     */
    public static function permissionExist(string $val): bool
    {
        $contr = DB::query("SELECT count(id) as tot FROM permessi_custom WHERE permission_name='{$val}' LIMIT 1");
        return ($contr['tot'] > 0);
    }

    /**
     * @fn permissionExistInGroup
     * @note Controlla che il permesso esista nel gruppo specificato
     * @param int $permission
     * @param int $group
     * @return bool
     */
    public function permissionExistInGroup(int $permission, int $group): bool
    {
        $contr = DB::query("SELECT count(id) as tot FROM permessi_group_assignment WHERE permission='{$permission}' AND group_id='{$group}' LIMIT 1");
        return ($contr['tot'] > 0);
    }

    /**
     * @fn permissionExistInCharacter
     * @note Controlla se il permesso esiste per quel personaggio
     * @param int $pg
     * @param int $permission
     * @return bool
     */
    public function permissionExistInCharacter(int $pg, int $permission): bool
    {
        $contr = DB::query("SELECT count(id) as tot FROM permessi_personaggio WHERE permission='{$permission}' AND personaggio='{$pg}' LIMIT 1");
        return ($contr['tot'] > 0);
    }

    /**
     * @fn permissionId
     * @note Estrae l'id del permesso testuale
     * @param string $val
     * @return int
     */
    public function permissionId(string $val): int
    {
        $contr = DB::query("SELECT id FROM permessi_custom WHERE permission_name='{$val}' LIMIT 1");
        return $contr['id'];
    }

    /**
     * @fn permission
     * @note Controlla se il personaggio loggato ha il permesso specificato, il controllo viene effettuato:
     * - Nei permessi di gruppo
     * - Nei permessi specifici del pg
     * @param string $permission
     * @return bool
     */
    public static function permission(string $permission):bool
    {
        # EDIT_CONSTANTS
        $permission = Filters::out($permission);

        if (self::permissionExist($permission)) {

            # Estraggo i gruppi del pg collegato
            $pg = Functions::getInstance()->getMyId();
            $perm_groups = self::getInstance()->extractPermissionGroups($pg);
            $perm_id = self::getInstance()->permissionId($permission);

            return (
                self::getInstance()->permissionInGroups($perm_groups, $perm_id) ||
                self::getInstance()->permissionExistInCharacter($pg,$perm_id)
            );


        } else {
            die("Permesso {$permission} inesistente.");
        }
    }

    /**
     * @fn permissionInGroups
     * @note Controlla che un permesso sia disponibile nei gruppi specificati
     * @param $groups
     * @param string $permission
     * @return bool
     */
    private function permissionInGroups($groups, string $permission): bool
    {

        $resp = false;
        $permission = Filters::int($permission);

        foreach ($groups as $group) {
            $group_id = Filters::int($group['group_id']);

            if ($this->permissionExistInGroup($permission, $group_id)) {
                $resp = true;
                break;
            }
        }

        return $resp;
    }
}