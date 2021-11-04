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

    public static function pgExist($id){
        $data = DB::query("SELECT count(id) AS tot FROM personaggio WHERE id='{$id}' LIMIT 1");
        return ($data['tot'] > 0);
    }

}