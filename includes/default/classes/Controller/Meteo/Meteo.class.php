<?php

/**
 * @class Stagioni
 * @note Classe per la gestione delle stagioni
 * @required PHP 7.1+
 */
class Meteo extends BaseClass
{
    private bool
        $moon_enabled,
        $weather_wind,
        $weather_webapi_icon,
        $weather_webapi;

    private string
        $weather_webapi_city,
        $weather_webapi_icon_format;

    private mixed
        $weather_last_wind,
        $weather_last_condition,
        $weather_last_temp,
        $weather_last_img;

    protected int
        $weather_season,
        $weather_update_range;

    protected string
        $weather_last_date;

    /**
     * @fn __construct
     * @note Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->moon_enabled = Functions::get_constant('WEATHER_MOON');
        $this->weather_season = Functions::get_constant('WEATHER_SEASON');
        $this->weather_webapi = Functions::get_constant('WEATHER_WEBAPI');
        $this->weather_webapi_city = Functions::get_constant('WEATHER_WEBAPI_CITY');
        $this->weather_webapi_icon = Functions::get_constant('WEATHER_WEBAPI_ICON');
        $this->weather_webapi_icon_format = Functions::get_constant('WEATHER_WEBAPI_FORMAT');
        $this->weather_wind = Functions::get_constant('WEATHER_WIND');
        $this->weather_last_date = Functions::get_constant('WEATHER_LAST_DATE');
        $this->weather_update_range = Functions::get_constant('WEATHER_UPDATE');
        $this->weather_last_wind = Functions::get_constant('WEATHER_LAST_WIND');
        $this->weather_last_condition = Functions::get_constant('WEATHER_LAST_CONDITION');
        $this->weather_last_temp = Functions::get_constant('WEATHER_LAST_TEMP');
        $this->weather_last_img = Functions::get_constant('WEATHER_LAST_IMG');
    }

    /*** CONTROLS ****/

    /**
     * @fn activeMoon
     * @note Controlla se la luna è attiva
     * @return bool
     */
    public function activeMoon(): bool
    {
        return $this->moon_enabled;
    }

    /**
     * @fn activeSeason
     * @note Controlla se le stagioni sono attive
     * @return bool
     */
    public function activeSeason(): bool
    {
        return $this->weather_season;
    }

    /**
     * @fn activeWind
     * @note Controlla se il vento è attivo
     * @return bool
     */
    public function activeWind(): bool
    {
        return $this->weather_wind;
    }

    /**
     * @fn activeWebApi
     * @note Controlla se il meteo viene preso dalle web api
     * @return bool
     */
    public function activeWebApi(): bool
    {
        return $this->weather_webapi;
    }

    /**
     * @fn weatherNeedRefresh
     * @note Controlla se il meteo GLOBALE necessita di essere aggiornato
     * @return bool
     */
    protected function weatherNeedRefresh(): bool
    {
        $date = date('Y-m-d H:i:s',strtotime($this->weather_last_date));
        return empty($this->weather_last_date) ||
            (CarbonWrapper::DatesDifferenceHours($date, CarbonWrapper::getNow()) > $this->weather_update_range);
    }

