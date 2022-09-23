<?php

/*** TODO - MANCANTI:
 * - Funzione cedi oggetti
 * - Funzione commenta oggetto
 ***/

class SchedaOggetti extends Scheda
{

    /**** CONTROLS ****/

    /**
     * @fn isPublic
     * @note Controlla se la scheda oggetti è pubblica
     * @return bool
     */
    public function isPublic(): bool
    {
        return Functions::get_constant('SCHEDA_OBJECTS_PUBLIC');
    }

    /**
     * @fn isAccessible
     * @note La scheda oggetti è accessibile
     * @param int $id_pg
     * @return bool
     */
    public function isAccessible(int $id_pg): bool
    {
        return ($this->isPublic() || $this->permissionViewObjects($id_pg));
    }

    /*** PERMESSI ***/

    /**
     * @fn permissionSchedaObjects
     * @note Controlla che si abbiano i permessi per rimuovere gli oggetti altrui
     * @param $pg
     * @return bool
     */
    public function permissionRemoveObjects($pg): bool
    {
        return Personaggio::isMyPg($pg) || Permissions::permission('REMOVE_SCHEDA_OBJECTS');
    }

    /**
     * @fn permissionSchedaObjects
     * @note Controlla che si abbiano i permessi per equipaggiare gli oggetti altrui
     * @param $pg
     * @return bool
     */
    public function permissionEquipObjects($pg): bool
    {
        return Personaggio::isMyPg($pg) || Permissions::permission('EQUIP_SCHEDA_OBJECTS');
    }

    /**
     * @fn permissionViewObjects
     * @note Controlla che si abbiano i permessi per visualizzare gli oggetti altrui
     * @param int $id_pg
     * @return bool
     */
    public function permissionViewObjects(int $id_pg): bool
    {
        return Personaggio::isMyPg($id_pg) || Permissions::permission('VIEW_SCHEDA_OBJECTS');
    }

    /*** FUNCTIONS ***/

