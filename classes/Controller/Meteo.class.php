<?php

/**
 * @class Stagioni
 * @note Classe per la gestione delle stagioni
 * @required PHP 7.1+
 */
class Meteo extends BaseClass
{
    private
        $weather,
        $array_vento;
    public function __construct()
    {
        parent::__construct();

        # Le abilita sono pubbliche?
        $this->weather = $this->getAllCondition();
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
        return ($this->permission > MODERATOR);
    }

    /**
     * @fn getAll
     * @note Estrae lista delle stagione
     * @return array
     */
    public function getAllSeason()
    {
        return DB::query("SELECT *  FROM meteo_stagioni", 'result');
    }

    /**
     * @fn getOne
     * @note Estrae una stagione
     * @return array
     */
    public function getOneSeason($id)
    {
        $id = Filters::int($id);
        return DB::query("SELECT * FROM meteo_stagioni WHERE id='{$id}'", 'query');
    }

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
    public function getAllCondition()
    {
        return DB::query("SELECT id, nome, vento, img FROM meteo_condizioni", 'result');
    }

    /**
     * @fn getOne
     * @note Estrae una condizione meteo
     * @return array
     */
    public function getOneCondition($id)
    {
        return DB::query("SELECT id, nome, vento, img FROM meteo_condizioni WHERE id='{$id}'", 'query');
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

    public function  newClimaticState($id_stagione, $id_condizione, $percentuale){
        $id_stagione = Filters::int($id_stagione);
        $id_condizione = Filters::int($id_condizione);
        $percentuale = Filters::int($percentuale);
        DB::query("INSERT INTO meteo_stati_climatici (stagione,condizione,percentuale )  VALUES
        ('{$id_stagione}', '{$id_condizione}' , '{$percentuale}') ");
    }
    public function  getAllState($stagione){
        return DB::query("SELECT nome, percentuale, meteo_stati_climatici.id FROM meteo_stati_climatici LEFT JOIN meteo_condizioni on condizione=meteo_condizioni.id where stagione='{$stagione}'", 'result');
    }

    public function diffselectState($stagione)
    {
        return DB::query("SELECT meteo_condizioni.id, nome, vento, img FROM meteo_condizioni WHERE id NOT IN (SELECT condizione FROM meteo_stati_climatici WHERE stagione= {$stagione})", 'result');
    }
    public function  deleteClimaticState($id){
        $id = Filters::int(  $id);
        DB::query("DELETE FROM meteo_stati_climatici WHERE id='{$id}'");
    }

}
