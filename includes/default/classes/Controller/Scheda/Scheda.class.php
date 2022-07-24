<?php


class Scheda extends BaseClass
{


    /**
     * @fn __construct
     * @note Class constructor
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * @fn available
     * @note Controlla se una scheda e' accessibile.
     * @param $id_pg
     * @return bool
     */
    public function available($id_pg): bool
    {
        $id_pg = Filters::int($id_pg);
        return (Personaggio::pgExist($id_pg));
    }

    /**** INDEX ****/

    /**
     * @fn loadCharacterPage
     * @note Routing delle pagine della scheda
     * @param string $op
     * @return string
     */
    public function loadCharacterPage(string $op): string
    {
        $op = Filters::out($op);

        switch ($op) {
            default:
                $page = 'main.php';
                break;

            // ABILITA
            case 'abilita':
                $page = 'abilita/index.php';
                break;

            // STATISTICHE
            case 'stats':
                $page = 'stats/index.php';
                break;

            // CONTATTi
            case 'contatti':
                $page = 'contatti/index.php';
                break;

            case 'contatti_new':
                $page = 'contatti/contact_new.php';
                break;

            case 'contatti_view':
                $page = 'contatti/note_view.php';
                break;

            case 'contatti_nota_new':
                $page = 'contatti/note_new.php';
                break;

            case 'contatti_nota_details':
                $page = 'contatti/note_details.php';
                break;

            // STORIA
            case 'storia':
                $page = 'storia.php';
                break;

            // TRANSAZIONI
            case 'transazioni':
                $page = 'transazioni/transazioni.php';
                break;

            // OGGETTI
            case 'oggetti':
                $page = 'oggetti/oggetti.php';
                break;

            // DIARIO
            case 'diario':
                $page = 'diario/index.php';
                break;

            case 'diario_view':
                $page = 'diario/view.php';
                break;

            case 'diario_new':
                $page = 'diario/new.php';
                break;

            case 'diario_edit':
                $page = 'diario/edit.php';
                break;

        }

        return $page;
    }

    /**** RENDER ****/


    /**
     * @fn getGroupIcons
     * @note Funzione che si occupa dell'estrazione delle icone della scheda
     * @param string $pg_id
     * @return string
     */
    private function getGroupIcons(string $pg_id): string
    {
        # Filtro il mittente passato
        $pg_id = Filters::int($pg_id);

        $icons = '';

        if (Gruppi::getInstance()->activeGroupIconChat()) {
            $roles = PersonaggioRuolo::getInstance()->getAllCharacterRolesWithRoleData($pg_id);

            foreach ($roles as $role) {
                $link = Router::getImgsDir() . $role['immagine'];
                $icons .= "<img src='{$link}' title='{$role['gruppo_nome']} - {$role['nome']}'>";
            }
        }

        return $icons;
    }

    /**
     * @fn getRaceIcon
     * @note Funzione che si occupa dell'estrazione dell'icone della razza
     * @param string $pg_id
     * @return string
     */
    private function getRaceIcon(string $pg_id): string
    {
        # Filtro il mittente passato
        $pg_id = Filters::int($pg_id);
        $character_data = Personaggio::getPgData($pg_id, 'razza');
        $race_id = Filters::int($character_data['razza']);
        $race_data = Razze::getInstance()->getRace($race_id, 'icon,sing_m,sing_f');
        $icon = Filters::out($race_data['icon']);
        $name = Filters::out($race_data['nome']);

        $link = Router::getImgsDir() . $icon;
        return "<img src='{$link}' title='{$name}'>";
    }

    /**
     * @fn renderMainPage
     * @note Renderizza la scheda pg
     * @param int $id_pg
     * @return array
     */
    public function renderMainPage(int $id_pg): array
    {

        $character_data = Personaggio::getPgData($id_pg);

        $data = [
            'id' => Filters::out($character_data['id']),
            'character_data' => $character_data,
            'groups_icons' => $this->getGroupIcons($id_pg),
            'race_icon' => $this->getRaceIcon($id_pg),
            'registration_day' => Filters::date($character_data['data_iscrizione'], 'd/m/Y'),
            'last_login' => Filters::date($character_data['ora_entrata'], 'd/m/Y')
        ];


        return $data;
    }

    /**
     * @fn characterPage
     * @note Renderizza la scheda pg
     * @param int $id_pg
     * @return mixed
     */
    public function characterMainPage(int $id_pg)
    {
        return Template::getInstance()->startTemplate()->render(
            'scheda/main',
            $this->renderMainPage($id_pg)
        );
    }


}