<?php


class Esiti extends BaseClass
{

    private
        $esiti_enabled,
        $esiti_chat,
        $esiti_from_player,
        $esiti_tiri;


    public function __construct()
    {
        parent::__construct();

        # Gli esiti sono attivi in chat?
        $this->esiti_enabled = Functions::get_constant('ESITI_ENABLE');

        #
        $this->esiti_chat = Functions::get_constant('ESITI_CHAT');

        # Gli esiti prevedono dei tiri dado?
        $this->esiti_tiri = Functions::get_constant('ESITI_TIRI');

        # Gli esiti sono creabili anche dai player?
        $this->esiti_from_player = Functions::get_constant('ESITI_FROM_PLAYER');
    }

    /**** ROUTING ***/

    /**
     * @fn loadManagementEsitiPage
     * @note Routing delle paginedi gestione
     * @param string $op
     * @return string
     */
    public function loadManagementEsitiPage(string $op): string
    {
        $op = Filters::out($op);

        switch ($op) {
            default:
                $page = 'esiti_list.php';
                break;

            case 'new':
                $page = 'esiti_new.php';
                break;

            case 'read':
                $page = 'esiti_read.php';
                break;

            case 'close':
                $page = 'esiti_close.php';
                break;

            case 'open':
                $page = 'esiti_open.php';
                break;

            case 'members':
                $page = 'esiti_members.php';
                break;

            case 'master':
                $page = 'esiti_master.php';
                break;
        }

        return $page;
    }

    /**
     * @fn loadServicePageEsiti
     * @note Routing delle pagine di servizi
     * @param string $op
     * @return string
     */
    public function loadServicePageEsiti(string $op): string
    {
        $op = Filters::out($op);

        switch ($op) {
            default:
                $page = 'esiti_list.php';
                break;

            case 'new':
                $page = 'esiti_new.php';
                break;

            case 'read':
                $page = 'esiti_read.php';
                break;

            case 'close':
                $page = 'esiti_close.php';
                break;
        }

        return $page;
    }

    /*** GETTER */

    /**
     * @fn esitiEnabled
     * @note Controlla se gli esiti sono abilitati
     * @return bool
     */
    public function esitiEnabled(): bool
    {
        return $this->esiti_enabled;
    }

    /**
     * @fn esitiTiriEnabled
     * @note Controlla se i tiri negli esiti sono abilitati
     * @return bool
     */
    public function esitiTiriEnabled(): bool
    {
        return $this->esiti_tiri;
    }

    /**
     * @fn esitiFromPlayerEnabled
     * @note Controlla se gli esiti sono creabili anche dai player
     * @return bool
     */
    public function esitiFromPlayerEnabled(): bool
    {
        return $this->esiti_from_player;
    }

    /*** PERMISSIONS */

    /**
     * @fn esitiManage
     * @note Controlla se si hanno i permessi per gestire gli esiti
     * @return bool
     */
    public function esitiManage(): bool
    {
        return Permissions::permission('MANAGE_ESITI');
    }

    /**
     * @fn esitiManage
     * @note Controlla se si hanno i permessi per gestire gli esiti altrui
     * @return bool
     */
    public function esitiManageAll(): bool
    {
        return Permissions::permission('MANAGE_ALL_ESITI');
    }

    /**
     * @fn esitoViewPermission
     * @note Controlla se si hanno i permessi per visualizzare quell'esito
     * @param int $id
     * @return bool
     */
    public function esitoViewPermission(int $id): bool
    {
        $id = Filters::int($id);

        if ($this->esitiManageAll()) {
            return true;
        } else {
            $data = $this->getEsito($id, 'autore,master');
            $autore = Filters::int($data['autore']);
            $master = Filters::int($data['master']);
            $player_perm = $this->esitoPlayerExist($id, $this->me_id);

            return (in_array($this->me_id, [$autore, $master]) || $player_perm);
        }
    }

