<?php

class Oggetti extends BaseClass
{

    /**
     * @fn __construct
     * @note Class constructor
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /*** OGGETTO TABLE HELPERS ***/

    /**
     * @fn getObject
     * @note Estrae i dati di un singolo oggetto
     * @param int $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getObject(int $id, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM oggetto WHERE id='{$id}' LIMIT 1");
    }

    /**
     * @fn getAllObjects
     * @note Estrae la lista di tutti gli oggetti esistenti
     * @param string $val
     * @return bool|int|mixed|string
     */
    private function getAllObjects(string $val = '*')
    {
        return DB::query("SELECT {$val} FROM oggetto WHERE 1 ORDER BY nome", 'result');
    }

    /**
     * @fn getObjectsByType
     * @note Estrae tutti gli oggetti di un tipo specifico
     * @param int $type
     * @param string $val
     * @return bool|int|mixed|string
     */
    private function getObjectsByType(int $type, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM oggetto WHERE tipo='{$type}' ORDER BY nome", 'result');
    }

    /*** OGGETTO_TIPO TABLE HELPERS ***/

    /**
     * @fn getObjectType
     * @note Estrae i dati di una tipologia di oggetto
     * @param int $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getObjectType(int $id, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM oggetto_tipo WHERE id='{$id}' LIMIT 1");
    }

    /**
     * @fn getAllObjectTypes
     * @note Estrae tutte le tipologia di oggetto esistenti
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllObjectTypes(string $val = '*')
    {
        return DB::query("SELECT {$val} FROM oggetto_tipo WHERE 1 ORDER BY nome", 'result');
    }

    /*** OGGETTO_POSIZIONE TABLE HELPERS ***/

    /**
     * @fn getObjectPosition
     * @note Estrae i dati di una posizione oggetto
     * @param int $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getObjectPosition(int $id, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM oggetto_posizioni WHERE id='{$id}' LIMIT 1");
    }

    /**
     * @fn getAllObjectPositions
     * @note Estrae tutte le posizioni di oggetto
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllObjectPositions(string $val = '*')
    {
        return DB::query("SELECT {$val} FROM oggetto_posizioni WHERE 1 ORDER BY nome", 'result');
    }

    /*** TABLES CONTROLS ***/

    /**
     * @fn existObject
     * @note Controlla se un oggetto esiste
     * @param int $id
     * @return bool
     */
    public function existObject(int $id): bool
    {
        $data = DB::query("SELECT * FROM oggetto WHERE id='{$id}' LIMIT 1");
        return (DB::rowsNumber($data) > 0);
    }

    /**
     * @fn existObjectType
     * @note Controlla se esiste una tipologia di oggetto
     * @param int $id
     * @return bool
     */
    public function existObjectType(int $id): bool
    {
        $data = DB::query("SELECT * FROM oggetto_tipo WHERE id='{$id}' LIMIT 1");
        return (DB::rowsNumber($data) > 0);
    }

    /*** PERMISSION ***/

    /**
     * @fn permissionManageObjects
     * @note Controlla se si hanno i permessi per la gestione degli oggetti
     * @return bool
     */
    public function permissionManageObjects(): bool
    {
        return Permissions::permission('MANAGE_OBJECTS');
    }

    /**
     * @fn permissionManageObjectsType
     * @note Controlla se si hanno i permessi per la gestione delle tipologie oggetto
     * @return bool
     */
    public function permissionManageObjectsType(): bool
    {
        return Permissions::permission('MANAGE_OBJECTS_TYPES');
    }

    /**
     * @fn permissionManageObjectsPositions
     * @note Controlla se si hanno i permessi per la gestione delle posizioni oggetto
     * @return bool
     */
    public function permissionManageObjectsPositions(): bool
    {
        return Permissions::permission('MANAGE_OBJECTS_POSITIONS');
    }

    /*** AJAX ***/

    /**
     * @fn ajaxObjectData
     * @note Estrae i dati di un oggetto alla modifica
     * @param array $post
     * @return array|void
     */
    public function ajaxObjectData(array $post)
    {

        if ( $this->permissionManageObjects() ) {

            $id = Filters::int($post['id']);

            $data = $this->getObject($id);

            return [
                'tipo' => Filters::int($data['tipo']),
                'nome' => Filters::out($data['nome']),
                'descrizione' => Filters::out($data['descrizione']),
                'immagine' => Filters::out($data['immagine']),
                'indossabile' => (bool)Filters::int($data['indossabile']),
                'cariche' => Filters::int($data['cariche']),
            ];
        }
    }

    /**
     * @fn ajaxObjectTypeData
     * @note Estrae i dati di una tipologia oggetto alla modifica
     * @param array $post
     * @return array|void
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

    /**
     * @fn ajaxObjectPositionData
     * @note Estrae i dati di una posizione oggetto alla modifica
     * @param array $post
     * @return array|void
     */
    public function ajaxObjectPositionData(array $post)
    {

        if ( $this->permissionManageObjectsType() ) {

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
     * @fn listObjectTypes
     * @note Crea le select delle tipologie oggetto
     * @param int $selected
     * @return string
     */
    public function listObjectTypes(): string
    {
        $list = $this->getAllObjectTypes('id,nome');
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $list);
    }

    /**
     * @fn listObjectPositions
     * @note Crea le select delle posizioni oggetto
     * @param int $selected
     * @return string
     */
    public function listObjectPositions(): string
    {
        $list = $this->getAllObjectPositions('id,nome');
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $list);
    }

