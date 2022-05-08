<?php

class MeteoVenti extends Meteo
{


    /*** PERMESSI ****/

    /**
     * @fn permissionManageWeatherConditions
     * @note Controlla se si hanno i permessi per gestire le condizioni meteo
     * @return bool
     */
    public function permissionManageWeatherWinds(): bool
    {
        return Permissions::permission('MANAGE_WEATHER_WINDS');
    }


    /** GETTER */

    /**
     * @fn getAllCondition
     * @note Estrae tutte le condizioni meteo
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllWinds(string $val = '*')
    {
        return DB::query("SELECT {$val} FROM meteo_venti ORDER BY nome", 'result');
    }

    /**
     * @fn getOne
     * @note Estrae una condizione meteo
     * @return bool|int|mixed|string
     */
    public function getWind(int $id, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM meteo_venti WHERE id='{$id}' LIMIT 1");
    }

    /** LIST */

    /**
     * @fn selectVento
     * @note Genera gli option per il vento
     * @return string
     */
    public function listWinds(): string
    {
        $winds = $this->getAllWinds();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $winds);
    }

    /** AJAX */

    /**
     * @fn ajaxCondList
     * @note Estrae la lista di condizioni
     * @return array
     */
    public function ajaxWindList():array
    {
        return ['List'=>$this->listWinds()];
    }

    /**
     * @fn ajaxCondData
     * @note Estrae la lista di condizioni
     * @param array $post
     * @return array
     */
    public function ajaxWindData(array $post): array
    {

        if ($this->permissionManageWeatherWinds()) {
            $id = Filters::int($post['id']);

            $data = $this->getWind($id);

            $nome = Filters::out($data['nome']);

            return [
                'response' => true,
                'nome'=>$nome
            ];

        }

        return ['response'=>false];
    }

    /** GESTIONE */

    /**
     * @fn new
     * @note Inserisce una nuova condizione
     */
    public function NewWind(array $post)
    {
        if ($this->permissionManageWeatherWinds()) {

            $nome = Filters::in($post['nome']);

            DB::query("INSERT INTO meteo_venti (nome)  VALUES ('{$nome}') ");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Vento creato.',
                'swal_type' => 'success'
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error'
            ];
        }
    }

    /**
     * @fn edit
     * @note Aggiorna una condizione meteo
     */
    public function ModWind(array $post)
    {
        if($this->permissionManageWeatherWinds()){
            $id=Filters::in( $post['id']);
            $nome = Filters::in( $post['nome']);

            DB::query("UPDATE  meteo_venti 
                SET nome = '{$nome}' WHERE id='{$id}'");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Vento modificato.',
                'swal_type' => 'success'
            ];
        } else{
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error'
            ];
        }
    }

    /**
     * @fn delete
     * @note Cancella una condizione meteo
     */
    public function DelWind(array $post)
    {
        if($this->permissionManageWeatherWinds()) {

            $id = Filters::in($post['id']);

            DB::query("DELETE FROM meteo_condizioni WHERE id='{$id}'");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Vento eliminato.',
                'swal_type' => 'success'
            ];
        } else{
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error'
            ];
        }
    }

}