<?php

class GruppiOggetto extends Gruppi
{

    private
        $active_storage;

    protected function __construct()
    {
        parent::__construct();
        $this->active_storage = Functions::get_constant('GROUPS_STORAGE');
    }


    /*** CONFIG ***/

    public function activeStorage()
    {
        return $this->active_storage;
    }

    /** PERMESSI */

    /**
     * @fn permissionManageStorages
     * @note Controlla permessi sulla gestione dei depositi
     * @return bool
     */
    public function permissionManageStorages(): bool
    {
        return Permissions::permission('MANAGE_GROUPS_STORAGES');
    }

    /**
     * @fn permissionViewStorage
     * @note Controlla permessi sulla visualizzazione dei depositi
     * @param int $group
     * @return bool
     */
    public function permissionViewStorage(int $group): bool
    {
        return ($this->activeStorage() && (GruppiRuoli::getInstance()->haveGroupRole($group) || $this->permissionManageStorages()));
    }

    /**
     * @fn permissionGetObjectFromStorage
     * @note Controlla permessi sul ritiro degli oggetti dei depositi
     * @param int $group
     * @return bool
     */
    public function permissionGetObjectFromStorage(int $group): bool
    {
        return ($this->activeStorage() && (GruppiRuoli::getInstance()->haveGroupPower($group) || $this->permissionManageStorages()));
    }


    /** TABLE HELPERS */

    /**
     * @fn existGroupObject
     * @note Controlla se un oggetto esiste nel deposito gruppo
     * @param int $id
     * @return bool
     */
    public function existGroupObject(int $id): bool
    {
        $data = DB::query("SELECT * FROM gruppi_oggetto WHERE id='{$id}' LIMIT 1");
        return (DB::rowsNumber($data) > 0);
    }

    /**
     * @fn getGroupObject
     * @note Estrae un oggetto preciso di un gruppo
     * @param int $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getGroupObject(int $id, string $val = 'gruppi_oggetto.*,oggetto.nome,oggetto.immagine')
    {
        return DB::query("SELECT {$val} FROM gruppi_oggetto LEFT JOIN oggetto ON oggetto.id = gruppi_oggetto.oggetto WHERE gruppi_oggetto.id='{$id}' LIMIT 1");
    }

    /**
     * @fn getAllObjects
     * @note Estrae tutti gli oggetti
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllObjects(string $val = '*')
    {
        return DB::query("SELECT {$val} FROM gruppi_oggetto WHERE 1", 'result');
    }

    /**
     * @fn getAllObjectsByGroup
     * @note Estrae tutti gli oggetti di un gruppo specifico
     * @param int $group
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllObjectsDataByGroup(int $group, string $val = 'gruppi_oggetto.id,oggetto.immagine,oggetto.nome')
    {
        return DB::query("SELECT {$val} FROM gruppi_oggetto LEFT JOIN oggetto ON oggetto.id = gruppi_oggetto.oggetto WHERE gruppo='{$group}'", 'result');
    }

    /** LISTS */

    /**
     * @fn listFounds
     * @note Genera gli option per i fondi
     * @return string
     */
    public function listFounds(): string
    {
        $founds = $this->getAllObjects();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $founds);
    }

    /**
     * @fn listFounds
     * @note Genera gli option per i fondi
     * @param int $group
     * @return string
     */
    public function listFoundsByGroup(int $group): string
    {
        $founds = $this->getAllObjectsDataByGroup($group);
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $founds);
    }


    /*** RENDER ***/

    /**
     * @fn renderStorageObject
     * @note Ordina i dati per il render degli oggetti
     * @param int $group
     * @return array[]
     */
    public function renderStorageObject(int $group): array
    {

        $objects = $this->getAllObjectsDataByGroup($group);
        $compiled_objects = [];

        foreach ($objects as $object) {

            $compiled_objects[] = [
                "id" => Filters::out($object['id']),
                "nome" => Filters::out($object['nome']),
                'immagine' => Router::getThemeDir() . 'imgs/items/' . Filters::out($object['immagine']),
            ];
        }


        return ['body_rows' => $compiled_objects];
    }


    /**
     * @fn objectListRender
     * @note Renderizza la lista degli oggetti nel magazzino
     * @param int $group
     * @return string
     */
    public function objectListRender(int $group): string
    {
        return Template::getInstance()->startTemplate()->render(
            'oggetti/storage-objects',
            $this->renderStorageObject($group)
        );
    }

    public function renderAjaxSingleObjectData($data)
    {
        $id = Filters::int($data['id']);
        $object = $this->getGroupObject($id);
        $object_data = [
            "id" => Filters::out($object['id']),
            "nome" => Filters::out($object['nome']),
            'immagine' => Router::getThemeDir() . 'imgs/items/' . Filters::out($object['immagine']),
            'get_permission' => $this->permissionGetObjectFromStorage($id),
        ];

        return [
            "template" => Template::getInstance()->startTemplate()->render(
                'oggetti/storage-single-object',
                $object_data
            )
        ];
    }


    /*** FUNCTIONS ***/

    /**
     * @fn retireObjectFromStorage
     * @note Ritiro oggetti dal magazzino
     * @param array $post
     * @return array
     */
    public function retireObjectFromStorage(array $post): array
    {

        $id = Filters::int($post['id']);

        $gruppi_oggetti = $this->getGroupObject($id);
        $group = Filters::int($gruppi_oggetti['gruppo']);

        if ($this->permissionGetObjectFromStorage($group)) {


            if ($this->existGroupObject($id)) {
                $oggetto = Filters::int($gruppi_oggetti['oggetto']);
                $cariche = Filters::int($gruppi_oggetti['cariche']);
                $commento = Filters::in($gruppi_oggetti['commento']);

                DB::query("INSERT INTO personaggio_oggetto(`personaggio`,`oggetto`,`cariche`,`commento`) 
                            VALUES('{$this->me_id}','{$oggetto}','{$cariche}','{$commento}')");


                DB::query("DELETE FROM gruppi_oggetto WHERE id='{$id}'");

                return [
                    'response' => true,
                    'swal_title' => 'Operazione riuscita!',
                    'swal_message' => 'Oggetto ritirato con successo.',
                    'swal_type' => 'success',
                    'new_view' => $this->objectListRender($group)
                ];
            } else {
                return [
                    'response' => false,
                    'swal_title' => 'Errore!',
                    'swal_message' => 'Oggetto non presente nel magazzino.',
                    'swal_type' => 'error'
                ];
            }
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error'
            ];
        }

    }

}