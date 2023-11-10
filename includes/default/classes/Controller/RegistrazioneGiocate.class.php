<?php

class RegistrazioneGiocate extends BaseClass
{

    private bool
        $registrazione_active;

    /**
     * @fn __construct()
     * @note Costruttore della classe
     * @throws Throwable
     */
    protected function __construct()
    {
        parent::__construct();

        $this->registrazione_active = Functions::get_constant('REGISTRAZIONI_ENABLED');
    }

    /*** CONTROLS ***/

    /**
     * @fn activeRegistrazioni
     * @note Restituisce se la registrazione è attiva
     * @return bool
     */
    public function activeRegistrazioni(): bool
    {
        return $this->registrazione_active;
    }

    /**** TABLE HELPERS ****/

    /**
     * @fn getRecord
     * @note Ottieni una registrazione giocata dall'id
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getRecord(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM giocate_registrate WHERE id=:id LIMIT 1", ['id' => $id]);
    }

    /**
     * @fn getAllRecords
     * @note Ottieni tutte le registrazioni giocate
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllRecords(string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM giocate_registrate WHERE 1  ORDER BY creato_il DESC", []);
    }

    /**
     * @fn getAllRecordsByCharacter
     * @note Ottieni tutte le registrazioni giocate di un personaggio
     * @param int $pg
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllRecordsByCharacter(int $pg, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM giocate_registrate WHERE autore=:autore ORDER BY creato_il DESC",
            ['autore' => $pg]
        );
    }

    /**
     * @fn getAllNewRecords
     * @note Ottieni tutte le registrazioni giocate non ancora lette
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllNewRecords(string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM giocate_registrate WHERE bloccata = 0 AND controllata = 0  ORDER BY creato_il DESC", []);
    }

    /**
     * @fn getAllBlockedRecords
     * @note Ottieni tutte le registrazioni giocate bloccate
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllBlockedRecords(string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM giocate_registrate WHERE bloccata = 1 AND controllata = 0  ORDER BY creato_il DESC", []);
    }

    /**
     * @fn getAllControlledRecords
     * @note Ottieni tutte le registrazioni giocate controllate
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllControlledRecords(string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM giocate_registrate WHERE controllata = 1 ORDER BY creato_il DESC", []);
    }


    /**** PERMISSIONS ****/

    /**
     * @fn permissionViewRecords
     * @note Controlla se l'utente può vedere le registrazioni
     * @param int $id_pg
     * @return bool
     * @throws Throwable
     */
    public function permissionViewRecords(int $id_pg = 0): bool
    {
        return Personaggio::isMyPg($id_pg) || Permissions::permission('SCHEDA_VIEW_RECORDS');
    }

    /**
     * @fn permissionUpdateRecords
     * @note Controlla se l'utente può modificare la registrazione
     * @param int $id_pg
     * @return bool
     * @throws Throwable
     */
    public function permissionUpdateRecords(int $id_pg = 0): bool
    {
        return Personaggio::isMyPg($id_pg) || Permissions::permission('SCHEDA_UPDATE_RECORDS');
    }

    /**
     * @fn permissionViewSingleRecord
     * @note Controlla se l'utente può vedere la registrazione richiesta
     * @param int $id
     * @return bool
     * @throws Throwable
     */
    public function permissionViewSingleRecord(int $id = 0): bool
    {
        # Estraggo i dati della registrazione
        $record = $this->getRecord($id, 'autore,controllata,bloccata');
        $autore = Filters::int($record['autore']);
        $controllata = Filters::bool($record['controllata']);
        $bloccata = Filters::bool($record['bloccata']);

        # Se la registrazione non è stata controllata o non sei l'autore, solo chi ha il permesso puo' vederla
        return ($controllata && Personaggio::isMyPg($autore) && !$bloccata) || Permissions::permission('SCHEDA_VIEW_RECORDS');
    }

    /**
     * @fn permissionViewSingleRecord
     * @note Controlla se l'utente può vedere la registrazione richiesta
     * @param int $id
     * @return bool
     * @throws Throwable
     */
    public function permissionUpdateSingleRecord(int $id): bool
    {
        # Estraggo i dati della registrazione
        $record = $this->getRecord($id, 'autore,controllata,bloccata');
        $autore = Filters::int($record['autore']);
        $controllata = Filters::bool($record['controllata']);
        $bloccata = Filters::bool($record['bloccata']);

        # Se la registrazione non è stata controllata o non sei l'autore, solo chi ha il permesso puo' vederla
        return ($controllata && Personaggio::isMyPg($autore) && !$bloccata) || Permissions::permission('SCHEDA_UPDATE_RECORDS');
    }


    /*** RENDER ***/

