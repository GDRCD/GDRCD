<?php

class RegistrazioneGiocate extends BaseClass
{

    private
        $registrazione_active;

    protected function __construct()
    {
        parent::__construct();

        $this->registrazione_active = Functions::get_constant('REGISTRAZIONI_ENABLED');
    }

    /*** CONTROLS ***/

    /**
     * @fn activeRegistrazioni
     * @note Restituisce se la registrazione è attiva
     * @return false|mixed
     */
    public function activeRegistrazioni()
    {
        return $this->registrazione_active;
    }

    /**** TABLE HELPERS ****/

    /**
     * @fn getRecord
     * @note Ottieni una registrazione giocata dall'id
     * @param int $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getRecord(int $id, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM giocate_registrate WHERE id='{$id}' LIMIT 1");
    }

    /**
     * @fn getAllRecords
     * @note Ottieni tutte le registrazioni giocate
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllRecords(string $val = '*')
    {
        return DB::query("SELECT {$val} FROM giocate_registrate WHERE 1  ORDER BY creato_il DESC", 'result');
    }

    /**
     * @fn getAllRecordsByCharacter
     * @note Ottieni tutte le registrazioni giocate di un personaggio
     * @param int $pg
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllRecordsByCharacter(int $pg, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM giocate_registrate WHERE autore='{$pg}' ORDER BY creato_il DESC", 'result');
    }

    /**
     * @fn getAllNewRecords
     * @note Ottieni tutte le registrazioni giocate non ancora lette
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllNewRecords(string $val = '*')
    {
        return DB::query("SELECT {$val} FROM giocate_registrate WHERE bloccata = 0 AND controllata = 0  ORDER BY creato_il DESC", 'result');
    }

    /**
     * @fn getAllBlockedRecords
     * @note Ottieni tutte le registrazioni giocate bloccate
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllBlockedRecords(string $val = '*')
    {
        return DB::query("SELECT {$val} FROM giocate_registrate WHERE bloccata = 1 AND controllata = 0  ORDER BY creato_il DESC", 'result');
    }

    /**
     * @fn getAllControlledRecords
     * @note Ottieni tutte le registrazioni giocate controllate
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllControlledRecords(string $val = '*')
    {
        return DB::query("SELECT {$val} FROM giocate_registrate WHERE controllata = 1 ORDER BY creato_il DESC", 'result');
    }


    /**** PERMISSIONS ****/

    /**
     * @fn permissionViewRecords
     * @note Controlla se l'utente può vedere le registrazioni
     * @param int $id_pg
     * @return bool
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
     */
    public function permissionUpdateRecords(int $id_pg = 0): bool
    {
        return Personaggio::isMyPg($id_pg) || Permissions::permission('SCHEDA_UPDATE_RECORDS');
    }

    /**
     * @fn permissionViewSingleRecord
     * @note Controlla se l'utente può vedere la registrazione
     * @param int $id
     * @return bool
     * @throws Throwable
     */
    public function permissionViewSingleRecord(int $id): bool
    {
        $record = $this->getRecord($id, 'autore');
        return $this->permissionViewRecords(Filters::int($record['autore']));
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
            'Comandi',
        ];

        $regs = $this->getAllRecordsByCharacter($id_pg);
        $records = [];

        foreach ( $regs as $reg ) {

            $chat_data = Chat::getInstance()->getChatData(Filters::int($reg['chat']), 'nome');

            $records[] = [
                "id" => Filters::int($reg['id']),
                "id_pg" => $id_pg,
                "titolo" => Filters::out($reg['titolo']),
                "chat" => Filters::out($chat_data['nome']),
                "inizio" => Filters::date($reg['inizio'], 'h:i:s d/m/Y'),
                "fine" => Filters::date($reg['fine'], 'h:i:s d/m/Y'),
                "view_permission" => $this->activeRegistrazioni() && $this->permissionViewRecords($id_pg),
                "update_permission" => $this->activeRegistrazioni() && $this->permissionUpdateRecords($id_pg) && !Filters::bool($reg['bloccata']) && !Filters::bool($reg['controllata']),
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

        $html = '';
        $record_data = $this->getRecord($id, 'chat,inizio,fine');
        $start = Filters::out($record_data['inizio']);
        $end = Filters::out($record_data['fine']);
        $chat = Filters::in($record_data['chat']);

        $chat_data = Chat::getInstance()->getChatData($chat, 'nome');
        $chat_name = Filters::out($chat_data['nome']);
        $chat_messages = Chat::getInstance()->getActionsByTime($chat, $start, $end);

        foreach ( $chat_messages as $message ) {
            $html .= Chat::getInstance()->Filter($message);
        }

        return $html;
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
        var_dump(1);

        return Template::getInstance()->startTemplate()->renderTable(
            'scheda/registrazioni/view',
            $this->renderCharacterRecordView($id)
        );
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
     */
    public function allRecords(string $type): string
    {

        return Template::getInstance()->startTemplate()->renderTable(
            'gestione/registrazioni',
            $this->renderAllRecordsList($type)
        );
    }


    /*** FUNCTIONS ***/

    /**
     * @fn newRegistrazione
     * @note Crea una nuova registrazione giocata
     * @return void
     */
    public function newRegistrazione(array $post): array
    {

        if ( $this->activeRegistrazioni() ) {

            $titolo = Filters::in($post['titolo']);
            $nota = Filters::text($post['nota']);
            $chat = Filters::int($post['chat']);
            $inizio = Filters::date($post['inizio'], 'Y-m-d H:i:s');
            $fine = Filters::date($post['fine'], 'Y-m-d H:i:s');

            DB::query("INSERT INTO giocate_registrate (titolo, nota, chat, inizio, fine, autore) 
                            VALUES ('{$titolo}', '{$nota}', '{$chat}', '{$inizio}', '{$fine}', '{$this->me_id}')");

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
     * @return void
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

            DB::query("UPDATE giocate_registrate SET titolo='{$titolo}', nota='{$nota}', chat='{$chat}', inizio='{$inizio}', fine='{$fine}' WHERE id='{$id}'");

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
     * @return void
     */
    public function deleteRegistrazione(array $post): array
    {

        $id = Filters::int($post['id']);
        $record_data = $this->getRecord($id);
        $owner = Filters::int($record_data['autore']);

        if ( $this->activeRegistrazioni() && $this->permissionUpdateRecords($owner) ) {

            DB::query("DELETE FROM giocate_registrate WHERE id='{$id}'");

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
     */
    public function setControlledRegistrazione(array $post): array
    {
        $id = Filters::int($post['id']);
        if ( $this->activeRegistrazioni() && $this->permissionUpdateRecords() ) {

            DB::query("UPDATE giocate_registrate SET controllata= IF (`controllata`, 0, 1) WHERE id='{$id}'");

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
     */
    public function setBlockedRegistrazione(array $post): array
    {
        $id = Filters::int($post['id']);
        if ( $this->activeRegistrazioni() && $this->permissionUpdateRecords() ) {

            DB::query("UPDATE giocate_registrate SET bloccata= IF (`bloccata`, 0, 1) WHERE id='{$id}'");

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