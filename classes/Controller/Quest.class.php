<?php

class Quest extends BaseClass
{

    /**
     * @var bool $quest_enabled
     * @var bool $trame_enabled
     * @var int $result_for_page
     */
    private
        $quest_enabled,
        $trame_enabled,
        $result_for_page;

    /**
     * @fn __construct
     *@note Inizializzo le variabili di classe con all'interno le costanti necessarie
     */
    public function __construct()
    {
        parent::__construct();

        # Controlla se le quest sono abilitate
        $this->quest_enabled = Functions::get_constant('QUEST_ENABLED');

        # Controlla se le trame sono abilitate
        $this->trame_enabled = Functions::get_constant('TRAME_ENABLED');

        # Numero di risultati per pagina nella lista quest
        $this->result_for_page = Functions::get_constant('QUEST_RESULTS_FOR_PAGE');

    }

    /**
     * @fn questEnabled
     * @note Controllo se le quest sono abilitate
     * @return bool
     */
    public function questEnabled():bool
    {
        return $this->quest_enabled;
    }

    /**
     * @fn trameEnabled
     * @note Controllo se le trame sono abilitate
     * @return bool
     */
    public function trameEnabled(): bool
    {
        return $this->trame_enabled;
    }

    /**
     * @fn managePermission
     * @note Controllo se se ho i permessi per gestire le quest
     * @return bool
     */
    public function manageQuestPermission(): bool
    {
        return Permissions::permission('MANAGE_QUESTS');
    }

    /**
     * @fn viewTramePermission
     * @note Controllo se ho i permessi per gestire le trame
     * @return bool
     */
    public function viewTramePermission(): bool
    {
        return Permissions::permission('MANAGE_TRAME_VIEW');
    }

    /**
     * @fn getTrama
     * @note Estrae i dati di una trama dalla tabella quest_trama
     * @param int $trama
     * @param string $value
     * @return bool|int|mixed|string
     */
    public function getTrama(int $trama, string $value = '*')
    {
        return DB::query("SELECT {$value} FROM quest_trama WHERE id = '{$trama}' LIMIT 1");
    }

    /**
     * @fn loadManagementPage
     * @note Sceglie quale pagina di gestione caricare
     * @param $op
     * @return string
     */
    public function loadManagementPage($op): string
    {

        if ($this->quest_enabled) {
            $op = Filters::out($op);


            switch ($op) {
                case 'edit_quest':
                case 'new_quest':
                    $url = 'registra_quest.php';
                    break;
                case 'doedit_quest':
                    $url = 'doedit_quest.php';
                    break;
                case 'insert_quest':
                    $url = 'insert_quest.php';
                    break;
                case 'doedit_trama':
                    $url = 'doedit_trama.php';
                    break;
                case 'insert_trama':
                    $url = 'insert_trama.php';
                    break;
                case 'edit_trama':
                case 'new_trama':
                    $url = 'registra_trama.php';
                    break;
                case 'delete_quest':
                    $url = 'delete_quest.php';
                    break;
                case 'delete_trama':
                    $url = 'delete_trama.php';
                    break;
                case 'lista_trame':
                    $url = 'lista_trame.php';
                    break;
                default: //Form di manutenzione
                    $url = 'index.php';
                    break;
            }
        } else {
            $url = '';
        }

        return Filters::out($url);
    }

    /**
     * @fn getAllQuests
     * @note Estrae tutte le quest che si possono leggere
     * @param int $start
     * @param int $end
     * @return bool|int|mixed|string
     */
    public function getAllQuests(int $start, int $end)
    {
        $where = (Permissions::permission('MANAGE_QUESTS_OTHER')) ? '1' : "autore = '{$this->me}'";
        return gdrcd_query("SELECT * FROM quest WHERE {$where} GROUP BY id ORDER BY trama, data DESC LIMIT {$start}, {$end}", 'result');
    }

    /**
     * @fn getTotalQuestsNumber
     * @note Estrae il numero totale di quest visibili
     * @return int
     */
    public function getTotalQuestsNumber(): int
    {
        $where = (Permissions::permission('MANAGE_QUESTS_OTHER')) ? '1' : "autore = '{$this->me}'";
        $list = gdrcd_query("SELECT count(*) as total FROM quest WHERE {$where} GROUP BY id ORDER BY trama, data");
        return Filters::int($list['total']);
    }

    /**
     * @fn getPageNumbers
     * @note Estrae l'impaginazione
     * @param int $offset
     * @return string
     */
    public function getPageNumbers(int $offset): string
    {

        $html = '';
        $total = $this->getTotalQuestsNumber();
        $offset = Filters::int($offset);

        if ($total > $this->result_for_page) {
            for ($i = 0; $i <= floor($total / $this->result_for_page); $i++) {
                $html .= ($i != $offset) ? "<a href='main.php?page=gestione_quest&offset={$i}'>".($i+1)."</a>" : ' '.($i+1).' ';
            }
        }

        return $html;
    }
}