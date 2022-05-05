<?php

/**
 * @class Stagioni
 * @note Classe per la gestione delle stagioni
 * @required PHP 7.1+
 */
class Meteo extends BaseClass
{
    private
        $array_vento,
        $moon_abilitated,
        $weather_webapi;

    /**
     * @fn __construct
     * @note Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->array_vento = array("Assente", "Brezza", "Brezza intensa", "Vento Forte", "Burrasca"); # TODO Spostare in db
        $this->moon_abilitated = Functions::get_constant('WEATHER_MOON');
        $this->weather_season = Functions::get_constant('WEATHER_SEASON');
        $this->weather_webapi = Functions::get_constant('WEATHER_WEBAPI');
    }

    /*** CONTROLS ****/

    /**
     * @fn activeMoon
     * @note Controlla se la luna e' attiva
     * @return bool
     */
    public function activeMoon():bool
    {
        return $this->moon_abilitated;
    }

    /**
     * @fn activeSeason
     * @note Controlla se le stagioni sono attive
     * @return bool
     */
    public function activeSeason():bool
    {
        return $this->weather_season;
    }

    /**
     * @fn activeWebApi
     * @note Controlla se il meteo viene preso dalle web api
     * @return bool
     */
    public function activeWebApi():bool
    {
        return $this->weather_webapi;
    }

    /**** PERMISSION ****/

    /**
     * @fn permissionManageWeather
     * @note Controlla se si hanno i permessi per gestire il meteo
     * @return bool
     */
    public function permissionManageWeather(): bool
    {
        return Permissions::permission('MANAGE_WEATHER');
    }

    /**** QUERY ***/

    /**
     * @fn getMeteoChat
     * @note Estrae il meteo per una chat singola
     * @param int $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getMeteoChat(int $id, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM meteo_chat WHERE id_chat='{$id}' LIMIT 1");
    }

    /**
     * @fn getMeteoMappa
     * @note Estrae il meteo per una mappa singola
     * @param int $id
     * @return bool|int|mixed|string
     */
    public function getMeteoMappa(int $id)
    {
        $id = Filters::int($id);
        return DB::query("SELECT * FROM meteo_mappa WHERE id_mappa='{$id}'", 'query');
    }

    /**
     * @fn getMeteoMappaChat
     * @note Estrae il meteo della mappa di appartenenza di una chat
     * @param int $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getMeteoMappaChat(int $id, string $val = 'meteo_mappa.*')
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
     * @fn createMeteoData
     * @note Creazione dati per meteo
     * @return array
     */
    public function createMeteoData():array
    {

        $data = [];

        if ($this->activeMoon()) {
            $data['moon'] = $this->lunarPhase();
        }

        if ($this->activeSeason()) {
            $data['meteo'] = $this->calcSeasonMeteo();
        } else if ($this->activeWebApi()) {//webapi
            $data['meteo'] = $this->calcWebApiMeteo();
        }

        return $data;
    }

    /**
     * @fn calcSeasonMeteo
     * @note Calcola il meteo per una stagione
     * @return array
     */
    public function calcSeasonMeteo():array
    {

        $data = [];

        // SE per la chat specifica e' settato un meteo
        if (!empty($meteo = $this->getMeteoChat(Personaggio::getPgLocation($this->me_id)))) {
            $data['meteo'] = $meteo['meteo'];
            if (Functions::get_constant('WEATHER_WIND') == 1) {
                $data['wind'] = $meteo['vento'];
            }
        } // altrimenti estraggo quello della mappa
        else if (!empty($meteo = $this->getMeteoMappa(Personaggio::getPgMap($this->me_id)))) {//meteo della mappa
            $meteo_map = MeteoStagioni::getInstance()->meteoMappaSeason($meteo['stagioni'], $_SESSION['mappa']);

            $data['meteo'] = $meteo_map['meteo'];
            if (Functions::get_constant('WEATHER_WIND') == 1) {
                $data['wind'] = $meteo_map['vento'];
            }
        } // Altrimenti lo creo da quello della stagione
        else {
            $data = MeteoStagioni::getInstance()->meteoSeason();
        }

        return $data;
    }

    /*** FUNCTIONS WEB API ***/

    /**
     * @fn calcWebApiMeteo
     * @note Calcola il meteo dalle web api
     * @return array
     */
    public function calcWebApiMeteo():array
    {
        if (!empty($meteo = $this->getMeteoChat(Personaggio::getPgLocation($this->me_id)))) {//Controllo se è presente un meteo per la città
            return $this->meteoWebApi($meteo['citta']);
        } else if (!empty($meteo = $this->getMeteoMappa(Personaggio::getPgMap($this->me_id)))) {//meteo della mappa
            return $this->meteoWebApi($meteo['citta']);
        } else {
            return $this->meteoWebApi();
        }
    }

    /**
     * @fn getWebApiWeather
     * @note Chiamata webapi per recuperare il meteo di una città passando l'api key e la città di default
     * @param string $city
     * @return array
     */
    public function getWebApiWeather(string $city = ''):array
    {
        if (empty($city)) {
            $city = Functions::get_constant('WEATHER_WEBAPI_CITY');
        }

        $api = Functions::get_constant('WEATHER_WEBAPIKEY');

        if (!empty($city) && !empty($api)) {
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
        } else {
            return [];
        }
    }

