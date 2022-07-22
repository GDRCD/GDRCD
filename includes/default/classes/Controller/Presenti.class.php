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
             WHERE `ora_entrata` > `ora_uscita` AND `ultimo_refresh` > DATE_SUB(NOW(), INTERVAL 45 MINUTE)',
            'result'
        );
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

        $characters = $this->getPresenti();
        $compiled_characters = [];

        foreach ($characters as $character) {

            $gender_data = Sessi::getInstance()->getGender($character['sesso']);

            $compiled_characters[] = [
                'id' => Filters::out($character['id']),
                'nome' => Filters::out($character['nome']),
                'cognome' => Filters::out($character['cognome']),
                'gender_name' => Filters::out($gender_data['nome']),
                'gender_icon' => Router::getImgsDir().Filters::out($gender_data['immagine'])
            ];

        }

        return $compiled_characters;
    }

    public function listMiniPresences()
    {
        return Template::getInstance()->startTemplate()->render(
            'presenti/mini',
            ['body_row' => $this->renderMiniPresences()]
        );
    }

}