    /**
     * @fn weatherChatNeedRefresh
     * @note Controlla se il meteo della singola CHAT necessita di essere aggiornato
     * @param int $id
     * @return bool
     * @throws Throwable
     */
    protected function weatherChatNeedRefresh(int $id): bool
    {
        $chat_data = Chat::getInstance()->getChatData($id, 'meteo_fisso');
        $fisso = Filters::bool($chat_data['meteo_fisso']);

        // Se è fisso non lo aggiorno a prescindere
        if ( $fisso ) {
            return false;
        }

        $chat_meteo = $this->getMeteoChat($id);

        // Se non esiste un meteo impostato, lo aggiorno a prescindere
        if ( empty($chat_meteo) ) {
            return true;
        }

        $last_update = Filters::out($chat_meteo['updated_at']);

        return empty($last_update) ||
            (CarbonWrapper::DatesDifferenceHours($last_update, CarbonWrapper::getNow()) > $this->weather_update_range);
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

    /**
     * @fn permissionEditChat
     * @note Permessi per modifica meteo in chat
     * @return bool
     * @throws Throwable
     */
    public function permissionEditChat(): bool
    {
        return $this->permissionManageWeather() && $this->activeSeason() && (Personaggio::getPgLocation() > 0);
    }

    /**** QUERY ***/

    /**
     * @fn getMeteoChat
     * @note Estrae il meteo per una chat singola
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getMeteoChat(int $id, string $val = 'meteo_chat.*,mappa.meteo_city'): DBQueryInterface
    {
        return DB::queryStmt(
            "SELECT {$val} FROM meteo_chat LEFT JOIN mappa ON mappa.id = meteo_chat.id_chat WHERE id_chat=:id LIMIT 1",
            ['id' => $id]
        );
    }

    /**
     * @fn getMeteoGlobal
     * @note Estrae il meteo globale attivo
     * @return array
     */
    public function getMeteoGlobal(): array
    {

        $meteo = $this->weather_last_condition;
        $vento = $this->weather_last_wind;
        $temp = $this->weather_last_temp;
        $img = $this->weather_last_img;

        return [
            'meteo' => $meteo,
            'temp' => $temp,
            'vento' => $vento,
            'img' => $img,
        ];
    }

    /**** FUNCTIONS ****/

    /**
     * @fn createMeteoData
     * @note Creazione dati per meteo
     * @return array
     * @throws Throwable
     */
    public function createMeteoData(): array
    {

        $data = [];

        if ( $this->activeMoon() ) {
            $data['moon'] = $this->lunarPhase();
        }

        $data['meteo'] = $this->getActualMeteo();

        return $data;
    }

    /**
     * @fn calcSeasonMeteo
     * @note Calcola il meteo per la posizione attuale
     * @return array
     * @throws Throwable
     */
    public function getActualMeteo(): array
    {
        $pg_chat = Personaggio::getPgLocation();
        $meteo_chat = $this->getMeteoChat($pg_chat)->getData()[0];

        // SE per la chat specifica è settato un meteo o se è settato per la mappa
        $meteo_data = !empty($meteo_chat) ? $meteo_chat : MeteoStagioni::getInstance()->getMeteoGlobal();

        // Se non esiste neanche quello globale, lo genero
        if ( empty(($meteo_data['meteo'])) ) {
            $meteo_data = $this->generateGlobalWeather();
        }

        if ( !empty($meteo_data['meteo']) ) {
            return $meteo_data;
        } else {
            die('Impossibile derivare il meteo');
        }
    }

    /**
     * @fn refreshWeather
     * @note Funzione che aggiorna il meteo della chat, della mappa e del
     * @return void
     * @throws Throwable
     */
    public function refreshWeather(): void
    {

        $chat = Personaggio::getPgLocation();

        if ( $this->weatherNeedRefresh() ) {
            $this->generateGlobalWeather();
        }

        if ( ($chat > 0) && $this->weatherChatNeedRefresh($chat) ) {
            $this->generateWeatherChat($chat);
        }
    }

    /**
     * @fn
     * @note Velocità del vento
     * @param int $speed
     * @return string
     */
    public function windValToText(int $speed): string
    {
        return match (true) {
            ($speed <= 5) => 'Debole',
            ($speed <= 10) => 'Moderato',
            ($speed <= 15) => 'Forte',
            default => 'Molto forte'
        };
    }

    /**** GEN GLOBAL ***/

    /**
     * @fn generateGlobalWeather
     * @note Funzione contenitore generazione meteo globale
     * @return array
     * @throws Throwable
     */
    public function generateGlobalWeather(): array
    {

        if ( !$this->activeWebApi() ) {
            $data = $this->generateGlobalWeatherFromSeason();
        } else {
            $data = $this->generateGlobalWeatherFromApi();
        }

        $this->saveWeather($data['meteo'], $data['meteo'], $data['temp'], $data['img']);

        return $data;
    }

    /**
     * @fn generateGlobalWeather
     * @note Genera il meteo globale dalle stagioni
     * @return array
     * @throws Throwable
     */
    public function generateGlobalWeatherFromSeason(): array
    {
        $stagione = MeteoStagioni::getInstance()->getCurrentSeason();
        $vento = 0;

        if ( empty($stagione) ) {
            die("Verifica di aver assegnato correttamente le stagioni alla mappa o di aver assicurato il range data inizio e fine nelle stagioni poiché non vi sono stagioni selezionabili per questo periodo dell'anno");
        }

        $meteo = $this->generateCondition($stagione);

        if ( empty($meteo) ) {
            die('Impossibile derivare una condizione. Assicurarsi che ci sia almeno una condizione per la stagione selezionata e che il totale delle percentuali sia il 100%.');
        }

        if ( $this->activeWind() ) {
            $vento = $this->generateWind($meteo['id']);
        }

        $temp = $this->generateTemp($stagione);

        return [
            'meteo' => $meteo['condizione'],
            'vento' => $vento,
            'temp' => $temp,
            'img' => $meteo['img'],
        ];
    }

    /**
     * @fn generateGlobalWeatherFromApi
     * @note Genera il meteo della chat dalle api
     * @return array
     */
    public function generateGlobalWeatherFromApi(): array
    {
        return $this->meteoWebApi($this->weather_webapi_city);
    }

    /**** GEN METEO CHAT STAGIONE ***/

    /**
     * @fn  generateWeatherChat
     * @note Funzione contenitore generazione meteo
     * @param int $id
     * @return void
     * @throws Throwable
     */
    public function generateWeatherChat(int $id): void
    {
        if ( !$this->activeWebApi() ) {
            $this->generateWeatherChatFromSeason($id);
        } else {
            $this->generateWeatherChatFromApi($id);
        }
    }

    /**
     * @fn calcWeatherFromSeason
     * @note Calcola il meteo dalla stagione
     * @param int $id
     * @return array
     * @throws Throwable
     */
    public function generateWeatherChatFromSeason(int $id): array
    {
        $stagione = MeteoStagioni::getInstance()->getCurrentSeason();
        $vento = 0;

        if ( empty($stagione) ) {
            die("Verifica di aver assegnato correttamente le stagioni alla mappa o di aver assicurato il range data inizio e fine nelle stagioni poiché non vi sono stagioni selezionabili per questo periodo dell'anno");
        }

        $meteo = $this->generateCondition($stagione);

        if ( empty($meteo) ) {
            die('Impossibile derivare una condizione. Assicurarsi che ci sia almeno una condizione per la stagione selezionata e che il totale delle percentuali sia il 100%.');
        }

        if ( $this->activeWind() ) {
            $vento = $this->generateWind($meteo['id']);
        }

        $temp = $this->generateTemp($stagione);

        $this->saveWeatherChat($meteo['condizione'], $vento, $temp, $meteo['img'], $id);

        return [
            'meteo' => $meteo['condizione'],
            'vento' => $vento,
            'temp' => $temp,
            'img' => $meteo['img'],
        ];
    }

    /**
     * @fn generateCondition
     * @note Calcola le condizioni meteo di una stagione
     * @param array $stagione
     * @return array
     * @throws Throwable
     */
    public function generateCondition(array $stagione): array
    {
        $stagione_id = Filters::int($stagione['id']);
        $meteo = false;
        $img = false;
        $id = false;

        $condizioni = MeteoStagioni::getInstance()->getAllSeasonCondition($stagione_id);
        shuffle($condizioni);

        $rand = rand(0, 100);
        $percentage = 0;
        foreach ( $condizioni as $condizione ) {
            $percentage += Filters::int($condizione['percentuale']);

            if ( ($rand <= $percentage) ) {
                $id = Filters::int($condizione['id']);
                $meteo = Filters::out($condizione['nome']);
                $img = Filters::out($condizione['img']);
                break;
            }
        }

        return [
            'id' => $id,
            'condizione' => $meteo,
            'img' => $img,
        ];
    }

    /**
     * @fn generateWind
     * @note Calcola il vento di una chat
     * @param int $id
     * @return string
     * @throws Throwable
     */
    public function generateWind(int $id): string
    {
        $condizione = MeteoCondizioni::getInstance()->getCondition($id);
        $venti = explode(",", $condizione['vento']);
        shuffle($venti);
        $vento = Filters::int($venti[0]);
        $vento_data = MeteoVenti::getInstance()->getWind($vento);
        return Filters::out($vento_data['nome']);
    }

    /**
     * @fn generateTemp
     * @note Calcola la temperatura di una stagione
     * @param array $stagione
     * @return int
     */
    public function generateTemp(array $stagione): int
    {
        $temp = rand($stagione['minima'], $stagione['massima']);
        return Filters::int($temp);
    }

    /*** GEN WEB API ***/

    /**
     * @fn calcWebApiMeteo
     * @note Calcola il meteo dalle web api
     * @param int $id
     * @return array
     * @throws Throwable
     */
    public
    function generateWeatherChatFromApi(int $id): array
    {
        $meteo_chat = $this->getMeteoChat($id);
        $city = Filters::out($meteo_chat['meteo_city']);
        return $this->meteoWebApi($city);
    }

    /**
     * @fn getWebApiWeather
     * @note Chiamata webapi per recuperare il meteo di una città passando l'api key e la città di default
     * @param string $city
     * @return array
     */
    public
    function getWebApiWeather(string $city): array
    {
        $api = Functions::get_constant('WEATHER_WEBAPIKEY');

        if ( !empty($city) && !empty($api) ) {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => 'http://api.openweathermap.org/data/2.5/weather?q=' . $city . '&appid=' . $api . '&units=metric&lang=it',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $response = curl_exec($curl);
            $result = json_decode($response, true);
            if ( curl_errno($curl) ) {
                echo 'Error:' . curl_error($curl);
            }
            curl_close($curl);
            return ($result);
        } else {
            die('Città non selezionata per il meteo o apikey mancante');
        }
    }

    /**
     * @fn meteoWebApi
     * @note Restituisce il meteo dalle webapi di una città per una singola chat
     * @param string $city
     * @return array
     */
    public
    function meteoWebApi(string $city = ''): array
    {

        if ( empty($city) ) {
            $city = Functions::get_constant('WEATHER_WEBAPI_CITY');
        }

        $api = $this->getWebApiWeather($city);

        $weather = $api['weather'][0];
        $icon = $weather['icon'];
        $meteo = $weather['description'];
        $vento = ($this->activeWind()) ? "{$this->windValToText($api['wind']['speed'])}" : '';
        $temp = Filters::int($api['main']['temp']);

        $img = ($this->weather_webapi_icon) ? "http://openweathermap.org/img/wn/{$icon}" : "imgs/meteo/{$icon}";
        $img .= (!$this->weather_webapi_icon) ? ".png" : $this->weather_webapi_icon_format;

        return [
            'meteo' => $meteo,
            'vento' => $vento,
            'temp' => $temp,
            'img' => $img,
        ];
    }

    /** GESTIONE */

    /**
     * @fn
     * @note Salvataggio del meteo per la stagione
     * @param string $meteo
     * @param string $wind
     * @param string $temp
     * @param string $img
     * @return void
     */
    public
    function saveWeather(string $meteo, string $wind, string $temp, string $img): void
    {
        Functions::set_constant('WEATHER_LAST_CONDITION', $meteo);
        Functions::set_constant('WEATHER_LAST_DATE', date("Y-m-d H:i"));
        Functions::set_constant('WEATHER_LAST_WIND', $wind);
        Functions::set_constant('WEATHER_LAST_TEMP', $temp);
        Functions::set_constant('WEATHER_LAST_IMG', $img);
    }

    /**
     * @fn saveWeatherChat
     * @note Save weather chat
     * @param string $meteo
     * @param string $wind
     * @param int $temp
     * @param string $img
     * @param int $id
     * @return void
     * @throws Throwable
     */
    public
    function saveWeatherChat(string $meteo, string $wind, int $temp, string $img, int $id): void
    {
        DB::queryStmt("UPDATE config SET val = :data WHERE const_name='WEATHER_LAST_DATE'", ['data' => CarbonWrapper::getNow("Y-m-d H:i")]);
        DB::queryStmt("DELETE FROM meteo_chat WHERE id_chat=:id", ['id' => $id]);
        DB::queryStmt(
            "INSERT INTO meteo_chat(vento,meteo,temp,img,id_chat,updated_at) VALUES(:wind,:meteo,:temp,:img,:id,NOW())",
            [
                "wind" => $wind,
                "meteo" => $meteo,
                "temp" => $temp,
                "img" => $img,
                "id" => $id,
            ]
        );
    }

    /**
     * @fn saveChat
     * @note Save chat meteo
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public
    function saveChat(array $post): array
    {

        if ( $this->permissionManageWeather() && $this->activeSeason() ) {
            $id = Filters::int($post['chat']);
            $vento = Filters::in($post['vento']);
            $temperatura = Filters::int($post['temp']);
            $meteo = Filters::in($post['condizione']);
            $img = Filters::in($post['img']);

            $this->saveWeatherChat($meteo, $vento, $temperatura, $img, $id);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Condizioni meteo chat modificate.',
                'swal_type' => 'success',
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error',
            ];
        }

    }

    /*** LUNA ***/

    /**
     * @fn
     * @note Fasi lunari
     * @return array
     */
    public
    function lunarPhase(): array
    {
        # Inizializzo dati necessari
        $year = date('Y');
        $month = date('n');
        $days = date('j');

        # Se è prima di aprile sottraggo un anno
        if ( $month < 4 ) {
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
        if ( $phase == 8 ) {
            $phase = 0;
        }
        # Creo gli array delle fasi
        $phase_array = ['nuova', 'crescente', 'primo-quarto', 'gibbosa-crescente', 'piena', 'gibbosa-calante', 'ultimo-quarto', 'calante'];
        $phase_title = ['Nuova', 'Crescente', 'Primo Quarto', 'Gibbosa crescente', 'Piena', 'Gibbosa calante', 'Ultimo quarto', 'Calante'];
        # Estraggo e ritorno la fase calcolata

        $img = Router::getImgsDir() . "luna/{$phase_array[$phase]}.png";

        return [
            'Img' => $img,
            'Title' => $phase_title[$phase],
        ];
    }

}
