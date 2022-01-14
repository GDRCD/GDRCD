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
     * @note Estrae lista delle stagioni
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
     * @note Select degli stati climatici non presenti nella stagione
     */
    public function diffselectSeason($array)
    {
        $option="";
        foreach ($array as $v){
            $option .= "<option selected>{$v}</option>";
        }
        $stagioni=$this->getAllSeason();
        var_dump($stagioni);
        foreach ($stagioni as $item) {
            $option .= "<option >{$item['nome']}</option>";

        }

       // $diff=array_diff($this->array_vento, $array);
        //foreach ($diff as $vento) {
       //     $option .= "<option>{$vento}</option>";
      //  }
        return $option;    }
    /**
     * @fn
     * @note Delete di uno stato climatico per una stagione
     */
    public function  deleteClimaticState($id){
        $id = Filters::int(  $id);
        DB::query("DELETE FROM meteo_stati_climatici WHERE id='{$id}'");
    }
    /**
     * @fn
     * @note Chiamata webapi per recuperare il meteo di una città passando l'api key e la città di default
     */
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
    /**
     * @fn
     * @note Chiamata webapi per recuperare il meteo di una città passando l'api key ed una città specifica
     */
    public  function getWebApiWeatherChat($city){

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
    /**
     * @fn
     * @note Salvataggio della pagina impostazioni
     */
    public function saveSetting($luna, $vento,$tipo,$api,$citta,$icone,$formato, $time){
        DB::query("UPDATE   config SET val = '{$luna}' WHERE const_name='WEATHER_MOON'");
        DB::query("UPDATE   config SET val = '{$vento}' WHERE const_name='WEATHER_WIND'");
        DB::query("UPDATE   config SET val = '{$tipo}' WHERE const_name='WEATHER_TYPE'");
        DB::query("UPDATE   config SET val = '{$api}' WHERE const_name='WEATHER_WEBAPI'");
        DB::query("UPDATE   config SET val = '{$citta}' WHERE const_name='WEATHER_WEBAPI_CITY'");
        DB::query("UPDATE   config SET val = '{$icone}' WHERE const_name='WEATHER_WEBAPI_ICON'");
        DB::query("UPDATE   config SET val = '{$formato}' WHERE const_name='WEATHER_WEBAPI_FORMAT'");
        DB::query("UPDATE   config SET val = '{$time}' WHERE const_name='WEATHER_UPDATE'");
    }
    /**
     * @fn
     * @note Fasi lunari
     */
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
        if ($phase == 8) {
            $phase = 0;
        }
        # Creo gli array delle fasi
        $phase_array = array('nuova', 'crescente', 'primo-quarto', 'gibbosa-crescente', 'piena', 'gibbosa-calante', 'ultimo-quarto', 'calante');
        $phase_title = array('Nuova', 'Crescente', 'Primo Quarto', 'Gibbosa crescente', 'Piena', 'Gibbosa calante', 'Ultimo quarto', 'Calante');
        # Estraggo e ritorno la fase calcolata
        return array('phase' => $phase_array[$phase], 'title' => $phase_title[$phase]);
    }
    /**
     * @fn
     * @note Velocità del vento
     */
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
    /**
     * @fn
     * @note Salvataggio del meteo per la stagione
     */
    public function saveWeather($meteo, $wind)
    {
        $data = date("Y-m-d H:i");
        DB::query("UPDATE   config SET val = '{$meteo}' WHERE const_name='WEATHER_LAST'");
        DB::query("UPDATE   config SET val = '{$data}' WHERE const_name='WEATHER_LAST_DATE'");
        DB::query("UPDATE   config SET val = '{$wind}' WHERE const_name='WEATHER_LAST_WIND'");
    }
    public function saveChat($vento, $temperatura, $condizione,$citta , $id)
    {

        switch (Functions::get_constant('WEATHER_TYPE')) {
            case 1:
                $condizione = $this->getOneCondition($condizione);
                $img = "<img src='" . $condizione['img'] . "' title='" . $condizione['nome'] . " ' >";
                $meteo= Filters::in($img . " " .$temperatura . " " .$vento);
                $citta='';
                break;
            default:
                $citta= Filters::in($citta);
                $meteo='';
               if (empty($citta)){ DB::query("DELETE FROM meteo_chat WHERE id_chat='{$id}'");}else $check="ok";
                break;
        }


        switch ($check){
            case "ok":
                if($this->checkMeteoChat($id)){
                    DB::query("UPDATE meteo_chat SET meteo = '{$meteo}', citta='{$citta}' WHERE id_chat={$id}");
                }else{
                    DB::query("INSERT INTO meteo_chat (citta, meteo, id_chat ) VALUES ('{$citta}',  '{$meteo}', {$id}) ");
                }
            default:
                break;

        }


    }
    public function saveMap($citta, $id)
    {
        switch (Functions::get_constant('WEATHER_TYPE')) {
            case 1:
                $citta= '';
                $meteo=Filters::in($citta);;
                var_dump(($meteo));

                break;
            default:
                $citta= Filters::in($citta);
                $meteo='';

                if (empty($citta)){
                    DB::query("DELETE FROM meteo_mappa WHERE id_mappa='{$id}'");

                }else {
                    $check="ok";
                }

                break;
        }
        switch ($check){
            case "ok":
                if($this->checkMeteoMappa($id)){
                    DB::query("UPDATE meteo_mappa SET meteo = '{$meteo}', citta='{$citta}' , stagioni='' WHERE id_mappa={$id}");
                }else{
                    DB::query("INSERT INTO meteo_mappa (citta, meteo, id_mappa, stagioni ) VALUES ('{$citta}',  '{$meteo}', {$id}, '') ");
                }
            default:
                break;

        }
    }
    /**
     * @fn
     * @note Calcolo differenza di ore fra la data/ora attuale e quella salvata del meteo
     */
    public function  dateDifference($date_1 , $date_2 , $differenceFormat = '%a' )
    {
        $datetime1 = date_create($date_1);
        $datetime2 = date_create($date_2);
        $interval = date_diff($datetime1, $datetime2);
        return $interval->format($differenceFormat);
    }
    /**
     * @fn
     * @note Restituisce il meteo dalle webapi
     */
    public function meteoWebApi(){
        $api = $this->getWebApiWeather();
        $wind = (Functions::get_constant('WEATHER_WIND') == 1) ? " - " . $this->wind($api['wind']['speed']) : '';
        $url = (Functions::get_constant('WEATHER_WEBAPI_ICON') == 0) ? "http://openweathermap.org/img/wn/" : "imgs/meteo/";
        $estensione = (Functions::get_constant('WEATHER_WEBAPI_ICON') == 0) ? "png" : Functions::get_constant('WEATHER_WEBAPI_FORMAT');
        $img = "<img src='" . $url . "" . $api['weather'][0]['icon'] . "." . $estensione . "' title='" . $api['weather'][0]['description'] . " ' >";
        $temp = Filters::int($api['main']['temp']) . "&deg;C";
        return $img . " " .$temp . " " .$wind;
    }

    /**
     * @fn
     * @note Restituisce il meteo dalle webapi di una città per una singola chat
     */
    public function meteoWebApiChat($citta){
        $api = $this->getWebApiWeatherChat($citta);
        $wind = (Functions::get_constant('WEATHER_WIND') == 1) ? " - " . $this->wind($api['wind']['speed']) : '';
        $url = (Functions::get_constant('WEATHER_WEBAPI_ICON') == 0) ? "http://openweathermap.org/img/wn/" : "imgs/meteo/";
        $estensione = (Functions::get_constant('WEATHER_WEBAPI_ICON') == 0) ? "png" : Functions::get_constant('WEATHER_WEBAPI_FORMAT');
        $img = "<img src='" . $url . "" . $api['weather'][0]['icon'] . "." . $estensione . "' title='" . $api['weather'][0]['description'] . " ' >";
        $temp = Filters::int($api['main']['temp']) . "&deg;C";
        return $img . " " .$temp . " " .$wind;
    }

    /**
     * @fn
     * @note Restituisce il meteo dalla stagione
     */
    public function meteoSeason()
    {
        $data1=date("Y-m-d H:i");
        $data2=Functions::get_constant('WEATHER_LAST_DATE');
        $time=Functions::get_constant('WEATHER_UPDATE');
        if(($this->dateDifference($data2,$data1 ,  '%h') >$time)|| (Functions::get_constant('WEATHER_LAST_DATE')=="")) {
            $data = date("Y-m-d");
            $stagione = DB::query("SELECT * FROM meteo_stagioni WHERE data_inizio <'{$data}' AND DATA_fine > '{$data}'", 'query');
            $condizioni = $this->getAllState($stagione['id']);
            $rand = rand(0, 100);
            while ($row = DB::query($condizioni, 'fetch')) {
                if (($rand >= $row['percentuale'])) {
                    $condizione = $row['condizione'];
                }
            }
            $condizione = $this->getOneCondition($condizione);
            $img = "<img src='" . $condizione['img'] . "' title='" . $condizione['nome'] . " ' >";
            $vento = explode(",", $condizione['vento']);
            shuffle($vento);
            $wind = (Functions::get_constant('WEATHER_WIND') == 1) ? $vento[0] : '';
            $temp = rand($stagione['minima'], $stagione['massima']);
            $temp = Filters::int($temp) . "&deg;C";
            $meteo= Filters::in($img . " " .$temp);
            $this->saveWeather($meteo, $wind);
        }
        return Functions::get_constant('WEATHER_LAST');
    }
    public function checkMeteoChat($id){
        $id = Filters::int($id);
        return DB::query("SELECT * FROM meteo_chat WHERE id_chat='{$id}'", 'query');
    }
    public function checkMeteoMappa($id){
        $id = Filters::int($id);
        return DB::query("SELECT * FROM meteo_mappa WHERE id_mappa='{$id}'", 'query');
    }
    public function checkMeteoMappaChat($id){
        $id = Filters::int($id);
        $chat= DB::query("SELECT id_mappa FROM mappa WHERE id='{$id}'", 'query');
        return DB::query("SELECT *  FROM meteo_mappa WHERE id_mappa='{$chat['id_mappa']}'", 'query');
    }

}
