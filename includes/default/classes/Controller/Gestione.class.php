<?php

class Gestione extends BaseClass
{

    /*** GENERICO */

    /**
     * @fn inputByType
     * @note Crea un input specifico in base al tipo di dato necessario
     * @param string $name
     * @param mixed $val
     * @param string $type
     * @param string $options
     * @return string
     * @throws Throwable
     */
    public function inputByType(string $name, $val, string $type, string $options = ''): string
    {

        $type = Filters::out($type);
        $name = Filters::out($name);
        $val = Filters::out($val);
        $options = Filters::out($options);

        switch ( $type ) {
            case 'bool':
                $checked = ($val == 1) ? 'checked' : '';
                $html = "<input type='checkbox' name='{$name}' {$checked} />";
                break;

            case 'int':
                $html = "<input type='number' name='{$name}' value='{$val}' />";
                break;

            case 'select':
                $options_list = $this->getOptions($options);

                $html = "<select name='{$name}'>";
                $html .= Template::getInstance()->startTemplate()->renderSelect('value', 'label', $val, $options_list);
                $html .= "</select>";
                break;

            default:
            case 'string':
                $html = "<input type='text' name='{$name}' value='{$val}' />";
                break;

        }

        return $html;
    }

    /*** QUERY HELPER ****
     * @throws Throwable
     */

    public function getOptions($section): DBQueryInterface
    {
        return DB::queryStmt("SELECT * FROM config_options WHERE `section`=:section", [':section' => $section]);
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
    public function constantList(): string
    {

        $html = '';

        $const_category = DB::query('SELECT section FROM config WHERE editable = 1 GROUP BY section ', 'result');

        foreach ( $const_category as $category ) {
            $section = Filters::out($category['section']);

            $html .= "<div class='single_section'>";
            $html .= "<div class='form_title'>{$section}</div>";
            $html .= "<div class='box_input'>";

            $const_list = DB::query("SELECT const_name,label,val,type,options,description FROM config WHERE section='{$section}' AND editable=1 ORDER BY label", 'result');

            foreach ( $const_list as $const ) {
                $name = Filters::out($const['const_name']);
                $label = Filters::out($const['label']);
                $val = Filters::out($const['val']);
                $type = Filters::out($const['type']);
                $options = Filters::out($const['options']);

                $html .= "<div class='single_input w-33'>";
                $html .= "<div class='label'>{$label}</div>";
                $html .= $this->inputByType($name, $val, $type, $options);
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
     * @return array
     */
    public final function updateConstants(array $post): array
    {
        if ( $this->constantsPermission() ) {

            $const_list = DB::query("SELECT * FROM config WHERE editable=1 ORDER BY label", 'result');
            $empty_const = [];

            foreach ( $const_list as $const ) {
                if ( ($post[$const['const_name']] == '') && ($const['type'] != 'bool') ) {
                    $empty_const[] = $const['const_name'];
                }
            }

            if ( empty($empty_const) ) {

                foreach ( $const_list as $const ) {
                    $type = Filters::out($const['type']);
                    $const_name = Filters::out($const['const_name']);
                    $val = Filters::in($post[$const_name]);

                    $save_resp = $this->saveConstant($const_name, $type, $val);

                    if ( !$save_resp ) {
                        $empty_const[] = $const_name;
                    }

                }

                if ( !empty($empty_const) ) {
                    return $this->errorConstant($empty_const, 'save');
                } else {
                    return [
                        'response' => true,
                        'swal_title' => 'Operazione riuscita!',
                        'swal_message' => 'Costanti salvate con successo.',
                        'swal_type' => 'success',
                    ];
                }

            } else {
                return $this->errorConstant($empty_const, 'empty');
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

    /**
     * @fn saveConstant
     * @note Funzione di salvataggio e controllo tipologia del dato salvato
     * @param string $name
     * @param string $type
     * @param mixed $val
     * @return bool|int|mixed|string
     */
    private function saveConstant(string $name, string $type, $val)
    {

        $name = Filters::in($name);
        $type = Filters::out($type);

        switch ( $type ) {
            case 'bool':
                $val = !empty($val) ? 1 : 0;
                break;

            case 'int':
                if ( !is_numeric($val) || is_float($val) ) {
                    return false;
                } else {
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
     * @return array
     */
    private function errorConstant(array $consts, string $type): array
    {

        switch ( $type ) {
            case 'empty':
                $resp = 'Le costanti devono essere tutte. Costanti mancanti nel form: ';
                break;
            case 'save':
                $resp = 'Errore durante l\'update delle costanti: ';
                break;
        }

        foreach ( $consts as $e ) {

            $resp .= " {$e},";
        }

        return [
            'response' => false,
            'swal_title' => 'Operazione fallita!',
            'swal_message' => $resp,
            'swal_type' => 'error',
        ];
    }
}