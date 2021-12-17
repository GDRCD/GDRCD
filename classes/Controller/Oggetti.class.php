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
    private function getAllObjectTypes(string $val = '*')
    {
        return DB::query("SELECT {$val} FROM oggetto_tipo WHERE 1 ORDER BY nome", 'result');
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

    /*** AJAX ***/

    /**
     * @fn ajaxObjectData
     * @note Estrae i dati di un oggetto alla modifica
     * @param array $post
     * @return array|void
     */
    public function ajaxObjectData(array $post){

        if($this->permissionManageObjects()){

            $id = Filters::int($post['id']);

            $data = $this->getObject($id);

            return [
                'tipo' => Filters::int($data['tipo']),
                'nome' => Filters::out($data['nome']),
                'descrizione' => Filters::out($data['descrizione']),
                'immagine' => Filters::out($data['immagine']),
                'indossabile' => (bool)Filters::int($data['indossabile']),
                'costo' => Filters::int($data['costo']),
                'cariche' => Filters::int($data['cariche'])
            ];
        }
    }

    /**
     * @fn ajaxObjectTypeData
     * @note Estrae i dati di una tipologia oggetto alla modifica
     * @param array $post
     * @return array|void
     */
    public function ajaxObjectTypeData(array $post){

        if($this->permissionManageObjectsType()){

            $id = Filters::int($post['id']);

            $data = $this->getObjectType($id);

            return [
                'nome' => Filters::out($data['nome']),
                'descrizione' => Filters::out($data['descrizione'])
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
    public function listObjectTypes(int $selected = 0): string
    {
        $html = '';
        $list = $this->getAllObjectTypes('id,nome');

        foreach ($list as $row){
            $id = Filters::int($row['id']);
            $nome = Filters::in($row['nome']);
            $sel = ($selected == $id) ? 'selected' : '';

            $html .= "<option value='{$id}' {$sel}>{$nome}</option>";
        }

        return $html;
    }

    /**
     * @fn listObjects
     * @note Crea le select degli oggetti
     * @param int $selected
     * @return string
     */
    public function listObjects(int $selected = 0): string
    {
        $html = '';
        $list = $this->getAllObjects('id,nome');

        foreach ($list as $row){
            $id = Filters::int($row['id']);
            $nome = Filters::in($row['nome']);
            $sel = ($selected == $id) ? 'selected' : '';

            $html .= "<option value='{$id}' {$sel}>{$nome}</option>";
        }

        return $html;
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

        if ($this->permissionManageObjects()) {

            $tipo = Filters::int($post['tipo']);
            $nome = Filters::in($post['nome']);
            $descrizione = Filters::in($post['descrizione']);
            $immagine = Filters::in($post['immagine']);
            $indossabile = Filters::checkbox($post['indossabile']);
            $costo = Filters::int($post['costo']);
            $cariche = Filters::int($post['cariche']);
            $creato_da = Filters::int($this->me_id);


            DB::query("INSERT INTO oggetto(tipo, nome, descrizione, immagine, indossabile, costo, cariche,creatore_da) 
                            VALUES('{$tipo}','{$nome}','{$descrizione}','{$immagine}','{$indossabile}','{$costo}','{$cariche}','{$creato_da}')");

            $resp = ['response' => true, 'mex' => 'Oggetto inserito correttamente.'];
        } else {
            $resp = ['response' => false, 'mex' => 'Permesso negato.'];
        }

        return $resp;
    }

    /**
     * @fn editObject
     * @note Modifica oggetto
     * @param array $post
     * @return array
     */
    public function editObject(array $post): array
    {

        if ($this->permissionManageObjects()) {

            $id = Filters::int($post['oggetto']);
            $tipo = Filters::int($post['tipo']);
            $nome = Filters::in($post['nome']);
            $descrizione = Filters::in($post['descrizione']);
            $immagine = Filters::in($post['immagine']);
            $indossabile = Filters::checkbox($post['indossabile']);
            $costo = Filters::int($post['costo']);
            $cariche = Filters::int($post['cariche']);

            DB::query("UPDATE oggetto 
                            SET tipo='{$tipo}',nome='{$nome}',descrizione='{$descrizione}',immagine='{$immagine}',
                                indossabile='{$indossabile}',costo='{$costo}',cariche='{$cariche}'
                            WHERE id='{$id}' LIMIT 1");

            $resp = ['response' => true, 'mex' => 'Oggetto modificato correttamente.'];
        } else {
            $resp = ['response' => false, 'mex' => 'Permesso negato.'];
        }

        return $resp;
    }

    /**
     * @fn deleteObject
     * @note Eliminazione oggetto
     * @param array $post
     * @return array
     */
    public function deleteObject(array $post): array
    {

        if ($this->permissionManageObjects()) {

            $id = Filters::int($post['oggetto']);

            DB::query("DELETE FROM oggetto WHERE id='{$id}'");

            $resp = ['response' => true, 'mex' => 'Oggetto eliminato correttamente.'];
        } else {
            $resp = ['response' => false, 'mex' => 'Permesso negato.'];
        }

        return $resp;
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

        if ($this->permissionManageObjectsType()) {

            $nome = Filters::in($post['nome']);
            $descrizione = Filters::in($post['descrizione']);

            DB::query("INSERT INTO oggetto_tipo( nome, descrizione) 
                            VALUES('{$nome}','{$descrizione}')");

            $resp = ['response' => true, 'mex' => 'Tipo Oggetto inserito correttamente.'];
        } else {
            $resp = ['response' => false, 'mex' => 'Permesso negato.'];
        }

        return $resp;
    }

    /**
     * @fn editObjectType
     * @note Modifica tipologia oggetto
     * @param array $post
     * @return array
     */
    public function editObjectType(array $post): array
    {

        if ($this->permissionManageObjectsType()) {

            $id = Filters::int($post['tipo']);
            $nome = Filters::in($post['nome']);
            $descrizione = Filters::in($post['descrizione']);

            DB::query("UPDATE oggetto_tipo 
                            SET nome='{$nome}',descrizione='{$descrizione}'
                            WHERE id='{$id}' LIMIT 1");

            $resp = ['response' => true, 'mex' => 'Tipo Oggetto modificato correttamente.'];
        } else {
            $resp = ['response' => false, 'mex' => 'Permesso negato.'];
        }

        return $resp;
    }

    /**
     * @fn deleteObjectType
     * @note Eliminazione tipologia oggetto
     * @param array $post
     * @return array
     */
    public function deleteObjectType(array $post): array
    {

        if ($this->permissionManageObjectsType()) {

            $id = Filters::int($post['tipo']);

            DB::query("DELETE FROM oggetto_tipo WHERE id='{$id}'");

            $resp = ['response' => true, 'mex' => 'Tipo Oggetto eliminato correttamente.'];
        } else {
            $resp = ['response' => false, 'mex' => 'Permesso negato.'];
        }

        return $resp;
    }

}