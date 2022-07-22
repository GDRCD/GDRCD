<?php

class PersonaggioRuolo extends Personaggio
{

    /**
     * @fn __construct
     * @note PersonaggioRuolo constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**** TABLE HELPERS ***/

    /**
     * @fn getCharacterRoleById
     * @note Ottiene un ruolo di un personaggio.
     * @param string $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getCharacterRoleById(string $id, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM personaggio_ruolo WHERE id = '{$id}' LIMIT 1");
    }

    /**
     * @fn getAllCharacterRoles
     * @note Ottiene tutti i ruoli di un personaggio.
     * @param int $pg
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllCharacterRoles(int $pg, string $val = '*')
    {
        return DB::query("
             SELECT {$val} FROM personaggio_ruolo 
             WHERE personaggio_ruolo.personaggio='{$pg}'",'result');
    }

    /**
     * @fn getAllCharacterRolesWithRoleData
     * @note Ottiene tutti i ruoli di un personaggio con i dati dei ruoli.
     * @param int $pg
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllCharacterRolesWithRoleData(int $pg, string $val = '*')
    {
        return DB::query("
             SELECT {$val} FROM personaggio_ruolo 
             LEFT JOIN gruppi_ruoli ON (gruppi_ruoli.id = personaggio_ruolo.ruolo) 
             WHERE personaggio_ruolo.personaggio='{$pg}'",'result');
    }

    /**
     * @fn getCharacterRolesNumbers
     * @note Conta quanti ruoli ha un personaggio
     * @param int $pg
     * @return int
     */
    public function getCharacterRolesNumbers(int $pg): int
    {

        $groups = DB::query("
                SELECT COUNT(personaggio_ruolo.id) AS 'TOT' FROM personaggio_ruolo 
                WHERE personaggio_ruolo.personaggio ='{$pg}'");

        return Filters::int($groups['TOT']);
    }
}