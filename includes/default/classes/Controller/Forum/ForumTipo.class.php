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


    /*** AJAX ***/

    /**
     * @fn ajaxForumTypeData
     * @note Ottiene i dati di un tipo di forum
     * @param $post
     * @return array
     * @throws Throwable
     */
    public function ajaxForumTypeData($post): array
    {
        return $this->getForumType($post['id'])->getData()[0];

    }

    /*** LISTS ***/

    /**
     * @fn listForums
     * @note Ritorna la lista dei forum
     * @param int $selected
     * @return string
     * @throws Throwable
     */
    public function listTypes(int $selected = 0): string
    {
        $forums_types = $this->getAllForumTypes();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', $selected, $forums_types);
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
        $forum_type = $this->getForum($forum_id, 'tipo');
        $type_data = $this->getForumType($forum_type['tipo'], 'pubblico');
        return Filters::bool($type_data['pubblico']);
    }


    /**** GESTIONE ****/

    /**
     * @fn newType
     * @note Inserisce una nuova tipologia di forum
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function newType(array $post): array
    {

        if ( ForumPermessi::getInstance()->permissionForumAdmin() ) {

            $name = Filters::in($post['nome']);
            $description = Filters::in($post['descrizione']);
            $public = Filters::checkbox($post['pubblico']);

            DB::queryStmt("INSERT INTO forum_tipo (nome, descrizione,pubblico) VALUES (:nome, :descrizione,:pubblico)", [
                'nome' => $name,
                'descrizione' => $description,
                'pubblico' => $public,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Tipologia forum creata correttamente.',
                'swal_type' => 'success',
                'forums_types_list' => $this->listTypes(),
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error',
            ];
        }
    }

    /**
     * @fn modType
     * @note Modifica una tipologia di forum
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function modType(array $post): array
    {

        if ( ForumPermessi::getInstance()->permissionForumAdmin() ) {

            $id = Filters::int($post['id']);
            $name = Filters::in($post['nome']);
            $description = Filters::in($post['descrizione']);
            $public = Filters::checkbox($post['pubblico']);

            DB::queryStmt("UPDATE forum_tipo SET nome=:nome, descrizione=:description, pubblico=:pubblico WHERE id=:id", [
                'id' => $id,
                'nome' => $name,
                'description' => $description,
                'pubblico' => $public,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Tipologia forum modificata correttamente.',
                'swal_type' => 'success',
                'forums_types_list' => $this->listTypes(),
            ];

        } else {

            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error',
            ];
        }
    }

    /**
     * @fn delType
     * @note Elimina una tipologia di forum
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function delType(array $post): array
    {

        if ( ForumPermessi::getInstance()->permissionForumAdmin() ) {

            $id = Filters::int($post['id']);

            DB::queryStmt("DELETE FROM forum_tipo WHERE id=:id", [
                'id' => $id,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Tipologia forum eliminata correttamente.',
                'swal_type' => 'success',
                'forums_types_list' => $this->listTypes(),
            ];

        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error',
            ];
        }

    }
}