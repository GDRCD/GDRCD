<?php

class MeteoCondizioni extends Meteo
{
    /*** PERMESSI ****/

    /**
     * @fn permissionManageWeatherConditions
     * @note Controlla se si hanno i permessi per gestire le condizioni meteo
     * @return bool
     */
    public function permissionManageWeatherConditions(): bool
    {
        return Permissions::permission('MANAGE_WEATHER_CONDITIONS');
    }

    /** GETTER */

    /**
     * @fn getAllCondition
     * @note Estrae tutte le condizioni meteo
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllCondition(string $val = '*')
    {
        return DB::query("SELECT {$val} FROM meteo_condizioni", 'result');
    }

    /**
     * @fn getOne
     * @note Estrae una condizione meteo
     * @return bool|int|mixed|string
     */
    public function getCondition(int $id, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM meteo_condizioni WHERE id='{$id}' LIMIT 1");
    }

    /** LISTS  */

    /**
     * @fn selectConditions
     * @note Genera gli option per le conditions
     * @return string
     */
    public function listConditions(): string
    {
        $conditions = $this->getAllCondition('id,nome');
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $conditions);
    }

    /**
     * @fn selectConditions
     * @note Genera gli option per le conditions
     * @param string $selected
     * @return string
     */
    public function listConditionsByText(string $selected): string
    {
        $conditions = $this->getAllCondition('nome');
        return Template::getInstance()->startTemplate()->renderSelect('nome', 'nome', $selected, $conditions);
    }

    /** AJAX */

    /**
     * @fn ajaxCondList
     * @note Estrae la lista di condizioni
     * @return array
     */
    public function ajaxCondList():array
    {
        return ['List'=>$this->listConditions()];
    }

    /**
     * @fn ajaxCondData
     * @note Estrae la lista di condizioni
     * @param array $post
     * @return array
     */
    public function ajaxCondData(array $post): array
    {

        if ($this->permissionManageWeatherConditions()) {
            $id = Filters::int($post['id']);

            $data = $this->getCondition($id);

            $nome = Filters::out($data['nome']);
            $img = Filters::out($data['img']);
            $vento = Filters::out($data['vento']);

            return [
                'response' => true,
                'nome'=>$nome,
                'img'=>$img,
                'vento'=>$vento
            ];

        }

        return ['response'=>false];
    }

    /** GESTIONE */

    /**
     * @fn new
     * @note Inserisce una nuova condizione
     * @param array $post
     * @return array
     */
    public function NewCondition(array $post): array
    {
        if ($this->permissionManageWeather()) {

            $nome = Filters::in($post['nome']);
            $vento = Filters::in(implode(",", $post['vento']));
            $img = Filters::in($post['immagine']);


            DB::query("INSERT INTO meteo_condizioni (nome,vento,img )  VALUES ('{$nome}', '{$vento}' , '{$img}') ");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Condizione meteo creata.',
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
     * @param array $post
     * @return array
     */
    public function ModAbiRequisito(array $post): array
    {
        if($this->permissionManageWeather()){
            $nome = Filters::in( $post['nome']);
            $vento = implode(",",$post['vento']);
            $id=Filters::in( $post['id']);
            $img = Filters::in( $post['img']);


            DB::query("UPDATE  meteo_condizioni 
                SET nome = '{$nome}',vento='{$vento}', img='{$img}' WHERE id='{$id}'");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Condizione meteo modificata.',
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
     * @param array $post
     * @return array
     */
    public function DelCondition(array $post):array
    {
        if($this->permissionManageWeather()) {

            $id = Filters::in($post['id']);

            DB::query("DELETE FROM meteo_condizioni WHERE id='{$id}'");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Condizione meteo eliminata.',
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