<?php

class Permissions extends BaseClass{


    /**
     * @fn getPermission
     * @note Estrae i permessi del personaggio specificato
     * @return false|int|mixed
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * @fn permissionsList
     * @note Estrae la lista dei permessi esistenti nella tabella 'config_permission'
     * @return array
     */
    public function permissionsList(): array
    {
        $data = [];
        $list = DB::query("SELECT * FROM config_permission WHERE 1", 'result');

        foreach ($list as $row) {
            $lev = Filters::int($row['level']);
            $name = Filters::out($row['permission_name']);

            $data[$lev] = $name;
        }

        return $data;
    }

    /**
     * @fn permissionExist
     * @note Controlla se il permesso specificato esiste
     * @param string $val
     * @return bool
     */
    public static function permissionExist(string $val): bool
    {
        $contr = DB::query("SELECT count(id) as tot FROM config_permission WHERE permission_name='{$val}' LIMIT 1");
        return ($contr['tot'] > 0);
    }

    /**
     * @fn getPermissionLevel
     * @note Estrae il livello gerarchico del permesso
     * @param string $val
     * @return int|void
     */
    public static function getPermissionLevel(string $val)
    {
        if (self::permissionExist($val)) {
            $contr = DB::query("SELECT level FROM config_permission WHERE permission_name='{$val}' LIMIT 1");

            return Filters::int($contr['level']);
        } else {
            die('Permesso inesistente: ' . $val);
        }
    }

    /**
     * @fn havePermission
     * @note Controlla se il personaggio loggato ha il permesso specificato
     * @param string $type
     * @param string $val
     * @return bool|void
     */
    public static function permission(string $type, string $val)
    {
        $type = Filters::out($type);
        $val = Filters::out($val);

        if (self::permissionExist($val)) {

            $permesso = Filters::int(Permissions::getInstance()->getPermission());
            $needed = self::getPermissionLevel($val);

            switch ($type) {
                case '+':
                    if ($permesso >= $needed) {
                        return true;
                    }
                    break;
                case '-':
                    if ($needed < $permesso) {
                        return true;
                    }
                    break;
                case '=':
                    if ($permesso == $needed) {
                        return true;
                    }
                    break;
                case '!':
                    if ($permesso != $needed) {
                        return true;
                    }
                    break;
            }
        } else {
            die('Il permesso selezionato risulta inesistente');
        }
    }

}