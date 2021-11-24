<?php


class Personaggio extends BaseClass{

    /*** TABLES HELPER ***/

    /**
     * @fn getPgLocation
     * @note Estrae la posizione del pg
     * @param int $pg
     * @return int
     */
    public static function getPgLocation(int $pg): int
    {
        $data = DB::query("SELECT ultimo_luogo FROM personaggio WHERE id='{$pg}' LIMIT 1");

        return Filters::int($data['ultimo_luogo']);
    }

    /**
     * @fn getPgData
     * @note Ottiene i dati di un pg
     * @param int $pg
     * @param string $val
     * @return bool|int|mixed|string
     */
    public static function getPgData(int $pg, string $val ='*'){
        return DB::query("SELECT {$val} FROM personaggio WHERE id='{$pg}' LIMIT 1");
    }


    /**** CONTROLS ****/

    /**
     * @fn pgExist
     * @note Controlla esistenza personaggio
     * @param int $id
     * @return bool
     */
    public static function pgExist(int $id): bool
    {
        $data = DB::query("SELECT count(id) AS tot FROM personaggio WHERE id='{$id}' LIMIT 1");
        return ($data['tot'] > 0);
    }

    /**
     * @fn isMyPg
     * @note Controlla se e' il proprio pg
     * # TODO togliere il combaciare del nome quando passera' tutto tramite id
     * @param int $pg
     * @return bool
     */
    public static function isMyPg(int $pg): bool
    {

        $pg = Filters::in($pg);
        $me = Functions::getInstance()->getMyId();
        $me_name = Functions::getInstance()->getMe();

        return ( ($pg == $me) || ($pg == $me_name) );
    }


    /*** FUNCTIONS  ***/

    /**
     * @fn nameFromId
     * @note Estrae il nome del pg dall'id
     * @var int $id
     * @return string
     */
    public static function nameFromId(int $id):string
    {
        $id = Filters::int($id);
        $data = DB::query("SELECT nome FROM personaggio WHERE id='{$id}' LIMIT 1");

        return Filters::out($data['nome']);
    }
}