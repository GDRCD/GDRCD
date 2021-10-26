<?php

/**
 * @class Functions
 * @note Classe contenitore delle funzioni principali degli helper
 */
class Functions{

    private static $_instance;

    private function __construct()
    {
    }

    /**
     * @fn getInstance
     * @note Self Instance for static recall (BaseClass::__construct();)
     * @return mixed
     */
    public static function getInstance()
    {

        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * @fn gdrcd_filter
     * @note Funzione per il filtraggio dei dati
     * @param mixed $type
     * @param mixed $val
     * @return mixed
     */
    public function gdrcd_filter($type,$val){

        switch (strtolower($type)) {
            case 'in':
            case 'get':
                $val = addslashes(str_replace('\\', '', $val));
                break;

            case 'num':
            case 'int':
                $val = (int)$val;
                break;

            case 'out':
                $val = html_entity_decode($val, ENT_HTML5, 'utf-8');
                break;

            case 'addslashes':
                $val = addslashes($val);
                break;

            case 'email':
                $val = (preg_match("#^[a-z0-9._-]+@[a-z0-9._-]+\.[a-z]{2,4}$#is", $val)) ? $val : false;
                break;

            case 'includes':
                $val = (preg_match("#[^:]#is")) ? htmlentities($val, ENT_QUOTES) : false;
                break;

            case 'url':
                $val = urlencode($val);
                break;

            case 'fullurl':
                $val = filter_var(str_replace(' ', '%20', $val), FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED);
                break;
        }

        return $val;
    }

    /**
     * @fn get_constants
     * @note Funzione di estrazione delle costanti dal db
     * @param string $name
     * @return mixed
     */
    public function get_constant(string $name){

        $name = gdrcd_filter('in',$name);

        $search = gdrcd_query("SELECT const_name,val FROM config WHERE const_name='{$name}'",'result');
        $num = gdrcd_query($search,'num_rows');

        if($num == 0){
            die("Costante {$name} inesistente nella tabella config.");
        }
        else if($num > 1){
            die("Esistono più costanti con il nome '{$name}'. Correggere e riprovare.");
        }
        else{
            $row = gdrcd_query($search,'fetch');
            return gdrcd_filter('out',$row['val']);
        }
    }

}