<?php

/**
 * @class Condizioni
 * @note Classe per la gestione delle condizioni meteo
 * @required PHP 7.1+
 */
class Condizioni
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
        $this->array_vento = array("Assente", "Brezza", "Brezza intensa", "Vento Forte", "Burrasca");
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
     * @note Estrae lista delle condizioni meteo
     * @return array
     */
    public function getAll()
    {
        return gdrcd_query("SELECT id, nome, vento, img FROM meteo_condizioni", 'result');
    }

    /**
     * @fn getOne
     * @note Estrae una condizione meteo
     * @return array
     */
    public function getOne($id)
    {
        $id = gdrcd_filter('num', $id);
        return gdrcd_query("SELECT id, nome, vento, img FROM meteo_condizioni WHERE id='{$id}'", 'query');
    }

    /**
     * @fn new
     * @note Inserisce una nuova condizione
     */
    public function new(string $nome, $vento,$img)
    {
        gdrcd_query("INSERT INTO meteo_condizioni (nome,vento,img )  VALUES
        ('{$nome}', '{$vento}' , '{$img}') ");
    }

    /**
     * @fn edit
     * @note Aggiorna una condizione meteo
     */
    public function edit(string $nome, $vento, $id, $img)
    {
        $id = gdrcd_filter('num', $id);
        gdrcd_query("UPDATE  meteo_condizioni 
                SET nome = '{$nome}',vento='{$vento}', img='{$img}' WHERE id='{$id}'");
    }

    /**
     * @fn delete
     * @note Cancella una condizione meteo
     */
    public function delete(int $id)
    {
        $id = gdrcd_filter('num', $id);
        gdrcd_query("DELETE FROM meteo_condizioni WHERE id='{$id}'");
    }
    /**
     * @fn selectVento
     * @note Genera gli option per il vento
     * @return string
     */
    public function selectVento():string
    {
        $option="";
        foreach ($this->array_vento as $vento) {
            $option .= "<option>{$vento}</option>";
        }
        return $option;
    }
    /**
     * @fn selectVento
     * @note Genera gli option per il vento per edit, ritornando gli option selezionati e non
     *  @return string
     */
    public function diffselectVento($array)
    {
        $option="";
        foreach ($array as $v){
            $option .= "<option selected>{$v}</option>";
        }
        $diff=array_diff($this->array_vento, $array);
        foreach ($diff as $vento) {
            $option .= "<option>{$vento}</option>";
        }
        return $option;
    }
}
