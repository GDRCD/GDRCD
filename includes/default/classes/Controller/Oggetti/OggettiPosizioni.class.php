<?php

class OggettiPosizioni extends Oggetti
{

    /*** TABLE HELPERS ***/

    /**
     * @fn getObjectPosition
     * @note Estrae i dati di una posizione oggetto
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getObjectPosition(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM oggetto_posizioni WHERE id=:id LIMIT 1",[
            'id' => $id
        ]);
    }

    /**
     * @fn getAllObjectPositions
     * @note Estrae tutte le posizioni di oggetto
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllObjectPositions(string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM oggetto_posizioni WHERE 1 ORDER BY nome", []);
    }

    /*** PERMISSION ***/

    /**
     * @fn permissionManageObjectsPositions
     * @note Controlla se si hanno i permessi per la gestione delle posizioni oggetto
     * @return bool
     * @throws Throwable
     */
    public function permissionManageObjectsPositions(): bool
    {
        return Permissions::permission('MANAGE_OBJECTS_POSITIONS');
    }

    /*** AJAX ***/

    /**
     * @fn ajaxObjectPositionData
     * @note Estrae i dati di una posizione oggetto alla modifica
     * @param array $post
     * @return array|void
     * @throws Throwable
     */
    public function ajaxObjectPositionData(array $post)
    {

        if ( OggettiTipo::getInstance()->permissionManageObjectsType() ) {

            $id = Filters::int($post['id']);

            $data = $this->getObjectPosition($id);

            return [
                'nome' => Filters::out($data['nome']),
                'immagine' => Filters::out($data['immagine']),
                'numero' => Filters::out($data['numero']),
            ];
        }
    }

    /*** LISTS ***/

    /**
     * @fn listObjectPositions
     * @note Crea le select delle posizioni oggetto
     * @param int $selected
     * @return string
     * @throws Throwable
     */
    public function listObjectPositions(int $selected = 0): string
    {
        $list = $this->getAllObjectPositions('id,nome');
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', $selected, $list);
    }

    /*** GESTIONE **/

    /**
     * @fn newObjectPosition
     * @note Crea una nuova posizione oggetto
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function newObjectPosition(array $post): array
    {

        if ( $this->permissionManageObjectsPositions() ) {

            $nome = Filters::in($post['nome']);
            $img = Filters::in($post['immagine']);
            $numero = Filters::int($post['numero']);

            DB::queryStmt("INSERT INTO oggetto_posizioni(nome,immagine,numero) VALUES (:nome,:immagine,:numero)",[
                'nome' => $nome,
                'immagine' => $img,
                'numero' => $numero
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Posizione Oggetto inserita correttamente.',
                'swal_type' => 'success',
                'obj_position_list' => $this->listObjectPositions(),
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
     * @fn editObjectPosition
     * @note Modifica posizione oggetto
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function editObjectPosition(array $post): array
    {

        if ( $this->permissionManageObjectsPositions() ) {

            $id = Filters::int($post['id']);
            $nome = Filters::in($post['nome']);
            $img = Filters::in($post['immagine']);
            $numero = Filters::int($post['numero']);

            DB::queryStmt("UPDATE oggetto_posizioni SET nome = :nome, immagine = :immagine, numero = :numero WHERE id = :id",[
                'id' => $id,
                'nome' => $nome,
                'immagine' => $img,
                'numero' => $numero
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Posizione Oggetto modificata correttamente.',
                'swal_type' => 'success',
                'obj_position_list' => $this->listObjectPositions(),
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
     * @fn deleteObjectPosition
     * @note Eliminazione posizione oggetto
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function deleteObjectPosition(array $post): array
    {

        if ( $this->permissionManageObjectsPositions() ) {

            $id = Filters::int($post['id']);

            DB::queryStmt("DELETE FROM oggetto_posizioni WHERE id = :id",[
                'id' => $id
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Posizione Oggetto eliminata correttamente.',
                'swal_type' => 'success',
                'obj_position_list' => $this->listObjectPositions(),
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