    /**
     * @fn esitoAnswerPermission
     * @note Controlla se si hanno i permessi per rispondere ad un esito
     * @param int $id
     * @return bool
     */
    public function esitoAnswerPermission(int $id): bool
    {
        $id = Filters::int($id);

        if ($this->esitiManageAll()) {
            return true;
        } else {
            $data = $this->getEsito($id, 'autore,master');
            $autore = Filters::int($data['autore']);
            $master = Filters::int($data['master']);
            $player_perm = $this->esitoPlayerExist($id, $this->me_id);

            return (in_array($this->me_id, [$autore, $master]) || $player_perm);
        }
    }

    /**
     * @fn esitoClosePermission
     * @note Controlla se si hanno i permessi per chiudere un esito
     * @param int $id
     * @return bool
     */
    public function esitoClosePermission(int $id): bool
    {
        $id = Filters::int($id);

        if ($this->esitiManageAll()) {
            return true;
        } else {
            $data = $this->getEsito($id, 'master');
            $master = Filters::int($data['master']);

            return ($this->me_id == $master);
        }
    }

    /**
     * @fn esitoResultPermission
     * @note Controlla se si hanno i permessi per chiudere un esito
     * @param int $id
     * @return bool
     */
    public function esitoResultPermission(int $id): bool
    {
        $id = Filters::int($id);

        if ($this->esitiManageAll()) {
            return true;
        } else {
            $data = $this->getEsito($id, 'master');
            $master = Filters::int($data['master']);

            return ($this->me_id == $master);
        }
    }

    /**
     * @fn esitoAddPermission
     * @note Controlla se si hanno i permessi per chiudere un esito
     * @param int $id
     * @return bool
     */
    public function esitoMembersPermission(int $id): bool
    {
        $id = Filters::int($id);

        if ($this->esitiManageAll()) {
            return true;
        } else {
            $data = $this->getEsito($id, 'master');
            $master = Filters::int($data['master']);

            return ($this->me_id == $master);
        }
    }

    /*** TABLES HELPERS ***/

    /**
     * @fn getAllEsito
     * @note Ottiene la lista degli esiti
     * @param string $val
     * @param string $order
     * @return bool|int|mixed|string
     */
    public function getAllEsito(string $val = '*', string $order = '')
    {
        $where = ($this->esitiManageAll()) ? '1' : "(master = '0' OR master = '{$this->me_id}')";

        return DB::query("SELECT {$val} FROM esiti WHERE {$where} {$order}", 'result');
    }

    /**
     * @fn getAllEsitoPlayer
     * @note Ottiene la lista degli esiti per il pg selezionato
     * @param int $pg
     * @param string $val
     * @param string $order
     * @return bool|int|mixed|string
     */
    public function getAllEsitoPlayer(int $pg, string $val = 'esiti.*', string $order = '')
    {
        return DB::query("SELECT {$val} FROM esiti LEFT JOIN esiti_personaggio ON (esiti.id = esiti_personaggio.esito) WHERE esiti_personaggio.personaggio = '{$pg}' AND esiti.closed = 0 {$order}", 'result');
    }

    /**
     * @fn getEsito
     * @note Ottiene i dati di un esito
     * @param int $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getEsito(int $id, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM esiti WHERE id='{$id}' LIMIT 1");
    }

    /**
     * @fn getAnswer
     * @note Ottiene i dati di una risposta singola degli esiti
     * @param int $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAnswer(int $id, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM esiti_risposte WHERE id='{$id}' LIMIT 1");
    }

    /**
     * @fn getAnswerResults
     * @note Ottiene la lista dei risultati di una risposta con dadi
     * @param int $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAnswerResults(int $id, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM esiti_risposte_risultati WHERE esito='{$id}'", 'result');
    }

    /**
     * @fn getEsitoAllAnswers
     * @note Ottiene i dati di una risposta ad un esito
     * @param int $id
     * @param string $val
     * @param string $dir
     * @return bool|int|mixed|string
     */
    public function getEsitoAllAnswers(int $id, string $val = '*', string $dir = 'ASC')
    {
        return DB::query("SELECT {$val} FROM esiti_risposte WHERE esito = {$id} ORDER BY data {$dir}", 'result');
    }

