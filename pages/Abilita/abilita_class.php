<?php

/**
 * @class Abilita
 * @note Classe per la gestione centralizzata delle abilita
 * @required PHP 7.1+
 */
class Abilita
{

    /***** GENERICO *****/

    /**
     * @fn ListaAbilita
     * @note Ritorna una serie di option per una select contenente la lista abilita'
     * @return string
     */
    public function ListaAbilita()
    {
        $html = '';
        $news = gdrcd_query("SELECT * FROM abilita WHERE 1 ORDER BY nome", 'result');

        foreach ($news as $new) {
            $nome = gdrcd_filter('out', $new['nome']);
            $id = gdrcd_filter('num', $new['id_abilita']);
            $html .= "<option value='{$id}'>{$nome}</option>";
        }

        return $html;
    }


    /***** GESTIONE ABILITA EXTRA *****/

    /**
     * @fn AbiExtraManagePermission
     * @note Permessi per la gestione della tabella abilita_extra
     * @return bool
     */
    function AbiExtraManagePermission(): bool
    {
        return ($_SESSION['permessi'] >= SUPERUSER);
    }

    /**
     * @fn DatiAbiExtra
     * @note Estrazione dinamica dati di una riga nella tabella abilita_extra
     * @param array $post
     * @return array
     */
    public function DatiAbiExtra(array $post): array
    {

        if ($_SESSION['permessi'] >= GAMEMASTER) {
            $abi = gdrcd_filter('num',$post['abi']);
            $grado = gdrcd_filter('num',$post['grado']);

            $data = gdrcd_query("SELECT * FROM abilita_extra WHERE abilita='{$abi}' AND grado='{$grado}' LIMIT 1");

            if(!empty($data['abilita'])) {
                $descr = gdrcd_filter('in', $data['descrizione']);
                $costo = gdrcd_filter('num', $data['costo']);

                return ['response' => true, 'Descr' => $descr, 'Costo' => $costo];
            }
            else{
                return ['response'=>false];
            }
        }
        else{
            return ['response'=>false];
        }

    }

    /**
     * @fn NewAbiExtra
     * @note Aggiunta di una riga nella tabella abilita_extra
     * @param array $post
     * @return bool
     */
    public function NewAbiExtra(array $post):bool
    {

        if ($this->AbiExtraManagePermission()) {

            $abi = gdrcd_filter('num', $post['abi']);
            $grado = gdrcd_filter('num', $post['grado']);
            $descr = gdrcd_filter('in', $post['descr']);
            $costo = gdrcd_filter('num', $post['costo']);

            $contr = gdrcd_query("SELECT count(id) as TOT FROM abilita_extra WHERE abilita='{$abi}' AND grado='{$grado}' LIMIT 1");

            if($contr['TOT'] == 0) {
                gdrcd_query("INSERT INTO abilita_extra(abilita,grado,descrizione,costo) VALUES('{$abi}','{$grado}','{$descr}','{$costo}')");
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * @fn ModAbiExtra
     * @note Modifica di una riga nella tabella abilita_extra
     * @param array $post
     * @return bool
     */
    public function ModAbiExtra(array $post): bool
    {

        if ($this->AbiExtraManagePermission()) {

            $abi = gdrcd_filter('num', $post['abi']);
            $grado = gdrcd_filter('num', $post['grado']);
            $descr = gdrcd_filter('in', $post['descr']);
            $costo = gdrcd_filter('num', $post['costo']);

            gdrcd_query("UPDATE abilita_extra SET abilita='{$abi}',grado='{$grado}',descrizione='{$descr}',costo='{$costo}' WHERE abilita='{$abi}' AND grado='{$grado}' LIMIT 1");

            return true;
        } else {
            return false;
        }
    }

    /**
     * @fn DelAbiExtra
     * @note Eliminazione di una riga nella tabella abilita_extra
     * @param array $post
     * @return bool
     */
    public function DelAbiExtra(array $post): bool
    {

        if ($this->AbiExtraManagePermission()) {

            $abi = gdrcd_filter('num', $post['abi']);
            $grado = gdrcd_filter('num', $post['grado']);

            gdrcd_query("DELETE FROM abilita_extra WHERE abilita='{$abi}' AND grado='{$grado}' LIMIT 1");

            return true;
        }
        else{
            return false;
        }
    }

}