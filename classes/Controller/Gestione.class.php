<?php

class Gestione extends BaseClass{


    public function costantList(){

        $html = '';

        $const_category = DB::query('SELECT section FROM config WHERE editable = 1 GROUP BY section ','result');

        foreach ($const_category as $category) {
            $section = Filters::out($category['section']);


            $html .= "<div class='gestione_form_title'>{$section}</div>";

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
        }

        return $html;
    }

    public function inputByType($name,$val,$type){

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

            default:
            case 'string':
                $html = "<input type='text' name='{$name}' value='{$val}' />";
                break;

        }

        return $html;
    }

    public function updateConstants($post){


        $const_list = DB::query("SELECT * FROM config WHERE editable=1 ORDER BY label",'result');
        $empty_const = [];


        foreach ($const_list as $const){
            if(($post[$const['const_name']] == '') && ($const['type'] != 'bool')){
                $empty_const[] = $const['const_name'];
            }
        }

        if(empty($empty_const)){

            foreach ($const_list as $const) {
                $type = Filters::out($const['type']);
                $const_name = Filters::out($const['const_name']);
                $val = Filters::in($post[$const_name]);

                $save_resp = $this->saveConstant($const_name,$type,$val);

                if(!$save_resp){
                    $empty_const[] = $const_name;
                }


            }

            if(!empty($empty_const)){
                $resp = $this->errorConstant($empty_const,'save');
            }
            else{
                $resp = 'Costanti aggiornate correttamente.';
            }


        }
        else{
            $resp = $this->errorConstant($empty_const,'empty');
        }

        return $resp;

    }

    private function errorConstant($consts,$type){

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

    private function saveConstant($name,$type,$val){

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


}