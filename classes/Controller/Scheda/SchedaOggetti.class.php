<?php

class SchedaOggetti extends Scheda
{

    /**** FUNCTIONS ****/

    public function isPublic(){
        return Functions::get_constant('SCHEDA_OBJECTS_PUBLIC');
    }

    public function isAccessible(int $id_pg){
        return ($this->isPublic() || $this->permissionViewObjects() || Personaggio::isMyPg($id_pg));
    }

    /*** CONTROLS ***/

    /**
     * @fn isPgObject
     * @note Controlla se l'oggetto (personaggio_oggetto.id) e' di proprieta' del personaggio
     * @param int $obj
     * @param int $pg
     * @return bool
     */
    public function isPgObject(int $obj,int $pg): bool
    {
        $data = $this->getPgObject($obj, 'personaggio_oggetto.personaggio');
        $personaggio = Filters::int($data['personaggio']);
        return ($pg === $personaggio);
    }

    /*** PERMESSI ***/

    /**
     * @fn permissionSchedaObjects
     * @note Controlla che si abbiano i permessi per rimuovere gli oggetti altrui
     * @return bool
     */
    public function permissionRemoveObjects(): bool
    {
        return Permissions::permission('REMOVE_SCHEDA_OBJECTS');
    }

    /**
     * @fn permissionSchedaObjects
     * @note Controlla che si abbiano i permessi per equipaggiare gli oggetti altrui
     * @return bool
     */
    public function permissionEquipObjects(): bool
    {
        return Permissions::permission('EQUIP_SCHEDA_OBJECTS');
    }

    /**
     * @fn permissionViewObjects
     * @note Controlla che si abbiano i permessi per visualizzare gli oggetti altrui
     * @return bool
     */
    public function permissionViewObjects(): bool
    {
        return Permissions::permission('VIEW_SCHEDA_OBJECTS');
    }


    /*** TABLE HELPERS ***/

    /**
     * @fn getPgObjectsByPosition
     * @note Estrae gli oggetti equipaggiati per quella parte del corpo
     * @param int $id_pg
     * @param int $position
     * @param int $limit
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getPgObjectsByPosition(int $id_pg, int $position, int $limit = 1, string $val = '*')
    {

        return DB::query("SELECT {$val}
                                FROM personaggio_oggetto 
                                LEFT JOIN oggetto ON (oggetto.id = personaggio_oggetto.oggetto)
                                WHERE personaggio_oggetto.indossato = 1 
                                  AND oggetto.posizione = '{$position}'
                                  AND personaggio_oggetto.personaggio = '{$id_pg}'
                                LIMIT {$limit}", 'result');

    }

    /**
     * @fn getAllPgObjectsByEquipped
     * @note Estrae tutti gli oggetti di un personaggio in base all'indossato
     * @param int $id_pg
     * @param int $equipped
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllPgObjectsByEquipped(int $id_pg, int $equipped, string $val = '*')
    {
        return DB::query("SELECT {$val}
                                FROM personaggio_oggetto 
                                LEFT JOIN oggetto ON (oggetto.id = personaggio_oggetto.oggetto)
                                WHERE personaggio_oggetto.indossato = '{$equipped}'
                                  AND personaggio_oggetto.personaggio = '{$id_pg}'", 'result');
    }

    /**
     * @fn getAllPgObjectsByEquipped
     * @note Estrae tutti gli oggetti di un personaggio
     * @param int $id_pg
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllPgObjects(int $id_pg, string $val = '*')
    {
        return DB::query("SELECT {$val}
                                FROM personaggio_oggetto 
                                LEFT JOIN oggetto ON (oggetto.id = personaggio_oggetto.oggetto)
                                  AND personaggio_oggetto.personaggio = '{$id_pg}'", 'result');
    }

    /**
     * @fn getPgObject
     * @note Estrae i dati di un oggetto di un personaggio
     * @param int $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getPgObject(int $id, string $val = 'oggetto.*,personaggio_oggetto.*')
    {
        return DB::query("SELECT {$val}
                                FROM personaggio_oggetto 
                                LEFT JOIN oggetto ON (oggetto.id = personaggio_oggetto.oggetto)
                                  WHERE personaggio_oggetto.id = '{$id}' LIMIT 1");
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
        $pg_name = Personaggio::nameFromId($pg);
        $obj_class = Oggetti::getInstance();

        $list = $obj_class->getAllObjectPositions();


        foreach ($list as $position) {

            $position_id = Filters::int($position['id']);
            $position_img = Filters::out($position['immagine']);
            $position_limit = Filters::int($position['numero']);

            $objs = $this->getPgObjectsByPosition($pg, $position_id, $position_limit, 'personaggio_oggetto.id,personaggio_oggetto.oggetto,oggetto.immagine,oggetto.nome');
            $obj_num = DB::rowsNumber($objs);

            $html .= Oggetti::renderObjects($objs, "main.php?page=scheda_equip&pg={$pg_name}&id_pg={$pg}&id_obj=");

            while ($obj_num < $position_limit) {
                $html .= "<div class='single_object'  title='Empty'>";
                $html .= "<div class='img'><img src='/themes/advanced/imgs/body/{$position_img}'></div>";
                $html .= "<div class='name'>Empty</div>";
                $html .= "</div>";
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
        $pg_name = Personaggio::nameFromId($pg);
        $objs = $this->getAllPgObjectsByEquipped($pg, false, 'personaggio_oggetto.id,personaggio_oggetto.oggetto,oggetto.nome,oggetto.immagine');
        return Oggetti::renderObjects($objs, "main.php?page=scheda_equip&pg={$pg_name}&id_pg={$pg}&id_obj=");
    }

    /**
     * @fn renderPgInventory
     * @note Renderizza un singolo oggetto del personaggio
     * @param int $obj
     * @param int $pg
     * @return mixed|void
     */
    public function renderObjectInfo(int $obj,int $pg)
    {

        $html = '';
        $obj_class = Oggetti::getInstance();
        $obj_data = $this->getPgObject($obj,'oggetto.*,personaggio_oggetto.*,personaggio_oggetto.cariche AS cariche_obj');
        $object = Filters::int($obj_data['oggetto']);


        if ($obj_class->existObject($object) && $this->isPgObject($obj, $pg)) {
            return $obj_data;
        }

    }

