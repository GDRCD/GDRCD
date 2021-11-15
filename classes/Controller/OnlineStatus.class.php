<?php


class OnlineStatus extends BaseClass{

    /*** TABLES HELPERS **/

    /**
     * @fn getOnlineStatusList
     * @note Ottiene la lista di option collegata a quella tipologia di stato online
     * @param string $type
     * @return bool|int|mixed|string
     */
    public function getOnlineStatusList(string $type){
        return DB::query("SELECT * FROM online_status WHERE type='{$type}'",'result');
    }


    /**** LISTS ****/

    /**
     * @fn getPgStatusOnlinePresenti
     * @note Ottiene il testo che viene scritto nei presenti
     * @param int $pg
     * @return string
     */
    public static function getPgStatusOnlinePresenti(int $pg): string
    {

        $pg = Filters::int($pg);

        $data = DB::query("SELECT online_time.text AS online_time, online_action_time.text AS online_action_time
                    FROM personaggio 
                    LEFT JOIN online_status AS online_time ON(personaggio.online_time = online_time.id)
                    LEFT JOIN online_status AS online_action_time ON(personaggio.online_action_time = online_action_time.id)
                    WHERE personaggio.id ='{$pg}' LIMIT 1");

        $online_time = !empty($data['online_time']) ? Filters::text($data['online_time']) : 'Non specificato';
        $online_action_time = !empty($data['online_action_time']) ? Filters::text($data['online_action_time']) : 'Non specificato';

        return Filters::text("Tempo online: {$online_time} | Tempo azione: {$online_action_time} | ");
    }


    /*** FUNCTIONS ***/

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

    /**
     * @fn renderOnlineStatusOptions
     * @note Render delle radio di scelta dello status online
     * @param string $type
     * @return string
     */
    public function renderOnlineStatusOptions(string $type): string
    {

        $html = '';
        $list = $this->getOnlineStatusList($type);

        foreach ($list as $row){
            $id = Filters::int($row['id']);
            $text = Filters::out($row['text']);

            $html .= "<li>";
            $html .= "<input type='radio' name='{$type}' value='{$id}' required> {$text}";
            $html .= "</li>";

        }

        return $html;
    }

    /**
     * @fn setOnlineStatus
     * @note Update dello status online
     * @param array $post
     * @return array
     */
    public function setOnlineStatus(array $post): array
    {
        if(Functions::get_constant('ONLINE_STATUS_ENABLED')){

            $online_time = Filters::int($post['online_time']);
            $online_action_time = Filters::int($post['online_action_time']);

            DB::query("
                    UPDATE personaggio 
                    SET online_time='{$online_time}',online_action_time='{$online_action_time}',online_last_refresh=NOW()
                    WHERE id='{$this->me_id}' LIMIT 1");

            return ['response'=>true,'mex'=>'Status online settato correttamente.'];
        }
        else{
            return ['response'=>false,'mex'=>'Errore, funzione non disponibile.'];
        }

    }
}