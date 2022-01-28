<?php

/**
 * @class Stagioni
 * @note Classe per la gestione delle stagioni
 * @required PHP 7.1+
 */
class Meteo extends BaseClass
{
    private
        $array_vento;

    public function __construct()
    {
        parent::__construct();
        $this->array_vento = array("Assente", "Brezza", "Brezza intensa", "Vento Forte", "Burrasca"); # TODO Spostare in db
    }

    /**** CONTROLS ****/

    /**
     * @fniVisibility
     * @note Controlla se si hanno i permessi per guardarla
     * @return bool
     */
    public function permissionManageWeather(): bool
    {
        return Permissions::permission('MANAGE_WEATHER');
    }

    /**** QUERY ***/

    public function getMeteoChat(int $id, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM meteo_chat WHERE id_chat='{$id}' LIMIT 1");
    }

    public function getMeteoMappa($id)
    {
        $id = Filters::int($id);
        return DB::query("SELECT * FROM meteo_mappa WHERE id_mappa='{$id}'", 'query');
    }

    public function getMeteoMappaChat($id, $val = 'meteo_mappa.*')
    {
        return DB::query(" SELECT {$val} FROM mappa 
                    LEFT JOIN meteo_mappa ON (mappa.id_mappa = meteo_mappa.id_mappa) 
                    WHERE mappa.id='{$id}'
                    LIMIT 1
        ");
    }


    /*** LISTS ***/

    /**
     * @fn selectVento
     * @note Genera gli option per il vento
     * @return string
     */
    public function listWinds(): string
    {
        $option = "";
        foreach ($this->array_vento as $vento) {
            $option .= "<option>{$vento}</option>";
        }
        return $option;
    }

    /**** FUNCTIONS ****/

    /**
     * @fn
     * @note Chiamata webapi per recuperare il meteo di una città passando l'api key e la città di default
     */
    public function getWebApiWeather()
    {
        $city = Functions::get_constant('WEATHER_WEBAPI_CITY');
        $api = Functions::get_constant('WEATHER_WEBAPI');

        if(!empty($city) && !empty($api)) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://api.openweathermap.org/data/2.5/weather?q=' . $city . '&appid=' . $api . '&units=metric&lang=it',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_SSL_VERIFYPEER => false,
            ));
            $response = curl_exec($curl);
            $result = json_decode($response, true);
            if (curl_errno($curl)) {
                echo 'Error:' . curl_error($curl);
            }
            curl_close($curl);
            return ($result);
        }
        else{
            return [];
        }
    }

    /**
     * @fn
     * @note Restituisce il meteo dalle webapi
     */
    public function meteoWebApi()
    {
        $api = $this->getWebApiWeather();
        $wind = (Functions::get_constant('WEATHER_WIND') == 1) ? " - " . $this->wind($api['wind']['speed']) : '';
        $url = (Functions::get_constant('WEATHER_WEBAPI_ICON') == 0) ? "http://openweathermap.org/img/wn/" : "imgs/meteo/";
        $estensione = (Functions::get_constant('WEATHER_WEBAPI_ICON') == 0) ? "png" : Functions::get_constant('WEATHER_WEBAPI_FORMAT');
        $img = "<img src='" . $url . "" . $api['weather'][0]['icon'] . "." . $estensione . "' title='" . $api['weather'][0]['description'] . " ' >";
        $temp = Filters::int($api['main']['temp']) . "&deg;C";
        return $img . " " . $temp . " " . $wind;
    }

    /**
     * @fn
     * @note Chiamata webapi per recuperare il meteo di una città passando l'api key ed una città specifica
     */
    public function getWebApiWeatherChat($city)
    {

        $api = Functions::get_constant('WEATHER_WEBAPI');
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://api.openweathermap.org/data/2.5/weather?q=' . $city . '&appid=' . $api . '&units=metric&lang=it',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_SSL_VERIFYPEER => false,
        ));
        $response = curl_exec($curl);
        $result = json_decode($response, true);
        if (curl_errno($curl)) {
            echo 'Error:' . curl_error($curl);
        }
        curl_close($curl);
        return ($result);
    }

    /**
     * @fn
     * @note Restituisce il meteo dalle webapi di una città per una singola chat
     */
    public function meteoWebApiChat($citta)
    {
        $api = $this->getWebApiWeatherChat($citta);
        $wind = (Functions::get_constant('WEATHER_WIND') == 1) ? " - " . $this->wind($api['wind']['speed']) : '';
        $url = (Functions::get_constant('WEATHER_WEBAPI_ICON') == 0) ? "http://openweathermap.org/img/wn/" : "imgs/meteo/";
        $estensione = (Functions::get_constant('WEATHER_WEBAPI_ICON') == 0) ? "png" : Functions::get_constant('WEATHER_WEBAPI_FORMAT');
        $img = "<img src='" . $url . "" . $api['weather'][0]['icon'] . "." . $estensione . "' title='" . $api['weather'][0]['description'] . " ' >";
        $temp = Filters::int($api['main']['temp']) . "&deg;C";
        return $img . " " . $temp . " " . $wind;
    }

    /**
     * @fn
     * @note Salvataggio della pagina impostazioni
     */
    public function saveSetting($luna, $vento, $tipo, $api, $citta, $icone, $formato, $time)
    {
        DB::query("UPDATE   config SET val = '{$luna}' WHERE const_name='WEATHER_MOON'");
        DB::query("UPDATE   config SET val = '{$vento}' WHERE const_name='WEATHER_WIND'");
        DB::query("UPDATE   config SET val = '{$tipo}' WHERE const_name='WEATHER_TYPE'");
        DB::query("UPDATE   config SET val = '{$api}' WHERE const_name='WEATHER_WEBAPI'");
        DB::query("UPDATE   config SET val = '{$citta}' WHERE const_name='WEATHER_WEBAPI_CITY'");
        DB::query("UPDATE   config SET val = '{$icone}' WHERE const_name='WEATHER_WEBAPI_ICON'");
        DB::query("UPDATE   config SET val = '{$formato}' WHERE const_name='WEATHER_WEBAPI_FORMAT'");
        DB::query("UPDATE   config SET val = '{$time}' WHERE const_name='WEATHER_UPDATE'");
        switch ($tipo) {
            case '1': // stagioni
                $chat = DB::query("SELECT * FROM meteo_chat WHERE meteo !=''", "num_rows");
                $map = DB::query("SELECT * FROM meteo_mappa WHERE meteo !=''", "num_rows");
                if (!$chat) {
                    DB::query("DELETE FROM meteo_chat WHERE meteo=''");
                }
                if (!$map) {
                    DB::query("DELETE FROM meteo_mappa WHERE meteo=''");
                }
                break;
            default://webapi
                $chat = DB::query("SELECT * FROM meteo_chat WHERE citta !=''", "num_rows");
                $map = DB::query("SELECT * FROM meteo_mappa WHERE citta !=''", "num_rows");
                if (!$chat) {
                    DB::query("DELETE FROM meteo_chat WHERE citta=''");
                }
                if (!$map) {
                    DB::query("DELETE FROM meteo_mappa WHERE citta=''");
                }

        }


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

    public function saveChat($vento, $temperatura, $condizione, $citta, $id)
    {

        switch (Functions::get_constant('WEATHER_TYPE')) {
            case 1:
                if (empty($condizione)) {
                    DB::query("DELETE FROM meteo_chat WHERE id_chat='{$id}'");
                } else $check = "ok";
                $condizione = MeteoCondizioni::getInstance()->getCondition($condizione);
                $img = "<img src='" . $condizione['img'] . "' title='" . $condizione['nome'] . " ' >";
                $meteo = Filters::in($img . " " . $temperatura . "&deg;C");
                $citta = '';

                break;
            default:
                $citta = Filters::in($citta);
                $meteo = '';
                if (empty($citta)) {
                    DB::query("DELETE FROM meteo_chat WHERE id_chat='{$id}'");
                } else $check = "ok";
                break;
        }
        switch ($check) {
            case "ok":
                if ($this->getMeteoChat($id)) {
                    DB::query("UPDATE meteo_chat SET meteo = '{$meteo}', citta='{$citta}', vento='{$vento}' WHERE id_chat={$id}");
                } else {
                    DB::query("INSERT INTO meteo_chat (citta, meteo, id_chat, vento ) VALUES ('{$citta}',  '{$meteo}', {$id}, '{$vento}') ");
                }
            default:
                break;

        }


    }

    public function saveMap($meteo, $id)
    {
        switch (Functions::get_constant('WEATHER_TYPE')) {
            case 1:

                $stagioni = Filters::in($meteo);
                $citta = '';
                if (empty($stagioni)) {
                    DB::query("DELETE FROM meteo_mappa WHERE id_mappa='{$id}'");
                } else {
                    $check = "ok";
                }
                break;
            default:
                $citta = Filters::in($meteo);
                $meteo = '';
                if (empty($citta)) {
                    DB::query("DELETE FROM meteo_mappa WHERE id_mappa='{$id}'");
                } else {
                    $check = "ok";
                }
                break;
        }
        switch ($check) {
            case "ok":
                if ($this->getMeteoMappa($id)) {
                    DB::query("UPDATE meteo_mappa SET meteo = '{$meteo}', citta='{$citta}' , stagioni='{$stagioni}' WHERE id_mappa={$id}");
                } else {
                    DB::query("INSERT INTO meteo_mappa (citta, meteo, id_mappa, stagioni ) VALUES ('{$citta}',  '{$meteo}', {$id}, '{$stagioni}') ");
                }
            default:
                break;
        }
    }

    /**
     * @fn
     * @note Calcolo differenza di ore fra la data/ora attuale e quella salvata del meteo
     */
    public function dateDifference($date_1, $date_2, $differenceFormat = '%a')
    {
        $datetime1 = date_create($date_1);
        $datetime2 = date_create($date_2);
        $interval = date_diff($datetime1, $datetime2);
        return $interval->format($differenceFormat);
    }

    public function saveWeatherMap($meteo, $wind, $id)
    {
        $data = date("Y-m-d H:i");

        DB::query("UPDATE   config SET val = '{$data}' WHERE const_name='WEATHER_LAST_DATE'");
        DB::query("UPDATE   meteo_mappa SET vento = '{$wind}', meteo='{$meteo}' WHERE id_mappa='{$id}'");
    }

    /*** VENTO ****/

    /**
     * @fn selectVento
     * @note Genera gli option per il vento per edit, ritornando gli option selezionati e non
     * @return string
     */
    public function diffselectVento($array)
    {
        $option = "";
        foreach ($array as $v) {
            $option .= "<option selected>{$v}</option>";
        }
        $diff = array_diff($this->array_vento, $array);
        foreach ($diff as $vento) {
            $option .= "<option>{$vento}</option>";
        }
        return $option;
    }

    /**
     * @fn
     * @note Velocità del vento
     */
    public function wind($speed)
    {
        $velocita =
            // "switch" comparison for $count
            $speed <= 5 ? 'Debole' :
                ($speed <= 10 ? 'Moderato' :
                    ($speed <= 15 ? 'Forte' :
                        // default above 60
                        'Molto forte'));
        return $velocita;
    }

    /*** LUNA ***/

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

}
