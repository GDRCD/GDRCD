<?php

class PersonaggioChatOpzioni extends Personaggio
{

    /**
     * @fn __construct
     * @note PersonaggioChatOpzioni constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**** TABLE HELPERS ***/

    /**
     * @fn getOptionValue
     * @note Ottiene il valore di un'opzione chat specifica in base al personaggio
     * @param string $option
     * @param int $pg
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getOptionValue(string $option, int $pg, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt(
            "SELECT {$val} FROM personaggio_chat_opzioni WHERE opzione=:option AND personaggio=:pg LIMIT 1",
            [
                'option' => $option,
                'pg' => $pg,
            ]
        );
    }

    /**
     * @fn getAllOptionsValues
     * @note Ottiene tutte le opzioni chat di un personaggio
     * @param int $pg
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllOptionsValues(int $pg, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM personaggio_chat_opzioni WHERE personaggio=:pg", [
            'pg' => $pg,
        ]);
    }

    /**
     * @fn getAllOptionsWithValues
     * @note Ottiene tutte le opzioni chat di un personaggio
     * @param int $pg
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllOptionsWithValues(int $pg, string $val = 'chat_opzioni.*,personaggio_chat_opzioni.*'): DBQueryInterface
    {

        return DB::queryStmt("SELECT {$val} FROM chat_opzioni 
                LEFT JOIN personaggio_chat_opzioni ON chat_opzioni.nome = personaggio_chat_opzioni.opzione AND personaggio_chat_opzioni.personaggio=:pg
                WHERE 1
                ", ['pg' => $pg]);
    }

}