    /**
     * @fn renderCharacterRecordsList
     * @note Restituisce la lista delle registrazioni giocate di un personaggio
     * @param int $id_pg
     * @return array
     * @throws Throwable
     */
    public function renderCharacterRecordsList(int $id_pg): array
    {
        $cells = [
            'Titolo',
            'Chat',
            'Inizio',
            'Fine',
            'Stato',
            'Comandi',
        ];

        $regs = $this->getAllRecordsByCharacter($id_pg);
        $records = [];

        foreach ( $regs as $reg ) {
            $chat_data = Chat::getInstance()->getChatData(Filters::int($reg['chat']), 'nome');
            $reg_id = Filters::int($reg['id']);

            $records[] = [
                "id" => $reg_id,
                "id_pg" => $id_pg,
                "titolo" => Filters::out($reg['titolo']),
                "chat" => Filters::out($chat_data['nome']),
                "inizio" => Filters::date($reg['inizio'], 'H:i d/m/Y'),
                "fine" => Filters::date($reg['fine'], 'H:i d/m/Y'),
                "bloccata" => Filters::bool($reg['bloccata']),
                "controllata" => Filters::bool($reg['controllata']),
                "view_permission" => $this->activeRegistrazioni() && $this->permissionViewSingleRecord($reg_id),
                "update_permission" => $this->activeRegistrazioni() && $this->permissionUpdateSingleRecord($reg_id),
            ];
        }

        $links = [
            ["href" => "main.php?page=scheda/index&op=registrazioni_new&id_pg={$id_pg}", "text" => "Nuova registrazione"],
        ];

        return [
            'body_rows' => $records,
            'cells' => $cells,
            'links' => $links,
            'table_title' => 'Giocate Registrate',
        ];
    }

    /**
     * @fn characterRecords
     * @note Renderizza la lista delle registrazioni giocate di un personaggio
     * @param int $id
     * @return string
     * @throws Throwable
     */
    public function characterRecords(int $id): string
    {
        return Template::getInstance()->startTemplate()->renderTable(
            'scheda/registrazioni/index',
            $this->renderCharacterRecordsList($id)
        );
    }

    /**
     * @fn renderCharacterRecordView
     * @note Renderizza la vista di una registrazione
     * @param int $id
     * @return string
     * @throws Throwable
     */
    public function renderCharacterRecordView(int $id): string
    {
        # Ottieni i dati della registrazione
        $record_data = $this->getRecord($id, 'chat,inizio,fine');
        $start = Filters::out($record_data['inizio']);
        $end = Filters::out($record_data['fine']);
        $chat = Filters::in($record_data['chat']);

        # Stampiamo la chat
        return Chat::getInstance()->printChatByTime($chat, $start, $end);
    }

    /**
     * @fn renderRecord
     * @note Restituisce i dati di una registrazione
     * @param int $id
     * @return string
     * @throws Throwable
     */
    public function characterRecord(int $id): string
    {
        return ($this->permissionViewSingleRecord($id)) ? $this->renderCharacterRecordView($id) : '';
    }

    /**
     * @fn renderCharacterRecordsList
     * @note Restituisce la lista delle registrazioni giocate di un personaggio
     * @param string $type
     * @return array
     * @throws Throwable
     */
    public function renderAllRecordsList(string $type): array
    {
        $cells = [
            'Autore',
            'Titolo',
            'Chat',
            'Inizio',
            'Fine',
            'Comandi',
        ];

        switch ( $type ) {
            case 'new':
                $regs = $this->getAllNewRecords();
                $table_title = 'Giocate Registrate - Nuove';
                break;
            case 'blocked':
                $regs = $this->getAllBlockedRecords();
                $table_title = 'Giocate Registrate - Bloccate';
                break;
            case 'controlled':
                $regs = $this->getAllControlledRecords();
                $table_title = 'Giocate Registrate - Controllate';
                break;
            default:
                die('Tipo di records non valido');
        }

        $records = [];

        foreach ( $regs as $reg ) {
            $chat_data = Chat::getInstance()->getChatData(Filters::int($reg['chat']), 'nome');
            $autore = Filters::int($reg['autore']);

            $records[] = [
                "id" => Filters::int($reg['id']),
                "autore" => Personaggio::nameFromId($autore),
                "id_autore" => $autore,
                "titolo" => Filters::out($reg['titolo']),
                "chat" => Filters::out($chat_data['nome']),
                "inizio" => Filters::date($reg['inizio'], 'h:i:s d/m/Y'),
                "fine" => Filters::date($reg['fine'], 'h:i:s d/m/Y'),
                "view_permission" => $this->activeRegistrazioni() && $this->permissionViewRecords(),
                "update_permission" => $this->activeRegistrazioni() && $this->permissionUpdateRecords(),
                "bloccata" => Filters::bool($reg['bloccata']),
                "controllata" => Filters::bool($reg['controllata']),
                "type" => $type,
            ];
        }

        return [
            'body_rows' => $records,
            'cells' => $cells,
            'table_title' => $table_title,
        ];
    }

