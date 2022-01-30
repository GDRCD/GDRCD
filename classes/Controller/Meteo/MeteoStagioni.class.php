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

    /**** FUNCTIONS ****/

    /**
     * @fn
     * @note Restituisce il meteo dalla stagione
     */
    public function meteoSeason()
    {
        $data1 = date("Y-m-d H:i");
        $data2 = Functions::get_constant('WEATHER_LAST_DATE');
        $time = Functions::get_constant('WEATHER_UPDATE');
        if (empty($data2) || ($this->dateDifference($data2, $data1, '%h') > $time) || (Functions::get_constant('WEATHER_LAST_DATE') == "")) {
            $data = date("Y-m-d");
            $stagione = DB::query("SELECT * FROM meteo_stagioni WHERE data_inizio <'{$data}' AND DATA_fine > '{$data}'", 'query');
            $condizioni = MeteoStati::getInstance()->getAllState($stagione['id']);
            $rand = rand(0, 100);
            while ($row = DB::query($condizioni, 'fetch')) {
                if (($rand >= $row['percentuale'])) {
                    $condizione = $row['condizione'];
                }
            }
            $condizione = (!empty($condizione)) ? MeteoCondizioni::getInstance()->getCondition($condizione) : '';
            $img = "<img src='" . $condizione['img'] . "' title='" . $condizione['nome'] . " ' >";
            $vento = explode(",", $condizione['vento']);
            shuffle($vento);
            $wind = $vento[0];
            $temp = rand($stagione['minima'], $stagione['massima']);
            $temp = Filters::int($temp) . "&deg;C";
            $meteo = Filters::in($img . " " . $temp);
            $this->saveWeather($meteo, $wind);

            return [
                'meteo' => $meteo,
                'vento'=> $vento
            ];
        }
        else{
            return [
                'meteo'=> Functions::get_constant('WEATHER_LAST'),
                'vento'=> Functions::get_constant('WEATHER_LAST_WIND'),
            ];
        }
    }

    public function meteoMappaSeason($stagioni, $id)
    {
        $data1 = date("Y-m-d H:i");
        $data2 = Functions::get_constant('WEATHER_LAST_DATE');
        $time = Functions::get_constant('WEATHER_UPDATE');
        if (empty($data2) || ($this->dateDifference($data2, $data1, '%h') > $time) || (Functions::get_constant('WEATHER_LAST_DATE') == "")) {
            $data = date("Y-m-d");
            $stagione = DB::query("SELECT * FROM meteo_stagioni WHERE data_inizio <'{$data}' AND DATA_fine > '{$data}' and id IN ({$stagioni})", 'query');
            if (empty($stagione)) {
                echo "verifica di aver assegnato correttamente le stagioni alla mappa o di aver assicurato il range data inizio e fine nelle stagioni poichè non vi sono stagioni selezionabili per questo periodo dell'anno";
            }

            $condizioni = MeteoStati::getInstance()->getAllState($stagione['id']);
            $rand = rand(0, 100);
            while ($row = DB::query($condizioni, 'fetch')) {
                if (($rand >= $row['percentuale'])) {
                    $condizione = $row['condizione'];
                }
            }
            $condizione = MeteoCondizioni::getInstance()->getCondition($condizione);
            $img = "<img src='" . $condizione['img'] . "' title='" . $condizione['nome'] . " ' >";
            $vento = explode(",", $condizione['vento']);
            shuffle($vento);
            $wind = (Functions::get_constant('WEATHER_WIND') == 1) ? $vento[0] : '';
            $temp = rand($stagione['minima'], $stagione['massima']);
            $temp = Filters::int($temp) . "&deg;C";
            $meteo = Filters::in($img . " " . $temp);
            $this->saveWeatherMap($meteo, $wind, $id);

            return [
                'meteo' => $meteo,
                'vento' => $vento
            ];
        } else{
            $data = $this->getMeteoMappa($id);

            return [
                'meteo' => $data['meteo'],
                'vento' => $data['vento']
            ];
        }
    }

    /**
     * @fn
     * @note Select degli stati climatici non presenti nella stagione
     */
    public function diffselectSeason($array)
    {
        $option = "";
        $stagioni = MeteoStagioni::getInstance()->getAllSeason();
        foreach ($stagioni as $item) {
            $option .= "<div class='form_field'>";
            if (in_array($item['id'], $array)) {
                $option .= "<input type='checkbox' name='stagioni[]' checked value='{$item['id']}'></div>";
            } else {
                $option .= "<input type='checkbox' name='stagioni[]' value='{$item['id']}'></div>";
            }

            $option .= "<div class='form_label'>{$item['nome']}</div>";
        }
        return $option;
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