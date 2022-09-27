<?php

class Esiti extends BaseClass
{

    private bool
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
     * @note Routing delle pagine di gestione
     * @param string $op
     * @return string
     */
    public function loadManagementEsitiPage(string $op): string
    {
        $op = Filters::out($op);

        return match ($op) {
            default => 'esiti_list.php',
            "new" => "esiti_new.php",
            "read" => "esiti_read.php",
            "close" => "esiti_close.php",
            "open" => "esiti_open.php",
            "members" => "esiti_members.php",
            "master" => "esiti_master.php"
        };
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

        return match ($op) {
            default => 'esiti_list.php',
            "new" => "esiti_new.php",
            "read" => "esiti_read.php",
            "close" => "esiti_close.php"
        };
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
     * @throws Throwable
     */
    public function esitoViewPermission(int $id): bool
    {
        $id = Filters::int($id);

        if ( $this->esitiManageAll() ) {
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
     * @note Controlla se si hanno i permessi per rispondere a un esito
     * @param int $id
     * @return bool
     * @throws Throwable
     */
    public function esitoAnswerPermission(int $id): bool
    {
        $id = Filters::int($id);

        if ( $this->esitiManageAll() ) {
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
     * @throws Throwable
     */
    public function esitoClosePermission(int $id): bool
    {
        $id = Filters::int($id);

        if ( $this->esitiManageAll() ) {
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
     * @throws Throwable
     */
    public function esitoResultPermission(int $id): bool
    {
        $id = Filters::int($id);

        if ( $this->esitiManageAll() ) {
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
     * @throws Throwable
     */
    public function esitoMembersPermission(int $id): bool
    {
        $id = Filters::int($id);

        if ( $this->esitiManageAll() ) {
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
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllEsito(string $val = '*', string $order = ''): DBQueryInterface
    {
        $where = ($this->esitiManageAll()) ? '1' : "(master = '0' OR master = '{$this->me_id}')";

        return DB::queryStmt("SELECT {$val} FROM esiti WHERE {$where} {$order}", []);
    }

    /**
     * @fn getAllEsitoPlayer
     * @note Ottiene la lista degli esiti per il pg selezionato
     * @param int $pg
     * @param string $val
     * @param string $order
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllEsitoPlayer(int $pg, string $val = 'esiti.*', string $order = ''): DBQueryInterface
    {
        return DB::queryStmt(
            "SELECT {$val} FROM esiti
                    LEFT JOIN esiti_personaggio ON (esiti.id = esiti_personaggio.esito) 
                    WHERE esiti_personaggio.personaggio = :pg AND esiti.closed = 0 {$order}",
            [
                'pg' => $pg,
            ]
        );
    }

    /**
     * @fn getEsito
     * @note Ottiene i dati di un esito
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getEsito(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt(
            "SELECT {$val} FROM esiti WHERE id=:id LIMIT 1",
            [
                'id' => $id,
            ]
        );
    }

    /**
     * @fn getAnswer
     * @note Ottiene i dati di una risposta singola degli esiti
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAnswer(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt(
            "SELECT {$val} FROM esiti_risposte WHERE id=:id LIMIT 1",
            [
                'id' => $id,
            ]
        );
    }

    /**
     * @fn getAnswerResults
     * @note Ottiene la lista dei risultati di una risposta con dadi
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAnswerResults(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM esiti_risposte_risultati WHERE esito=:id",
            [
                'id' => $id,
            ]
        );
    }

    /**
     * @fn getEsitoAllAnswers
     * @note Ottiene i dati di una risposta a un esito
     * @param int $id
     * @param string $val
     * @param string $dir
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getEsitoAllAnswers(int $id, string $val = '*', string $dir = 'ASC'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM esiti_risposte WHERE esito = :id ORDER BY data {$dir}",
            [
                'id' => $id,
            ]
        );
    }

    /**
     * @fn getEsitoAnswers
     * @note Ottiene il numero di risposte a un esito
     * @param int $id
     * @return int
     * @throws Throwable
     */
    public function getEsitoAnswersNum(int $id): int
    {
        $data = DB::queryStmt(
            "SELECT count(id) as tot FROM esiti_risposte 
                WHERE esito = :id",
            ['id' => $id,]
        );

        return Filters::int($data['tot']);
    }

    /**
     * @fn getLastEsitoId
     * @note Ottiene l'ultimo id inserito nella tabella esiti
     * @return int
     * @throws Throwable
     */
    public function getLastEsitoId(): int
    {
        $data = DB::queryStmt("SELECT max(id) as id FROM esiti WHERE 1 ORDER BY id DESC LIMIT 1", []);
        return Filters::int($data['id']);
    }

    /**
     * @fn getLastAnswerId
     * @note Ottiene l'ultimo id inserito nella tabella esiti
     * @return int
     * @throws Throwable
     */
    public function getLastAnswerId(): int
    {
        $data = DB::queryStmt("SELECT max(id) as id FROM esiti_risposte WHERE 1 ORDER BY id DESC LIMIT 1");
        return Filters::int($data['id']);
    }

    /**
     * @fn getPlayerEsito
     * @note Ottiene i dati di un pg rispetto a un esito
     * @param int $id
     * @param int $pg
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getPlayerEsito(int $id, int $pg, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt(
            "SELECT {$val} FROM esiti_personaggio WHERE esito=:id AND personaggio=:pg LIMIT 1",
            [
                'id' => $id,
                'pg' => $pg,
            ]
        );
    }

    /**
     * @fn getPlayerEsito
     * @note Ottiene i dati di un pg rispetto a un esito
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllPlayerEsito(int $id, string $val = 'esiti_personaggio.*'): DBQueryInterface
    {
        return DB::queryStmt(
            "SELECT {$val} FROM esiti_personaggio 
                                LEFT JOIN personaggio ON (personaggio.id = esiti_personaggio.personaggio) 
                                WHERE esito=:id ORDER BY personaggio.nome",
            ['id' => $id]
        );
    }

    /**
     * @fn getPassedEsitoCD
     * @note Estrazione delle cd superate per un esito
     * @param int $id
     * @param int $result
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getPassedEsitoCD(int $id, int $result): DBQueryInterface
    {
        return DB::queryStmt(
            "SELECT * FROM esiti_risposte_cd WHERE esito= :id AND cd <= :result",
            [
                'id' => $id,
                'result' => $result,
            ]
        );
    }


    /*** CONTROLS ***/

    /**
     * @fn esitoExist
     * @note Controlla l'esistenza di un esito
     * @param int $id
     * @return bool
     * @throws Throwable
     */
    public function esitoExist(int $id): bool
    {
        $data = DB::queryStmt(
            "SELECT count(id) AS tot FROM esiti WHERE id=:id LIMIT 1",
            ['id' => $id,]
        );
        return ($data['tot'] > 0);
    }

    /**
     * @fn esitoPlayerExist
     * @note Controlla l'esistenza di un giocatore in un esito
     * @param int $id
     * @param int $pg
     * @return bool
     * @throws Throwable
     */
    public function esitoPlayerExist(int $id, int $pg): bool
    {
        $data = DB::queryStmt(
            "SELECT count(id) AS tot FROM esiti_personaggio WHERE esito=:id AND personaggio=:pg LIMIT 1",
            [
                'id' => $id,
                'pg' => $pg,
            ]
        );
        return ($data['tot'] > 0);
    }

    /**
     * @fn getEsitoRead
     * @note Ottiene i dati di una lettura di un esito
     * @param int $id
     * @param int $pg
     * @return bool
     * @throws Throwable
     */
    public function esitoReaded(int $id, int $pg): bool
    {
        $data = DB::queryStmt(
            "SELECT count(id) AS tot FROM esiti_risposte_letture WHERE esito = :id AND personaggio=:pg",
            [
                'id' => $id,
                'pg' => $pg,
            ]
        );

        return ($data['tot'] > 0);
    }

    /**
     * @fn getEsitoRead
     * @note Ottiene i dati di una lettura di un esito
     * @param int $id
     * @return bool
     * @throws Throwable
     */
    public function esitoClosed(int $id): bool
    {
        $data = DB::queryStmt(
            "SELECT closed FROM esiti WHERE id = :id LIMIT 1",
            [
                'id' => $id,
            ]
        );

        return Filters::bool($data['closed']);
    }

    /*** ESITI CHAT */

    /**
     * @fn esitiChatList
     * @note Render list esiti in chat per il pg connesso
     * @return string
     * @throws Throwable
     */
    public function esitiChatList(): string
    {

        if ( $this->esitiEnabled() && $this->esitiTiriEnabled() ) {
            $luogo = Personaggio::getPgLocation();

            $esiti = DB::queryStmt(
                "SELECT esiti_risposte.* , esiti.titolo
                    FROM esiti 
                    LEFT JOIN esiti_risposte ON (esiti.id = esiti_risposte.esito)
                    LEFT JOIN esiti_personaggio ON (esiti.id = esiti_personaggio.esito AND esiti_personaggio.personaggio =:me_1)
                    LEFT JOIN esiti_risposte_risultati ON (esiti_risposte_risultati.esito = esiti_risposte.id AND esiti_risposte_risultati.personaggio = :me_2)
                    WHERE esiti_risposte.chat = :luogo
                    AND esiti_personaggio.id IS NOT NULL
                    AND esiti_risposte_risultati.id IS NULL
                    AND esiti.closed = 0
                    ORDER BY esiti_risposte.data DESC", [
                "me_1" => $this->me_id,
                "me_2" => $this->me_id,
                "luogo" => $luogo,
            ]);

            $cells = [
                'Titolo',
                'Data',
                'Dadi',
                'Abilità',
                'Comandi',
            ];

            $esiti_data = [];

            foreach ( $esiti as $esito ) {

                $id = Filters::int($esito['id']);
                $abi_data = Abilita::getInstance()->getAbilita(Filters::int($esito['abilita']), 'nome');
                $dice_num = Filters::int($esito['dice_num']);
                $dice_face = Filters::int($esito['dice_face']);

                $esiti_data[] = [
                    'id' => $id,
                    "titolo" => Filters::out($esito['titolo']),
                    "data" => CarbonWrapper::format($esito['data'], 'd/m/Y H:i'),
                    "dice" => "{$dice_num} dadi da {$dice_face}",
                    "abi_name" => Filters::out($abi_data['nome']),
                ];
            }

            return Template::getInstance()->startTemplate()->renderTable(
                'esiti/list',
                [
                    'body_rows' => $esiti_data,
                    'cells' => $cells,
                    'table_title' => 'Lista Esiti',
                ]
            );
        } else {
            return '';
        }
    }

    /**
     * @fn rollEsito
     * @note Utilizzo di un esito in chat
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function rollEsito(array $post): array
    {
        $chat = new Chat();
        $id = Filters::int($post['id']);
        $data = $this->getAnswer($id);
        $id_answer = Filters::int($data['id']);
        $dice_face = Filters::int($data['dice_face']);
        $dice_num = Filters::int($data['dice_num']);
        $luogo = Personaggio::getPgLocation();

        $abi_roll = $chat->rollAbility(Filters::int($data['abilita']));

        # Filtro i dati ricevuti
        $abi_dice = Filters::int($abi_roll['abi_dice']);
        $abi_nome = Filters::in($abi_roll['abi_nome']);
        $car = Filters::int($abi_roll['car']);

        $dice = $chat->rollCustomDice($dice_num, $dice_face);
        $result = ($dice + $abi_dice + $car);

        $testo = 'Tiro: ' . $abi_nome . ', risultato totale: ' . $result . ' ';
        $testo_sussurro = Filters::in($this->esitoResultText($id_answer, $result));

        DB::queryStmt(
            "INSERT INTO chat(stanza, mittente,destinatario,tipo,testo)
				  VALUE(:luogo, 'Esiti',:me,'C',:testo)",
            [
                'luogo' => $luogo,
                'me' => $this->me_id,
                'testo' => $testo,
            ]);

        DB::queryStmt(
            "INSERT INTO chat(stanza, mittente,destinatario,tipo,testo)
				  VALUE(:luogo, 'Esiti',:me,'S',:testo)",
            [
                'luogo' => $luogo,
                'me' => $this->me_id,
                'testo' => $testo_sussurro,
            ]
        );

        DB::queryStmt(
            "INSERT INTO esiti_risposte_risultati(esito,personaggio,risultato,testo) 
                VALUES(:id_answer,:me,:result,:testo)",
            [
                'id_answer' => $id_answer,
                'me' => $this->me_id,
                'result' => $result,
                'testo' => $testo_sussurro,
            ]);

        return ['response' => true, 'error' => ''];
    }

    /**
     * @fn esitoResultText
     * @note Testo dei risultati trovati dalla prova esito
     * @param int $id
     * @param int $result
     * @return string
     * @throws Throwable
     */
    public function esitoResultText(int $id, int $result): string
    {
        $html = ' Hai scoperto: ';
        $list = $this->getPassedEsitoCD($id, $result);

        if ( DB::rowsNumber($list) > 0 ) {
            foreach ( $list as $cd ) {
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
     * @throws Throwable
     */
    public function haveNewResponse(int $id): int
    {
        $new = DB::queryStmt("SELECT count(esiti_risposte.id) as tot FROM esiti_risposte 
                    LEFT JOIN esiti_risposte_letture ON (esiti_risposte_letture.esito = esiti_risposte.id AND esiti_risposte_letture.personaggio = :me)
                    WHERE esiti_risposte.esito = :id AND esiti_risposte_letture.id IS NULL",
            [
                'id' => $id,
                'me' => $this->me_id,
            ]
        );

        return Filters::int($new['tot']);
    }

    /**
     * @fn esitiList
     * @note Render html della lista degli esiti
     * @return string
     * @throws Throwable
     */
    public function esitiListPLayer(): string
    {
        $list = $this->getAllEsitoPlayer($this->me_id, 'esiti.*', 'ORDER BY closed ASC,data ASC');
        return Template::getInstance()->startTemplate()->renderTable(
            'gestione/esiti/list',
            $this->renderEsitiList($list, 'servizi')
        );
    }

    /**
     * @fn esitiListManagement
     * @note Render html della lista degli esiti
     * @return string
     * @throws Throwable
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
     * @throws Throwable
     */
    public function renderEsitiList(object $list, string $page): array
    {
        $row_data = [];
        $path = ($page == 'servizi') ? 'servizi/esiti/esiti_index' : 'gestione/esiti/esiti_index';
        $backlink = ($page == 'servizi') ? 'uffici' : 'gestione';

        foreach ( $list as $row ) {

            $id = Filters::int($row['id']);

            if ( Filters::int($row['master']) != 0 ) {
                $master = 'Presa in carico';
            } else if ( $row['closed'] ) {
                $master = 'Chiuso';
            } else {
                $master = '<u> In attesa di risposta </u>';
            }

            $array = [
                'id' => $id,
                'author' => Personaggio::nameFromId(Filters::in($row['autore'])),
                'totale_esiti' => $this->getEsitoAnswersNum($id),
                'new_response' => $this->haveNewResponse($id),
                'closed' => Filters::int($row['closed']),
                'closed_cls' => Filters::bool($row['closed']) ? 'closed' : '',
                'date' => Filters::date($row['data'], 'd/m/Y'),
                'titolo' => Filters::out($row['titolo']),
                'master' => $master,
                'esito_view_permission' => $this->esitoViewPermission($id),
                'esito_membri_permission' => $this->esitoMembersPermission($id),
                'esito_manage' => $this->esitiManageAll(),
                'esiti_close_permission' => $this->esitoClosePermission($id),
                'esiti_from_player_enabled' => $this->esitiFromPlayerEnabled(),
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
            'Controlli',
        ];
        $links = [
            ['href' => "/main.php?page={$path}&op=new", 'text' => 'Nuovo esito'],
            ['href' => "/main.php?page={$backlink}", 'text' => 'Indietro'],
        ];
        return [
            'body' => 'gestione/esiti/list',
            'body_rows' => $row_data,
            'cells' => $cells,
            'links' => $links,
            'path' => $path,
            'page' => $page,
        ];
    }

    /**
     * @fn htmlCDAdd
     * @note Aggiunge un form alla pagina per aggiungere una cd
     * @return array
     */
    public function htmlCDAdd(): array
    {
        return ['InputHtml' => Template::getInstance()->startTemplate()->render('esiti/form', [])];
    }

    /*** NEW ESITO ***/

    /**
     * @fn newEsitoManagement
     * @note Inserisce un nuovo esito da parte del master
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function newEsitoManagement(array $post): array
    {

        if ( $this->esitiManage() ) {

            $titolo = Filters::in($post['titolo']);
            $ms = Filters::in($post['contenuto']);
            $dice_num = Filters::int($post['dice_num']);
            $dice_face = Filters::int($post['dice_face']);
            $abilita = Filters::int($post['abilita']);
            $chat = Filters::int($post['chat']);

            DB::queryStmt(
                "INSERT INTO esiti(titolo, autore) VALUES(:titolo, :me)",
                [
                    'titolo' => $titolo,
                    'me' => $this->me_id,
                ]
            );

            $last_id = $this->getLastEsitoId();

            DB::queryStmt(
                "INSERT INTO esiti_risposte(esito, autore, contenuto, dice_num, dice_face, abilita, chat) VALUES(:esito, :me, :contenuto, :dice_num, :dice_face, :abilita, :chat)",
                [
                    'esito' => $last_id,
                    'me' => $this->me_id,
                    'contenuto' => $ms,
                    'dice_num' => $dice_num,
                    'dice_face' => $dice_face,
                    'abilita' => $abilita,
                    'chat' => $chat,
                ]
            );

            if ( !empty($post['add_cd']) ) {
                $last_answer = $this->getLastAnswerId();
                $this->addCD($last_answer, $post['add_cd']);
            }

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Esito creato con successo.',
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
     * @fn newEsitoManagement
     * @note Inserisce un nuovo esito da parte del master
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function newEsitoPlayer(array $post): array
    {

        if ( $this->esitiFromPlayerEnabled() ) {
            $titolo = Filters::in($post['titolo']);
            $ms = Filters::in($post['contenuto']);

            DB::queryStmt(
                "INSERT INTO esiti(titolo, autore) VALUES(:titolo, :me)",
                [
                    'titolo' => $titolo,
                    'me' => $this->me_id,
                ]
            );

            $last_id = $this->getLastEsitoId();

            DB::queryStmt(
                "INSERT INTO esiti_risposte(esito, autore, contenuto) VALUES(:esito, :me, :contenuto)",
                [
                    'esito' => $last_id,
                    'me' => $this->me_id,
                    'contenuto' => $ms,
                ]
            );

            DB::queryStmt(
                "INSERT INTO esiti_personaggio(personaggio, esito, assegnato_da) VALUES(:pg,:esito, :me)",
                [
                    'esito' => $last_id,
                    'me' => $this->me_id,
                    'pg' => $this->me_id,
                ]
            );

            return ['response' => true, 'mex' => 'Esito creato con successo.'];
        } else {
            return ['response' => false, 'mex' => 'Permesso negato'];
        }
    }

    /*** READ ESITO */

    /**
     * @fn renderEsitoAnswers
     * @note Render html della lista delle risposte a un esito
     * @param int $id
     * @return string
     * @throws Throwable
     */
    public function renderEsitoAnswers(int $id): string
    {
        $id = Filters::int($id);
        $data = [];

        if ( $this->esitoViewPermission($id) ) {

            $list = $this->getEsitoAllAnswers($id);

            foreach ( $list as $answer ) {
                $to_push = [];

                $id_answer = Filters::int($answer['id']);
                $this->readAnswer($id_answer);

                $autore = Filters::int($answer['autore']);
                $dice_num = Filters::int($answer['dice_num']);
                $id_abi = Filters::int($answer['abilita']);

                if ( ($dice_num > 0) && $this->esitiTiriEnabled() ) {

                    $abi_data = Abilita::getInstance()->getAbilita($id_abi, 'nome');

                    $chat = new Chat();
                    $chat_id = Filters::int($answer['chat']);
                    $results = $this->getAnswerResults($id_answer);

                    $lanciati = [];
                    foreach ( $results as $result ) {

                        $pg = Filters::int($result['personaggio']);
                        $pg_name = Personaggio::nameFromId($pg);
                        $res_text = Filters::text($result['testo']);
                        $res_num = Filters::int($result['risultato']);

                        if ( $this->esitoResultPermission($id) || ($pg == $this->me_id) ) {
                            $lanciati[] = [
                                'pg' => $pg_name,
                                'res_text' => $res_text,
                                'res_num' => $res_num,
                            ];
                        }
                    }

                    $to_push['abi_name'] = Filters::out($abi_data['nome']);
                    $to_push['chat_id'] = $chat_id;
                    $to_push['chat_name'] = $chat->getChatData($chat_id, 'nome')['nome'];
                    $to_push['dice_enabled'] = true;
                    $to_push['lanciati'] = $lanciati;
                }

                $to_push['mine'] = ($autore == $this->me_id) ? 'mine' : 'other';
                $to_push['contenuto'] = Filters::text($answer['contenuto']);
                $to_push['dice_num'] = Filters::int($answer['dice_num']);
                $to_push['dice_face'] = Filters::int($answer['dice_face']);
                $to_push['autore'] = Personaggio::nameFromId($autore);
                $to_push['date'] = CarbonWrapper::format($answer['data'], 'd/m/Y H:i');

                $data[] = $to_push;

            }
        }

        return Template::getInstance()->startTemplate()->render('esiti/single_esito', ['data' => $data]);
    }

    /**
     * @fn readAnswersMaster
     * @note Segna le risposte di un esito come lette dal master
     * @param int $id
     * @return void
     * @throws Throwable
     */
    public function readAnswer(int $id): void
    {
        if ( !$this->esitoReaded($id, $this->me_id) ) {

            DB::queryStmt(
                "INSERT INTO esiti_letti(esito, personaggio) VALUES(:esito, :me)",
                [
                    'esito' => $id,
                    'me' => $this->me_id,
                ]
            );
        }
    }

    /**
     * @fn newAnswer
     * @note Inserisce una nuova risposta in un esito
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function newAnswer(array $post): array
    {

        $id = Filters::int($post['id_record']);

        if ( $this->esitoAnswerPermission($id) && !$this->esitoClosed($id) ) {

            $perm_dadi = ($this->esitiTiriEnabled() && ($this->esitiManage() || $this->esitiManageAll()));

            $contenuto = Filters::in($post['contenuto']);
            $dice_num = ($perm_dadi) ? Filters::int($post['dadi_num']) : 0;
            $dice_face = ($perm_dadi) ? Filters::int($post['dadi_face']) : 0;
            $abilita = ($perm_dadi) ? Filters::int($post['abilita']) : 0;
            $chat = ($perm_dadi) ? Filters::int($post['chat']) : 0;

            DB::queryStmt(
                "INSERT INTO esiti_risposte(esito, autore, contenuto, dice_num, dice_face, abilita, chat) VALUES(:esito, :me, :contenuto, :dice_num, :dice_face, :abilita, :chat)",
                [
                    'esito' => $id,
                    'me' => $this->me_id,
                    'contenuto' => $contenuto,
                    'dice_num' => $dice_num,
                    'dice_face' => $dice_face,
                    'abilita' => $abilita,
                    'chat' => $chat,
                ]
            );

            if ( !empty($post['add_cd']) ) {
                $last_id = $this->getLastAnswerId();
                $this->addCD($last_id, $post['add_cd']);
            }

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Risposta aggiunta correttamente.',
                'swal_type' => 'success',
                'new_view' => $this->renderEsitoAnswers($id),
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
     * @fn addCD
     * @note Aggiunge delle cd per una risposta
     * @param int $id
     * @param array $cds
     * @return void
     * @throws Throwable
     */
    public function addCD(int $id, array $cds = []): void
    {

        $id = Filters::int($id);

        foreach ( $cds['cd'] as $index => $cd ) {

            $cd = Filters::int($cd);
            $testo = Filters::in($cds['text'][$index]);

            if ( $cd > 0 ) {

                DB::queryStmt(
                    "INSERT INTO esiti_risposte_cd(esito, cd, testo) VALUES(:esito, :cd, :testo)",
                    [
                        'esito' => $id,
                        'cd' => $cd,
                        'testo' => $testo,
                    ]
                );
            }
        }

    }

    /*** MEMBERS LIST ***/

    /**
     * @fn membersList
     * @note Render html dei membri di un esito
     * @param int $id
     * @return string
     * @throws Throwable
     */
    public function membersList(int $id): string
    {

        $cells = [
            'Membro',
            'Comandi',
        ];

        $list = $this->getAllPlayerEsito($id);
        $data = [];

        foreach ( $list as $row ) {
            $id_row = Filters::int($row['id']);

            $data[] = [
                'id' => $id,
                'id_row' => $id_row,
                'name' => Personaggio::nameFromId($row['personaggio']),
            ];

        }

        return Template::getInstance()->startTemplate()->renderTable('esiti/members',
            [
                'body_rows' => $data,
                'cells' => $cells,
                'table_title' => 'Lista Esiti',
            ]
        );
    }

    /**
     * @fn addMember
     * @note Aggiunge un membro a un esito
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function addMember(array $post): array
    {

        $id = Filters::int($post['id']);

        if ( $this->esitoMembersPermission($id) ) {

            $pg = Filters::int($post['personaggio']);

            if ( !$this->esitoPlayerExist($id, $pg) ) {

                DB::queryStmt(
                    "INSERT INTO esiti_personaggio(esito, personaggio, assegnato_da) VALUES(:esito, :pg,:me)",
                    [
                        'esito' => $id,
                        'pg' => $pg,
                        'me' => $this->me_id,
                    ]
                );

                return [
                    'response' => true,
                    'swal_title' => 'Operazione riuscita!',
                    'swal_message' => 'Personaggio inserito correttamente.',
                    'swal_type' => 'success',
                    'members_list' => $this->membersList($id),
                ];
            } else {
                return [
                    'response' => false,
                    'swal_title' => 'Operazione fallita!',
                    'swal_message' => 'Personaggio già esistente.',
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
     * @fn deleteMember
     * @note Rimuove un membro da un esito
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function deleteMember(array $post): array
    {

        $id = Filters::int($post['id']);
        $id_esito = Filters::int($post['id_esito']);

        if ( $this->esitoMembersPermission($id_esito) ) {

            DB::queryStmt(
                "DELETE FROM esiti_personaggio WHERE id = :id LIMIT 1",
                [
                    'id' => $id,
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Personaggio rimosso correttamente.',
                'swal_type' => 'success',
                'members_list' => $this->membersList($id_esito),
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

    /*** MASTER ASSIGN **/

    /**
     * @fn esitiManagersList
     * @note Estrae la lista di personaggi che hanno i permessi per gestire gli esiti
     * @return string
     * @throws Throwable
     */
    public function esitiManagersList(): string
    {

        $list = Permissions::getPgListPermissions(['MANAGE_ESITI']);
        return Template::getInstance()->startTemplate()->renderSelect('id','nome','',$list,'Seleziona un master');
    }

    /**
     * @throws Throwable
     */
    public function setMaster($post): array
    {

        $id = Filters::int($post['id']);

        if ( $this->esitiManageAll() ) {

            $pg = Filters::int($post['personaggio']);

            DB::queryStmt(
                "UPDATE esiti SET master = :pg WHERE id = :id LIMIT 1",
                [
                    'pg' => $pg,
                    'id' => $id,
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Master assegnato con successo.',
                'swal_type' => 'success',
            ];

        } else {

            return [
                'response' => true,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error',
            ];
        }

    }

    /*** RE-OPEN ESITO ***/

    /**
     * @fn esitoOpen
     * @note Riapertura di un esito
     * @param int $id
     * @return array
     * @throws Throwable
     */
    public function esitoOpen(int $id): array
    {
        $id = Filters::int($id);

        if ( $this->esitiManageAll() ) {

            DB::queryStmt(
                "UPDATE esiti SET closed = 0 WHERE id = :id LIMIT 1",
                [
                    'id' => $id,
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Esito riaperto con successo.',
                'swal_type' => 'success',
                'esiti_list' => $this->esitiListManagement(),
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

    /*** CLOSE ESITO ***/

    /**
     * @fn esitoClose
     * @note Chiusura di un esito
     * @param int $id
     * @return array
     * @throws Throwable
     */
    public function esitoClose(int $id): array
    {
        $id = Filters::int($id);

        if ( $this->esitoClosePermission($id) ) {

            DB::queryStmt(
                "UPDATE esiti SET closed = 1 WHERE id = :id LIMIT 1",
                [
                    'id' => $id,
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Esito chiuso con successo.',
                'swal_type' => 'success',
                'esiti_list' => $this->esitiListManagement(),
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
}