    /**
     * @fn getEsitoAnswers
     * @note Ottiene il numero di risposte ad un esito
     * @param int $id
     * @return int
     */
    public function getEsitoAnswesNum(int $id): int
    {
        $data = DB::query("SELECT count(id) as tot FROM esiti_risposte 
                WHERE esito = {$id}");

        return Filters::int($data['tot']);
    }

    /**
     * @fn getLastEsitoId
     * @note Ottiene l'ultimo id inserito nella tabella esiti
     * @return int
     */
    public function getLastEsitoId(): int
    {
        $data = DB::query("SELECT max(id) as id FROM esiti WHERE 1 ORDER BY id DESC LIMIT 1");

        return Filters::int($data['id']);
    }


    /**
     * @fn getLastAnswerId
     * @note Ottiene l'ultimo id inserito nella tabella esiti
     * @return int
     */
    public function getLastAnswerId(): int
    {
        $data = DB::query("SELECT max(id) as id FROM esiti_risposte WHERE 1 ORDER BY id DESC LIMIT 1");

        return Filters::int($data['id']);
    }

    /**
     * @fn getPlayerEsito
     * @note Ottiene i dati di un pg rispetto ad un esito
     * @param int $id
     * @param int $pg
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getPlayerEsito(int $id, int $pg, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM esiti_personaggio WHERE esito='{$id}' AND personaggio='{$pg}' LIMIT 1");
    }

    /**
     * @fn getPlayerEsito
     * @note Ottiene i dati di un pg rispetto ad un esito
     * @param int $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllPlayerEsito(int $id, string $val = 'esiti_personaggio.*')
    {
        return DB::query("SELECT {$val} FROM esiti_personaggio 
                                LEFT JOIN personaggio ON (personaggio.id = esiti_personaggio.personaggio) 
                                WHERE esito='{$id}' ORDER BY personaggio.nome", 'result');
    }

    /**
     * @fn getPassedEsitoCD
     * @note Estrazione delle cd superate per un esito
     * @param int $id
     * @param int $result
     * @return bool|int|mixed|string
     */
    public function getPassedEsitoCD(int $id, int $result)
    {
        return DB::query("SELECT * FROM esiti_risposte_cd WHERE esito='{$id}' AND cd <= '{$result}'", 'result');
    }


    /*** CONTROLS ***/

    /**
     * @fn esitoExist
     * @note Controlla l'esistenza di un esito
     * @param int $id
     * @return bool
     */
    public function esitoExist(int $id): bool
    {
        $data = DB::query("SELECT count(id) AS tot FROM esiti WHERE id='{$id}' LIMIT 1");

        return ($data['tot'] > 0);
    }

    /**
     * @fn esitoPlayerExist
     * @note Controlla l'esistenza di un giocatore in un esito
     * @param int $id
     * @param int $pg
     * @return bool
     */
    public function esitoPlayerExist(int $id, int $pg): bool
    {
        $data = DB::query("SELECT count(id) AS tot FROM esiti_personaggio WHERE esito='{$id}' AND personaggio='{$pg}' LIMIT 1");
        return ($data['tot'] > 0);
    }

    /**
     * @fn getEsitoRead
     * @note Ottiene i dati di una lettura di un esito
     * @param int $id
     * @param int $pg
     * @return bool
     */
    public function esitoReaded(int $id, int $pg)
    {
        $data = DB::query("SELECT count(id) AS tot FROM esiti_risposte_letture WHERE esito = {$id} AND personaggio='{$pg}'");

        return ($data['tot'] > 0);
    }

    /**
     * @fn getEsitoRead
     * @note Ottiene i dati di una lettura di un esito
     * @param int $id
     * @return bool
     */
    public function esitoClosed(int $id): bool
    {
        $data = DB::query("SELECT closed FROM esiti WHERE id = {$id} LIMIT 1");

        return Filters::bool($data['closed']);
    }

    /*** ESITI CHAT */

    /**
     * @fn esitiChatList
     * @note Render list esiti in chat per il pg loggato
     * @return string
     */
    public function esitiChatList(): string
    {
        $html = '';
        $abilita = Abilita::getInstance();

        if ($this->esitiEnabled() && $this->esitiTiriEnabled()) {
            $luogo = Personaggio::getPgLocation($this->me_id);

            $list = DB::query("SELECT esiti_risposte.* , esiti.titolo
                    FROM esiti 
                    LEFT JOIN esiti_risposte ON (esiti.id = esiti_risposte.esito)
                    LEFT JOIN esiti_personaggio ON (esiti.id = esiti_personaggio.esito AND esiti_personaggio.personaggio = '{$this->me_id}')
                    LEFT JOIN esiti_risposte_risultati ON (esiti_risposte_risultati.esito = esiti_risposte.id AND esiti_risposte_risultati.personaggio = '{$this->me_id}')
                    WHERE esiti_risposte.chat = '{$luogo}' 
                    AND esiti_personaggio.id IS NOT NULL
                    AND esiti_risposte_risultati.id IS NULL
                    AND esiti.closed = 0
                    ORDER BY esiti_risposte.data DESC", 'result');

            foreach ($list as $row) {

                $id = Filters::int($row['id']);
                $abi_data = $abilita->getAbilita(Filters::int($row['abilita']), 'nome');

                $html .= "<div class='tr'>";
                $html .= "<div class='td'>" . Filters::out($row['titolo']) . "</div>";
                $html .= "<div class='td'>" . Filters::date($row['data'], 'd/M/Y') . "</div>";
                $html .= "<div class='td'>" . Filters::int($row['dice_num']) . " dadi da " . Filters::int($row['dice_face']) . "</div>";
                $html .= "<div class='td'>" . Filters::text($abi_data['nome']) . "</div>";
                $html .= "<div class='td'>
                            <form method='POST' class='chat_form_ajax'>
                                <input type='hidden' name='action' value='send_esito'>
                                <input type='hidden' name='id' value='{$id}'>
                                <button type='submit'><i class='fas fa-dice'></i></button>
                            </form>
                         </div>";
                $html .= "</div>";
            }
        }

        return $html;
    }

    /**
     * @fn rollEsito
     * @note Utilizzo di un esito in chat
     * @param array $post
     * @return array
     */
    public function rollEsito(array $post): array
    {
        $chat = new Chat();
        $id = Filters::int($post['id']);
        $data = $this->getAnswer($id);
        $id_answer = Filters::int($data['id']);
        $dice_face = Filters::int($data['dice_face']);
        $dice_num = Filters::int($data['dice_num']);
        $luogo = Personaggio::getPgLocation($this->me_id);

        $abi_roll = $chat->rollAbility(Filters::int($data['abilita']));

        # Filtro i dati ricevuti
        $abi_dice = Filters::int($abi_roll['abi_dice']);
        $abi_nome = Filters::in($abi_roll['nome']);
        $car = Filters::int($abi_roll['car']);

        $dice = $chat->rollCustomDice($dice_num, $dice_face);
        $result = ($dice + $abi_dice + $car);

        $testo = 'Tiro: ' . $abi_nome . ', risultato totale: ' . $result . ' ';
        $testo_sussurro = Filters::in($this->esitoResultText($id_answer, $result));

        DB::query("INSERT INTO chat(stanza, mittente,destinatario,tipo,testo)
								  VALUE('{$luogo}', 'Esiti','{$this->me}','C','{$testo}')");

        DB::query("INSERT INTO chat(stanza, mittente,destinatario,tipo,testo)
								  VALUE('{$luogo}', 'Esiti','{$this->me}','S','{$testo_sussurro}')");

        DB::query("INSERT INTO esiti_risposte_risultati(esito,personaggio,risultato,testo) VALUES('{$id_answer}','{$this->me_id}','{$result}','{$testo_sussurro}')");

        return ['response' => true, 'error' => ''];
    }

    /**
     * @fn esitoResultText
     * @note Testo dei risultati trovati dalla prova esito
     * @param int $id
     * @param int $result
     * @return string
     */
    public function esitoResultText(int $id, int $result): string
    {
        $html = ' Hai scoperto: ';
        $list = $this->getPassedEsitoCD($id, $result);

        if (DB::rowsNumber($list) > 0) {
            foreach ($list as $cd) {
                $text = Filters::text($cd['testo']);

                $html .= "  {$text}  |";
            }
        } else {
            $html = 'Non hai scoperto nulla.';
        }

        return trim($html, '| ');
    }

    /*** ESITI INDEX ***/

    /**
     * @fn haveNewResponse
     * @note Controlla se un esito ha nuove risposte
     * @param int $id
     * @return int
     */
    public function haveNewResponse(int $id): int
    {
        $new = DB::query("SELECT count(esiti_risposte.id) as tot FROM esiti_risposte 
                    LEFT JOIN esiti_risposte_letture ON (esiti_risposte_letture.esito = esiti_risposte.id AND esiti_risposte_letture.personaggio = '{$this->me_id}')
                    WHERE esiti_risposte.esito = {$id} AND esiti_risposte_letture.id IS NULL");

        return Filters::int($new['tot']);
    }

    /**
     * @fn esitiList
     * @note Render html della lista degli esiti
     * @return string
     */
    public function esitiListPLayer(): string
    {
        $list = $this->getAllEsitoPlayer($this->me_id, 'esiti.*', 'ORDER BY closed ASC,data ASC');
        return $this->renderEsitiList($list, 'servizi');
    }

    /**
     * @fn esitiListManagement
     * @note Render html della lista degli esiti
     * @return string
     */
    public function esitiListManagement(): string
    {
        $template = Template::getInstance()->startTemplate();
        $list = $this->getAllEsito('*', 'ORDER BY closed ASC,data ASC');
        return $template->renderTable(
            'gestione/esiti/list',
            $this->renderEsitiList($list, 'gestione')
        );
    }

    /**
     * @fn renderEsitiList
     * @note Render html lista esiti
     * @param object $list
     * @param string $page
     * @return array
     */
    public function renderEsitiList(object $list, string $page): array
    {
        $row_data = [];
        $path = ($page == 'servizi') ? 'servizi_esiti' : 'gestione_esiti';
        $backlink = ($page == 'servizi') ? 'uffici' : 'gestione';

        foreach ($list as $row) {

            $id = Filters::int($row['id']);

            if (Filters::int($row['master']) != 0) {
                $master = 'Presa in carico';
            } elseif ($row['closed']) {
                $master = 'Chiuso';
            } else {
                $master = '<u> In attesa di risposta </u>';
            }

            $array = [
                'id' => $id,
                'author'=> Personaggio::nameFromId(Filters::in($row['autore'])),
                'totale_esiti'=> $this->getEsitoAnswesNum($id),
                'new_response'=>$this->haveNewResponse($id),
                'closed' =>Filters::int($row['closed']),
                'closed_cls'=>Filters::bool($row['closed']) ? 'closed' : '',
                'date'=>Filters::date($row['data'], 'd/m/Y'),
                'titolo'=>Filters::out($row['titolo']),
                'master' => $master,
                'esito_view_permission'=> $this->esitoViewPermission($id),
                'esito_membri_permission' => $this->esitoMembersPermission($id),
                'esito_manage' => $this->esitiManageAll(),
                'esiti_close_permission' => $this->esitoClosePermission($id),
                'esiti_from_player_enabled' => $this->esitiFromPlayerEnabled()
            ];

            $row_data[] = $array;
        }

        $cells = [
            'Data',
            'Autore',
            'Stato',
            'Titolo',
            'Numero Esiti',
            'Nuove risposte',
            'Controlli'
        ];
        $links = [
            ['href' => "/main.php?page={$path}&op=new", 'text' => 'Nuovo esito'],
            ['href' => "/main.php?page={$backlink}", 'text' => 'Indietro']
        ];
        $footer_text = 'Testo di prova footer';
        return [
                'body' => 'gestione/esiti/list',
                'body_rows'=> $row_data,
                'cells' => $cells,
                'links' => $links,
                'path'=>$path,
            'page'=>$page,
                'footer_text' => $footer_text
            ];
    }

    /**
     * @fn htmlCDAdd
     * @note Aggiunge un input alla pagina per aggiungere una cd
     * @return array
     */
    public function htmlCDAdd(): array
    {
        $html = '';

        $html .= "<div class='single_cd' > ";

        $html .= "<div class='single_input' > ";
        $html .= "<div class='label' > CD</div > ";
        $html .= "<input type = 'number' name = 'add_cd[cd][]' > ";
        $html .= "</div > ";

        $html .= "<div class='single_input' > ";
        $html .= "<div class='label' > Testo</div > ";
        $html .= "<textarea name = 'add_cd[text][]' ></textarea > ";
        $html .= "</div > ";

        $html .= "</div > ";


        return ['InputHtml' => $html];

    }

    /*** NEW ESITO ***/

    /**
     * @fn newEsitoManagement
     * @note Inserisce un nuovo esito da parte del master
     * @param array $post
     * @return array
     */
    public function newEsitoManagement(array $post): array
    {

        if ($this->esitiManage()) {

            $titolo = Filters::in($post['titolo']);
            $ms = Filters::in($post['contenuto']);
            $dice_num = Filters::int($post['dice_num']);
            $dice_face = Filters::int($post['dice_face']);
            $abilita = Filters::int($post['abilita']);
            $chat = Filters::int($post['chat']);

            DB::query("INSERT INTO esiti(titolo, autore) VALUES('{$titolo}', '{$this->me_id}')");

            $last_id = $this->getLastEsitoId();

            DB::query("INSERT INTO esiti_risposte(esito, autore, contenuto, dice_face, dice_num, abilita, chat)
                        VALUES('{$last_id}', '{$this->me_id}', '{$ms}', '{$dice_face}', '{$dice_num}', '{$abilita}', '{$chat}')  ");

            if (!empty($post['add_cd'])) {
                $last_answer = $this->getLastAnswerId();
                $this->addCD($last_answer, $post['add_cd']);
            }

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Esito creato con successo.',
                'swal_type' => 'success'
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error'
            ];
        }
    }

    /**
     * @fn newEsitoManagement
     * @note Inserisce un nuovo esito da parte del master
     * @param array $post
     * @return array
     */
    public function newEsitoPlayer(array $post): array
    {

        if ($this->esitiFromPlayerEnabled()) {
            $titolo = Filters::in($post['titolo']);
            $ms = Filters::in($post['contenuto']);

            DB::query("INSERT INTO esiti(titolo, autore) VALUES('{$titolo}', '{$this->me_id}')");

            $last_id = $this->getLastEsitoId();

            DB::query("INSERT INTO esiti_risposte(esito, autore, contenuto)
                        VALUES('{$last_id}', '{$this->me_id}', '{$ms}')  ");
            DB::query("INSERT INTO esiti_personaggio(personaggio, esito, assegnato_da)
                        VALUES('{$this->me_id}', '{$last_id}', '{$this->me_id}')  ");

            return ['response' => true, 'mex' => 'Esito creato con successo.'];
        } else {
            return ['response' => false, 'mex' => 'Permesso negato'];
        }
    }

    /*** READ ESITO */

    /**
     * @fn renderEsitoAnswers
     * @note Render html della lista delle risposte ad un esito
     * @param int $id
     * @return string
     */
    public function renderEsitoAnswers(int $id): string
    {
        $html = '';
        $id = Filters::int($id);

        if ($this->esitoViewPermission($id)) {

            $list = $this->getEsitoAllAnswers($id);

            foreach ($list as $answer) {

                $id_answer = Filters::int($answer['id']);
                $this->readAnswer($id_answer);

                $autore = Filters::int($answer['autore']);
                $mine = ($autore == $this->me_id) ? 'mine' : 'other';
                $dice_face = Filters::int($answer['dice_face']);
                $dice_num = Filters::int($answer['dice_num']);
                $id_abi = Filters::int($answer['abilita']);

                $html .= "<div class='single_answer {$mine}' > ";

                $html .= "<div class='text' > " . Filters::text($answer['contenuto']) . "</div > ";

                if (($dice_num > 0) && $this->esitiTiriEnabled()) {

                    $abi = Abilita::getInstance();
                    $abi_data = $abi->getAbilita($id_abi, 'nome');
                    $abi_name = Filters::out($abi_data['nome']);

                    $chat = new Chat();
                    $chat_id = Filters::int($answer['chat']);
                    $chat_name = $chat->getChatData($chat_id, 'nome')['nome'];

                    $html .= "<div class='dice' > ";
                    $html .= "<span > Sono richiesti {
                $dice_num} dadi da {
                $dice_face} su {
                $abi_name} in chat: <a href = '/main.php?dir={$chat_id}' >{
                $chat_name}</a > . </span > ";
                    $html .= "</div > ";

                    $results = $this->getAnswerResults($id_answer);

                    foreach ($results as $result) {

                        $pg = Filters::int($result['personaggio']);
                        $pg_name = Personaggio::nameFromId($pg);
                        $res_text = Filters::text($result['testo']);
                        $res_num = Filters::int($result['risultato']);

                        if ($this->esitoResultPermission($id) || ($pg == $this->me_id)) {
                            $html .= "<div class='dice_result' > {
                $pg_name} : <span >{
                $res_num}</span > <div class='internal_text' > {
                $res_text}</div > </div > ";
                        }
                    }


                }

                $html .= "<div class='sub_text' > ";
                $html .= "<span class='author' > " . Personaggio::nameFromId($autore) . "</span > -";
                $html .= "<span class='date' > " . Filters::date($answer['data'], 'H:i d/m/Y') . "</span > ";
                $html .= "</div > ";

                $html .= "</div > ";

            }
        }

        return $html;
    }

    /**
     * @fn readAnswersMaster
     * @note Segna le risposte di un estio come lette dal master
     * @param int $id
     * @return void
     */
    public function readAnswer(int $id): void
    {
        if (!$this->esitoReaded($id, $this->me_id)) {
            DB::query("INSERT INTO esiti_risposte_letture(esito, personaggio) VALUES('{$id}', '{$this->me_id}')");
        }
    }

    /**
     * @fn newAnswer
     * @note Inserisce una nuova risposta in un esito
     * @param array $post
     * @return array
     */
    public function newAnswer(array $post): array
    {

        $id = Filters::int($post['id_record']);

        if ($this->esitoAnswerPermission($id) && !$this->esitoClosed($id)) {

            $perm_dadi = ($this->esitiTiriEnabled() && ($this->esitiManage() || $this->esitiManageAll()));

            $contenuto = Filters::in($post['contenuto']);
            $dice_num = ($perm_dadi) ? Filters::int($post['dadi_num']) : 0;
            $dice_face = ($perm_dadi) ? Filters::int($post['dadi_face']) : 0;
            $abilita = ($perm_dadi) ? Filters::int($post['abilita']) : 0;
            $chat = ($perm_dadi) ? Filters::int($post['chat']) : 0;

            DB::query("INSERT INTO esiti_risposte(esito, autore, contenuto, dice_face, dice_num, abilita, chat)
                        VALUES('{$id}', '{$this->me_id}', '{$contenuto}', '{$dice_face}', '{$dice_num}', '{$abilita}', '{$chat}')  ");

            if (!empty($post['add_cd'])) {
                $last_id = $this->getLastAnswerId();
                $this->addCD($last_id, $post['add_cd']);
            }

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Risposta aggiunta correttamente.',
                'swal_type' => 'success',
                'new_view' => $this->renderEsitoAnswers($id)
            ];

        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error'
            ];
        }
    }

    /**
     * @fn addCD
     * @note Aggiunge delle cd per una risposta
     * @param int $id
     * @param array $cds
     * @return void
     */
    public function addCD(int $id, array $cds = []): void
    {

        $id = Filters::int($id);

        foreach ($cds['cd'] as $index => $cd) {

            $cd = Filters::int($cd);
            $testo = Filters::in($cds['text'][$index]);

            if ($cd > 0) {
                DB::query("INSERT INTO esiti_risposte_cd(esito, cd, testo) VALUES('{$id}', '{$cd}', '{$testo}')");
            }
        }

    }

    /*** MEMBERS LIST ***/

    /**
     * @fn membersList
     * @note Render html dei membri di un esito
     * @param int $id
     * @return string
     */
    public function membersList(int $id): string
    {

        $html = '<div class="tr header">
            <div class="td">Membro</div>
            <div class="td">Controlli</div>
        </div>';
        $list = $this->getAllPlayerEsito($id);

        foreach ($list as $row) {
            $id_row = Filters::int($row['id']);

            $html .= "<div class='tr' > ";
            $html .= "<div class='td' > " . Personaggio::nameFromId(Filters::int($row['personaggio'])) . "</div > ";
            $html .= "<div class='td' > ";

            $html .= "<form method = 'POST' class='delete_member_form' >
                        <input type = 'hidden' name = 'action' value = 'delete_member' >
                        <input type = 'hidden' name = 'id' value = '{$id_row}' >
                        <input type = 'hidden' name = 'id_esito' value = '{$id}' >
                        <button type = 'submit' title = 'Elimina membro' ><i class='fas fa-user-minus' ></i ></button >
                        </form > ";

            $html .= "</div > ";
            $html .= "</div > ";

        }

        return $html;
    }

    /**
     * @fn addMember
     * @note Aggiunge un membro ad un esito
     * @param array $post
     * @return array
     */
    public function addMember(array $post): array
    {

        $id = Filters::int($post['id']);

        if ($this->esitoMembersPermission($id)) {

            $pg = Filters::int($post['personaggio']);

            if (!$this->esitoPlayerExist($id, $pg)) {

                DB::query("INSERT INTO esiti_personaggio(esito, personaggio, assegnato_da) VALUE('{$id}', '{$pg}', '{$this->me_id}')");

                return [
                    'response' => true,
                    'swal_title' => 'Operazione riuscita!',
                    'swal_message' => 'Personaggio inserito correttamente.',
                    'swal_type' => 'success',
                    'members_list' => $this->membersList($id)
                ];
            } else {
                return [
                    'response' => false,
                    'swal_title' => 'Operazione fallita!',
                    'swal_message' => 'Personaggio già esistente.',
                    'swal_type' => 'error'
                ];
            }


        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error'
            ];
        }

    }

    /**
     * @fn deleteMember
     * @note Rimuove un membro da un esito
     * @param array $post
     * @return array
     */
    public function deleteMember(array $post): array
    {

        $id = Filters::int($post['id']);
        $id_esito = Filters::int($post['id_esito']);

        if ($this->esitoMembersPermission($id_esito)) {

            DB::query("DELETE FROM esiti_personaggio WHERE id = '{$id}' LIMIT 1");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Personaggio rimosso correttamente.',
                'swal_type' => 'success',
                'members_list' => $this->membersList($id_esito)
            ];

        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error'
            ];
        }

    }

    /*** MASTER ASSIGN **/

    /**
     * @fn esitiManagersList
     * @note Estrae la lista di personaggi che hanno i permessi per gestire gli esiti
     * @return string
     */
    public function esitiManagersList(): string
    {

        $html = '';
        $list = Permissions::getPgListPermissions(['MANAGE_ESITI']);

        foreach ($list as $pg) {
            $name = Personaggio::nameFromId($pg);
            $html .= " < option value = '{$pg}' >{
                $name}</option > ";
        }

        return $html;
    }