    /**
     * @fn characterRecords
     * @note Renderizza la lista delle registrazioni giocate di un personaggio
     * @param string $type
     * @return string
     * @throws Throwable
     */
    public function allRecords(string $type): string
    {
        return Template::getInstance()->startTemplate()->renderTable(
            'gestione/registrazioni/index',
            $this->renderAllRecordsList($type)
        );
    }


    /*** FUNCTIONS ***/

    /**
     * @fn newRegistrazione
     * @note Crea una nuova registrazione giocata
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function newRegistrazione(array $post): array
    {
        if ( $this->activeRegistrazioni() ) {
            $titolo = Filters::in($post['titolo']);
            $nota = Filters::text($post['nota']);
            $chat = Filters::int($post['chat']);
            $inizio = Filters::date($post['inizio'], 'Y-m-d H:i:s');
            $fine = Filters::date($post['fine'], 'Y-m-d H:i:s');

            DB::queryStmt("INSERT INTO giocate_registrate (titolo, nota, chat, inizio, fine, autore) 
                            VALUES (:titolo, :nota, :chat, :inizio, :fine, :id)",
                [
                    'titolo' => $titolo,
                    'nota' => $nota,
                    'chat' => $chat,
                    'inizio' => $inizio,
                    'fine' => $fine,
                    'id' => $this->me_id,
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Registrazione inserita correttamente.',
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
     * @fn editRegistrazione
     * @note Modifica una registrazione giocata
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function editRegistrazione(array $post): array
    {
        $id = Filters::int($post['id']);
        $record_data = $this->getRecord($id);
        $owner = Filters::int($record_data['autore']);

        if ( $this->activeRegistrazioni() && $this->permissionUpdateRecords($owner) ) {
            $titolo = Filters::in($post['titolo']);
            $nota = Filters::text($post['nota']);
            $chat = Filters::int($post['chat']);
            $inizio = Filters::date($post['inizio'], 'Y-m-d H:i:s');
            $fine = Filters::date($post['fine'], 'Y-m-d H:i:s');

            DB::queryStmt("UPDATE giocate_registrate SET titolo = :titolo, nota = :nota, chat = :chat, inizio = :inizio, fine = :fine 
                            WHERE id = :id",
                [
                    'titolo' => $titolo,
                    'nota' => $nota,
                    'chat' => $chat,
                    'inizio' => $inizio,
                    'fine' => $fine,
                    'id' => $id,
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Registrazione modificata correttamente.',
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
     * @fn deleteRegistrazione
     * @note Elimina una registrazione giocata
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function deleteRegistrazione(array $post): array
    {
        $id = Filters::int($post['id']);
        $record_data = $this->getRecord($id);
        $owner = Filters::int($record_data['autore']);

        if ( $this->activeRegistrazioni() && $this->permissionUpdateRecords($owner) ) {
            DB::queryStmt("DELETE FROM giocate_registrate WHERE id = :id", ['id' => $id]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Registrazione eliminata correttamente.',
                'swal_type' => 'success',
                'new_template' => $this->allRecords('new'),
                'blocked_template' => $this->allRecords('blocked'),
                'completed_template' => $this->allRecords('controlled'),
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
     * @fn setControlledRegistrazione
     * @note Imposta una registrazione giocata come controllata
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function setControlledRegistrazione(array $post): array
    {
        $id = Filters::int($post['id']);
        if ( $this->activeRegistrazioni() && $this->permissionUpdateRecords() ) {

            DB::queryStmt("UPDATE giocate_registrate SET controllata = IF(`controllata`, 0, 1) WHERE id = :id", ['id' => $id]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Registrazione modificata correttamente.',
                'swal_type' => 'success',
                'new_template' => $this->allRecords('new'),
                'blocked_template' => $this->allRecords('blocked'),
                'completed_template' => $this->allRecords('controlled'),
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
     * @fn setBlockedRegistrazione
     * @note Imposta una registrazione giocata come bloccata
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function setBlockedRegistrazione(array $post): array
    {
        $id = Filters::int($post['id']);
        if ( $this->activeRegistrazioni() && $this->permissionUpdateRecords() ) {

            DB::queryStmt("UPDATE giocate_registrate SET bloccata = IF(`bloccata`, 0, 1) WHERE id = :id", ['id' => $id]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Registrazione modificata correttamente.',
                'swal_type' => 'success',
                'new_template' => $this->allRecords('new'),
                'blocked_template' => $this->allRecords('blocked'),
                'completed_template' => $this->allRecords('controlled'),
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