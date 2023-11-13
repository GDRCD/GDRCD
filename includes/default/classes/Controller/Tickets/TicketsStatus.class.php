<?php


class TicketsStatus extends BaseClass
{

    /**
     * @fn __construct
     * @note Costruttore della classe
     * @throws Throwable
     */
    public function __construct()
    {
        parent::__construct();
    }


    /*** GETTERS */

    /*** CONTROLS ***/

    /*** PERMISSIONS ***/

    /**
     * @fn manageStatusPermission
     * @note Controlla se si hanno i permessi per la gestione degli stati dei ticket
     * @return bool
     * @throws Throwable
     */
    public function manageStatusPermission(): bool
    {
        return Permissions::permission('MANAGE_TICKETS_STATUS');
    }


    /*** TABLES HELPERS **/

    /**
     * @fn getStatus
     * @note Estrae i dati di uno stato
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getStatus(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM tickets_status WHERE id=:id LIMIT 1", [
            'id' => $id,
        ]);
    }

    /**
     * @fn getAllStatus
     * @note Estrae i dati di tutti gli stati
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllStatus(string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM tickets_status", []);
    }

    /**
     * @fn getTicketStatus
     * @note Estrae lo stato online di un ticket specifico
     * @param int $ticket
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getTicketStatus(int $ticket, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM ticket LEFT JOIN tickets_status ON ticket.status = tickets_status.id WHERE ticket.id=:ticket LIMIT 1", [
            'ticket' => $ticket,
        ]);
    }


    /*** AJAX ***/

    /**
     * @fn ajaxStatusData
     * @note Estrae i dati di uno stato
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function ajaxStatusData(array $post): array
    {
        if ( $this->manageStatusPermission() ) {
            $id = Filters::int($post['id']);
            $data = $this->getStatus($id);
            return [
                'titolo' => Filters::out($data['titolo']),
                'descrizione' => Filters::out($data['descrizione']),
                'colore' => Filters::out($data['colore']),
                'is_initial_state' => Filters::bool($data['is_initial_state']),
                'is_blocked' => Filters::bool($data['is_blocked']),
            ];
        }

        return [];
    }


    /**** LIST ****/

    /**
     * @fn listStatus
     * @note Render della lista degli stati disponibili, divisi per tipo
     * @param int $selected
     * @param string $label
     * @return string
     * @throws Throwable
     */
    public function listStatus(int $selected = 0, string $label = ''): string
    {
        $status = self::getInstance()->getAllStatus();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'titolo', $selected, $status, $label);
    }

    /*** FUNCTIONS ***/

    /*** MANAGEMENT FUNCTIONS **/

    /**
     * @fn insertStatus
     * @note Funzione d'inserimento di un nuovo stato
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function insertStatus(array $post): array
    {

        if ( $this->manageStatusPermission() ) {

            $titolo = Filters::in($post['titolo']);
            $descrizione = Filters::in($post['descrizione']);
            $colore = Filters::in($post['colore']);
            $is_initial_state = Filters::checkbox($post['is_initial_state']);
            $is_blocked = Filters::checkbox($post['is_blocked']);

            DB::queryStmt("INSERT INTO tickets_status (titolo,descrizione,colore,is_initial_state,is_blocked,creato_da) VALUES (:titolo,:descrizione,:colore,:is_initial_state,:is_blocked,:creato_da)", [
                'titolo' => $titolo,
                'descrizione' => $descrizione,
                'colore' => $colore,
                'is_initial_state' => $is_initial_state,
                'is_blocked' => $is_blocked,
                'creato_da' => $this->me_id,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Stato inserito correttamente.',
                'swal_type' => 'success',
                'status_list' => $this->listStatus(),
            ];

        }

        return [
            'response' => false,
            'swal_title' => 'Operazione fallita!',
            'swal_message' => 'Permesso negato.',
            'swal_type' => 'error',
        ];

    }

    /**
     * @fn editStatus
     * @note Funzione di modifica di uno stato
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function editStatus(array $post): array
    {

        if ( $this->manageStatusPermission() ) {

            $id = Filters::int($post['id']);
            $titolo = Filters::in($post['titolo']);
            $descrizione = Filters::in($post['descrizione']);
            $colore = Filters::in($post['colore']);
            $is_initial_state = Filters::checkbox($post['is_initial_state']);
            $is_blocked = Filters::checkbox($post['is_blocked']);

            DB::queryStmt("UPDATE tickets_status SET titolo=:titolo, descrizione=:descrizione, colore=:colore, is_initial_state=:is_initial_state, is_blocked=:is_blocked WHERE id=:id LIMIT 1", [
                'id' => $id,
                'titolo' => $titolo,
                'descrizione' => $descrizione,
                'colore' => $colore,
                'is_initial_state' => $is_initial_state,
                'is_blocked' => $is_blocked,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Stato modificato correttamente.',
                'swal_type' => 'success',
                'status_list' => $this->listStatus(),
            ];

        }

        return [
            'response' => false,
            'swal_title' => 'Operazione fallita!',
            'swal_message' => 'Permesso negato.',
            'swal_type' => 'error',
        ];
    }

    /**
     * @fn editStatus
     * @note Funzione di eliminazione di uno stato
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function deleteStatus(array $post): array
    {

        if ( $this->manageStatusPermission() ) {

            $id = Filters::int($post['id']);

            DB::queryStmt("DELETE FROM tickets_status WHERE id=:id LIMIT 1", [
                'id' => $id,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Stato eliminato correttamente.',
                'swal_type' => 'success',
                'status_list' => $this->listStatus(),
            ];

        }

        return [
            'response' => false,
            'swal_title' => 'Operazione fallita!',
            'swal_message' => 'Permesso negato.',
            'swal_type' => 'error',
        ];
    }
}