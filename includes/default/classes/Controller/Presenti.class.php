<?php


class Presenti extends BaseClass
{

    /**
     * @fn __construct
     */
    protected function __construct()
    {
        parent::__construct();
    }


    /**** TABLE HELPERS ****/

    /**
     * @fn getPresenti
     * @note Ottieni i presenti
     * @return bool|int|mixed|string
     */
    public function getPresenti()
    {
        return DB::query('
             SELECT * FROM `personaggio` 
             WHERE `ora_entrata` > `ora_uscita` AND `ultimo_refresh` > DATE_SUB(NOW(), INTERVAL 45 MINUTE) ORDER BY `nome`',
            'result'
        );
    }

    /**
     * @fn getFullPresences
     * @note Ottieni i presenti estesi
     * @return bool|int|mixed|string
     */
    public function getFullPresences()
    {
        return DB::query('
             SELECT * FROM `personaggio` 
             WHERE `ora_entrata` > `ora_uscita` AND `ultimo_refresh` > DATE_SUB(NOW(), INTERVAL 45 MINUTE)
                 ORDER BY `ultimo_luogo` ASC,
                          `is_invisible` ASC',
            'result'
        );
    }

    /**
     * @fn getPresentiFromCurrentPosition
     * @note Ottieni i presenti per un luogo specifico
     * @return bool|int|mixed|string
     */
    public function getPresentiFromCurrentPosition()
    {
        $location = Personaggio::getPgLocation();

        return DB::query("
             SELECT * FROM `personaggio` 
             WHERE `ora_entrata` > `ora_uscita` 
                AND `ultimo_refresh` > DATE_SUB(NOW(), INTERVAL 45 MINUTE)
                AND `ultimo_luogo` = '{$location}' 
                AND ( (`is_invisible` = 0 AND `id` != '{$this->me_id}') OR `id`='{$this->me_id}')",
            'result'
        );
    }

    /*** AJAX ***/

    /**
     * @fn ajaxPresences
     * @note Richiamo dei presenti mini via ajax
     * @return array
     */
    public function ajaxPresences(): array
    {
        return ['template' => $this->listMiniPresences(), 'counter' => $this->numberOfPresences()];
    }

    /**** RENDERING ****/

    /**
     * @fn numberOfPresences
     * @note Ottieni il numero di presenti
     * @return int
     */
    public function numberOfPresences(): int
    {
        $presences = $this->getPresenti();
        return DB::rowsNumber($presences);
    }

    /**
     * @fn renderMiniPresences
     * @note Renderizza i presenti in mini
     * @return array
     */
    public function renderMiniPresences(): array
    {

        $characters = $this->getPresentiFromCurrentPosition();
        $compiled_characters = [];

        foreach ($characters as $character) {

            $gender_data = Sessi::getInstance()->getGender($character['sesso']);
            $availability_data = Disponibilita::getInstance()->getAvailability($character['disponibile']);

            $compiled_characters[] = [
                'id' => Filters::out($character['id']),
                'nome' => Filters::out($character['nome']),
                'cognome' => Filters::out($character['cognome']),
                'gender_name' => Filters::out($gender_data['nome']),
                'gender_icon' => Router::getImgsDir() . Filters::out($gender_data['immagine']),
                'availability_name' => Filters::out($availability_data['nome']),
                'availability_icon' => Router::getImgsDir() . Filters::out($availability_data['immagine']),
            ];

        }

        return $compiled_characters;
    }

    /***
     * @fn listMiniPresences
     * @note Renderizza i presenti in mini
     * @return mixed
     */
    public function listMiniPresences()
    {
        return Template::getInstance()->startTemplate()->render(
            'presenti/mini',
            ['body_row' => $this->renderMiniPresences()]
        );
    }

    /**
     * @fn renderMiniPresences
     * @note Renderizza i presenti in mini
     * @return array
     */
    public function renderFullPresences(): array
    {

        $characters = $this->getFullPresences();
        $compiled_characters = [];
        $invisible_character = [];
        $last_position = 0;

        foreach ($characters as $character) {

            $position = Filters::int($character['ultimo_luogo']);
            $gender_data = Sessi::getInstance()->getGender($character['sesso']);
            $availability_data = Disponibilita::getInstance()->getAvailability($character['disponibile']);

            $data = [
                'id' => Filters::out($character['id']),
                'nome' => Filters::out($character['nome']),
                'cognome' => Filters::out($character['cognome']),
                'gender_name' => Filters::out($gender_data['nome']),
                'gender_icon' => Router::getImgsDir() . Filters::out($gender_data['immagine']),
                'availability_name' => Filters::out($availability_data['nome']),
                'availability_icon' => Router::getImgsDir() . Filters::out($availability_data['immagine']),
                'position_id' => Filters::int($character['ultimo_luogo']),
                'mini_avatar' => Filters::out($character['url_img_chat']),
                'invisible' => Filters::bool($character['is_invisible']),
            ];

            if ($position != $last_position) {
                $last_position = $position;
                // TODO sostiture query con funzione get di classe Mappa, quando sarà disponibile
                if ($position > 0) {
                    $location_data = DB::query("SELECT * FROM `mappa` WHERE `id` = '{$position}' LIMIT 1",);
                    $data['position'] = Filters::out($location_data['nome']);
                } else {
                    $data['position'] = 'Mappa';
                }
            }


            if (Filters::bool($character['is_invisible'])) {
                $invisible_character[] = $data;
            } else {
                $compiled_characters[] = $data;
            }
        }

        return ['characters' => $compiled_characters, 'invisible' => $invisible_character];
    }

    public function listFullPresences()
    {
        return Template::getInstance()->startTemplate()->render(
            'presenti/full',
            $this->renderFullPresences()
        );
    }

}