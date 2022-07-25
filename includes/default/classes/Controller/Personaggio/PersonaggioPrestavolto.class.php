<?php

class PersonaggioPrestavolto extends BaseClass
{

    /**
     * @var mixed
     * @var mixed
     */
    private $prestavolti_enabled;

    public function __construct()
    {
        parent::__construct();
        $this->prestavolti_enabled = Functions::get_constant('PRESTAVOLTO_ENABLED');
    }

    /*** GETTERS */

    /**
     * @fn
     * @return false|mixed
     */
    public function prestavoltiEnabled()
    {
        return $this->prestavolti_enabled;
    }

    /** LOADER */

    /**
     * @fn loadServicePage
     * @note Index della pagina servizi dei gruppi
     * @param string $op
     * @return string
     */
    public function loadServicePage(string $op): string
    {
        $op = Filters::out($op);

        switch ( $op ) {
            case 'read':
                $page = 'read.php';
                break;
            default:
                $page = 'list.php';
                break;
        }

        return $page;

    }

    /** TABLE HELPERS */

    /**
     * @fn getAll
     * @note Estrae tutti i prestavolti
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllPrestavolto(string $val = '*')
    {
        return DB::query("SELECT {$val} FROM personaggio_prestavolto WHERE 1", 'result');
    }
    /** RENDER */

    /**
     * @fnList
     * @note Render html della lista dei gruppi
     * @return string
     */
    public function serviziPrestavoltoList(): string
    {
        $template = Template::getInstance()->startTemplate();
        return $template->renderTable(
            'servizi/prestavolti_list',
            $this->renderList()
        );
    }

    /**
     * @fn renderList
     * @note Render html lista gruppi
     * @return array
     */
    public function renderList(): array
    {
        $list = $this->getAllPrestavolto();
        $row_data = [];

        foreach ( $list as $row ) {

            $array = [

                'nome' => Filters::out($row['nome']),
                'cognome' => Filters::out($row['cognome']),
                'fonte' => Filters::out($row['fonte']),
                'personaggio' => Personaggio::nameFromId(Filters::int($row['personaggio'])),

            ];

            $row_data[] = $array;
        }

        $cells = [
            'Personaggio',
            'Nome',
            'Cognome',
            'Fonte',
        ];

        $links = [
            ['href' => "/main.php?page=uffici", 'text' => 'Indietro'],
        ];

        return [
            'body_rows' => $row_data,
            'cells' => $cells,
            'links' => $links,
        ];
    }

}