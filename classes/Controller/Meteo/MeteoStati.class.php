<?php

class MeteoStati extends Meteo{

    /**
     * @fn
     * @note Select di tutte le condizioni climatiche per quella stagione
     * @param string $stagione
     * @return mixed
     */
    public function  getAllState(string $stagione){
        return DB::query("SELECT nome, percentuale, meteo_stati_climatici.id, condizione FROM meteo_stati_climatici LEFT JOIN meteo_condizioni on condizione=meteo_condizioni.id where stagione='{$stagione}' order by percentuale", 'result');
    }

    /**
     * @fn
     * @note Select degli stati climatici non presenti nella stagione
     * @param string $stagione
     * @return mixed
     */
    public function diffselectState(string $stagione)
    {
        return DB::query("SELECT meteo_condizioni.id, nome, vento, img FROM meteo_condizioni WHERE id NOT IN (SELECT condizione FROM meteo_stati_climatici WHERE stagione= {$stagione} )", 'result');
    }

    /**
     * @fn
     * @note Inserisce una condizione climatica per la stagione
     * @param array $post
     * @return void
     */
    public function  newClimaticState(array $post):void
    {
        $id_stagione= Filters::in($post['id']);
        $id_condizione= Filters::in($post['condizione']);
        $percentuale= Filters::in($post['percentuale']);
        DB::query("INSERT INTO meteo_stati_climatici (stagione,condizione,percentuale )  VALUES
        ('{$id_stagione}', '{$id_condizione}' , '{$percentuale}') ");
    }

    /**
     * @fn
     * @note Delete di uno stato climatico per una stagione
     * @param array $post
     * @return void
     */
    public function  deleteClimaticState(array $post):void
    {
        $id=Filters::in($post['id']);
        DB::query("DELETE FROM meteo_stati_climatici WHERE id='{$id}'");
    }

}