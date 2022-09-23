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
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getCharacterRoleById(string $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM personaggio_ruolo WHERE id = :id LIMIT 1", [
            'id' => $id,
        ]);
    }

    /**
     * @fn getAllCharacterRoles
     * @note Ottiene tutti i ruoli di un personaggio.
     * @param int $pg
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllCharacterRoles(int $pg, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("
             SELECT {$val} FROM personaggio_ruolo 
             WHERE personaggio_ruolo.personaggio=:pg", [
            'pg' => $pg,
        ]);
    }

    /**
     * @fn getAllCharacterRolesWithRoleData
     * @note Ottiene tutti i ruoli di un personaggio con i dati dei ruoli.
     * @param int $pg
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllCharacterRolesWithRoleData(int $pg, string $val = 'personaggio_ruolo.*,gruppi_ruoli.*,gruppi.nome AS gruppo_nome'): DBQueryInterface
    {
        return DB::queryStmt("
             SELECT {$val} FROM personaggio_ruolo 
             LEFT JOIN gruppi_ruoli ON (gruppi_ruoli.id = personaggio_ruolo.ruolo) 
             LEFT JOIN gruppi ON (gruppi.id = gruppi_ruoli.gruppo) 
             WHERE personaggio_ruolo.personaggio=:pg", ['pg' => $pg]);
    }

    /**
     * @fn getCharacterRolesNumbers
     * @note Conta quanti ruoli ha un personaggio
     * @param int $pg
     * @return int
     * @throws Throwable
     */
    public function getCharacterRolesNumbers(int $pg): int
    {

        $groups = DB::queryStmt("
                SELECT COUNT(personaggio_ruolo.id) AS 'TOT' FROM personaggio_ruolo 
                WHERE personaggio_ruolo.personaggio =:pg", ['pg' => $pg]);

        return Filters::int($groups['TOT']);
    }
}