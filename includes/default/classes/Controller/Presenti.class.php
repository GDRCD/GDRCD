<?php

class Presenti extends BaseClass
{

    /**** TABLE HELPERS ****/

    /**
     * @fn getPresenti
     * @note Ottieni i presenti
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getPresenti(): DBQueryInterface
    {
        return DB::queryStmt(
            'SELECT * FROM `personaggio` 
             WHERE `ora_entrata` > `ora_uscita` AND `ultimo_refresh` > DATE_SUB(NOW(), INTERVAL 45 MINUTE) ORDER BY `nome`',
            []
        );
    }

    /**
     * @fn getFullPresences
     * @note Ottieni i presenti estesi
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getFullPresences(): DBQueryInterface
    {
        return DB::queryStmt('
             SELECT * FROM `personaggio` 
             WHERE `ora_entrata` > `ora_uscita` AND `ultimo_refresh` > DATE_SUB(NOW(), INTERVAL 45 MINUTE)
             ORDER BY `ultimo_luogo` ASC, `is_invisible` ASC',
            []
        );
    }

    /**
     * @fn getPresentiFromCurrentPosition
     * @note Ottieni i presenti per un luogo specifico
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getPresentiFromCurrentPosition(): DBQueryInterface
    {
        $location = Personaggio::getPgLocation();

        return DB::queryStmt("
             SELECT * FROM `personaggio` 
             WHERE `ora_entrata` > `ora_uscita` AND `ultimo_refresh` > DATE_SUB(NOW(), INTERVAL 45 MINUTE)
             AND `ultimo_luogo` = '{$location}' AND ( (`is_invisible` = 0 AND `id` != :me_1) OR `id`=:me_2)",
            [
                "me_1" => $this->me_id,
                "me_2" => $this->me_id,
            ]
        );
    }

    /*** AJAX ***/

    /**
     * @fn ajaxPresences
     * @note Richiamo dei presenti mini via ajax
     * @return array
     * @throws Throwable
     */
    public function ajaxPresences(): array
    {
        return ['template' => $this->renderListMiniPresences(), 'counter' => $this->numberOfPresences()];
    }

    /**** RENDERING ****/

    /**
     * @fn numberOfPresences
     * @note Ottieni il numero di presenti
     * @return int
     * @throws Throwable
     */
    public function numberOfPresences(): int
    {
        $presences = $this->getPresenti();
        return $presences->getNumRows();
    }

    /**
     * @fn renderMiniPresences
     * @note Renderizza i presenti in mini
     * @return array
     * @throws Throwable
     */
    public function renderMiniPresences(): array
    {

        $characters = $this->getPresentiFromCurrentPosition();
        $compiled_characters = [];

        foreach ( $characters as $character ) {

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

    /**
     * @fn renderListMiniPresences
     * @note Renderizza i presenti in mini
     * @return string
     * @throws Throwable
     */
    public function renderListMiniPresences(): string
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
     * @throws Throwable
     */
    public function renderFullPresences(): array
    {

        $characters = $this->getFullPresences();
        $compiled_characters = [];
        $invisible_character = [];
        $last_position = 0;

        foreach ( $characters as $character ) {

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

            if ( $position != $last_position ) {
                $last_position = $position;
                // TODO Sostituire query con funzione get di classe Mappa, quando sarà disponibile
                if ( $position > 0 ) {
                    $location_data = DB::queryStmt("SELECT * FROM `mappa` WHERE `id` = :position LIMIT 1",[
                        "position" => $position
                    ]);
                    $data['position'] = Filters::out($location_data['nome']);
                } else {
                    $data['position'] = 'Mappa';
                }
            }

            if ( Filters::bool($character['is_invisible']) ) {
                $invisible_character[] = $data;
            } else {
                $compiled_characters[] = $data;
            }
        }

        return ['characters' => $compiled_characters, 'invisible' => $invisible_character];
    }

    /**
     * @fn renderListMiniPresences
     * @note Renderizza i presenti in mini
     * @return string
     * @throws Throwable
     */
    public function renderListFullPresences(): string
    {
        return Template::getInstance()->startTemplate()->render(
            'presenti/full',
            $this->renderFullPresences()
        );
    }

}