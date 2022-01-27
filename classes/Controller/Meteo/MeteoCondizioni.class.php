<?php

class MeteoCondizioni extends Meteo{


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

    /**
     * @fn new
     * @note Inserisce una nuova condizione
     */
    public function newCondition(string $nome, $vento,$img)
    {
        DB::query("INSERT INTO meteo_condizioni (nome,vento,img )  VALUES
        ('{$nome}', '{$vento}' , '{$img}') ");
    }

    /**
     * @fn edit
     * @note Aggiorna una condizione meteo
     */
    public function editCondition(string $nome, $vento, $id, $img)
    {
        $id = Filters::int( $id);
        DB::query("UPDATE  meteo_condizioni 
                SET nome = '{$nome}',vento='{$vento}', img='{$img}' WHERE id='{$id}'");
    }

    /**
     * @fn delete
     * @note Cancella una condizione meteo
     */
    public function deleteCondition(int $id)
    {
        $id = Filters::int(  $id);
        DB::query("DELETE FROM meteo_condizioni WHERE id='{$id}'");
    }

}