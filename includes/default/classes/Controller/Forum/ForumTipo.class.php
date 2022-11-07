<?php

class ForumTipo extends Forum
{
    /*** TABLES HELPER ***/

    /**
     * @fn getForumType
     * @note Ottiene i dati del tipo di forum
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getForumType(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM forum_tipo WHERE id=:id LIMIT 1", ['id' => $id]);
    }

    /**
     * @fn getAllForumTypes
     * @note Ottieni tutti i tipi di forum
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllForumTypes(string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM forum_tipo WHERE 1  ORDER BY id", []);
    }


    /*** FUNCTIONS ***/

    /**
     * @fn isTypePublic
     * @note Ritorna true se il tipo di forum è pubblico, false altrimenti
     * @param int $type_id
     * @return bool
     * @throws Throwable
     */
    public function isTypePublic(int $type_id): bool
    {
        $type_data = $this->getForumType($type_id, 'pubblico');
        return Filters::bool($type_data['pubblico']);
    }


    /**
     * @fn isForumPublic
     * @note Ritorna true se il forum è pubblico
     * @param int $forum_id
     * @return bool
     * @throws Throwable
     */
    public function isForumPublic(int $forum_id): bool
    {
        $forum_type = $this->getForum($forum_id,'tipo');
        $type_data = $this->getForumType($forum_type['tipo'], 'pubblico');
        return Filters::bool($type_data['pubblico']);
    }
}