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
        $data = DB::query("SELECT count(id) AS tot FROM esiti_letture WHERE esito = {$id} AND personaggio='{$pg}'");

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
                    LEFT JOIN esiti_letture ON (esiti_letture.esito = esiti_risposte.id AND esiti_letture.personaggio = '{$this->me_id}')
                    WHERE esiti_risposte.esito = {$id} AND esiti_letture.id IS NULL");

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
        $list = $this->getAllEsito('*', 'ORDER BY closed ASC,data ASC');
        return $this->renderEsitiList($list, 'gestione');
    }

    /**
     * @fn renderEsitiList
     * @note Render html lista esiti
     * @param object $list
     * @param string $page
     * @return string
     */
    public function renderEsitiList(object $list, string $page): string
    {

        $html = '';

        $path = ($page == 'servizi') ? 'servizi_esiti' : 'gestione_esiti';

        foreach ($list as $row) {

            $id = Filters::int($row['id']);
            $author = Filters::in($row['autore']);
            $totale_esiti = $this->getEsitoAnswesNum($id);
            $new_response = $this->haveNewResponse($id);
            $closed = ($row['closed']) ? 'closed' : '';


            if ($row['master'] != 0) {
                $master = 'Presa in carico';
            } elseif ($row['closed']) {
                $master = 'Chiuso';
            } else {
                $master = '<u> In attesa di risposta </u>';
            }

            $html .= "<div class='tr {$closed}'>";
            $html .= "<div class='td'>" . Filters::date($row['data'], 'd/m/Y') . '</div>';
            $html .= "<div class='td'>" . Personaggio::nameFromId($author) . '</div>';
            $html .= "<div class='td'>{$master}</div>";
            $html .= "<div class='td'>" . Filters::out($row['titolo']) . '</div>';
            $html .= "<div class='td'>{$totale_esiti}</div>";
            $html .= "<div class='td'>{$new_response}</div>";
            $html .= "<div class='td'>";

            if ($this->esitoViewPermission($id)) {
                $html .= "<a href='/main.php?page={$path}&op=read&id_record={$id}' title='Leggi'><i class='fas fa-eye'></i></a>";
            }

            if ($this->esitoClosePermission($id) && ($page == 'gestione')) {

                $html .= " <a href='/main.php?page={$path}&op=close&id_record={$id}' title='Chiudi'><i class='far fa-times-circle'></i></a>";
            }

            $html .= "</div>";
            $html .= "</div>";
        }

        return $html;
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
            $note = Filters::in($post['note']);

            DB::query("INSERT INTO esiti(titolo, autore) VALUES('{$titolo}','{$this->me_id}')");

            $last_id = $this->getLastEsitoId();

            DB::query("INSERT INTO esiti_risposte(esito, autore, contenuto, noteoff,dice_face,dice_num )
                        VALUES('{$last_id}','{$this->me_id}','{$ms}','{$note}','{$dice_face}','{$dice_num}')  ");

            return ['response' => true, 'mex' => 'Esito creato con successo.'];
        } else {
            return ['response' => false, 'mex' => 'Permesso negato.'];
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

        if($this->esitiFromPlayerEnabled()) {
            $titolo = Filters::in($post['titolo']);
            $ms = Filters::in($post['contenuto']);

            DB::query("INSERT INTO esiti(titolo, autore) VALUES('{$titolo}','{$this->me_id}')");

            $last_id = $this->getLastEsitoId();

            DB::query("INSERT INTO esiti_risposte(esito, autore, contenuto)
                        VALUES('{$last_id}','{$this->me_id}','{$ms}')  ");
            DB::query("INSERT INTO esiti_personaggio(personaggio, esito, assegnato_da)
                        VALUES('{$this->me_id}','{$last_id}','{$this->me_id}')  ");

            return ['response' => true, 'mex' => 'Esito creato con successo.'];
        } else{
            return ['response'=>false,'mex'=>'Permesso negato'];
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

                $this->readAnswer($answer['id']);

                $autore = Filters::int($answer['autore']);
                $mine = ($autore == $this->me_id) ? 'mine' : 'other';
                $dice_face = Filters::int($answer['dice_face']);
                $dice_num = Filters::int($answer['dice_num']);

                $html .= "<div class='single_answer {$mine}'>";

                $html .= "<div class='text'>" . Filters::text($answer['contenuto']) . "</div>";

                if (($dice_num > 0) && $this->esitiTiriEnabled()) {
                    $html .= "<div class='dice'>";
                    $html .= "<span> Per questo esito sono richiesti {$dice_num} dadi da {$dice_face}. </span>";
                    $html .= "</div>";
                }

                $html .= "<div class='sub_text'>";
                $html .= "<span class='author'>" . Personaggio::nameFromId($autore) . "</span> - ";
                $html .= "<span class='date'>" . Filters::date($answer['data'], 'H:i d/m/Y') . "</span>";
                $html .= "</div>";

                $html .= "</div>";

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
            DB::query("INSERT INTO esiti_letture(esito, personaggio) VALUES('{$id}','{$this->me_id}')");
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

        $id = Filters::int($id);

        if ($this->esitoAnswerPermission($id) && !$this->esitoClosed($id)) {

            $perm_dadi = ($this->esitiTiriEnabled() && ($this->esitiManage() || $this->esitiManageAll()));

            $contenuto = Filters::in($post['contenuto']);
            $dice_num = ($perm_dadi) ? Filters::int($post['dadi_num']) : 0;
            $dice_face = ($perm_dadi) ? Filters::int($post['dadi_face']) : 0;

            DB::query("INSERT INTO esiti_risposte(esito, autore, contenuto,dice_face,dice_num )
                        VALUES('{$id}','{$this->me_id}','{$contenuto}','{$dice_face}','{$dice_num}')  ");

            $resp = ['response' => true, 'mex' => 'Risposta aggiunta correttamente.'];

        } else {
            $resp = ['response' => false, 'mex' => 'Permesso Negato'];
        }

        return $resp;
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

            DB::query("UPDATE esiti SET closed=1 WHERE id='{$id}' LIMIT 1");

            $resp = ['response' => true, 'mex' => 'Esito chiuso con successo.'];
        } else {
            $resp = ['response' => false, 'mex' => 'Permesso negato'];
        }

        return $resp;
    }
}