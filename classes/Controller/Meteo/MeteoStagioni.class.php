<?php

class MeteoStagioni extends Meteo{

    /**
     * @fn getAll
     * @note Estrae lista delle stagioni
     * @return bool|int|mixed|string
     */
    public function getAllSeason(string $val = '*')
    {
        return DB::query("SELECT {$val}  FROM meteo_stagioni WHERE 1 ORDER BY nome", 'result');
    }

    /**
     * @fn getOne
     * @note Estrae una stagione
     * @return bool|int|mixed|string
     */
    public function getSeason(int $id, string $val)
    {
        $id = Filters::int($id);
        return DB::query("SELECT '{$val}' FROM meteo_stagioni WHERE id='{$id}' LIMIT 1");
    }

    /**** GESTIONE ****/

    /**
     * @fn new
     * @note Inserisce una stagione
     */
    public function newSeason(string $nome, $minima,$massima, $data_inizio, $alba, $tramonto)
    {
        DB::query("INSERT INTO meteo_stagioni (nome,minima,massima, data_inizio, alba, tramonto )  VALUES
        ('{$nome}', '{$minima}' , '{$massima}', '{$data_inizio}', '{$alba}', '{$tramonto}') ");
    }

    /**
     * @fn edit
     * @note Aggiorna una stagione
     */
    public function editSeason(string $nome, $minima,$massima, $data_inizio, $alba, $tramonto, $id)
    {
        $id = Filters::int($id);
        DB::query("UPDATE  meteo_stagioni 
                SET nome = '{$nome}',minima='{$minima}', massima='{$massima}', data_inizio='{$data_inizio}', alba='{$alba}', tramonto='{$tramonto}' WHERE id='{$id}'");
    }

    /**
     * @fn delete
     * @note Cancella una stagione
     */
    public function deleteSeason(int $id)
    {
        $id = Filters::int($id);
        DB::query("DELETE FROM meteo_stagioni WHERE id='{$id}'");
    }

}