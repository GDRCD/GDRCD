<?php

/**
 * @class Functions
 * @note Classe contenitore delle funzioni principali degli helper
 */
class Functions extends BaseClass
{

    /**
     * @fn get_constant
     * @note Funzione di estrazione delle costanti dal db
     * @param string $name
     * @return mixed
     */
    public static function get_constant(string $name)
    {

        $name = gdrcd_filter('in', $name);

        $search = gdrcd_query("SELECT const_name,val FROM config WHERE const_name='{$name}'", 'result');
        $num = gdrcd_query($search, 'num_rows');

        if ($num == 0) {
            die("Costante {$name} inesistente nella tabella config.");
        } else if ($num > 1) {
            die("Esistono più costanti con il nome '{$name}'. Correggere e riprovare.");
        } else {
            $row = gdrcd_query($search, 'fetch');
            return gdrcd_filter('out', $row['val']);
        }
    }

    /**
     * @fn getPgId
     * @note Estrae l'id del pg dal suo nome
     * @param string $pg
     * @return int
     */
    public static function getPgId(string $pg): int
    {
        $pg = Filters::in($pg);
        $data = DB::query("SELECT id FROM personaggio WHERE nome='{$pg}' LIMIT 1");
        return Filters::int($data['id']);
    }


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
     * @fn getMe
     * @note Estrae il nome del pg collegato
     * @return false|int|mixed
     */
    public function getMe()
    {
        return $this->me;
    }

    /**
     * @fn getMe
     * @note Estrae il nome del pg collegato
     * @return false|int|mixed
     */
    public function getMyId()
    {
        return $this->me_id;
    }
}