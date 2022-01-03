<?php

/**
 * @class AbilitaExtra
 * @note Classe per la gestione centralizzata degli extra delle abilita'
 * @required PHP 7.1+
 */
class AbilitaExtra extends Abilita
{

    /*** TABLE HELPER */

    /**
     * @fn getAbilitaExtra
     * @note Ottiene gli extra di un'abilita'
     * @param int $id
     * @param int $grado
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAbilitaExtra(int $id, int $grado, string $val = '*'){
        return DB::query("SELECT {$val} FROM abilita_extra WHERE abilita = '{$id}' AND grado ='{$grado}' LIMIT 1");
    }

    /*** PERMISSIONS ***/

    /**
     * @fn permissionManageAbiExtra
     * @note Controlla se si hanno i permessi per gestire gli extra delle abilita
     * @return bool
     */
    public function permissionManageAbiExtra(): bool
    {
        return Permissions::permission('MANAGE_ABILITY_EXTRA');
    }

    /*** AJAX ***/

    /**
     * @fn DatiAbiExtra
     * @note Estrazione dinamica dati di una riga nella tabella abilita_extra
     * @param array $post
     * @return array
     */
    public function ajaxExtraData(array $post): array
    {

        if ($_SESSION['permessi'] >= GAMEMASTER) {
            $abi = Filters::int($post['abilita']);
            $grado = Filters::int($post['grado']);

            $data = DB::query("SELECT * FROM abilita_extra WHERE abilita='{$abi}' AND grado='{$grado}' LIMIT 1");

            if (!empty($data['abilita'])) {
                $descr = Filters::in($data['descrizione']);
                $costo = Filters::int($data['costo']);

                return ['response' => true, 'Descr' => $descr, 'Costo' => $costo];
            } else {
                return ['response' => false];
            }
        } else {
            return ['response' => false];
        }

    }

    /***** GESTIONE *****/

    /**
     * @fn NewAbiExtra
     * @note Aggiunta di una riga nella tabella abilita_extra
     * @param array $post
     * @return bool
     */
    public function NewAbiExtra(array $post): bool
    {

        if ($this->permissionManageAbiExtra()) {
            $abi = Filters::int($post['abilita']);
            $grado = Filters::int($post['grado']);
            $descr = Filters::in($post['descr']);
            $costo = Filters::int($post['costo']);

            $contr = DB::query("SELECT count(id) as TOT FROM abilita_extra WHERE abilita='{$abi}' AND grado='{$grado}' LIMIT 1");

            if ($contr['TOT'] == 0) {
                DB::query("INSERT INTO abilita_extra(abilita,grado,descrizione,costo) VALUES('{$abi}','{$grado}','{$descr}','{$costo}')");
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

        if ($this->permissionManageAbiExtra()) {
            $abi = Filters::int($post['abilita']);
            $grado = Filters::int($post['grado']);
            $descr = Filters::in($post['descr']);
            $costo = Filters::int($post['costo']);

            DB::query("UPDATE abilita_extra SET abilita='{$abi}',grado='{$grado}',descrizione='{$descr}',costo='{$costo}' WHERE abilita='{$abi}' AND grado='{$grado}' LIMIT 1");

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

        if ($this->permissionManageAbiExtra()) {
            $abi = Filters::int($post['abilita']);
            $grado = Filters::int($post['grado']);

            DB::query("DELETE FROM abilita_extra WHERE abilita='{$abi}' AND grado='{$grado}' LIMIT 1");

            return true;
        } else {
            return false;
        }
    }

}