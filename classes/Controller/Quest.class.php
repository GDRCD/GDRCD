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
        $px_code = PX;

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

    /*** CONTROLS ***/

    /**
     * @fn questExist
     * @note Controlla l'esistenza di una quest
     * @param int $id
     * @return bool
     */
    public function questExist($id): bool
    {
        $data = DB::query("SELECT COUNT(id) AS tot FROM quest WHERE id='{$id}' LIMIT 1");
        return ($data['tot'] > 0);
    }

    /*** CONFIGUTAZIONI */

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
     * @param $op
     * @return string
     */
    public function loadManagementPage($op): string
    {

        if ($this->quest_enabled) {
            $op = Filters::out($op);


            switch ($op) {

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

        foreach ($list as $item) {
            $name = Filters::out($item['titolo']);
            $id = Filters::int($item['id']);
            $sel = ($selected == $id) ? 'selected' : '';

            $html .= "<option value='{$id}' {$sel}>{$name}</option>";
        }

        return $html;
    }

    /*** FUNCTIONS */

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
                $html .= ($i != $offset) ? "<a href='main.php?page=gestione_quest&offset={$i}'>" . ($i + 1) . "</a>" : ' ' . ($i + 1) . ' ';
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

        foreach ($members as $member) {

            $pg_id = Filters::in($member['personaggio']);
            $px = Filters::int($member['px_assegnati']);
            $commento = Filters::out($member['commento']);

            $html .= "<div class='form_subtitle'>Partecipante</div>";
            $html .= '<div class="single_input">';

            $html .= '<div class="label"> Nome </div>';
            $html .= "<select name='part[{$pg_id}]'>";
            $html .= "<option value=''></option>";
            $html .= Functions::getPgList($pg_id);
            $html .= "</select>";

            $html .= '<div class="label"> Px </div>';
            $html .= " <input name='part[{$pg_id}][px]' value='{$px}'>";

            $html .= '<div class="label"> Commento </div>';
            $html .= "<textarea name='part[{$pg_id}][commento]'>{$commento}</textarea>";

            $html .= "</div>";

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

        foreach ($list as $member) {

            array_push($array, Personaggio::nameFromId(Filters::int($member)));
        }

        return implode(',', $array);
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

    /*** INSERT QUEST */

    /**
     * @fn insertQuest
     * @note Inserimento di una nuova quest
     * @param array $post
     * @return array
     */
    public function insertQuest(array $post): array
    {

        $resp = ['response' => false, 'mex' => 'Errore sconosciuto, contattare lo staff.'];
        $me = Functions::getInstance()->getMyId();

        if ($this->manageQuestPermission()) {

            $titolo = Filters::in($post['titolo']);
            $descr = Filters::in($post['descrizione']);
            $trama = Filters::int($post['trama']);
            $partecipanti = $post['new_part'];

            DB::query("INSERT INTO quest(titolo, partecipanti, descrizione, trama, data, autore) 
                    VALUES('{$titolo}','','{$descr}','{$trama}',NOW(),'{$me}')");

            $last_quest = DB::query("SELECT max(id) AS id FROM quest WHERE 1 LIMIT 1");
            $quest_id = Filters::int($last_quest['id']);
            $data_exp = $this->assignExp($partecipanti, $titolo, $quest_id);
            $assigned = Filters::in($data_exp['assigned']);


            DB::query("UPDATE quest SET partecipanti = '{$assigned}' WHERE id= {$quest_id}");

            return ['response' => true, 'Quest creata con successo'];

        } else {
            $resp = ['response' => false, 'mex' => 'Permessi negati.'];
        }


        return $resp;
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
        $me = Functions::getInstance()->getMyId();
        $quest_id = Filters::int($quest_id);
        $titolo = Filters::in($titolo);

        foreach ($partecipanti['pg'] as $index => $id_pg) {

            if (!in_array($id_pg, $assigned)) {

                $pg_px = $partecipanti['px'][$index];
                $pg_comm = $partecipanti['commento'][$index];
                $pg_name = Personaggio::nameFromId($id_pg);

                DB::query("INSERT INTO personaggio_quest(id_quest, personaggio, data, commento, px_assegnati, autore) 
                                VALUES('{$quest_id}','{$id_pg}',NOW(),'{$pg_comm}','{$pg_px}','{$me}')");

                DB::query("UPDATE personaggio SET esperienza = esperienza + {$pg_px} WHERE id='{$id_pg}' LIMIT 1");

                $log_text = Filters::in("Creata nuova quest '{$titolo}'");
                DB::query("INSERT INTO log(nome_interessato, autore, data_evento, codice_evento, descrizione_evento) 
                        VALUES('{$pg_name}','{$me}',NOW(),'{$this->px_code}','{$log_text}')  ");

                if ($this->notify_enabled) {
                    $notify_text = Filters::in("Creata nuova quest '{$titolo}");
                    $notify_title = Filters::in("Nuova quest");
                    DB::query("INSERT INTO messaggi (mittente, destinatario,oggetto, testo) VALUES ('Resoconti Quest','{$pg_name}','{$notify_title}','{$notify_text}')");
                }

                array_push($assigned, $id_pg);
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
        if ($this->manageQuestPermission()) {

            $quest = Filters::int($post['quest']);
            $titolo = Filters::in($post['titolo']);
            $descr = Filters::in($post['descrizione']);
            $partecipanti = $post['part'];
            $partecipanti_new = $post['new_part'];


            # Se la quest esiste
            if ($this->questExist($quest)) {

                # Assegno l'esperienza
                $this->updateQuestExp($post);
                $this->assignExp($partecipanti_new, $titolo, $quest);

                $partecipanti_id = $this->getIdsForMembers($partecipanti);

                if (Permissions::permission('MANAGE_TRAME')) {
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

                $resp = ['response' => true, 'mex' => 'Quest aggiornata con successo'];
            } else {
                $resp = ['response' => false, 'mex' => 'La quest scelta risulta inesistente.'];
            }

        } else {
            $resp = ['response' => true, 'mex' => 'Non hai i permessi per modificare un quest.'];
        }

        return $resp;

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

        foreach ($partecipanti as $id => $data) {
            array_push($array, $id);
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
        $me = Functions::getInstance()->getMyId();
        $partecipanti = $post['part'];

        # Per ogni partecipante scelto
        foreach ($partecipanti as $id => $pg) {

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
            if (Personaggio::pgExist($pg_id)) {

                # Se il personaggio ha gia' un'assegnazione inerente alla quest
                if (!empty($pg_quest_data['id'])) {

                    # Se l'esperienza e' cambiata
                    if ($pg_px != $pg_original_exp) {

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
                    DB::query("INSERT INTO personaggio_quest(id_quest, commento, personaggio, px_assegnati, autore) VALUES('{$quest}','{$pg_commento}','{$pg_id}','{$pg_px}','{$me}')");

                    # Update dell'esperienza
                    DB::query("UPDATE personaggio SET esperienza = esperienza + {$pg_px} WHERE id='{$pg_id}' LIMIT 1");

                    # Crea testi necessari
                    $notify_text = Filters::in("Il resoconto quest relativo alla Quest: <b>{$title}</b> è stato inserito. Puoi consultarlo andando su Scheda > Esperienza > Resoconti quest");
                    $notify_title = Filters::in("Inserimento nuovo resoconto.");
                    $notify = true;

                    $log_text = Filters::in("({$pg_px} xp) Assegnazione quest.");
                }

                # Inserisco il log
                if (isset($log_text)) {
                    DB::query("INSERT INTO log (nome_interessato, autore, codice_evento, descrizione_evento) VALUES('{$pg_nome}','{$me}','{$this->px_code}','{$log_text}')");
                }

                # Notifico l'utente
                if ($notify) {
                    if ($this->notify_enabled) {
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

        $quest_id = Filters::int($post['quest']);
        $resp = ['response' => false, 'mex' => 'Errore sconosciuto, contattare lo staff.'];

        if ($this->manageQuestPermission()) {
            if ($this->questExist($quest_id)) {


                $quest_data = $this->getQuest($quest_id);

                $this->deleteQuestExp($quest_data);

                DB::query("DELETE FROM quest WHERE id='{$quest_id}' LIMIT 1");

                $resp = ['response' => true, 'mex' => 'Quest eliminata con successo'];
            } else {
                $resp = ['response' => false, 'mex' => 'Quest inesistente.'];
            }
        } else {
            $resp = ['response' => false, 'mex' => 'Permessi negati.'];
        }

        return $resp;
    }

    /**
     * @fn deleteQuestExp
     * @note Elimina le assegnazioni di exp per la quest
     * @param array $data
     * @return void
     */
    private function deleteQuestExp(array $data): void
    {

        $quest_id = Filters::int($data['id']);
        $partecipanti = Filters::out($data['partecipanti']);
        $titolo = Filters::in($data['titolo']);
        $membri = explode(',', $partecipanti);
        $me = Functions::getInstance()->getMyId();

        foreach ($membri as $membro) {
            $data = $this->getQuestMemberData($quest_id, $membro);
            $pg_name = Personaggio::nameFromId($membro);
            $exp = Filters::int($data['px_assegnati']);

            DB::query("DELETE FROM personaggio_quest WHERE personaggio='{$membro}' AND id_quest='{$quest_id}'");

            DB::query("UPDATE personaggio SET esperienza = esperienza - {$exp} WHERE id='{$membro}' LIMIT 1");

            $log_text = Filters::in("La Quest '{$titolo}' è stata eliminata.");

            DB::query("INSERT INTO log(nome_interessato, autore, data_evento, codice_evento, descrizione_evento) 
                        VALUES('{$pg_name}','{$me}',NOW(),'{$this->px_code}','{$log_text}')  ");

            if ($this->notify_enabled) {

                $notify_title = Filters::in('Cancellazione quest.');
                $notify_text = Filters::in("La Quest '{$titolo}' è stata eliminata.");

                DB::query("INSERT INTO messaggi (mittente, destinatario,oggetto, testo) VALUES ('Resoconti Quest','{$pg_name}','{$notify_title}','{$notify_text}')");
            }
        }
    }

}