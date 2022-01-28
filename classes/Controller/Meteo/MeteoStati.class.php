<?php

class MeteoStati extends Meteo{

    /**
     * @fn
     * @note Select di tutte le condizioni climatiche per quella stagione
     */
    public function  getAllState($stagione){
        return DB::query("SELECT nome, percentuale, meteo_stati_climatici.id, condizione FROM meteo_stati_climatici LEFT JOIN meteo_condizioni on condizione=meteo_condizioni.id where stagione='{$stagione}' order by percentuale", 'result');
    }

    /**
     * @fn
     * @note Select degli stati climatici non presenti nella stagione
     */
    public function diffselectState($stagione)
    {
        return DB::query("SELECT meteo_condizioni.id, nome, vento, img FROM meteo_condizioni WHERE id NOT IN (SELECT condizione FROM meteo_stati_climatici WHERE stagione= {$stagione} )", 'result');
    }

    /**
     * @fn
     * @note Inserisce una condizione climatica per la stagione
     */
    public function  newClimaticState($id_stagione, $id_condizione, $percentuale){
        $id_stagione = Filters::int($id_stagione);
        $id_condizione = Filters::int($id_condizione);
        $percentuale = Filters::int($percentuale);
        DB::query("INSERT INTO meteo_stati_climatici (stagione,condizione,percentuale )  VALUES
        ('{$id_stagione}', '{$id_condizione}' , '{$percentuale}') ");
    }

    /**
     * @fn
     * @note Delete di uno stato climatico per una stagione
     */
    public function  deleteClimaticState($id){
        $id = Filters::int(  $id);
        DB::query("DELETE FROM meteo_stati_climatici WHERE id='{$id}'");
    }

}