    /**** FUNCTIONS ****/

    /**
     * @fn equipObj
     * @note Funzione di equipaggiamento e rimozione di un oggetto del personaggio
     * @param array $post
     * @return array
     */
    public function equipObj(array $post):array{

        $id_obj = Filters::int($post['object']);
        $id_pg = Filters::int($post['pg']);
        $obj_class = Oggetti::getInstance();

        if($this->isPgObject($id_obj,$id_pg)){

            $obj_data = $this->getPgObject($id_obj);
            $indossato = Filters::int($obj_data['indossato']);
            $obj_position = Filters::int($obj_data['posizione']);
            $position_data = $obj_class->getObjectPosition($obj_position);

            if(!$indossato) {
                $max_number = Filters::int($position_data['numero']);
                $equipped = $this->getPgObjectsByPosition($id_pg,$obj_position,$max_number);
                $equipped_number = DB::rowsNumber($equipped);

                if($equipped_number >= $max_number){
                    return ['response' =>false,'mex'=>'Numero massimo di oggetti equipaggiati raggiunto.'];
                }
            }

            DB::query("UPDATE personaggio_oggetto SET indossato = !indossato WHERE id='{$id_obj}' AND personaggio='{$id_pg}' LIMIT 1");

            return ['response'=>true,'mex'=>'Operazione effettuata con successo.'];

        }
        else{
            return ['response'=>false,'mex'=>'Permesso negato.'];
        }

    }

    public function removeObj(array $post):array {

        $id_obj = Filters::int($post['object']);
        $id_pg = Filters::int($post['pg']);
        $obj_class = Oggetti::getInstance();

        if($this->isPgObject($id_obj,$id_pg)){
            $obj_class->removeObjectFromPg($id_obj,$id_pg);

            return ['response'=>true,'mex'=>'Oggetto rimosso correttamente.'];
        }else{
            return ['response'=>false,'mex'=>'Permesso negato'];
        }

    }
}