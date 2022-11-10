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
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAbilitaExtra(int $id, int $grado, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt(
            "SELECT {$val} FROM abilita_extra WHERE abilita = :id AND grado =:grado LIMIT 1",
            [ 'id' => $id, 'grado' => $grado]
        );
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
     * @throws Throwable
     */
    public function ajaxExtraData(array $post): array
    {

        if ( $this->permissionManageAbiExtra() ) {
            $abi = Filters::int($post['abilita']);
            $grado = Filters::int($post['grado']);

            $data = DB::queryStmt("SELECT * FROM abilita_extra WHERE abilita=:abi AND grado=:grado LIMIT 1", [
                'abi' => $abi,
                'grado' => $grado,
            ]);

            if ( !empty($data['abilita']) ) {
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
     * @return array
     * @throws Throwable
     */
    public function NewAbiExtra(array $post): array
    {

        if ( $this->permissionManageAbiExtra() ) {
            $abi = Filters::int($post['abilita']);
            $grado = Filters::int($post['grado']);
            $descr = Filters::in($post['descr']);
            $costo = Filters::int($post['costo']);


            $contr = DB::queryStmt("SELECT count(id) as TOT FROM abilita_extra WHERE abilita=:abi AND grado=:grado LIMIT 1", [
                'abi' => $abi,
                'grado' => $grado,
            ]);

            if ( $contr['TOT'] == 0 ) {
                DB::queryStmt("INSERT INTO abilita_extra (abilita, grado, descrizione, costo) VALUES (:abi, :grado, :descr, :costo)", [
                    'abi' => $abi,
                    'grado' => $grado,
                    'descr' => $descr,
                    'costo' => $costo,
                ]);
            }

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Dati extra abilità creati.',
                'swal_type' => 'success',
            ];
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
     * @fn ModAbiExtra
     * @note Modifica di una riga nella tabella abilita_extra
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function ModAbiExtra(array $post): array
    {

        if ( $this->permissionManageAbiExtra() ) {
            $abi = Filters::int($post['abilita']);
            $grado = Filters::int($post['grado']);
            $descr = Filters::in($post['descr']);
            $costo = Filters::int($post['costo']);

            DB::queryStmt("UPDATE abilita_extra SET abilita='{$abi}',grado='{$grado}',descrizione='{$descr}',costo='{$costo}' WHERE abilita='{$abi}' AND grado='{$grado}' LIMIT 1", [
                'abi' => $abi,
                'grado' => $grado,
                'descr' => $descr,
                'costo' => $costo,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Dati extra abilità modificati.',
                'swal_type' => 'success',
            ];
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
     * @fn DelAbiExtra
     * @note Eliminazione di una riga nella tabella abilita_extra
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function DelAbiExtra(array $post): array
    {

        if ( $this->permissionManageAbiExtra() ) {
            $abi = Filters::int($post['abilita']);
            $grado = Filters::int($post['grado']);

            DB::queryStmt("DELETE FROM abilita_extra WHERE abilita='{$abi}' AND grado='{$grado}' LIMIT 1", [
                'abi' => $abi,
                'grado' => $grado,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Dati extra abilità eliminati.',
                'swal_type' => 'success',
            ];
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