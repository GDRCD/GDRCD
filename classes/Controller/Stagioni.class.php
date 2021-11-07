<?php

/**
 * @class Stagioni
 * @note Classe per la gestione delle stagioni
 * @required PHP 7.1+
 */
class Stagioni extends BaseClass
{
    private
        $weather;
    public function __construct()
    {
        parent::__construct();

        # Le abilita sono pubbliche?
        $this->weather = Condizioni::getAll();
    }

    /**** CONTROLS ****/

    /**
     * @fniVisibility
     * @note Controlla se si hanno i permessi per guardarla
     * @return bool
     */
    public function Visibility(): bool
    {
        return ($this->permission > MODERATOR);
    }

    /**
     * @fn getAll
     * @note Estrae lista delle stagione
     * @return array
     */
    public function getAll()
    {
        return DB::query("SELECT *  FROM meteo_stagioni", 'result');
    }

    /**
     * @fn getOne
     * @note Estrae una stagione
     * @return array
     */
    public function getOne($id)
    {
        $id = Filters::int($id);
        return DB::query("SELECT * FROM meteo_stagioni WHERE id='{$id}'", 'query');
    }

    /**
     * @fn new
     * @note Inserisce una stagione
     */
    public function new(string $nome, $minima,$massima, $data_inizio, $alba, $tramonto)
    {
        DB::query("INSERT INTO meteo_stagioni (nome,minima,massima, data_inizio, alba, tramonto )  VALUES
        ('{$nome}', '{$minima}' , '{$massima}', '{$data_inizio}', '{$alba}', '{$tramonto}') ");
    }

    /**
     * @fn edit
     * @note Aggiorna una stagione
     */
    public function edit(string $nome, $minima,$massima, $data_inizio, $alba, $tramonto, $id, $condizioni)
    {
        $id = Filters::int($id);
        DB::query("UPDATE  meteo_stagioni 
                SET nome = '{$nome}',minima='{$minima}', massima='{$massima}', data_inizio='{$data_inizio}', alba='{$alba}', tramonto='{$tramonto}', condizioni='{$condizioni}' WHERE id='{$id}'");
    echo "UPDATE  meteo_stagioni 
                SET nome = '{$nome}',minima='{$minima}', massima='{$massima}', data_inizio='{$data_inizio}', alba='{$alba}', tramonto='{$tramonto}', condizioni='{$condizioni}' WHERE id='{$id}'";
    }

    /**
     * @fn delete
     * @note Cancella una stagione
     */
    public function delete(int $id)
    {
        $id = Filters::int($id);
        DB::query("DELETE FROM meteo_stagioni WHERE id='{$id}'");
    }

}
