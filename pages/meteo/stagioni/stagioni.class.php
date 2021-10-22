<?php

/**
 * @class Stagioni
 * @note Classe per la gestione delle stagioni
 * @required PHP 7.1+
 */
class Stagioni
{
    private $me,
        $permessi,
        $parameters,
        $array_vento;

    public function __construct()
    {
        global $PARAMETERS;
        $this->me = gdrcd_filter('in', $_SESSION['login']);
        $this->permessi = gdrcd_filter('num', $_SESSION['permessi']);
        $this->parameters = $PARAMETERS;

    }

    /**** CONTROLS ****/

    /**
     * @fniVisibility
     * @note Controlla se si hanno i permessi per guardarla
     * @return bool
     */
    public function Visibility(): bool
    {
        return ($this->permessi > MODERATOR);
    }

    /**
     * @fn getAll
     * @note Estrae lista delle stagione
     * @return array
     */
    public function getAll()
    {
        return gdrcd_query("SELECT *  FROM meteo_stagioni", 'result');
    }

    /**
     * @fn getOne
     * @note Estrae una stagione
     * @return array
     */
    public function getOne($id)
    {
        $id = gdrcd_filter('num', $id);
        return gdrcd_query("SELECT * FROM meteo_stagioni WHERE id='{$id}'", 'query');
    }

    /**
     * @fn new
     * @note Inserisce una stagione
     */
    public function new(string $nome, $minima,$massima, $data_inizio, $alba, $tramonto)
    {
        gdrcd_query("INSERT INTO meteo_stagioni (nome,minima,massima, data_inizio, alba, tramonto )  VALUES
        ('{$nome}', '{$minima}' , '{$massima}', '{$data_inizio}', '{$alba}', '{$tramonto}') ");
    }

    /**
     * @fn edit
     * @note Aggiorna una stagione
     */
    public function edit(string $nome, $minima,$massima, $data_inizio, $alba, $tramonto, $id)
    {
        $id = gdrcd_filter('num', $id);
        gdrcd_query("UPDATE  meteo_stagioni 
                SET nome = '{$nome}',minima='{$minima}', massima='{$massima}', data_inizio='{$data_inizio}', alba='{$alba}', tramonto='{$tramonto}' WHERE id='{$id}'");
    }

    /**
     * @fn delete
     * @note Cancella una stagione
     */
    public function delete(int $id)
    {
        $id = gdrcd_filter('num', $id);
        gdrcd_query("DELETE FROM meteo_stagioni WHERE id='{$id}'");
    }

}
