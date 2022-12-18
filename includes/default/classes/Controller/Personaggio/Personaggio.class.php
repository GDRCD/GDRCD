<?php

class Personaggio extends BaseClass
{
    /*** TABLES HELPER ***/

    /**
     * @fn getPgLocation
     * @note Estrae la posizione del pg
     * @param int $pg
     * @return int
     * @throws Throwable
     */
    public static function getPgLocation(int $pg = 0): int
    {

        if ( empty($pg) ) {
            $pg = Functions::getInstance()->getMyId();
        }

        $data = DB::queryStmt(
            "SELECT ultimo_luogo FROM personaggio WHERE id=:id LIMIT 1",
            [
                'id' => $pg,
            ]
        );

        return Filters::int($data['ultimo_luogo']);
    }

    /**
     * @fn getPgMap
     * @note Estrae la mappa del pg
     * @param int $pg
     * @return int
     * @throws Throwable
     */
    public static function getPgMap(int $pg): int
    {
        $data = DB::queryStmt(
            "SELECT ultima_mappa FROM personaggio WHERE id=:id LIMIT 1",
            [
                'id' => $pg,
            ]
        );

        return Filters::int($data['ultima_mappa']);
    }

    /**
     * @fn getPgData
     * @note Ottiene i dati di un pg
     * @param int $pg
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public static function getPgData(int $pg, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM personaggio WHERE id=:id LIMIT 1", ['id' => $pg]);
    }

    /**
     * @fn getAllPG
     * @note Ritorna la lista dei pg registrati escludendo quelli già presenti fra i contatti e l'utente stesso
     * @param string $val
     * @param string $where
     * @param string $order
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllPG(string $val = '*', string $where = '1', string $order = ''):DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM personaggio  WHERE {$where} {$order}", []);
    }

    /***** LISTS *****/

    /**
     * @fn listPgs
     * @note Genera gli option per i personaggi
     * @param int $selected
     * @return string
     * @throws Throwable
     */
    public function listPgs(int $selected = 0, $label = 'Personaggi'): string
    {
        $pgs = $this->getAllPg();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', $selected, $pgs, $label);
    }

    /**
     * @fn listPgs
     * @note Genera gli option per i personaggi
     * @param array $selected
     * @return string
     * @throws Throwable
     */
    public function listPgsMultiselect(array $selected = []): string
    {
        $pgs = $this->getAllPg();
        return Template::getInstance()->startTemplate()->renderSelectMulti('id', 'nome', $selected, $pgs);
    }

    /**** CONTROLS ****/

    /**
     * @fn pgExist
     * @note Controlla esistenza personaggio
     * @param int $id
     * @return bool
     * @throws Throwable
     */
    public static function pgExist(int $id): bool
    {
        $data = DB::queryStmt("SELECT count(id) AS tot FROM personaggio WHERE id=:id LIMIT 1", ['id' => $id]);
        return ($data['tot'] > 0);
    }

    /**
     * @fn isMyPg
     * @note Controlla se è il proprio pg
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
     * @param int $id
     * @return string
     * @throws Throwable
     */
    public static function nameFromId(int $id): string
    {
        $id = Filters::int($id);
        $data = DB::queryStmt("SELECT nome FROM personaggio WHERE id=:id LIMIT 1", ['id' => $id]);
        return Filters::out($data['nome']);
    }

    /**
     * @fn updatePgData
     * @note Update pg data
     * @param int $id
     * @param string $set
     * @param array $params
     * @return void
     * @throws Throwable
     */
    public static function updatePgData(int $id, string $set, array $params): void
    {
        DB::queryStmt("UPDATE personaggio SET {$set} WHERE id='{$id}' LIMIT 1", $params);
    }
}