<?php


class OnlineStatus extends BaseClass{

    /**
     * @var mixed
     * @var mixed
     */
    private $enabled,
        $login_refresh;

    public function __construct()
    {
        parent::__construct();
        $this->enabled = Functions::get_constant('ONLINE_STATUS_ENABLED');
        $this->login_refresh = Functions::get_constant('ONLINE_STATUS_LOGIN_REFRESH');
    }

    /*** GETTERS */

    public function isEnabled(){
        return $this->enabled;
    }

    public function refreshOnLogin(){
        return $this->login_refresh;
    }


    /*** CONTROLS ***/

    /**
     * @fn onlineStatusNeedRefresh
     * @note Controlla se lo status online richiede un refresh
     * @return bool
     */
    public function onlineStatusNeedRefresh(): bool
    {

        $pg_data = Personaggio::getPgData($this->me_id,'online_last_refresh,ora_entrata');
        $last_refresh = Filters::out($pg_data['online_last_refresh']);
        $entrata = Filters::out($pg_data['ora_entrata']);

        return (($last_refresh < $entrata) || empty($last_refresh) || ($last_refresh == 0) );

    }


    /*** PERMISSIONS ***/

    /**@fn manageStatusPermission
     * @note Controlla se si hanno i permessi per la gestione degli status online
     * @return bool
     */
    public function manageStatusPermission(): bool
    {
        return Permissions::permission('MANAGE_ONLINE_STATUS');
    }


    /*** TABLES HELPERS **/

    /**
     * @fn getStatus
     * @note Estrae i dati di uno stato
     * @param int $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getStatus(int $id, string $val = '*'){
        return DB::query("SELECT {$val} FROM online_status WHERE id='{$id}' LIMIT 1");
    }

    public function getStatysByType($type,$val = '*'){
        return DB::query("SELECT {$val} FROM online_status WHERE type='{$type}' ORDER by text",'result');
    }

    /**
     * @fn getStatusTypes
     * @note Estrae tutti i tipi di stato esistenti
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getStatusTypes(string $val = '*'){
        return DB::query("SELECT * FROM online_status_type WHERE 1 ORDER BY label",'result');
    }

    /**
     * @fn getStatusType
     * @note Estrae tutti i dati di uno stato specifico
     * @param int $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getStatusType(int $id,string $val = '*'){
        return DB::query("SELECT {$val} FROM online_status_type WHERE id='{$id}' LIMIT 1");
    }

    public function getPgStatus($pg,$type,$last_refresh = '',$val = '*'){
        $last_refresh = (!empty($last_refresh)) ? " AND last_refresh >= '{$last_refresh}' " : '';
        return DB::query("SELECT {$val} FROM personaggio_online_status WHERE personaggio='{$pg}' AND type='{$type}' {$last_refresh} LIMIT 1");
    }

    /*** AJAX ***/

    public function getAjaxStatusData($post){

        if($this->manageStatusPermission()){

            $id = Filters::int($post['id']);

            $data = $this->getStatus($id);

            return [
                'type' => Filters::int($data['type']),
                'text' => Filters::out($data['text'])
            ];
        }
    }

    public function getAjaxStatusTypeData($post){

        if($this->manageStatusPermission()){

            $id = Filters::int($post['id']);

            $data = $this->getStatusType($id);

            return [
                'label' => Filters::out($data['label']),
                'request' => Filters::out($data['request'])
            ];
        }
    }

    /**** RENDER ****/


    /**
     * @fn renderOnlineStatusOptions
     * @note Render delle radio di scelta dello status online
     * @return string
     */
    public function renderOnlineStatusOptions(): string
    {

        $html = '';
        $types = $this->getStatusTypes();

        foreach ($types as $type){
            $type_id = Filters::int($type['id']);
            $type_request = Filters::out($type['request']);

            $list = $this->getStatysByType($type_id);

            if(DB::rowsNumber($list) > 0) {

                $html .= "<div class='subtitle'>{$type_request}</div>";
                $html .= "<ul>";

                foreach ($list as $row) {
                    $id = Filters::int($row['id']);
                    $name = Filters::out($row['text']);

                    $html .= "<li>";
                    $html .= "<input type='radio' name='online_status[online_{$type_id}]' value='{$id}'>{$name}</input>";
                    $html .= "</li>";
                }

                $html .= "</ul>";

            }
        }

        return $html;
    }

    /**
     * @fn renderManageStatusList
     * @note Crea la lista select dei tipi
     * @return string
     */
    public function renderStatusTypeList(): string
    {
        $html = '';
        $types = $this->getStatusTypes();

        foreach ($types as $type) {
            $type_id = Filters::int($type['id']);
            $type_label = Filters::out($type['label']);

            $html .= "<option value='{$type_id}'>{$type_label}</option>";

        }

        return $html;
    }

    /**
     * @fn renderStatusList
     * @note Render della lista degli stati disponibili, divisi per tipo
     * @return string
     */
    public function renderStatusList(): string
    {

        $html = '';
        $types = $this->getStatusTypes();

        foreach ($types as $type){
            $type_id = Filters::int($type['id']);
            $type_label = Filters::out($type['label']);

            $list = $this->getStatysByType($type_id);

            $html .= "<optgroup label='{$type_label}'>";

            foreach ($list as $row){
                $id = Filters::int($row['id']);
                $name = Filters::out($row['text']);

                $html .= "<option value='{$id}'>{$name}</option>";
            }

            $html .= "</optgroup>";


        }

        return $html;

    }

