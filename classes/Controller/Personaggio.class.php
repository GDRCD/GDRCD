<?php


class Personaggio extends BaseClass{

    /*** PERSONAGGIO TABLES HELPER ***/

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

    /*** OGGETTI TABLES HELPERT ***/

    /**
     * @fn getCharAllObjects
     * @note Estrae tutti gli oggetti di un personaggio
     * @param int $pg
     * @param string $val
     * @return bool|int|mixed|string
     */
    private function getCharAllObjects(int $pg, string $val = 'oggetto.*'){
        return DB::query("SELECT {$val} FROM personaggio_oggetto LEFT JOIN oggetto ON(oggetto.id_oggetto = personaggio_oggetto.oggetto) WHERE personaggio_oggetto.personaggio='{$pg}' ORDER BY oggetto.nome ",'result');
    }

    /**
     * @fn getCharSingleObject
     * @note Estrae un oggetto dalla tabella personaggio_oggetto
     * @param int $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    private function getCharSingleObject(int $id, string $val = '*'){
        return DB::query("SELECT {$val} FROM personaggio_oggetto LEFT JOIN oggetto ON(oggetto.id_oggetto = personaggio_oggetto.oggetto) WHERE personaggio_oggetto.personaggio='{$pg}' AND personaggio_oggetto.id='{$id}' ORDER BY oggetto.nome ",'result');
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

    /**
     * @fn updatePgData
     * @note Update pg data
     * @param int $id
     * @param string $set
     * @return void
     */
    public static function updatePgData(int $id, string $set):void
    {
        DB::query("UPDATE personaggio SET {$set} WHERE id='{$id}' LIMIT 1");
    }

    /**** OGGETTI */

    /**
     * @fn addObject
     * @note Add a NEW object to a pg
     * @param int $obj
     * @param int $pg
     * @return void
     */
    public static function addObject(int $obj, int $pg):void
    {
        $obj_data = Oggetti::getInstance()->getObject($obj,'cariche');
        $cariche = Filters::int($obj_data['cariche']);

        DB::query("INSERT INTO personaggio_oggetto(personaggio, oggetto, cariche) VALUES('{$pg}','{$obj}','{$cariche}') ");
    }

    /**
     * @fn getPgAllObjects
     * @note Estrae tutti gli oggetti di un personaggio
     * @param int $pg
     * @param bool $only_equipped
     * @return bool|int|mixed|string
     */
    public static function getPgAllObjects(int $pg, bool $only_equipped, $val = 'personaggio_oggetto.*,oggetto.*'){

        $pg = Filters::int($pg);

        $extra_query = ($only_equipped) ? ' AND personaggio_oggetto.indossato != 0 AND oggetto.indossabile = 1 ' : '';

        return DB::query("SELECT {$val}
                                        FROM personaggio_oggetto 
                                        LEFT JOIN oggetto 
                                        ON (personaggio_oggetto.oggetto = oggetto.id)                         
                                        WHERE personaggio_oggetto.personaggio ='{$pg}' {$extra_query}
                                        ", 'result');
    }

    /**
     * @fn calcAllObjsBonus
     * @note Calcola i bonus statistiche dell'oggetto
     * @param int $pg
     * @param int $car
     * @param array $excluded
     * @return int
     */
    public static function calcAllObjsBonus(int $pg, int $car, array $excluded = []): int
    {

        //#TODO Adattare con le stat oggetto

        $extra_query = '';
        $total_bonus = 0;

        if(!empty($excluded)){
            $implode = implode(',',$excluded);
            $extra_query = " AND personaggio_oggetto.id NOT IN ({$excluded})";
        }

        # Estraggo i bonus di tutti gli oggetti equipaggiati
        $objects = DB::query("SELECT oggetto.bonus_car{$car},personaggio_oggetto.id
                                        FROM personaggio_oggetto 
                                        LEFT JOIN oggetto 
                                        ON (personaggio_oggetto.oggetto = oggetto.id)
                                         
                                        WHERE personaggio_oggetto.personaggio ='{$pg}' {$extra_query}
                                        AND personaggio_oggetto.indossato > 0 AND oggetto.indossabile = 1

                                        ", 'result');

        # Per ogni oggetto equipaggiato
        foreach ($objects as $object) {
            # Aggiungo il suo bonus al totale
            $total_bonus += Filters::int($object["bonus_car{$car}"]);
        }

        return $total_bonus;
    }
}