<?php

class GruppiOggetto extends Gruppi
{

    private bool
        $active_storage;

    protected function __construct()
    {
        parent::__construct();
        $this->active_storage = Functions::get_constant('GROUPS_STORAGE');
    }

    /*** CONFIG ***/

    /**
     * @fn activeStorage
     * @note Ritorna se il sistema di gestione magazzino dei gruppi è attivo
     * @return bool
     */
    public function activeStorage(): bool
    {
        return $this->active_storage;
    }

    /**** PERMESSI ****/

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
     * @throws Throwable
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
     * @throws Throwable
     */
    public function permissionGetObjectFromStorage(int $group): bool
    {
        return ($this->activeStorage() && (GruppiRuoli::getInstance()->haveGroupPower($group) || $this->permissionManageStorages()));
    }


    /**** TABLE HELPERS ****/

    /**
     * @fn existGroupObject
     * @note Controlla se un oggetto esiste nel deposito gruppo
     * @param int $id
     * @return bool
     * @throws Throwable
     */
    public function existGroupObject(int $id): bool
    {
        $data = DB::queryStmt("SELECT * FROM gruppi_oggetto WHERE id=:id LIMIT 1", ['id' => $id]);
        return (DB::rowsNumber($data) > 0);
    }

    /**
     * @fn getGroupObject
     * @note Estrae un oggetto preciso di un gruppo
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getGroupObject(int $id, string $val = 'gruppi_oggetto.*,oggetto.nome,oggetto.immagine'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM gruppi_oggetto LEFT JOIN oggetto ON gruppi_oggetto.oggetto=oggetto.id WHERE gruppi_oggetto.id=:id LIMIT 1", ['id' => $id]);
    }

    /**
     * @fn getAllObjects
     * @note Estrae tutti gli oggetti
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllObjects(string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM gruppi_oggetto WHERE 1",[]);
    }

    /**
     * @fn getAllObjectsByGroup
     * @note Estrae tutti gli oggetti di un gruppo specifico
     * @param int $group
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllObjectsDataByGroup(int $group, string $val = 'gruppi_oggetto.id,oggetto.immagine,oggetto.nome'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM gruppi_oggetto LEFT JOIN oggetto ON gruppi_oggetto.oggetto=oggetto.id WHERE gruppi_oggetto.gruppo=:group", ['group' => $group]);
    }

    /**** LISTS ****/

    /**
     * @fn listFounds
     * @note Genera gli option per i fondi
     * @return string
     * @throws Throwable
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
     * @throws Throwable
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
     * @throws Throwable
     */
    public function renderStorageObject(int $group): array
    {

        $objects = $this->getAllObjectsDataByGroup($group);
        $compiled_objects = [];

        foreach ( $objects as $object ) {

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
     * @throws Throwable
     */
    public function objectListRender(int $group): string
    {
        return Template::getInstance()->startTemplate()->render(
            'oggetti/storage-objects',
            $this->renderStorageObject($group)
        );
    }

    /**
     * @fn renderAjaxSingleObjectData
     * @note Render in ajax dei dati del singolo oggetto
     * @param array $data
     * @return array
     * @throws Throwable
     */
    public function renderAjaxSingleObjectData(array $data): array
    {
        $id = Filters::int($data['id']);
        $object = $this->getGroupObject($id);
        $object_data = [
            "id" => Filters::out($object['id']),
            "nome" => Filters::out($object['nome']),
            'immagine' => Router::getImgsDir() . 'items/' . Filters::out($object['immagine']),
            'get_permission' => $this->permissionGetObjectFromStorage($id),
        ];

        return [
            "template" => Template::getInstance()->startTemplate()->render(
                'oggetti/storage-single-object',
                $object_data
            ),
        ];
    }


    /*** FUNCTIONS ***/

    /**
     * @fn retireObjectFromStorage
     * @note Ritiro oggetti dal magazzino
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function retireObjectFromStorage(array $post): array
    {

        $id = Filters::int($post['id']);

        $gruppi_oggetti = $this->getGroupObject($id);
        $group = Filters::int($gruppi_oggetti['gruppo']);

        if ( $this->permissionGetObjectFromStorage($group) ) {

            if ( $this->existGroupObject($id) ) {
                $oggetto = Filters::int($gruppi_oggetti['oggetto']);
                $cariche = Filters::int($gruppi_oggetti['cariche']);
                $commento = Filters::in($gruppi_oggetti['commento']);

                DB::queryStmt("INSERT INTO personaggio_oggetto(`personaggio`,`oggetto`,`cariche`,`commento`) VALUES(:personaggio,:oggetto,:cariche,:commento)", [
                    'personaggio' => $this->me_id,
                    'oggetto' => $oggetto,
                    'cariche' => $cariche,
                    'commento' => $commento,
                ]);

                DB::queryStmt("DELETE FROM gruppi_oggetto WHERE id=:id", ['id' => $id]);

                return [
                    'response' => true,
                    'swal_title' => 'Operazione riuscita!',
                    'swal_message' => 'Oggetto ritirato con successo.',
                    'swal_type' => 'success',
                    'new_view' => $this->objectListRender($group),
                ];
            } else {
                return [
                    'response' => false,
                    'swal_title' => 'Errore!',
                    'swal_message' => 'Oggetto non presente nel magazzino.',
                    'swal_type' => 'error',
                ];
            }
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