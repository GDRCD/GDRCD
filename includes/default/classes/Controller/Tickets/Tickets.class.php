<?php


class Tickets extends BaseClass
{

    private bool $enabled;

    /**
     * @fn __construct
     * @note Costruttore della classe
     * @throws Throwable
     */
    public function __construct()
    {
        $this->enabled = Functions::get_constant('ONLINE_STATUS_ENABLED');
        parent::__construct();
    }


    /*** GETTERS */

    /**
     * @fn isEnabled
     * @note Controlla se la funzione è abilitata
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /*** ROUTING ***/

    /**
     * @fn loadPage
     * @note Carica la pagina richiesta
     * @param string $op
     * @return string
     */
    public function loadPage(string $op): string
    {
        $op = Filters::out($op);

        switch ( $op ) {
            default:
                $page = 'list/index.php';
                break;

            // Lista news
            case 'read':
                $page = 'read/index.php';
                break;
        }

        return $page;
    }


    /*** CONTROLS ***/

    /*** PERMISSIONS ***/

    /**
     * @fn permissionReadTicket
     * @note Controlla se l'utente ha i permessi per leggere un ticket
     * @param int $ticket_id
     * @return bool
     * @throws Throwable
     */
    public function permissionReadTicket(int $ticket_id): bool
    {
        return Permissions::permission('TICKET_MANAGER') || $this->isTicketResolver($ticket_id) || $this->isMineTicket($ticket_id);
    }

    /**
     * @fn isTicketResolver
     * @note Controlla se l'utente è il risolutore di un ticket
     * @param int $ticket_id
     * @return bool
     * @throws Throwable
     */
    public function isTicketResolver(int $ticket_id): bool
    {
        $ticket_data = $this->getTicket($ticket_id, 'assegnato_a');
        return Permissions::permission('TICKET_RESOLVER') && Filters::int($ticket_data['assegnato_a']) === $this->me_id;
    }

    /**
     * @fn isMineTicket
     * @note Controlla se l'utente è il creatore di un ticket
     * @param int $ticket_id
     * @return bool
     * @throws Throwable
     */
    public function isMineTicket(int $ticket_id): bool
    {
        $ticket_data = $this->getTicket($ticket_id, 'creato_da');
        return Filters::int($ticket_data['creato_da']) === $this->me_id;
    }


    /*** TABLES HELPERS **/

    /**
     * @fn getSection
     * @note Estrae i dati di una sezione
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getTicket(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM tickets WHERE id=:id LIMIT 1", [
            'id' => $id,
        ]);
    }

    /**
     * @fn getTicketMessages
     * @note Estrae i messaggi di un ticket
     * @param int $ticket_id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getTicketMessages(int $ticket_id, string $val = '*'): DBQueryInterface {
        return DB::queryStmt("SELECT {$val} FROM tickets_messaggi WHERE ticket=:ticket_id ORDER BY tickets_messaggi.creato_il ASC", [
            'ticket_id' => $ticket_id,
        ]);
    }

    /**
     * @fn getAllTicketsByStatus
     * @note Estrae tutti i ticket di uno stato
     * @param int $status
     * @param bool $archived
     * @param int|null $author_id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllTicketsByStatus(int $status, bool $archived = false, int $author_id = null, string $val = 'tickets.*,ticket_sezioni.*,personaggio.*'): DBQueryInterface
    {
        $extra_where = '';
        if($author_id !== null) {
            $extra_where = "AND tickets.creato_da=$author_id";
        }


        return DB::queryStmt("SELECT {$val} FROM tickets LEFT JOIN personaggio ON tickets.assegnato_a = personaggio.id LEFT JOIN tickets_sezioni ON tickets_sezioni.id = tickets.sezione WHERE status=:status AND archived=:archived {$extra_where} LIMIT 1", [
            'status' => $status,
            'archived' => $archived,

        ]);
    }



    /*** AJAX ***/

    /**** LIST ****/

    /*** FUNCTIONS ***/

    /*** RENDER ***/
    /**
     * @fn renderFrameText
     * @note Renderizza il testo del frame laterale
     * @return string
     * @throws Throwable
     */
    public function renderFrameText(): string
    {
        return Template::getInstance()->startTemplate()->render(
            'tickets/tickets_frame',
            [
                'new_tickets' => 0,
            ]
        );
    }

