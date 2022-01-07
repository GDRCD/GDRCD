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
        return DB::query("SELECT nome, percentuale, meteo_stati_climatici.id, condizione FROM meteo_stati_climatici LEFT JOIN meteo_condizioni on condizione=meteo_condizioni.id where stagione='{$stagione}' order by percentuale", 'result');
    }

    public function diffselectState($stagione)
    {
        return DB::query("SELECT meteo_condizioni.id, nome, vento, img FROM meteo_condizioni WHERE id NOT IN (SELECT condizione FROM meteo_stati_climatici WHERE stagione= {$stagione} )", 'result');
    }
    public function  deleteClimaticState($id){
        $id = Filters::int(  $id);
        DB::query("DELETE FROM meteo_stati_climatici WHERE id='{$id}'");
    }
    public  function getWebApiWeather(){
        $city=Functions::get_constant('WEATHER_WEBAPI_CITY');
        $api=Functions::get_constant('WEATHER_WEBAPI');
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://api.openweathermap.org/data/2.5/weather?q='.$city.'&appid='.$api.'&units=metric&lang=it',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_SSL_VERIFYPEER => false,
        ));
        $response = curl_exec($curl);
        $result = json_decode($response,true);
        if (curl_errno($curl)) {
            echo 'Error:' . curl_error($curl);
        }
        curl_close($curl);
        return ($result);
    }
    public function saveSetting($luna, $vento,$tipo,$api,$citta,$icone,$formato){

        DB::query("UPDATE   config SET val = '{$luna}' WHERE const_name='WEATHER_MOON'");
        DB::query("UPDATE   config SET val = '{$vento}' WHERE const_name='WEATHER_WIND'");
        DB::query("UPDATE   config SET val = '{$tipo}' WHERE const_name='WEATHER_TYPE'");
        DB::query("UPDATE   config SET val = '{$api}' WHERE const_name='WEATHER_WEBAPI'");
        DB::query("UPDATE   config SET val = '{$citta}' WHERE const_name='WEATHER_WEBAPI_CITY'");
        DB::query("UPDATE   config SET val = '{$icone}' WHERE const_name='WEATHER_WEBAPI_ICON'");
        DB::query("UPDATE   config SET val = '{$formato}' WHERE const_name='WEATHER_WEBAPI_FORMAT'");
    }
    public function lunar_phase()
    {
        # Inizializzo dati necessari
        $year = date('Y');
        $month = date('n');
        $days = date('j');

        # Se e' prima di aprile sottraggo un anno
        if ($month < 4) {
            $year = $year - 1;
            $month = $month + 12;
        }

        # Eseguo calcoli astronomici
        $days_y = 365.25 * $year;
        $days_m = 30.42 * $month;
        $plenilunio = $days_y + $days_m + $days - 694039.09;
        $plenilunio = $plenilunio / 29.53;
        $phase = intval($plenilunio);
        $plenilunio = $plenilunio - $phase;
        $phase = round($plenilunio * 8 + 0.5);

        # ...
        if ($phase == 8) {
            $phase = 0;
        }
        # Creo gli array delle fasi
        $phase_array = array('nuova', 'crescente', 'primo-quarto', 'gibbosa-crescente', 'piena', 'gibbosa-calante', 'ultimo-quarto', 'calante');
        $phase_title = array('Nuova', 'Crescente', 'Primo Quarto', 'Gibbosa crescente', 'Piena', 'Gibbosa calante', 'Ultimo quarto', 'Calante');
        # Estraggo e ritorno la fase calcolata
        return array('phase' => $phase_array[$phase], 'title' => $phase_title[$phase]);
    }
    public function wind($speed){
        $velocita =
            // "switch" comparison for $count
            $speed <= 5 ? 'Debole' :
                ($speed <= 10 ? 'Moderato' :
                    ($speed <= 15 ? 'Forte' :
                        // default above 60
                        'Molto forte'));
        return $velocita;
    }
    public function saveWeather($meteo){
        $data= date("Y-m-d H:i");
        DB::query("UPDATE   config SET val = '{$meteo}' WHERE const_name='WEATHER_LAST'");
        DB::query("UPDATE   config SET val = '{$data}' WHERE const_name='WEATHER_LAST_DATE'");
    }
    public function  dateDifference($date_1 , $date_2 , $differenceFormat = '%a' )
                    {
                        $datetime1 = date_create($date_1);
                        $datetime2 = date_create($date_2);

                        $interval = date_diff($datetime1, $datetime2);

                        return $interval->format($differenceFormat);

                    }

}
