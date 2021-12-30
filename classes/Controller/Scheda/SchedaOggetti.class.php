<?php

/*** TODO - MANCANTI:
- Funzione cedi oggetti
- Funzione commenta oggetto
***/

class SchedaOggetti extends Scheda
{

    /**** CONTROLS ****/

    /**
     * @fn isPublic
     * @note Controlla se la scheda oggetti e' pubblica
     * @return mixed
     */
    public function isPublic(){
        return Functions::get_constant('SCHEDA_OBJECTS_PUBLIC');
    }

    /**
     * @fn isAccesible
     * @note La scheda oggetti e' accessibile
     * @param int $id_pg
     * @return bool
     */
    public function isAccessible(int $id_pg){
        return ($this->isPublic() || $this->permissionViewObjects() || Personaggio::isMyPg($id_pg));
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

    /*** FUNCTIONS ***/

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

        if(PersonaggioOggetti::isPgObject($id_obj,$id_pg)){

            $obj_data = PersonaggioOggetti::getPgObject($id_obj);
            $indossato = Filters::int($obj_data['indossato']);
            $obj_position = Filters::int($obj_data['posizione']);
            $position_data = $obj_class->getObjectPosition($obj_position);

            if(!$indossato) {
                $max_number = Filters::int($position_data['numero']);
                $equipped = PersonaggioOggetti::getPgObjectsByPosition($id_pg,$obj_position,$max_number);
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

        if(PersonaggioOggetti::isPgObject($id_obj,$id_pg)){
            Oggetti::removeObjectFromPg($id_obj,$id_pg);

            return ['response'=>true,'mex'=>'Oggetto rimosso correttamente.'];
        }else{
            return ['response'=>false,'mex'=>'Permesso negato'];
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
        $pg_name = Personaggio::nameFromId($pg);
        $obj_class = Oggetti::getInstance();

        $list = $obj_class->getAllObjectPositions();


        foreach ($list as $position) {

            $position_id = Filters::int($position['id']);
            $position_img = Filters::out($position['immagine']);
            $position_limit = Filters::int($position['numero']);

            $objs = PersonaggioOggetti::getPgObjectsByPosition($pg, $position_id, $position_limit, 'personaggio_oggetto.id,personaggio_oggetto.oggetto,oggetto.immagine,oggetto.nome');
            $obj_num = DB::rowsNumber($objs);

            $html .= $this->renderObjects($objs, "main.php?page=scheda_oggetti&pg={$pg_name}&id_pg={$pg}&id_obj=");

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
        $objs = PersonaggioOggetti::getAllPgObjectsByEquipped($pg, false, 'personaggio_oggetto.id,personaggio_oggetto.oggetto,oggetto.nome,oggetto.immagine');
        return $this->renderObjects($objs, "main.php?page=scheda_oggetti&pg={$pg_name}&id_pg={$pg}&id_obj=");
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
        $obj_data = PersonaggioOggetti::getPgObject($obj,'oggetto.*,personaggio_oggetto.*,personaggio_oggetto.cariche AS cariche_obj');
        $object = Filters::int($obj_data['oggetto']);


        if ($obj_class->existObject($object) && PersonaggioOggetti::isPgObject($obj, $pg)) {
            return $obj_data;
        }

    }

    /**
     * @fn renderObjects
     * @note Renderizzazione oggetti da una lista di oggetti
     * @param object $objs
     * @return string
     */
    public static function renderObjects(object $objs, $link = ''): string
    {
        $html = '';
        $obj_class = Oggetti::getInstance();

        foreach ($objs as $obj) {
            $id = Filters::int($obj['id']);
            $obj_id = Filters::int($obj['oggetto']);
            $img = '/themes/advanced/imgs/items/' . Filters::out($obj['immagine']);
            $nome = Filters::out($obj['nome']);

            if ($obj_class->existObject($obj_id)) {
                $html .= "<div class='single_object' title='{$nome}'>";
                $html .= "<div class='img'><img src='{$img}'></div>";
                if (empty($link)) {
                    $html .= "<div class='name'>{$nome}</div>";
                } else {
                    $html .= "<div class='name'><a href='{$link}{$id}'>{$nome}</a></div>";
                }
                $html .= "</div>";
            }
        }

        return $html;
    }
}