    /**
     * @fn equipObj
     * @note Funzione di equipaggiamento di un oggetto del personaggio
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function equipObj(array $post): array
    {

        $id_obj = Filters::int($post['object']);
        $obj_class = Oggetti::getInstance();
        $obj_data = PersonaggioOggetti::getPgObject($id_obj);
        $owner = Filters::int($obj_data['personaggio']);

        if ( $this->permissionEquipObjects($owner) ) {

            $indossato = Filters::int($obj_data['indossato']);
            $obj_position = Filters::int($obj_data['posizione']);
            $position_data = $obj_class->getObjectPosition($obj_position);
            $indossato_text = ($indossato) ? 'rimosso' : 'indossato';

            if ( !$indossato ) {
                $max_number = Filters::int($position_data['numero']);
                $equipped = PersonaggioOggetti::getPgObjectsByPosition($owner, $obj_position, $max_number);
                $equipped_number = DB::rowsNumber($equipped);

                if ( $equipped_number >= $max_number ) {
                    return [
                        'response' => false,
                        'swal_title' => 'Errore!',
                        'swal_message' => 'Raggiunto numero massimo per questa parte del corpo.',
                        'swal_type' => 'error',
                    ];
                }
            }

            DB::queryStmt(
                "UPDATE personaggio_oggetto SET indossato = !indossato WHERE id=:id AND personaggio=:pg LIMIT 1",
                [
                    'id' => $id_obj,
                    'pg' => $owner,
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => "Oggetto {$indossato_text} correttamente.",
                'swal_type' => 'success',
                'new_equip' => $this->renderPgEquipment($owner),
                'new_inventory' => $this->renderPgInventory($owner),
            ];

        } else {
            return [
                'response' => false,
                'swal_title' => 'Errore!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error',
            ];
        }

    }

    /**
     * @fn equipObj
     * @note Funzione di rimozione di un oggetto del personaggio
     * @param array $post
     * @return array
     */
    public function removeObj(array $post): array
    {

        $id_obj = Filters::int($post['object']);
        $obj_data = PersonaggioOggetti::getPgObject($id_obj);
        $owner = Filters::int($obj_data['personaggio']);

        if ( $this->permissionRemoveObjects($owner) ) {

            Oggetti::removeObjectFromPg($id_obj, $owner);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => "Oggetto rimosso correttamente.",
                'swal_type' => 'success',
                'new_equip' => $this->renderPgEquipment($owner),
                'new_inventory' => $this->renderPgInventory($owner),
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Errore!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error',
            ];
        }

    }

    /*** RENDERING **/

    /**
     * @fn renderPgEquipment
     * @note Renderizza gli oggetti equipaggiati da un pg
     * @param int $pg
     * @return string
     */
    public function renderPgEquipment(int $pg): string
    {

        $html = '';
        $pg = Filters::int($pg);
        $obj_class = Oggetti::getInstance();

        $list = $obj_class->getAllObjectPositions();

        foreach ( $list as $position ) {

            $position_id = Filters::int($position['id']);
            $position_limit = Filters::int($position['numero']);

            $objs = PersonaggioOggetti::getPgObjectsByPosition($pg, $position_id, $position_limit, 'personaggio_oggetto.id,personaggio_oggetto.oggetto,oggetto.immagine,oggetto.nome');
            $obj_num = DB::rowsNumber($objs);

            $html .= $this->renderObjects($objs);

            while ( $obj_num < $position_limit ) {

                $data = [
                    "nome" => Filters::out($position['nome']),
                    "immagine" => Router::getImgsDir() . Filters::out(Filters::out($position['immagine'])),
                    "link" => '',
                ];

                $html .= Template::getInstance()->startTemplate()->render(
                    'scheda/oggetti/single_obj',
                    $data
                );

                $obj_num++;
            }

        }

        return $html;

    }

    /**
     * @fn renderPgInventory
     * @note Renderizza l'inventario del personaggio
     * @param int $pg
     * @return string
     */
    public function renderPgInventory(int $pg): string
    {
        $pg = Filters::int($pg);
        $objs = PersonaggioOggetti::getAllPgObjectsByEquipped($pg, false, 'personaggio_oggetto.id,personaggio_oggetto.oggetto,oggetto.nome,oggetto.immagine');
        return $this->renderObjects($objs);
    }

    /**
     * @fn renderPgInventory
     * @note Renderizza un singolo oggetto del personaggio
     * @param array $post
     * @return array
     */
    public function renderObjectInfo(array $post): array
    {

        $obj = Filters::int($post['obj']);

        $obj_class = Oggetti::getInstance();
        $obj_data = PersonaggioOggetti::getPgObject($obj, 'oggetto.*,personaggio_oggetto.*,personaggio_oggetto.cariche AS cariche_obj');
        $object = Filters::int($obj_data['oggetto']);
        $pg = Filters::int($obj_data['personaggio']);
        $type_data = $obj_class->getObjectType(Filters::int($obj_data['tipo']));

        if ( $obj_class->existObject($object) && $this->permissionViewObjects($pg) ) {

            $data = [
                "id" => $obj,
                "id_obj" => $object,
                "immagine" => Filters::out($obj_data['immagine']),
                "nome" => Filters::out($obj_data['nome']),
                "descrizione" => Filters::out($obj_data['descrizione']),
                "cariche" => Filters::int($obj_data['cariche_obj']),
                "tipo" => Filters::out($type_data['nome']),
                "indossato" => (Filters::bool($obj_data['indossato'])) ? 'Rimuovi' : 'Equipaggia',
                "equip_permission" => $this->permissionEquipObjects($pg),
                "remove_permission" => $this->permissionRemoveObjects($pg),
            ];

            return
                ['template' =>
                    Template::getInstance()->startTemplate()->render(
                        'scheda/oggetti/obj_info',
                        $data
                    ),
                ];

        }

        return [];

    }

    /**
     * @fn renderObjects
     * @note Renderizzazione oggetti da una lista di oggetti
     * @param object $objs
     * @return string
     */
    public static function renderObjects(object $objs): string
    {

        $html = '';

        foreach ( $objs as $obj ) {

            $data = [
                "id" => Filters::int($obj['id']),
                "nome" => Filters::out($obj['nome']),
                "immagine" => Router::getImgsDir() . Filters::out($obj['immagine']),
            ];

            $html .= Template::getInstance()->startTemplate()->render(
                'scheda/oggetti/single_obj',
                $data
            );
        }

        return $html;
    }
}