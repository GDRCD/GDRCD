<?php


class OggettiTipo extends Oggetti {

    /*** TABLE HELPERS ***/

    /**
     * @fn getObjectType
     * @note Estrae i dati di una tipologia di oggetto
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getObjectType(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM oggetto_tipo WHERE id=:id LIMIT 1",[
            'id' => $id
        ]);
    }

    /**
     * @fn getAllObjectTypes
     * @note Estrae tutte le tipologia di oggetto esistenti
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllObjectTypes(string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM oggetto_tipo WHERE 1 ORDER BY nome", []);
    }


    /*** PERMISSION ***/

    /**
     * @fn permissionManageObjectsType
     * @note Controlla se si hanno i permessi per la gestione delle tipologie oggetto
     * @return bool
     * @throws Throwable
     */
    public function permissionManageObjectsType(): bool
    {
        return Permissions::permission('MANAGE_OBJECTS_TYPES');
    }

    /*** AJAX ***/

    /**
     * @fn ajaxObjectTypeData
     * @note Estrae i dati di una tipologia oggetto alla modifica
     * @param array $post
     * @return array|void
     * @throws Throwable
     */
    public function ajaxObjectTypeData(array $post)
    {

        if ( $this->permissionManageObjectsType() ) {

            $id = Filters::int($post['id']);

            $data = $this->getObjectType($id);

            return [
                'nome' => Filters::out($data['nome']),
                'descrizione' => Filters::out($data['descrizione']),
            ];
        }
    }

    /*** LISTS ***/

    /**
     * @fn listObjectTypes
     * @note Crea le select delle tipologie oggetto
     * @param int $selected
     * @return string
     * @throws Throwable
     */
    public function listObjectTypes(int $selected = 0): string
    {
        $list = $this->getAllObjectTypes('id,nome');
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', $selected, $list);
    }


    /*** MANAGEMENT FUNCTIONS - OBJECT TYPES **/

    /**
     * @fn newObjectType
     * @note Crea una nuova tipologia di oggetto
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function newObjectType(array $post): array
    {

        if ( $this->permissionManageObjectsType() ) {

            $nome = Filters::in($post['nome']);
            $descrizione = Filters::in($post['descrizione']);

            DB::queryStmt("INSERT INTO oggetto_tipo(nome,descrizione) VALUES (:nome,:descrizione)",[
                'nome' => $nome,
                'descrizione' => $descrizione
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Tipo Oggetto inserito correttamente.',
                'swal_type' => 'success',
                'obj_type_list' => $this->listObjectTypes(),
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
     * @fn editObjectType
     * @note Modifica tipologia oggetto
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function editObjectType(array $post): array
    {

        if ( $this->permissionManageObjectsType() ) {

            $id = Filters::int($post['tipo']);
            $nome = Filters::in($post['nome']);
            $descrizione = Filters::in($post['descrizione']);

            DB::queryStmt("UPDATE oggetto_tipo SET nome = :nome, descrizione = :descrizione WHERE id = :id",[
                'id' => $id,
                'nome' => $nome,
                'descrizione' => $descrizione
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Tipo Oggetto modificato correttamente.',
                'swal_type' => 'success',
                'obj_type_list' => $this->listObjectTypes(),
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
     * @fn deleteObjectType
     * @note Eliminazione tipologia oggetto
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function deleteObjectType(array $post): array
    {

        if ( $this->permissionManageObjectsType() ) {

            $id = Filters::int($post['tipo']);

            DB::queryStmt("DELETE FROM oggetto_tipo WHERE id = :id",[
                'id' => $id
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Tipo Oggetto eliminato correttamente.',
                'swal_type' => 'success',
                'obj_type_list' => $this->listObjectTypes(),
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