    public function setMaster($post)
    {

        $id = Filters::int($post['id']);

        if ($this->esitiManageAll()) {

            $pg = Filters::int($post['personaggio']);

            DB::query("UPDATE esiti SET master = '{$pg}' WHERE id = '{$id}' LIMIT 1");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Master assegnato con successo.',
                'swal_type' => 'success'
            ];

        } else {

            return [
                'response' => true,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error'
            ];
        }


    }

    /*** RE-OPEN ESITO ***/

    /**
     * @fn esitoOpen
     * @note Riapertura di un esito
     * @param int $id
     * @return array
     */
    public function esitoOpen(int $id): array
    {
        $id = Filters::int($id);

        if ($this->esitiManageAll()) {

            DB::query("UPDATE esiti SET closed = 0 WHERE id = '{$id}' LIMIT 1");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Esito riaperto con successo.',
                'swal_type' => 'success',
                'esiti_list' => $this->esitiListManagement()
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error'
            ];
        }
    }

    /*** CLOSE ESITO ***/

    /**
     * @fn esitoClose
     * @note Chiusura di un esito
     * @param int $id
     * @return array
     */
    public function esitoClose(int $id): array
    {
        $id = Filters::int($id);

        if ($this->esitoClosePermission($id)) {

            DB::query("UPDATE esiti SET closed = 1 WHERE id = '{$id}' LIMIT 1");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Esito chiuso con successo.',
                'swal_type' => 'success',
                'esiti_list' => $this->esitiListManagement()
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error'
            ];
        }
    }
}