    /**
     * @fn renderStatusOnline
     * @note Visualizza le risposte nello status online
     * @param $pg
     * @return string
     */
    public function renderStatusOnline($pg){
        $html = '';
        $pg = Filters::int($pg);
        $pg_data = Personaggio::getPgData($pg,'ora_entrata');
        $last_login = ($this->refreshOnLogin()) ? Filters::out($pg_data['ora_entrata']) : '';
        $types = $this->getStatusTypes();


        foreach ($types as $type){
            $type_id = Filters::int($type['id']);
            $type_label = Filters::out($type['label']);

            $list = $this->getPgStatus($pg,$type_id,$last_login,'value');
            if(!empty($list['value'])) {
                $status_data = $this->getStatus(Filters::int($list['value']), 'text');
                $status_text = Filters::out($status_data['text']);
            }
            else{
                $status_text = 'Non definito';
            }

            $html .= " <span>{$type_label} : {$status_text}</span> | ";
        }

        return $html;
    }

    /*** FUNCTIONS ***/

    /**
     * @fn setOnlineStatus
     * @note Update dello status online
     * @param array $post
     * @return array
     */
    public function setOnlineStatus(array $post): array
    {
        if($this->isEnabled()){

            $online_status = $post['online_status'];

            foreach ($online_status as $status_id => $value) {

                $value = Filters::int($value);
                $split = explode('_',$status_id);
                $type = Filters::int($split[1]);

                $contr = $this->getPgStatus($this->me_id,$type);

                if(isset($contr['id'])){
                    DB::query("UPDATE personaggio_online_status SET value='{$value}',last_refresh=NOW() WHERE personaggio='{$this->me_id}' AND type='{$type}' LIMIT 1");
                } else {
                    DB::query("INSERT INTO personaggio_online_status(personaggio, type, value) VALUES('{$this->me_id}','{$type}','{$value}') ");
                }

            }

            DB::query("UPDATE personaggio SET online_last_refresh=NOW() WHERE id='{$this->me_id}' LIMIT 1");

            return ['response'=>true,'mex'=>'Status online settato correttamente.'];
        }
        else{
            return ['response'=>false,'mex'=>'Errore, funzione non disponibile.'];
        }

    }


    /*** MANAGEMENT FUNCTIONS **/

    /**
     * @fn insertStatus
     * @note Funzione di inserimento di un nuovo stato
     * @param array $post
     * @return array
     */
    public function insertStatus(array $post): array
    {

        if($this->manageStatusPermission()){

            $type = Filters::int($post['type']);
            $text = Filters::in($post['text']);

            DB::query("INSERT INTO online_status(type, text) VALUES ('{$type}','{$text}')");

            $resp = ['response'=>true,'mex'=>'Stato inserito correttamente.'];

        }else{
            $resp = ['response'=>false,'mex'=>'Permesso negato.'];
        }

        return $resp;

    }

    /**
     * @fn editStatus
     * @note Funzione di modifica di uno stato
     * @param array $post
     * @return array
     */
    public function editStatus(array $post): array
    {

        if($this->manageStatusPermission()){

            $id = Filters::int($post['id']);
            $type = Filters::int($post['type']);
            $text = Filters::in($post['text']);

            DB::query("UPDATE online_status SET type='{$type}',text='{$text}' WHERE id='{$id}' LIMIT 1");

            $resp = ['response'=>true,'mex'=>'Stato modificato correttamente.'];

        }else{
            $resp = ['response'=>false,'mex'=>'Permesso negato.'];
        }

        return $resp;

    }

    /**
     * @fn editStatus
     * @note Funzione di eliminazione di uno stato
     * @param array $post
     * @return array
     */
    public function deleteStatus(array $post): array
    {

        if($this->manageStatusPermission()){

            $id = Filters::int($post['id']);

            DB::query("DELETE FROM online_status WHERE id='{$id}'");

            $resp = ['response'=>true,'mex'=>'Stato eliminato correttamente.'];

        }else{
            $resp = ['response'=>false,'mex'=>'Permesso negato.'];
        }

        return $resp;

    }


    /**
     * @fn insertStatusType
     * @note Funzione di inserimento di un nuovo tipo di stato
     * @param array $post
     * @return array
     */
    public function insertStatusType(array $post): array
    {

        if($this->manageStatusPermission()){

            $label = Filters::in($post['label']);
            $request = Filters::in($post['request']);

            DB::query("INSERT INTO online_status_type(label, request) VALUES ('{$label}','{$request}')");

            $resp = ['response'=>true,'mex'=>'Tipo di stato inserito correttamente.'];

        }else{
            $resp = ['response'=>false,'mex'=>'Permesso negato.'];
        }

        return $resp;

    }

    /**
     * @fn editStatusType
     * @note Funzione di modifica di un tipo di stato
     * @param array $post
     * @return array
     */
    public function editStatusType(array $post): array
    {

        if($this->manageStatusPermission()){

            $id = Filters::int($post['id']);
            $label = Filters::in($post['label']);
            $request = Filters::in($post['request']);

            DB::query("UPDATE online_status_type SET label='{$label}',request='{$request}' WHERE id='{$id}' LIMIT 1");

            $resp = ['response'=>true,'mex'=>'Tipo di stato modificato correttamente.'];

        }else{
            $resp = ['response'=>false,'mex'=>'Permesso negato.'];
        }

        return $resp;

    }

    /**
     * @fn deleteStatusType
     * @note Funzione di eliminazione di un tipo di stato
     * @param array $post
     * @return array
     */
    public function deleteStatusType(array $post): array
    {

        if($this->manageStatusPermission()){

            $id = Filters::int($post['id']);

            DB::query("DELETE FROM online_status_type WHERE id='{$id}'");
            DB::query("DELETE FROM online_status WHERE type='{$id}'");

            $resp = ['response'=>true,'mex'=>'Tipo di stato eliminato correttamente.'];

        }else{
            $resp = ['response'=>false,'mex'=>'Permesso negato.'];
        }

        return $resp;

    }
}