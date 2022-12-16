<?php

class PersonaggioOggetti extends Personaggio
{

    /**** TABLES HELPERS ***/

    /**
     * @fn getPgObjectsByPosition
     * @note Estrae gli oggetti equipaggiati per quella parte del corpo
     * @param int $id_pg
     * @param int $position
     * @param int $limit
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public static function getPgObjectsByPosition(int $id_pg, int $position, int $limit = 1, string $val = '*'): DBQueryInterface
    {

        return DB::queryStmt(
            "SELECT {$val} FROM personaggio_oggetto 
                  LEFT JOIN oggetto ON (oggetto.id = personaggio_oggetto.oggetto)
                  WHERE personaggio_oggetto.indossato = 1 AND oggetto.posizione = :position AND personaggio_oggetto.personaggio = :pg
                  LIMIT {$limit}",
            [
                'pg' => $id_pg,
                'position' => $position,
            ]
        );

    }

    /**
     * @fn getAllPgObjectsByEquipped
     * @note Estrae tutti gli oggetti di un personaggio in base all'indossato
     * @param int $id_pg
     * @param int $equipped
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public static function getAllPgObjectsByEquipped(int $id_pg, int $equipped, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt(
            "SELECT {$val} FROM personaggio_oggetto 
                  LEFT JOIN oggetto ON (oggetto.id = personaggio_oggetto.oggetto)
                  WHERE personaggio_oggetto.indossato = :equipped AND personaggio_oggetto.personaggio = :pg",
            [
                'pg' => $id_pg,
                'equipped' => $equipped,
            ]
        );
    }

    /**
     * @fn getAllPgObjectsByEquipped
     * @note Estrae tutti gli oggetti di un personaggio
     * @param int $id_pg
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public static function getAllPgObjects(int $id_pg, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt(
            "SELECT {$val} FROM personaggio_oggetto 
                  LEFT JOIN oggetto ON (oggetto.id = personaggio_oggetto.oggetto) AND personaggio_oggetto.personaggio = :pg",
            [
                'pg' => $id_pg,
            ]
        );
    }

    /**
     * @fn getPgObject
     * @note Estrae i dati di un oggetto di un personaggio
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public static function getPgObject(int $id, string $val = 'oggetto.*,personaggio_oggetto.*'): DBQueryInterface
    {
        return DB::queryStmt(
            "SELECT {$val} FROM personaggio_oggetto 
                LEFT JOIN oggetto ON (oggetto.id = personaggio_oggetto.oggetto)
                WHERE personaggio_oggetto.id = :id LIMIT 1",
            [
                "id" => $id,
            ]
        );
    }

    /**** LISTS ****/

    /**
     * @fn listChatPgEquipments
     * @note Lista gli oggetti indossati dal personaggio in chat
     * @param int $id_pg
     * @param bool $equipped
     * @param int $selected
     * @param string $label
     * @return string
     * @throws Throwable
     */
    public function listChatPgEquipments(int $id_pg, bool $equipped,int $selected = 0, string $label = 'Oggetti'): string
    {
        $list = $this->getAllPgObjectsByEquipped($id_pg, $equipped,'personaggio_oggetto.id,oggetto.nome');
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', $selected, $list, $label);
    }

    /*** CONTROLS ***/

    /**
     * @fn isPgObject
     * @note Controlla se l'oggetto (personaggio_oggetto.id) è di proprietà del personaggio
     * @param int $obj
     * @param int $pg
     * @return bool
     * @throws Throwable
     */
    public static function isPgObject(int $obj, int $pg): bool
    {
        $data = PersonaggioOggetti::getPgObject($obj, 'personaggio_oggetto.personaggio');
        $personaggio = Filters::int($data['personaggio']);
        return ($pg === $personaggio);
    }

}