    /**
     * @fn renderNewsTitle
     * @note Renderizza il titolo del ticket
     * @param int $ticket_id
     * @return string
     * @throws Throwable
     */
    public function renderTicketTitle(int $ticket_id): string
    {
        $ticket_data = $this->getTicket($ticket_id);
        return Filters::out($ticket_data['titolo']);
    }

    /**
     * @fn ticketsList
     * @note Renderizza la lista dei ticket
     * @return string
     * @throws Throwable
     */
    public function ticketsListUser(): string
    {
        return Template::getInstance()->startTemplate()->renderTable(
            'tickets/tickets_list',
            $this->renderTicketsListUser()
        );

    }

    /**
     * @fn renderNewsList
     * @note Renderizza la lista delle news
     * @return array
     * @throws Throwable
     */
    public function renderTicketsListUser(): array
    {
        $tickets = [];
        $tickets_status = TicketsStatus::getInstance()->getAllStatus();

        foreach ( $tickets_status as $ticket_status ) {
            $status_id = Filters::int($ticket_status['id']);

            $tickets[$status_id] = [
                'type_data' => $ticket_status,
                'news' => [],
            ];

            $tickets_list = $this->getAllTicketsByStatus($status_id,false, $this->me_id,'tickets.*, tickets_sezioni.titolo as sezione, personaggio.nome as assigned_nome, personaggio.id as assigned_id');

            foreach ( $tickets_list as $tickets_data ) {
                $ticket_id = Filters::int($tickets_data['id']);

                $tickets[$status_id]['tickets'][] = [
                    'id' => $ticket_id,
                    'title' => Filters::out($tickets_data['titolo']),
                    'sezione' => Filters::out($tickets_data['sezione']),
                    'assegnato_a' => Filters::out($tickets_data['assigned_nome']),
                    'assegnato_id' => Filters::out($tickets_data['assigned_id']),
                    'date' => Filters::date($tickets_data['creato_il'], 'd/m/Y'),
                ];
            }

        }

        return ["tickets" => $tickets];
    }

    /**
     * @fn ticketRead
     * @note Renderizza la pagina di lettura di un ticket
     * @param int $ticket_id
     * @return string
     * @throws Throwable
     */
    public function ticketRead(int $ticket_id): string
    {
        return Template::getInstance()->startTemplate()->render(
            'tickets/tickets_read',
            $this->renderTicketRead($ticket_id)
        );
    }

    /**
     * @fn renderTicketRead
     * @note Renderizza la pagina di lettura di un ticket
     * @param int $news_id
     * @return array
     * @throws Throwable
     */
    public function renderTicketRead(int $news_id): array
    {
        $news = $this->getTicket($news_id);

        $pg = Personaggio::getPgData(Filters::int($news['creato_da']));

        $ticket_data = [
            "id" => Filters::int($news['id']),
            "title" => Filters::out($news['titolo']),
            "text" => Filters::out($news['testo']),
            "author" => Filters::out($news['creato_da']),
            "author_name" => Filters::out($pg['nome']),
            "author_pic" => Filters::out($pg['url_img_chat']),
            "date" => Filters::date($news['creata_il'], 'd/m/Y'),
            "messages" => $this->renderTicketMessages($news_id),
        ];

        return ["ticket" => $ticket_data];
    }

    /**
     * @fn renderTicketMessages
     * @note Renderizza i messaggi di un ticket
     * @param int $ticket_id
     * @return array
     * @throws Throwable
     */
    public function renderTicketMessages(int $ticket_id): array {
        $messages = [];
        $messages_list = $this->getTicketMessages($ticket_id);

        foreach($messages_list as $message) {
            $pg = Personaggio::getPgData(Filters::int($message['creato_da']));

            $messages[] = [
                "id" => Filters::int($message['id']),
                "text" => Filters::out($message['testo']),
                "author" => Filters::out($message['creato_da']),
                "author_name" => Filters::out($pg['nome']),
                "author_pic" => Filters::out($pg['url_img_chat']),
                "date" => Filters::date($message['creato_il'], 'd/m/Y'),
            ];
        }

        return $messages;
    }

    /*** MANAGEMENT FUNCTIONS **/

}