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
        $result_for_page,
        $notify_enabled,
        $px_code = PX,
        $num_log_scheda = 10;

    /**
     * @fn __construct
     * @note Inizializzo le variabili di classe con all'interno le costanti necessarie
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

        $this->notify_enabled = Functions::get_constant('QUEST_NOTIFY');

    }

    /**
     * @fn createMemberInput
     * @note Creazione degli input di inserimento pg
     * @return array
     */
    public function createMemberInput(): array
    {
        $html = '';

        $html .= "<div class='form_subtitle'>Partecipante</div>";
        $html .= '<div class="single_input">';

        $html .= '<div class="label"> Nome </div>';
        $html .= "<select name='new_part[pg][]'>";
        $html .= "<option value=''></option>";
        $html .= Functions::getPgList();
        $html .= "</select>";

        $html .= '<div class="label"> Px </div>';
        $html .= " <input name='new_part[px][]'>";

        $html .= '<div class="label"> Commento </div>';
        $html .= "<textarea name='new_part[commento][]'></textarea>";

        $html .= "</div>";

        return ['Input' => $html];
    }

    /*** TABLES HELPERS **/

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
     * @fn getQuest
     * @note Estrae i dati di una trama dalla tabella quest_trama
     * @param int $quest
     * @param string $value
     * @return bool|int|mixed|string
     */
    public function getQuest(int $quest, string $value = '*')
    {
        return DB::query("SELECT {$value} FROM quest WHERE id = '{$quest}' LIMIT 1");
    }

    /**
     * @fn getQuestMembers
     * @note Estrae la lista personaggi di una quest
     * @param int $quest
     * @param string $value
     * @return bool|int|mixed|string
     */
    public function getQuestMembers(int $quest, string $value = '*')
    {
        return DB::query("SELECT {$value} FROM personaggio_quest WHERE id_quest = '{$quest}'", 'result');
    }

    /**
     * @fn getQuestMemberData
     * @note Estrae i dati di una quest per quel personaggio
     * @param int $quest
     * @param int $pg
     * @param string $value
     * @return bool|int|mixed|string
     */
    public function getQuestMemberData(int $quest, int $pg, string $value = '*')
    {
        return DB::query("SELECT {$value} FROM personaggio_quest WHERE id_quest = '{$quest}' AND personaggio='{$pg}' LIMIT 1");
    }

    /**
     * @fn getTrameQuestNums
     * @note Estrae il numero di quest associate ad una trama
     * @param int $id_trama
     * @return int
     */
    public function getTrameQuestNums(int $id_trama): int
    {
        $tot = DB::query("SELECT count(*) as tot FROM quest WHERE trama='{$id_trama}'");
        return Filters::int($tot['tot']);
    }

    /*** CONTROLS ***/

    /**
     * @fn questExist
     * @note Controlla l'esistenza di una quest
     * @param int $id
     * @return bool
     */
    public function questExist(int $id): bool
    {
        $data = DB::query("SELECT COUNT(id) AS tot FROM quest WHERE id='{$id}' LIMIT 1");
        return ($data['tot'] > 0);
    }

    /**
     * @fn trameExist
     * @note Controlla l'esistenza di una trama
     * @param int $id
     * @return bool
     */
    public function tramaExist(int $id): bool
    {
        $data = DB::query("SELECT COUNT(id) AS tot FROM quest_trama WHERE id='{$id}' LIMIT 1");
        return ($data['tot'] > 0);
    }

    /*** GETTERS */

    /**
     * @fn questEnabled
     * @note Controllo se le quest sono abilitate
     * @return bool
     */
    public function questEnabled(): bool
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


    /*** PERMISSION ***/

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
     * @note Controllo se ho i permessi per visualizzare le trame
     * @return bool
     */
    public function viewTramePermission(): bool
    {
        return Permissions::permission('MANAGE_TRAME_VIEW');
    }

    /**
     * @fn manageTramePermission
     * @note Controllo se ho i permessi per gestire le trame
     * @return bool
     */
    public function manageTramePermission(): bool
    {
        return Permissions::permission('MANAGE_TRAME');
    }

    /*** ROUTING **/

    /**
     * @fn loadManagementPage
     * @note Sceglie quale pagina di gestione caricare
     * @param string $op
     * @return string
     */
    public function loadManagementQuestPage(string $op = ''): string
    {

        if ( $this->quest_enabled ) {
            $op = Filters::out($op);

            switch ( $op ) {

                default: //Form di manutenzione
                    $url = 'gestione_quest_list.php';
                    break;

                case 'insert_quest': # Modifica Quest
                    $url = 'gestione_quest_insert.php';
                    break;

                case 'edit_quest': # Modifica Quest
                    $url = 'gestione_quest_edit.php';
                    break;

                case 'delete_quest':
                    $url = 'gestione_quest_delete.php';
                    break;

            }
        } else {
            $url = '';
        }

        return Filters::out($url);
    }

    /**
     * @fn loadManagementTramePage
     * @note Routing della pagina delle trame
     * @param string $op
     * @return string
     */
    public function loadManagementTramePage(string $op = ''): string
    {

        if ( $this->trame_enabled ) {
            $op = Filters::out($op);

            switch ( $op ) {

                default: //Form di manutenzione
                    $url = 'gestione_trame_list.php';
                    break;

                case 'insert_trama': # Modifica Quest
                    $url = 'gestione_trame_insert.php';
                    break;

                case 'edit_trama': # Modifica Quest
                    $url = 'gestione_trame_edit.php';
                    break;

                case 'delete_trama':
                    $url = 'gestione_trame_delete.php';
                    break;
            }
        } else {
            $url = '';
        }

        return Filters::out($url);
    }


    /*** SELECT LISTS */

    /**
     * @fn getTrameList
     * @note Estrae la lista select delle trame esistenti
     * @param int $selected
     * @return string
     */
    public function getTrameList(int $selected = 0): string
    {

        $html = '';
        $selected = Filters::int($selected);
        $list = DB::query("SELECT * FROM quest_trama ORDER BY titolo", 'result');

        foreach ( $list as $item ) {
            $name = Filters::out($item['titolo']);
            $id = Filters::int($item['id']);
            $sel = ($selected == $id) ? 'selected' : '';

            $html .= "<option value='{$id}' {$sel}>{$name}</option>";
        }

        return $html;
    }

    /**
     * @fn trameStatusList
     * @note Estrae la lista degli stati delle trame disponibili
     * @param int $selected
     * @return string
     */
    public function trameStatusList(int $selected = 0): string
    {

        $html = '<option value=""></option>';
        $selected = Filters::int($selected);
        $array = [0 => 'In Corso', 1 => 'Chiusa'];

        foreach ( $array as $index => $item ) {
            $sel = ($selected == $index) ? 'selected' : '';

            $html .= "<option value='{$index}' {$sel}>{$item}</option>";
        }

        return $html;
    }

    /*** SCHEDA PX ***/

    /**
     * @fn renderQuestList
     * @param int $page
     * @return string
     */
    public function renderQuestList(int $page): string
    {
        $html = '';
        $pagebegin = $page * 10;
        $pageend = 10;
        $quests = $this->getAllQuests($pagebegin, $pageend);

        $html .= '
                <div class="tr header">
                    <div class="td">
                        Data
                    </div>
                    <div class="td">
                        Titolo
                    </div>
                    <div class="td">
                        Autore
                    </div>
                    <div class="td">
                        Partecipanti
                    </div>';
        if ( $this->viewTramePermission() ) {
            $html .= '<div class="td">
                    Trama
                </div>';
        }

        $html .= '
                <div class="td">
                    Autore modifica
                </div>
                <div class="td">
                    Ultima modifica
                </div>
                <div class="td">
                    Controlli
                </div>
                </div>';

        foreach ( $quests as $row ) {

            $id = Filters::int($row['id']);
            $date = Filters::date($row['data'], 'd/m/Y');
            $title = Filters::out($row['titolo']);
            $master = Personaggio::nameFromId(Filters::int($row['autore']));
            $partecipanti = $this->getPartecipantsNames($row['partecipanti']);
            $autore_modifica = (!empty($row['autore_modifica'])) ? Filters::out($row['autore_modifica']) : '';
            $ultima_modifica = (!empty($row['ultima_modifica'])) ? Filters::date($row['ultima_modifica'], 'd/m/Y') : '';

            $html .= "
                    <div class='tr'>
                        <div class='td'>
                            {$date}
                        </div>
                        <div class='td'>
                            {$title}
                        </div>
                        <div class='td'>
                            {$master}
                        </div>
                        <div class='td'>
                            {$partecipanti}
                        </div>
    ";

            if ( $this->viewTramePermission() ) {
                $data = $this->getTrama(Filters::int($row['trama']));
                $subtitle = (!empty($data['titolo'])) ? Filters::out($data['titolo']) : 'Nessuna';

                $html .= "
                            <div class='td'>
                                {$subtitle}
                            </div>
                            ";
            }

            $html .= "
                    <div class='td'>
                        {$autore_modifica}
                    </div>
                    <div class='td'>
                        {$ultima_modifica}
                    </div>
                
                    <div class='td commands'><!-- Iconcine dei controlli -->
                        <a href='/main.php?page=gestione/quest/gestione_quest_index&op=edit_quest&id_record={$id}'>
                            <i class='fas fa-edit'></i>
                        </a>
                        <a class='ajax_link' data-id='{$id}' data-page='{$page}' data-action='delete_quest' href='#'>
                            <i class='fas fa-eraser'></i>
                        </a>
                    </div>
                </div>";
        }

        $html .= '
            <div class="tr footer">
                <a href="main.php?page=gestione/quest/gestione_quest_index&op=insert_quest">
                    Registra nuova quest
                </a> |
                <a href="main.php?page=gestione">
                    Indietro
                </a>
            </div>';

        return $html;
    }

    public function renderTrameList(int $page)
    {
        $html = '';

        $pagebegin = (int)$_REQUEST['offset'] * 10;
        $pageend = 10;

        $trame = $this->getAllTrame($pagebegin, $pageend);

        $html .= '<div class="tr header">
                <div class="td">
                    Data
                </div>
                <div class="td">
                    Titolo
                </div>
                <div class="td">
                    Autore
                </div>
                <div class="td">
                    Numero quest
                </div>
                <div class="td">
                    Stato
                </div>
                <div class="td">
                    Autore modifica
                </div>
                <div class="td">
                    Ultima modifica
                </div>
                <div class="td">
                    Controlli
                </div>
            </div>';

        foreach ( $trame as $trama ) {

            $id = Filters::int($trama['id']);
            $data = Filters::date($trama['data'], 'd/m/Y');
            $titolo = Filters::out($trama['titolo']);
            $autore = Personaggio::nameFromId(Filters::int($trama['autore']));
            $nums = $this->getTrameQuestNums(Filters::int($trama['id']));
            $status = $this->getTramaStatusText(Filters::int($trama['stato']));
            $autore_modifica = (!empty($trama['autore_modifica'])) ? Filters::out($trama['autore_modifica']) : '';
            $data_modifica = (!empty($trama['ultima_modifica'])) ? Filters::date($trama['ultima_modifica'], 'd/m/Y') : '';

            $html .= "<div class='tr'>
                    <div class='td'>
                        {$data}
                    </div>
                    <div class='td'>
                        {$titolo}
                    </div>
                    <div class='td'>
                        {$autore}
                    </div>
                    <div class='td'>
                        {$nums}
                    </div>
                    <div class='td'>
                        {$status}
                    </div>
                    <div class='td'>
                        {$autore_modifica}
                    </div>
                    <div class='td'>
                        {$data_modifica}
                    </div>
        
                    <div class='td commands'><!-- Iconcine dei controlli -->
                        <a href='/main.php?page=gestione_trame&op=edit_trama&id_record={$id}'>
                            <i class='fas fa-edit'></i>
                        </a>
                        <a class ='ajax_link' data-id='{$id}' data-action='delete_trama' data-page='{$page}' href='#'>
                            <i class='fas fa-eraser'></i>
                        </a>
                    </div>
                </div>";
        }

        $html .= '<div class="tr footer">';

        if ( $this->manageTramePermission() ) {
            $html .= '<a href="main.php?page=gestione_trame&op=insert_trama">
                Registra nuova trama
            </a> |';
        }

        $html .= '<a href="main.php?page=gestione">
                    Indietro
                </a>
            </div>';

        return $html;
    }

    /*** TRAME LISTA PAGE **/

    /**
     * @fn getAllTrame
     * @note Estrae tutte le trame che si possono leggere
     * @param int $start
     * @param int $end
     * @return mixed
     */
    public function getAllTrame(int $start, int $end)
    {
        $where = (Permissions::permission('MANAGE_TRAME_OTHER')) ? '1' : "autore = '{$this->me_id}'";
        return gdrcd_query("SELECT * FROM quest_trama WHERE {$where} GROUP BY id ORDER BY titolo, data DESC LIMIT {$start}, {$end}", 'result');
    }

    /**
     * @fn getTotalTrameNumber
     * @note Estrae il numero totale di trame visibili
     * @return int
     */
    public function getTotalTrameNumber(): int
    {
        $where = (Permissions::permission('MANAGE_TRAME_OTHER')) ? '1' : "autore = '{$this->me}'";
        $list = gdrcd_query("SELECT count(*) as total FROM quest_trama WHERE {$where} GROUP BY id ORDER BY titolo, data");
        return Filters::int($list['total']);
    }

    /**
     * @fn getTramePageNumbers
     * @note Estrae l'impaginazione delle trame
     * @param int $offset
     * @return string
     */
    public function getTramePageNumbers(int $offset): string
    {

        $html = '';
        $total = $this->getTotalQuestsNumber();
        $offset = Filters::int($offset);

        if ( $total > $this->result_for_page ) {
            for ( $i = 0; $i <= floor($total / $this->result_for_page); $i++ ) {
                $html .= ($i != $offset) ? "<a href='main.php?page=gestione_trame&offset={$i}'>" . ($i + 1) .
                    "</a>" : ' ' . ($i + 1) . ' ';
            }
        }

        return $html;
    }

    /**
     * @fn getTramaStatusText
     * @note Estrae il nome dello stato dal suo numero identificativo
     * @param int $stato
     * @return string
     */
    public function getTramaStatusText(int $stato): string
    {
        $stato = Filters::int($stato);

        switch ( $stato ) {
            default:
            case 0:
                $text = 'In corso';
                break;
            case 1:
                $text = 'Chiusa';
                break;
        }

        return $text;
    }

    /*** QUEST LIST PAGE */

    /**
     * @fn getAllQuests
     * @note Estrae tutte le quest che si possono leggere
     * @param int $start
     * @param int $end
     * @return mixed
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
     * @note Estrae l'impaginazione delle quest
     * @param int $offset
     * @return string
     */
    public function getQuestsPageNumbers(int $offset): string
    {

        $html = '';
        $total = $this->getTotalQuestsNumber();
        $offset = Filters::int($offset);

        if ( $total > $this->result_for_page ) {
            for ( $i = 0; $i <= floor($total / $this->result_for_page); $i++ ) {
                $html .= ($i != $offset) ? "<a href='main.php?page=gestione_quest&offset={$i}'>" . ($i + 1) .
                    "</a>" : ' ' . ($i + 1) . ' ';
            }
        }

        return $html;
    }

    /**
     * @fn getQuestMembersList
     * @note Estraggo la lista dei membri di una quest
     * @param int $id
     * @return string
     */
    public function getQuestMembersList(int $id): string
    {
        $html = '';
        $id = Filters::int($id);
        $members = $this->getQuestMembers($id);

        foreach ( $members as $member ) {

            $pg_id = Filters::in($member['personaggio']);
            $px = Filters::int($member['px_assegnati']);
            $commento = Filters::out($member['commento']);

            $html .= "
<div class='form_subtitle'>Partecipante</div>";
            $html .= '
<div class="single_input">';

            $html .= '
    <div class="label"> Nome</div>
    ';
            $html .= "<select name='part[{$pg_id}]'>";
            $html .= "
        <option value=''></option>
        ";
            $html .= Functions::getPgList($pg_id);
            $html .= "</select>";

            $html .= '
    <div class="label"> Px</div>
    ';
            $html .= " <input name='part[{$pg_id}][px]' value='{$px}'>";

            $html .= '
    <div class="label"> Commento</div>
    ';
            $html .= "<textarea name='part[{$pg_id}][commento]'>{$commento}</textarea>";

            $html .= "
</div>";

        }

        return $html;
    }

    /**
     * @fn getPartecipantsNames
     * @note Converte la lista di id dei partecipanti in nomi
     * @param string $members
     * @return string
     */
    public function getPartecipantsNames(string $members): string
    {

        $members = Filters::out($members);
        $list = explode(',', $members);
        $array = [];

        foreach ( $list as $member ) {

            $array[] = Personaggio::nameFromId(Filters::int($member));
        }

        return implode(',', $array);
    }

    /*** INSERT TRAME ***/

    public function insertTrama($post)
    {

        if ( $this->manageTramePermission() ) {

            $titolo = Filters::in($post['titolo']);
            $descr = Filters::in($post['descrizione']);
            $stato = Filters::int($post['stato']);

            DB::query("INSERT INTO quest_trama(titolo, descrizione, data, autore, stato)
VALUES('{$titolo}','{$descr}',NOW(),'{$this->me_id}','{$stato}') ");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Trama creata con successo.',
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

    /*** EDIT TRAME ***/

    public function editTrama($post)
    {

        $id_trama = Filters::int($post['trama']);

        if ( $this->manageTramePermission() ) {
            if ( $this->tramaExist($id_trama) ) {

                $titolo = Filters::in($post['titolo']);
                $descr = Filters::in($post['descrizione']);
                $status = Filters::int($post['stato']);

                DB::query("UPDATE quest_trama SET titolo='{$titolo}',descrizione='{$descr}',stato='{$status}'
                    WHERE id='{$id_trama}' LIMIT 1");

                return [
                    'response' => true,
                    'swal_title' => 'Operazione riuscita!',
                    'swal_message' => 'Trama modificata con successo.',
                    'swal_type' => 'success',
                ];

            } else {
                return [
                    'response' => false,
                    'swal_title' => 'Operazione fallita!',
                    'swal_message' => 'Trama inesistente.',
                    'swal_type' => 'error',
                ];
            }
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error',
            ];
        }
    }

    /*** DELETE TRAME ***/

    /**
     * @fn deleteTrama
     * @note Elimina una trama
     * @param $post array
     * @return array
     */
    public function deleteTrama(array $post): array
    {
        $id_trama = Filters::int($post['id']);
        $page = Filters::int($post['page']);

        if ( $this->manageTramePermission() ) {
            if ( $this->tramaExist($id_trama) ) {

                DB::query("DELETE FROM quest_trama WHERE id='{$id_trama}' LIMIT 1");
                DB::query("UPDATE quest SET trama=0 WHERE trama='{$id_trama}'");

                return [
                    'response' => true,
                    'swal_title' => 'Operazione riuscita!',
                    'swal_message' => 'Trama eliminata con successo.',
                    'swal_type' => 'success',
                    'trame_list' => $this->renderTrameList($page),
                ];

            } else {
                return [
                    'response' => false,
                    'swal_title' => 'Operazione fallita!',
                    'swal_message' => 'Trama inesistente.',
                    'swal_type' => 'error',
                ];
            }
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error',
            ];
        }
    }

    /*** INSERT QUEST */

    /**
     * @fn insertQuest
     * @note Inserimento di una nuova quest
     * @param array $post
     * @return array
     */
    public function insertQuest(array $post): array
    {

        if ( $this->manageQuestPermission() ) {

            $titolo = Filters::in($post['titolo']);
            $descr = Filters::in($post['descrizione']);
            $trama = Filters::int($post['trama']);
            $partecipanti = $post['new_part'];

            DB::query("INSERT INTO quest(titolo, partecipanti, descrizione, trama, data, autore)
VALUES('{$titolo}','','{$descr}','{$trama}',NOW(),'{$this->me_id}')");

            $last_quest = DB::query("SELECT max(id) AS id FROM quest WHERE 1 LIMIT 1");

            if ( !empty($partecipanti) ) {
                $quest_id = Filters::int($last_quest['id']);
                $data_exp = $this->assignExp($partecipanti, $titolo, $quest_id);
                $assigned = Filters::in($data_exp['assigned']);

                DB::query("UPDATE quest SET partecipanti = '{$assigned}' WHERE id= {$quest_id}");
            }

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Quest creata con successo.',
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

    /**
     * @fn assignExp
     * @note Assegna exp a nuovi partecipanti
     * @param array $partecipanti
     * @param string $titolo
     * @param int $quest_id
     * @return array
     */
    private function assignExp(array $partecipanti, string $titolo, int $quest_id): array
    {

        $assigned = [];
        $quest_id = Filters::int($quest_id);
        $titolo = Filters::in($titolo);

        foreach ( $partecipanti['pg'] as $index => $id_pg ) {

            if ( !in_array($id_pg, $assigned) ) {

                $pg_px = $partecipanti['px'][$index];
                $pg_comm = $partecipanti['commento'][$index];
                $pg_name = Personaggio::nameFromId(Filters::int($id_pg));

                DB::query("INSERT INTO personaggio_quest(id_quest, personaggio, data, commento, px_assegnati, autore)
VALUES('{$quest_id}','{$id_pg}',NOW(),'{$pg_comm}','{$pg_px}','{$this->me_id}')");

                Personaggio::updatePgData($id_pg, "esperienza = esperienza + '{$pg_px}'");

                Log::newLog([
                    "autore" => $this->me_id,
                    "destinatario" => $id_pg,
                    "tipo" => $this->px_code,
                    "testo" => Filters::in("Creata nuova quest '{$titolo}' ed assegnati '{$pg_px}' px a {$pg_name}"),
                ]);

                if ( $this->notify_enabled ) {
                    $notify_text = Filters::in("Creata nuova quest '{$titolo}");
                    $notify_title = Filters::in("Nuova quest");
                    DB::query("INSERT INTO messaggi (mittente, destinatario,oggetto, testo) VALUES ('Resoconti Quest','{$pg_name}','{$notify_title}','{$notify_text}')");
                }

                $assigned[] = $id_pg;
            }
        }

        return ['assigned' => implode(',', $assigned)];
    }

    /*** EDIT QUEST ***/

    /**
     * @fn editQuest
     * @note Modifica di una quest esistente
     * @param array $post
     * @return array
     */
    public function editQuest(array $post): array
    {
        # Se ho i permessi di modifica
        if ( $this->manageQuestPermission() ) {

            $quest = Filters::int($post['quest']);
            $titolo = Filters::in($post['titolo']);
            $descr = Filters::in($post['descrizione']);
            $partecipanti = $post['part'];
            $partecipanti_new = $post['new_part'];
            $extra_set = '';

            # Se la quest esiste
            if ( $this->questExist($quest) ) {

                # Assegno l'esperienza
                $this->updateQuestExp($post);
                if ( !empty($partecipanti_new) ) {
                    $this->assignExp($partecipanti_new, $titolo, $quest);
                }

                $partecipanti_id = (!empty($partecipanti)) ? $this->getIdsForMembers($partecipanti) : '';

                if ( Permissions::permission('MANAGE_TRAME') ) {
                    $trama = Filters::in($post['trama']);
                    $extra_set = ",trama='{$trama}' ";
                }

                DB::query("UPDATE quest SET
                    titolo='{$titolo}',
                    ultima_modifica=NOW(),
                    descrizione='{$descr}',
                    partecipanti = '{$partecipanti_id}'
                    {$extra_set}
                    WHERE id='{$quest}' LIMIT 1");

                return [
                    'response' => true,
                    'swal_title' => 'Operazione riuscita!',
                    'swal_message' => 'Quest aggiornata con successo.',
                    'swal_type' => 'success',
                ];
            } else {
                return [
                    'response' => false,
                    'swal_title' => 'Operazione fallita!',
                    'swal_message' => 'Quest inesistente.',
                    'swal_type' => 'error',
                ];
            }

        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error',
            ];
        }
    }

    /**
     * @fn getIdsForMembers
     * @note Estrae gli id dei partecipanti
     * @param array $partecipanti
     * @return string
     */
    public function getIdsForMembers(array $partecipanti): string
    {
        $array = [];

        foreach ( $partecipanti as $id => $data ) {
            $array[] = $id;
        }

        return implode(',', $array);
    }

    /**
     * @fn updateQuestExp
     * @note Update dell'exp per chi ha gia' avuto un'assegnazione per quella questa
     * @param array $post
     * @return void
     */
    private function updateQuestExp(array $post): void
    {

        $quest = Filters::int($post['quest']);
        $title = Filters::in($post['titolo']);
        $partecipanti = $post['part'];

        # Per ogni partecipante scelto
        foreach ( $partecipanti as $id => $pg ) {

            # Estraggoi dati dall'array dei partecipanti
            $pg_id = Filters::int($id);
            $pg_nome = Personaggio::nameFromId($pg_id);
            $pg_px = Filters::int($pg['px']);
            $pg_commento = Filters::in($pg['commento']);

            # Estraggo la riga dal db inerente al pg->quest
            $pg_quest_data = $this->getQuestMemberData($quest, $pg_id);
            $pg_original_exp = Filters::int($pg_quest_data['px_assegnati']);

            # Starto le variabili vuoti necessarie
            $notify = false;
            $notify_text = '';
            $notify_title = '';

            $log_text = '';

            # Se il personaggio esiste
            if ( Personaggio::pgExist($pg_id) ) {

                # Se il personaggio ha gia' un'assegnazione inerente alla quest
                if ( !empty($pg_quest_data['id']) ) {

                    # Se l'esperienza e' cambiata
                    if ( $pg_px != $pg_original_exp ) {

                        # Calcolo la nuova esperienza
                        $new_px = ($pg_px - $pg_original_exp);

                        # Aggiorno l'esperienza
                        DB::query("UPDATE personaggio SET esperienza = esperienza + {$new_px} WHERE id = '{$pg_id}' LIMIT 1 ");

                        $log_text = "({$new_px} px) Modifica Quest : {$title}";;

                        $notify_title = Filters::in("Modifica esperienza resoconto.");
                        $notify_text = Filters::in("Il resoconto quest relativo alla Quest: <b>{$title}</b> è stato modificato. Puoi consultarlo andando su Scheda > Esperienza > Resoconti quest");
                        $notify = true;
                    }

                    #Modifico il record in personaggio_quest
                    DB::query("UPDATE personaggio_quest SET commento='{$pg_commento}',px_assegnati='{$pg_px}' WHERE id_quest='{$quest}' AND personaggio ='{$pg_id}' LIMIT 1");
                } # Se non ha un'assegnazione per la quest
                else {

                    # Aggiunto il personaggio alla quest
                    DB::query("INSERT INTO personaggio_quest(id_quest, commento, personaggio, px_assegnati, autore) VALUES('{$quest}','{$pg_commento}','{$pg_id}','{$pg_px}','{$this->me_id}')");

                    # Update dell'esperienza
                    DB::query("UPDATE personaggio SET esperienza = esperienza + {$pg_px} WHERE id='{$pg_id}' LIMIT 1");

                    $notify_text = Filters::in("Il resoconto quest relativo alla Quest: <b>{$title}</b> è stato inserito. Puoi consultarlo andando su Scheda > Esperienza > Resoconti quest");
                    $notify_title = Filters::in("Inserimento nuovo resoconto.");
                    $notify = true;

                    $log_text = Filters::in("({$pg_px} xp) Assegnazione quest.");
                }

                # Inserisco il log
                if ( isset($log_text) ) {
                    Log::newLog([
                        "autore" => $this->me_id,
                        "destinatario" => $pg_id,
                        "tipo" => $this->px_code,
                        "testo" => "{$log_text}",
                    ]);
                }

                # Notifico l'utente
                if ( $notify ) {
                    if ( $this->notify_enabled ) {
                        DB::query("INSERT INTO messaggi (mittente, destinatario,oggetto, testo) VALUES ('Resoconti Quest','{$pg_nome}','{$notify_title}','{$notify_text}')");
                    }
                }
            }
        }

    }

    /*** DELETE QUEST ***/

    /**
     * @fn deleteQuest
     * @note Eliminazione di una quest
     * @param array $post
     * @return array
     */
    public function deleteQuest(array $post): array
    {

        $quest_id = Filters::int($post['id']);
        $page = Filters::int($post['page']);

        if ( $this->manageQuestPermission() ) {
            if ( $this->questExist($quest_id) ) {

                $quest_data = $this->getQuest($quest_id);

                $this->deleteQuestExp($quest_data);

                DB::query("DELETE FROM quest WHERE id='{$quest_id}' LIMIT 1");

                return [
                    'response' => true,
                    'swal_title' => 'Operazione riuscita!',
                    'swal_message' => 'Quest eliminata con successo.',
                    'swal_type' => 'success',
                    'quest_list' => $this->renderQuestList($page),
                ];
            } else {
                return [
                    'response' => false,
                    'swal_title' => 'Operazione fallita!',
                    'swal_message' => 'Quest inesistente.',
                    'swal_type' => 'info',
                ];
            }
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error',
            ];
        }
    }

    /**
     * @fn deleteQuestExp
     * @note Elimina le assegnazioni di exp per la quest
     * @param array $data
     * @return void
     */
    private function deleteQuestExp(array $data): bool
    {

        $quest_id = Filters::int($data['id']);
        $partecipanti = Filters::out($data['partecipanti']);
        $titolo = Filters::in($data['titolo']);
        $membri = explode(',', $partecipanti);

        if ( empty($membri) ) {
            return false;
        }

        foreach ( $membri as $membro ) {
            $membro_id = Filters::int($membro);
            $data_quest = $this->getQuestMemberData($quest_id, $membro_id);

            $pg_name = Personaggio::nameFromId($membro_id);
            $exp = Filters::int($data_quest['px_assegnati']);

            DB::query("DELETE FROM personaggio_quest WHERE personaggio='{$membro_id}' AND id_quest='{$quest_id}'");

            DB::query("UPDATE personaggio SET esperienza = esperienza - {$exp} WHERE id='{$membro_id}' LIMIT 1");

            $log_text = Filters::in("La Quest '{$titolo}' è stata eliminata.");

            Log::newLog([
                "autore" => $this->me_id,
                "destinatario" => $membro_id,
                "tipo" => $this->px_code,
                "testo" => $log_text,
            ]);

            if ( $this->notify_enabled ) {

                $notify_title = Filters::in('Cancellazione quest.');
                $notify_text = Filters::in("La Quest '{$titolo}' è stata eliminata.");

                DB::query("INSERT INTO messaggi (mittente, destinatario,oggetto, testo) VALUES ('Resoconti Quest','{$pg_name}','{$notify_title}','{$notify_text}')");
            }
        }

        return true;
    }

}