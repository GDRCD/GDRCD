<?php


class Prestavolto extends BaseClass
{

    /**
     * @var mixed
     * @var mixed
     */
    private $enabled;

    public function __construct()
    {
        parent::__construct();
        $this->enabled = Functions::get_constant('PRESTAVOLTO_ENABLED');
    }

    /*** GETTERS */

    public function isEnabled(){
        return $this->enabled;
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

        switch ($op) {
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
    public function List(): string
    {
        $template = Template::getInstance()->startTemplate();
        return $template->renderTable(
            'servizi/prestavolti_list',
            $this->renderList($this->getAllPrestavolto())
        );
    }
    /**
     * @fn renderList
     * @note Render html lista gruppi
     * @param array $list
     * @return array
     */
    public function renderList(object $list): array
    {
        $row_data = [];

        foreach ($list as $row) {

            $array = [

                'nome' => Filters::out($row['nome']),
                'cognome' => Filters::out($row['cognome']),
                'fonte' => Filters::out($row['fonte']),
                'personaggio' => Personaggio::nameFromId(Filters::int($row['personaggio']))

            ];

            $row_data[] = $array;
        }

        $cells = [
            'Personaggio',
            'Nome',
            'Cognome',
            'Fonte'
        ];
        $links = [
            ['href' => "/main.php?page=uffici", 'text' => 'Indietro']
        ];
        return [
            'body_rows' => $row_data,
            'cells' => $cells,
            'links' => $links
        ];
    }


}