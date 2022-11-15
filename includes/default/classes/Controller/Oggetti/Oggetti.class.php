<?php

class Oggetti extends BaseClass
{

    /*** OGGETTO TABLE HELPERS ***/

    /**
     * @fn getObject
     * @note Estrae i dati di un singolo oggetto
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getObject(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM oggetto WHERE id=:id LIMIT 1", [
            'id' => $id,
        ]);
    }

    /**
     * @fn getAllObjects
     * @note Estrae la lista di tutti gli oggetti esistenti
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    private function getAllObjects(string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM oggetto WHERE 1 ORDER BY nome", []);
    }

    /*** TABLES CONTROLS ***/

    /**
     * @fn existObject
     * @note Controlla se un oggetto esiste
     * @param int $id
     * @return bool
     * @throws Throwable
     */
    public function existObject(int $id): bool
    {
        $data = DB::queryStmt("SELECT * FROM oggetto WHERE id=:id LIMIT 1", [
            'id' => $id,
        ]);
        return ($data->getNumRows() > 0);
    }


    /*** PERMISSION ***/

    /**
     * @fn permissionManageObjects
     * @note Controlla se si hanno i permessi per la gestione degli oggetti
     * @return bool
     * @throws Throwable
     */
    public function permissionManageObjects(): bool
    {
        return Permissions::permission('MANAGE_OBJECTS');
    }

    /*** AJAX ***/

    /**
     * @fn ajaxObjectData
     * @note Estrae i dati di un oggetto alla modifica
     * @param array $post
     * @return array|void
     * @throws Throwable
     */
    public function ajaxObjectData(array $post)
    {
        if ( $this->permissionManageObjects() ) {
            $id = Filters::int($post['id']);
            return $this->getObject($id)->getData()[0];
        }
    }


    /*** LISTS ***/

    /**
     * @fn listObjects
     * @note Crea le select degli oggetti
     * @param int $selected
     * @return string
     * @throws Throwable
     */
    public function listObjects(int $selected = 0): string
    {
        $list = $this->getAllObjects('id,nome');
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', $selected, $list);
    }

    /*** FUNCTIONS */

    /**
     * @fn addObjectToPg
     * @note Add a NEW object to a pg
     * @param int $obj
     * @param int $pg
     * @return void
     * @throws Throwable
     */
    public function addObjectToPg(int $obj, int $pg): void
    {
        $obj_data = $this->getObject($obj, 'cariche');
        $cariche = Filters::int($obj_data['cariche']);

        DB::queryStmt("INSERT INTO personaggio_oggetto(oggetto,personaggio,cariche) VALUES (:obj,:pg,:cariche)", [
            'obj' => $obj,
            'personaggio' => $pg,
            'cariche' => $cariche,
        ]);
    }

    /**
     * @fn removeObjectFromPg
     * @note Add a NEW object to a pg
     * @param int $obj
     * @param int $pg
     * @return void
     * @throws Throwable
     */
    public static function removeObjectFromPg(int $obj, int $pg): void
    {
        DB::queryStmt("DELETE FROM personaggio_oggetto WHERE id = :obj AND personaggio = :pg", [
            'obj' => $obj,
            'pg' => $pg,
        ]);
    }

    /*** MANAGEMENT FUNCTIONS - OBJECTS **/

    /**
     * @fn insertObject
     * @note Inserimento oggetto
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function insertObject(array $post): array
    {

        if ( $this->permissionManageObjects() ) {

            $tipo = Filters::int($post['tipo']);
            $posizione = Filters::int($post['posizione']);
            $nome = Filters::in($post['nome']);
            $descrizione = Filters::in($post['descrizione']);
            $immagine = Filters::in($post['immagine']);
            $indossabile = Filters::checkbox($post['indossabile']);
            $cariche = Filters::int($post['cariche']);
            $creato_da = Filters::int($this->me_id);

            DB::queryStmt("INSERT INTO oggetto(tipo,nome,descrizione,immagine,indossabile,posizione,cariche,creato_da) VALUES (:tipo,:nome,:descrizione,:immagine,:indossabile,:posizione,:cariche,:creato_da)", [
                'tipo' => $tipo,
                'nome' => $nome,
                'descrizione' => $descrizione,
                'immagine' => $immagine,
                'indossabile' => $indossabile,
                'posizione' => $posizione,
                'cariche' => $cariche,
                'creato_da' => $creato_da,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Oggetto inserito correttamente.',
                'swal_type' => 'success',
                'obj_list' => $this->listObjects(),
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
     * @fn editObject
     * @note Modifica oggetto
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function editObject(array $post): array
    {

        if ( $this->permissionManageObjects() ) {

            $id = Filters::int($post['oggetto']);
            $posizione = Filters::int($post['posizione']);
            $tipo = Filters::int($post['tipo']);
            $nome = Filters::in($post['nome']);
            $descrizione = Filters::in($post['descrizione']);
            $immagine = Filters::in($post['immagine']);
            $indossabile = Filters::checkbox($post['indossabile']);
            $cariche = Filters::int($post['cariche']);

            DB::queryStmt("UPDATE oggetto SET tipo = :tipo, nome = :nome, descrizione = :descrizione, immagine = :immagine, indossabile = :indossabile,posizione=:posizione, cariche = :cariche WHERE id = :id", [
                'id' => $id,
                'tipo' => $tipo,
                'nome' => $nome,
                'descrizione' => $descrizione,
                'immagine' => $immagine,
                'indossabile' => $indossabile,
                'posizione' => $posizione,
                'cariche' => $cariche,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Oggetto modificato correttamente.',
                'swal_type' => 'success',
                'obj_list' => $this->listObjects(),
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
     * @fn deleteObject
     * @note Eliminazione oggetto
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function deleteObject(array $post): array
    {

        if ( $this->permissionManageObjects() ) {

            $id = Filters::int($post['oggetto']);

            DB::queryStmt("DELETE FROM oggetto WHERE id = :id", [
                'id' => $id,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Oggetto eliminato correttamente.',
                'swal_type' => 'success',
                'obj_list' => $this->listObjects(),
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
     * @fn assignObject
     * @note Assegnazione oggetto a un PG
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function assignObject(array $post): array
    {
        if ( $this->permissionManageObjects() ) {

            $pg = Filters::int($post['personaggio']);
            $object = Filters::int($post['oggetto']);

            $this->addObjectToPg($pg, $object);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Oggetto assegnato correttamente.',
                'swal_type' => 'success'
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