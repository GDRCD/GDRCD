<?php


class Personaggio extends BaseClass
{

    /*** PERSONAGGIO TABLES HELPER ***/

    /**
     * @fn getPgLocation
     * @note Estrae la posizione del pg
     * @param int $pg
     * @return int
     */
    public static function getPgLocation(int $pg = 0): int
    {

        if (empty($pg)) {
            $pg = Functions::getInstance()->getMyId();
        }

        $data = DB::query("SELECT ultimo_luogo FROM personaggio WHERE id='{$pg}' LIMIT 1");

        return Filters::int($data['ultimo_luogo']);
    }

    /**
     * @fn getPgMap
     * @note Estrae la mappa del pg
     * @param int $pg
     * @return int
     */
    public static function getPgMap(int $pg): int
    {
        $data = DB::query("SELECT ultima_mappa FROM personaggio WHERE id='{$pg}' LIMIT 1");

        return Filters::int($data['ultima_mappa']);
    }

    /**
     * @fn getPgData
     * @note Ottiene i dati di un pg
     * @param int $pg
     * @param string $val
     * @return bool|int|mixed|string
     */
    public static function getPgData(int $pg, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM personaggio WHERE id='{$pg}' LIMIT 1");
    }

    /**
     * @fn getAllPg
     * @note Estrae tutti i personaggi
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllPg(string $val = '*')
    {
        return DB::query("SELECT {$val} FROM personaggio WHERE 1", 'result');
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
     * @param int $pg
     * @return bool
     */
    public static function isMyPg(int $pg): bool
    {

        $pg = Filters::in($pg);
        $me = Functions::getInstance()->getMyId();

        return ($pg == $me);
    }


    /*** FUNCTIONS  ***/

    /**
     * @fn nameFromId
     * @note Estrae il nome del pg dall'id
     * @var int $id
     * @return string
     */
    public static function nameFromId(int $id): string
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
    public static function updatePgData(int $id, string $set): void
    {
        DB::query("UPDATE personaggio SET {$set} WHERE id='{$id}' LIMIT 1");
    }


    /**
     * @fn listPgs
     * @note Genera gli option per i personaggi
     * @return string
     */
    public function listPgs(): string
    {
        $roles = $this->getAllPg();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $roles);
    }
}