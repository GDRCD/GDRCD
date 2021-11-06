<?php


class Personaggio extends BaseClass{


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

    /**
     * @fn pgExist
     * @note Controlla esistenza personaggio
     * @param int $id
     * @return bool
     */
    public static function pgExist($id){
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
    public static function isMyPg($pg){

        $pg = Filters::in($pg);
        $me = Functions::getInstance()->getMyId();
        $me_name = Functions::getInstance()->getMe();

        return ( ($pg == $me) || ($pg == $me_name) );
    }
}