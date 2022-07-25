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
     * @return bool|int|mixed|string
     */
    public static function getPgObjectsByPosition(int $id_pg, int $position, int $limit = 1, string $val = '*')
    {

        return DB::query("SELECT {$val}
                                FROM personaggio_oggetto 
                                LEFT JOIN oggetto ON (oggetto.id = personaggio_oggetto.oggetto)
                                WHERE personaggio_oggetto.indossato = 1 
                                  AND oggetto.posizione = '{$position}'
                                  AND personaggio_oggetto.personaggio = '{$id_pg}'
                                LIMIT {$limit}", 'result');

    }

    /**
     * @fn getAllPgObjectsByEquipped
     * @note Estrae tutti gli oggetti di un personaggio in base all'indossato
     * @param int $id_pg
     * @param int $equipped
     * @param string $val
     * @return bool|int|mixed|string
     */
    public static function getAllPgObjectsByEquipped(int $id_pg, int $equipped, string $val = '*')
    {
        return DB::query("SELECT {$val}
                                FROM personaggio_oggetto 
                                LEFT JOIN oggetto ON (oggetto.id = personaggio_oggetto.oggetto)
                                WHERE personaggio_oggetto.indossato = '{$equipped}'
                                  AND personaggio_oggetto.personaggio = '{$id_pg}'", 'result');
    }

    /**
     * @fn getAllPgObjectsByEquipped
     * @note Estrae tutti gli oggetti di un personaggio
     * @param int $id_pg
     * @param string $val
     * @return bool|int|mixed|string
     */
    public static function getAllPgObjects(int $id_pg, string $val = '*')
    {
        return DB::query("SELECT {$val}
                                FROM personaggio_oggetto 
                                LEFT JOIN oggetto ON (oggetto.id = personaggio_oggetto.oggetto)
                                  AND personaggio_oggetto.personaggio = '{$id_pg}'", 'result');
    }

    /**
     * @fn getPgObject
     * @note Estrae i dati di un oggetto di un personaggio
     * @param int $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    public static function getPgObject(int $id, string $val = 'oggetto.*,personaggio_oggetto.*')
    {
        return DB::query("SELECT {$val}
                                FROM personaggio_oggetto 
                                LEFT JOIN oggetto ON (oggetto.id = personaggio_oggetto.oggetto)
                                  WHERE personaggio_oggetto.id = '{$id}' LIMIT 1");
    }

    /*** CONTROLS ***/

    /**
     * @fn isPgObject
     * @note Controlla se l'oggetto (personaggio_oggetto.id) e' di proprieta' del personaggio
     * @param int $obj
     * @param int $pg
     * @return bool
     */
    public static function isPgObject(int $obj, int $pg): bool
    {
        $data = PersonaggioOggetti::getPgObject($obj, 'personaggio_oggetto.personaggio');
        $personaggio = Filters::int($data['personaggio']);
        return ($pg === $personaggio);
    }

}