    /**
     * @fn meteoWebApi
     * @note Restituisce il meteo dalle webapi di una città per una singola chat
     * @param string $citta
     * @return array
     */
    public function meteoWebApi(string $citta = ''): array
    {

        $data = [];

        $api = $this->getWebApiWeather($citta);

        $data['img'] = ((Functions::get_constant('WEATHER_WEBAPI_ICON') == 1) ? "http://openweathermap.org/img/wn/" : "imgs/meteo/");
        $data['img'] .= $api['weather'][0]['icon'];
        $data['img'] .= (Functions::get_constant('WEATHER_WEBAPI_ICON') == 0) ? ".png" : Functions::get_constant('WEATHER_WEBAPI_FORMAT');
        $data['meteo'] = Filters::int($api['main']['temp']);
        $data['vento'] = (Functions::get_constant('WEATHER_WIND') == 1) ? " - " . $this->wind($api['wind']['speed']) : '';

        return $data;
    }


    /** GESTIONE */

    /**
     * @fn
     * @note Salvataggio della pagina impostazioni
     * @param array $post
     * @return void
     */
    public function saveSetting(array $post):void
    {

        $luna = Filters::in( $post['moon']);
        $vento = Filters::in( $post['wind']);
        $tipo = Filters::in( $post['type']);
        $api = Filters::in( $post['webapi_key']);
        $citta = Filters::in( $post['webapi_city']);
        $icone = Filters::in( $post['webapi_icon']);
        $formato=Filters::in( $post['webapi_format']);
        $time=Filters::in( $post['weather_time']);

        DB::query("UPDATE config SET val = '{$luna}' WHERE const_name='WEATHER_MOON'");
        DB::query("UPDATE config SET val = '{$vento}' WHERE const_name='WEATHER_WIND'");
        DB::query("UPDATE config SET val = '{$tipo}' WHERE const_name='WEATHER_TYPE'");
        DB::query("UPDATE config SET val = '{$api}' WHERE const_name='WEATHER_WEBAPI'");
        DB::query("UPDATE config SET val = '{$citta}' WHERE const_name='WEATHER_WEBAPI_CITY'");
        DB::query("UPDATE config SET val = '{$icone}' WHERE const_name='WEATHER_WEBAPI_ICON'");
        DB::query("UPDATE config SET val = '{$formato}' WHERE const_name='WEATHER_WEBAPI_FORMAT'");
        DB::query("UPDATE config SET val = '{$time}' WHERE const_name='WEATHER_UPDATE'");

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
     * @param string $meteo
     * @param string $wind
     * @return void
     */
    public function saveWeather(string $meteo, string $wind):void
    {
        $data = date("Y-m-d H:i");
        DB::query("UPDATE config SET val = '{$meteo}' WHERE const_name='WEATHER_LAST'");
        DB::query("UPDATE config SET val = '{$data}' WHERE const_name='WEATHER_LAST_DATE'");
        DB::query("UPDATE config SET val = '{$wind}' WHERE const_name='WEATHER_LAST_WIND'");
    }

    /**
     * @fn saveChat
     * @note Save chat meteo
     * @param array $post
     * @param int $id
     * @return void
     */
    public function saveChat(array $post, int $id):void
    {

        $vento = Filters::in( $post['vento']);
        $temperatura = Filters::in( $post['temperatura']);
        $condizione = Filters::in( $post['condizione']);
        $citta = Filters::in( $post['webapi_city']);

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

    /**
     * @fn saveMap
     * @note Save map meteo
     * @param string $meteo
     * @param int $id
     * @return void
     */
    public function saveMap(string $meteo, int $id):void
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
     * @fn saveWeatherMap
     * @note Save weather map
     * @param string $meteo
     * @param string $wind
     * @param int $id
     * @return void
     */
    public function saveWeatherMap(string $meteo, string $wind, int $id):void
    {
        $data = date("Y-m-d H:i");

        DB::query("UPDATE   config SET val = '{$data}' WHERE const_name='WEATHER_LAST_DATE'");
        DB::query("UPDATE   meteo_mappa SET vento = '{$wind}', meteo='{$meteo}' WHERE id_mappa='{$id}'");
    }

    /*** VENTO ****/

    /**
     * @fn selectVento
     * @note Genera gli option per il vento per edit, ritornando gli option selezionati e non
     * @param array $array
     * @return string
     */
    public function diffselectVento(array $array): string
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
     * @param int $speed
     * @return string
     */
    public function wind(int $speed): string
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
     * @return array
     */
    public function lunarPhase(): array
    {
        $theme = gdrcd_filter('out', $PARAMETERS['themes']['current_theme']);
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

        $img = "themes/{$theme}/imgs/luna/{$phase_array[$phase]}.png";

        return [
            'Img' => $img,
            'Title' => $phase_title[$phase]
        ];
    }

}
