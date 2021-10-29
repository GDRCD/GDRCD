<?php

class Gestione extends BaseClass{

    /*** GENERICO */

    /**
     * @fn inputByType
     * @note Crea un input specifico in base al tipo di dato necessario
     * @param string $name
     * @param mixed $val
     * @param string $type
     * @return string
     */
    private final function inputByType(string $name, $val, string $type): string
    {

        $type = Filters::out($type);
        $name = Filters::out($name);
        $val = Filters::out($val);

        switch ($type){
            case 'bool':
                $checked = ($val == 1) ? 'checked' : '';
                $html = "<input type='checkbox' name='{$name}' {$checked} />";
                break;

            case 'int':
                $html = "<input type='number' name='{$name}' value='{$val}' />";
                break;

            case 'permission':
                $list = Permissions::permissionsList();

                $html = "<select name='{$name}'>";

                foreach ($list as $perm => $perm_name){
                    $selected = ($perm == $val) ? 'selected' : '';

                    $html .= "<option value='{$perm_name}' {$selected}>{$perm_name}</option>";
                }

                $html .= "</select>";

                break;

            default:
            case 'string':
                $html = "<input type='text' name='{$name}' value='{$val}' />";
                break;

        }

        return $html;
    }


    /*** COSTANTI */

    /**
     * @fn constantsPermission
     * @note Permessi per la gestione delle costanti
     * @return bool
     */
    public function constantsPermission(): bool
    {
        return (Permissions::permission('MANAGE_CONSTANTS'));
    }

    /**
     * @fn costantList
     * @note Creazione della sezione della gestione delle costanti
     * @return string
     */
    public function constantList():string{

        $html = '';

        $const_category = DB::query('SELECT section FROM config WHERE editable = 1 GROUP BY section ','result');

        foreach ($const_category as $category) {
            $section = Filters::out($category['section']);

            $html .= "<div class='single_section'>";
            $html .= "<div class='gestione_form_title'>{$section}</div>";
            $html .= "<div class='box_input'>";

            $const_list = DB::query("SELECT const_name,label,val,type,description FROM config WHERE section='{$section}' AND editable=1 ORDER BY label",'result');

            foreach ($const_list as $const){
                $name = Filters::out($const['const_name']);
                $label = Filters::out($const['label']);
                $val = Filters::out($const['val']);
                $type = Filters::out($const['type']);


                $html .= "<div class='single_input w-33'>";
                $html .= "<div class='label'>{$label}</div>";
                $html .= $this->inputByType($name,$val,$type);
                $html .= "</div>";
            }

            $html .= "</div>";
            $html .= "</div>";
        }

        return $html;
    }

    /**
     * @fn updateConstants
     * @note Funzione pubblica/contenitore per l'update delle costanti via POST
     * @param array $post
     * @return string
     */
    public final function updateConstants(array $post): string
    {
        if($this->constantsPermission()) {

            $const_list = DB::query("SELECT * FROM config WHERE editable=1 ORDER BY label", 'result');
            $empty_const = [];


            foreach ($const_list as $const) {
                if (($post[$const['const_name']] == '') && ($const['type'] != 'bool')) {
                    $empty_const[] = $const['const_name'];
                }
            }

            if (empty($empty_const)) {

                foreach ($const_list as $const) {
                    $type = Filters::out($const['type']);
                    $const_name = Filters::out($const['const_name']);
                    $val = Filters::in($post[$const_name]);

                    $save_resp = $this->saveConstant($const_name, $type, $val);

                    if (!$save_resp) {
                        $empty_const[] = $const_name;
                    }


                }

                if (!empty($empty_const)) {
                    $resp = $this->errorConstant($empty_const, 'save');
                } else {
                    $resp = 'Costanti aggiornate correttamente.';
                }


            } else {
                $resp = $this->errorConstant($empty_const, 'empty');
            }

            return $resp;
        }
        else{
            return '';
        }
    }

    /**
     * @fn saveConstant
     * @note Funzione di salvataggio e controllo tipologia del dato salvato
     * @param string $name
     * @param string $type
     * @param mixed $val
     * @return bool|int|mixed|string
     */
    private final function saveConstant(string $name, string $type, $val){

        $name = Filters::in($name);
        $type = Filters::out($type);

        switch ($type){
            case 'bool':
                $val = !empty($val) ? 1 : 0;
                break;

            case 'int':
                if(!is_numeric($val) || is_float($val)){
                    return false;
                }
                else{
                    $val = Filters::int($val);
                }
                break;

            default:
            case 'string':
                $val = Filters::in($val);
                break;
        }

        return DB::query("UPDATE config SET val='{$val}' WHERE const_name='{$name}' LIMIT 1");
    }

    /**
     * @fn errorConstant
     * @note Crea l'errore e la lista delle costanti in errore
     * @param array $consts
     * @param string $type
     * @return string
     */
    private final function errorConstant(array $consts, string $type): string
    {

        switch ($type){
            case 'empty':
                $resp = 'Le costanti devono essere tutte. Costanti mancanti nel form: <br>';
                break;
            case 'save':
                $resp = 'Errore durante l\'update delle costanti: <br>';
                break;
        }

        foreach ($consts as $e){

            $resp .= "- {$e} <br>";
        }

        return $resp;
    }


    /*** PERMESSI */
    /**
     * @fn permissionsListDrag
     * @note Creazione della lista dei permessi esistenti per l'ordinamento della gerarchia
     * @return string
     */
    public function permissionsListDrag():string{

        $html = '';

        $permessi = Permissions::permissionsList();

        foreach ($permessi as $level => $name) {
            $name = Filters::out($name);

            $html .= "<li data-id='{$name}'>{$name}</li>";

        }

        return $html;
    }

    public function orderPermission($post){

        $order = $post['order'];

        foreach ($order as $key => $permission) {
            $level = Filters::int($key);
            $permission = Filters::in($permission);

            DB::query("UPDATE config_permission SET level='{$level}' WHERE permission_name='{$permission}' LIMIT 1");
        }

        return ['response'=>true];
    }
}