    /**
     * @fn listObjects
     * @note Crea le select degli oggetti
     * @param int $selected
     * @return string
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
     */
    public static function addObjectToPg(int $obj, int $pg): void
    {
        $obj_data = Oggetti::getInstance()->getObject($obj, 'cariche');
        $cariche = Filters::int($obj_data['cariche']);

        DB::query("INSERT INTO personaggio_oggetto(personaggio, oggetto, cariche) VALUES('{$pg}','{$obj}','{$cariche}') ");
    }

    /**
     * @fn removeObjectFromPg
     * @note Add a NEW object to a pg
     * @param int $obj
     * @param int $pg
     * @return void
     */
    public static function removeObjectFromPg(int $obj, int $pg): void
    {
        DB::query("DELETE FROM personaggio_oggetto WHERE id='{$obj}' AND personaggio='{$pg}' ");
    }

    /*** MANAGEMENT FUNCTIONS - OBJECTS **/

    /**
     * @fn insertObject
     * @note Inserimento oggetto
     * @param array $post
     * @return array
     */
    public function insertObject(array $post): array
    {

        if ( $this->permissionManageObjects() ) {

            $tipo = Filters::int($post['tipo']);
            $nome = Filters::in($post['nome']);
            $descrizione = Filters::in($post['descrizione']);
            $immagine = Filters::in($post['immagine']);
            $indossabile = Filters::checkbox($post['indossabile']);
            $cariche = Filters::int($post['cariche']);
            $creato_da = Filters::int($this->me_id);

            DB::query("INSERT INTO oggetto(tipo, nome, descrizione, immagine, indossabile, cariche,creatore_da) 
                            VALUES('{$tipo}','{$nome}','{$descrizione}','{$immagine}','{$indossabile}','{$cariche}','{$creato_da}')");

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
     */
    public function editObject(array $post): array
    {

        if ( $this->permissionManageObjects() ) {

            $id = Filters::int($post['oggetto']);
            $tipo = Filters::int($post['tipo']);
            $nome = Filters::in($post['nome']);
            $descrizione = Filters::in($post['descrizione']);
            $immagine = Filters::in($post['immagine']);
            $indossabile = Filters::checkbox($post['indossabile']);
            $cariche = Filters::int($post['cariche']);

            DB::query("UPDATE oggetto 
                            SET tipo='{$tipo}',nome='{$nome}',descrizione='{$descrizione}',immagine='{$immagine}',
                                indossabile='{$indossabile}',cariche='{$cariche}'
                            WHERE id='{$id}' LIMIT 1");

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
     */
    public function deleteObject(array $post): array
    {

        if ( $this->permissionManageObjects() ) {

            $id = Filters::int($post['oggetto']);

            DB::query("DELETE FROM oggetto WHERE id='{$id}'");

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

    /*** MANAGEMENT FUNCTIONS - OBJECT TYPES **/

    /**
     * @fn insertObjectType
     * @note Inserimento tipologia oggetto
     * @param array $post
     * @return array
     */
    public function insertObjectType(array $post): array
    {

        if ( $this->permissionManageObjectsType() ) {

            $nome = Filters::in($post['nome']);
            $descrizione = Filters::in($post['descrizione']);

            DB::query("INSERT INTO oggetto_tipo( nome, descrizione) 
                            VALUES('{$nome}','{$descrizione}')");

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
     */
    public function editObjectType(array $post): array
    {

        if ( $this->permissionManageObjectsType() ) {

            $id = Filters::int($post['tipo']);
            $nome = Filters::in($post['nome']);
            $descrizione = Filters::in($post['descrizione']);

            DB::query("UPDATE oggetto_tipo 
                            SET nome='{$nome}',descrizione='{$descrizione}'
                            WHERE id='{$id}' LIMIT 1");

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
     */
    public function deleteObjectType(array $post): array
    {

        if ( $this->permissionManageObjectsType() ) {

            $id = Filters::int($post['tipo']);

            DB::query("DELETE FROM oggetto_tipo WHERE id='{$id}'");

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

    /*** MANAGEMENT FUNCTIONS - OBJECT POSITIONS **/

    /**
     * @fn insertObjectPosition
     * @note Inserimento posizione oggetto
     * @param array $post
     * @return array
     */
    public function insertObjectPosition(array $post): array
    {

        if ( $this->permissionManageObjectsPositions() ) {

            $nome = Filters::in($post['nome']);
            $img = Filters::in($post['immagine']);
            $numero = Filters::int($post['numero']);

            DB::query("INSERT INTO oggetto_posizioni( nome, immagine,numero) 
                            VALUES('{$nome}','{$img}','{$numero}')");

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
     */
    public function editObjectPosition(array $post): array
    {

        if ( $this->permissionManageObjectsPositions() ) {

            $id = Filters::int($post['id']);
            $nome = Filters::in($post['nome']);
            $img = Filters::in($post['immagine']);
            $numero = Filters::int($post['numero']);

            DB::query("UPDATE oggetto_posizioni 
                            SET nome='{$nome}',immagine='{$img}',numero='{$numero}'
                            WHERE id='{$id}' LIMIT 1");

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
     */
    public function deleteObjectPosition(array $post): array
    {

        if ( $this->permissionManageObjectsPositions() ) {

            $id = Filters::int($post['id']);

            DB::query("DELETE FROM oggetto_posizioni WHERE id='{$id}'");

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