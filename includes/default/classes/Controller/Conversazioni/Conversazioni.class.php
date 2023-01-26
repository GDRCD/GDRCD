<?php

# TODO Logica segnalazioni post via messaggio - Attesa sezione messaggi

class Conversazioni extends BaseClass
{

    protected bool $conversations_enabled;

    /**
     * @fn __construct
     * @note Costruttore della classe
     * @throws Throwable
     */
    protected function __construct()
    {
        parent::__construct();
        $this->conversations_enabled = Functions::get_constant('CONVERSATIONS_ENABLED');
    }


    /*** GETTER ***/

    /**
     * @fn conversationsEnabled
     * @note Controlla se le conversazioni sono abilitate
     * @return bool|string
     */
    public function conversationsEnabled(): bool|string
    {
        return $this->conversations_enabled;
    }

    /*** PERMESSI ***/

    /**
     * @fn permissionConversation
     * @note Controlla se l'utente ha i permessi per accedere alla conversazione
     * @param int $id
     * @return bool
     * @throws Throwable
     */
    public function permissionConversation(int $id): bool
    {
        $conversation_data = $this->getConversation($id);

        if ( empty(Filters::out($conversation_data['eliminato_il'])) ) {

            $conversation_data = $this->getConversationMember($this->me_id, $id);

            if ( Filters::bool($conversation_data['proprietario']) ) {
                return true;
            } else {
                $members = $this->getConversationMembers($id, true, 'conversazioni_membri.personaggio')->getData();
                $members_list = array_column($members, 'personaggio');
                return in_array($this->me_id, $members_list);
            }
        } else {
            return false;
        }
    }

    /**
     * @fn permissionDeleteConversation
     * @note Controlla se l'utente ha i permessi per eliminare la conversazione
     * @param int $id
     * @return bool
     * @throws Throwable
     */
    public function permissionDeleteConversation(int $id): bool
    {
        $conversation_data = $this->getConversationMember($this->me_id, $id);
        return Filters::bool($conversation_data['proprietario']);
    }

    /**
     * @fn permissionUpdateConversation
     * @note Controlla se l'utente ha i permessi per modificare la conversazione
     * @param int $id
     * @return bool
     * @throws Throwable
     */
    public function permissionUpdateConversation(int $id): bool
    {
        $conversation_data = $this->getConversationMember($this->me_id, $id);
        return Filters::bool($conversation_data['proprietario']);
    }


    /*** TABLES HELPER ***/

    /**
     * @fn getAllConversationsByMember
     * @note Ottiene tutte le conversazioni di un utente (proprietario o membro)
     * @param int $pg
     * @param int $member
     * @param string $title
     * @param string $val
     * @return array
     * @throws Throwable
     */
    public function getAllConversationsByMember(int $pg, int $member = 0, string $title = '', string $val = 'conversazioni.*'): array
    {

        $extra_query = '';

        if ( !empty($title) ) {
            $extra_query .= " AND conversazioni.nome LIKE \"%{$title}%\" ";
        }

        $results = DB::queryStmt("SELECT {$val} FROM conversazioni 
                LEFT JOIN conversazioni_membri 
                    ON conversazioni_membri.conversazione = conversazioni.id 
                    AND conversazioni_membri.personaggio = :pg
                WHERE conversazioni_membri.personaggio IS NOT NULL
                    AND conversazioni.eliminato_il IS NULL
                {$extra_query}
                ORDER BY ultimo_messaggio DESC",
            ['pg' => $pg]
        )->getData();

        if ( !empty($member) ) {

            foreach ( $results as $index => $result ) {
                $conversation_id = Filters::int($result['id']);

                if ( !$this->getConversationMember($member, $conversation_id)->getNumRows() ) {
                    unset($results[$index]);
                }

            }
        }

        return $results;
    }

    /**
     * @fn getConversation
     * @note Ottiene i dati di una conversazione
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getConversation(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM conversazioni 
                        WHERE conversazioni.id=:id LIMIT 1", ['id' => $id]);
    }

    /**
     * @fn getConversationAttachments
     * @note Ottiene gli allegati di una conversazione
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getConversationAttachments(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM conversazioni_messaggi_allegati 
                        WHERE conversazioni_messaggi_allegati.messaggio=:id", ['id' => $id]);
    }

    /**
     * @fn getConversationMessages
     * @note Ottiene i messaggi di una conversazione
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getConversationMessages(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM conversazioni_messaggi 
                        WHERE conversazioni_messaggi.conversazione=:id ORDER BY creato_il ASC", ['id' => $id]);
    }

    /**
     * @fn getConversationMembers
     * @note Ottiene i membri di una conversazione
     * @param int $id
     * @param bool $members_only
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getConversationMembers(int $id, bool $members_only = false, string $val = 'personaggio.*,conversazioni_membri.*'): DBQueryInterface
    {
        $extra_query = '';

        if ( $members_only ) {
            $extra_query = " AND conversazioni_membri.proprietario=0";
        }

        return DB::queryStmt("SELECT {$val} FROM conversazioni_membri 
                        LEFT JOIN personaggio ON personaggio.id = conversazioni_membri.personaggio
                        WHERE conversazioni_membri.conversazione=:id {$extra_query}", ['id' => $id]);
    }

    /**
     * @fn getConversationMember
     * @note Ottiene i dati di un membro di una conversazione
     * @param int $pg
     * @param int $conversation
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getConversationMember(int $pg, int $conversation, string $val = 'personaggio.*,conversazioni_membri.*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM conversazioni_membri 
                        LEFT JOIN personaggio ON personaggio.id = conversazioni_membri.personaggio
                        WHERE conversazioni_membri.conversazione=:conversazione AND conversazioni_membri.personaggio=:pg LIMIT 1",
            ['conversazione' => $conversation, 'pg' => $pg]
        );
    }

    /**
     * @fn getConversationOwner
     * @note Ottiene i dati del proprietario di una conversazione
     * @param int $conversation
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getConversationOwner(int $conversation, string $val = 'personaggio.*,conversazioni_membri.*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM conversazioni_membri 
                        LEFT JOIN personaggio ON personaggio.id = conversazioni_membri.personaggio
                        WHERE conversazioni_membri.conversazione=:conversazione AND conversazioni_membri.proprietario=1 LIMIT 1",
            ['conversazione' => $conversation]
        );
    }

    /**
     * @fn getConversationsToRead
     * @note Ottiene le conversazioni da leggere
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getConversationsToRead(): DBQueryInterface
    {
        return DB::queryStmt("SELECT conversazioni.id FROM conversazioni 
                LEFT JOIN conversazioni_membri 
                    ON conversazioni_membri.conversazione = conversazioni.id 
                    AND conversazioni_membri.personaggio = :member
                WHERE conversazioni_membri.personaggio IS NOT NULL
                    AND conversazioni.eliminato_il IS NULL
                    AND conversazioni.ultimo_messaggio > conversazioni_membri.ultima_lettura",
            ['member' => $this->me_id]
        );
    }

    /*** ROUTING ****/

    /**
     * @fn loadPage
     * @note Routing della pagina messaggi
     * @param string $op
     * @return string
     */
    public function loadPage(string $op): string
    {
        $op = Filters::out($op);

        return match ($op) {
            default => 'view.php',
        };
    }


    /*** AJAX ***/

    /**
     * @fn ajaxFrameText
     * @note Ritorna il testo del frame laterale
     * @return array
     * @throws Throwable
     */
    public function ajaxFrameText(): array
    {
        return ['text' => $this->renderFrameText()];
    }

    /**
     * @fn ajaxConversations
     * @note Ritorna le conversazioni
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function ajaxConversations(array $post): array
    {
        $member = Filters::int($post['member']);
        $title = Filters::out($post['title']);

        return [
            'response' => true,
            'new_conversations' => $this->conversationsList($member, $title),
        ];
    }

    /*** LISTS ***/

    /**
     * @fn listConversations
     * @note Ritorna la lista delle conversazioni
     * @param int $selected
     * @param string $label
     * @return string
     * @throws Throwable
     */
    public function listConversations(int $selected = 0, string $label = 'Conversazioni'): string
    {
        $conversations = $this->getAllConversationsByMember($this->me_id);
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', $selected, $conversations, $label);
    }

    /*** RENDER ***/

    /**
     * @fn conversationsList
     * @note Ritorna la lista delle conversazioni renderizzata
     * @param int $member
     * @param string $title
     * @return string
     * @throws Throwable
     */
    public function conversationsList(int $member = 0, string $title = ''): string
    {
        return Template::getInstance()->startTemplate()->render(
            'conversazioni/conversazioni_list',
            $this->renderConversationsList($member, $title)
        );
    }

    /**
     * @fn renderConversationsList
     * @note Renderizza la lista delle conversazioni
     * @param int $member
     * @param string $title
     * @return array
     * @throws Throwable
     */
    public function renderConversationsList(int $member = 0, string $title = ''): array
    {
        $conversations = [];
        $conversations_list = $this->getAllConversationsByMember($this->me_id, $member, $title);

        foreach ( $conversations_list as $conversation ) {
            $conversation_member = $this->getConversationMember($this->me_id, Filters::int($conversation['id']), 'conversazioni_membri.*')->getData()[0];

            $conversation['delete_permission'] = $this->permissionDeleteConversation(Filters::int($conversation['id']));
            $conversation['update_permission'] = $this->permissionUpdateConversation(Filters::int($conversation['id']));
            $conversation['new_messages'] = ($conversation_member['ultima_lettura']) ? CarbonWrapper::greaterThan($conversation['ultimo_messaggio'], $conversation_member['ultima_lettura']) : true;

            $conversations[] = $conversation;
        }

        return ["conversations" => $conversations];
    }

    /**
     * @fn conversation
     * @note Ritorna una conversazione renderizzata
     * @param int $id
     * @param string $op
     * @return string
     * @throws Throwable
     */
    public function conversation(int $id, string $op = 'view'): string
    {

        if ( $id && $this->permissionConversation($id) ) {
            switch ( $op ) {
                case 'view':
                default:
                    return Template::getInstance()->startTemplate()->render(
                        'conversazioni/conversazione',
                        $this->renderConversation($id)
                    );
                case 'edit':
                    $members = $this->getConversationMembers($id, true, 'conversazioni_membri.personaggio')->getData();
                    $members_list = array_column($members, 'personaggio');
                    $owner = $this->getConversationOwner($id)->getData()[0]['personaggio'];

                    return Template::getInstance()->startTemplate()->render(
                        'conversazioni/modifica_conversazione',
                        [
                            "personaggi" => Personaggio::getInstance()->listPgsMultiselect($members_list),
                            "personaggi_proprietario" => Personaggio::getInstance()->listPgs(Filters::int($owner)),
                            "data" => $this->getConversation($id),
                        ]
                    );
                case 'members':
                    return Template::getInstance()->startTemplate()->render(
                        'conversazioni/membri',
                        [
                            "members" => $this->getConversationMembers($id, false, 'personaggio.id AS pg_id,personaggio.nome,personaggio.cognome,personaggio.url_img_chat,conversazioni_membri.*')->getData(),
                        ]
                    );
            }
        } else {
            return Template::getInstance()->startTemplate()->render(
                'conversazioni/nuova_conversazione',
                [
                    "personaggi" => Personaggio::getInstance()->listPgsMultiselect(),
                    "data" => $this->getConversation($id)->getData(),
                ]
            );
        }
    }

    /**
     * @fn renderConversation
     * @note Renderizza una conversazione
     * @param $id
     * @return array
     * @throws Throwable
     */
    public function renderConversation($id): array
    {
        $conversation = $this->getConversation($id);

        $conversation_data = [
            'data' => $conversation,
            'messages' => [],
        ];
        $conversation_messages = $this->getConversationMessages($id);

        foreach ( $conversation_messages as $message ) {

            $message_id = Filters::int($message['id']);
            $is_me = (Filters::int($message['mittente']) === Filters::int($this->me_id));
            $author_data = Personaggio::getPgData($message['mittente']);

            $allegati = [];
            $allegati_list = $this->getConversationAttachments($message_id);

            foreach ( $allegati_list as $allegato ) {

                $type = Filters::string($allegato['tipo']);
                $allegato = Filters::string($allegato['allegato']);
                $title = '';
                $link = '';

                switch ($type){
                    case 'forum':
                        if(ForumPermessi::getInstance()->permissionPost($allegato)) {
                            $title = ForumPosts::getInstance()->renderPostName($allegato);
                        }
                        break;
                    case 'calendario':
                        if(Calendario::getInstance()->existEvent($allegato)) {
                            $title = Calendario::getInstance()->renderEventName($allegato);
                        }
                        break;
                }

                if($title){
                    $allegati[] = [
                        'type' => $type,
                        'title' => $title,
                        'allegato' => $allegato,
                        'link' => $link,
                    ];
                }
            }

            $message['creato_il'] = CarbonWrapper::format($message['creato_il'], 'd/m/y H:i');

            $conversation_data['messages'][] = [
                "is_me" => $is_me,
                'data' => $message,
                'author' => $author_data,
                'allegati' => $allegati,
            ];
        }

        $this->updateRead($id);

        return ["conversation" => $conversation_data];
    }

    /**
     * @fn renderFrameText
     * @note Renderizza il testo del frame laterale
     * @return string
     * @throws Throwable
     */
    public function renderFrameText(): string
    {
        return Template::getInstance()->startTemplate()->render(
            'conversazioni/conversazioni_frame',
            [
                'new_post' => $this->getConversationsToRead()->getNumRows(),
            ]
        );
    }


    /**** FUNCTIONS ****/

    /**
     * @fn sendMessage
     * @note Invia un messaggio in una conversazione
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function sendMessage(array $post): array
    {
        $id = Filters::in($post['id']);

        if ( $this->permissionConversation($id) ) {

            $text = Filters::in($post['testo']);

            DB::queryStmt("UPDATE conversazioni SET ultimo_messaggio=NOW() WHERE id=:id", [
                'id' => $id,
            ]);

            DB::queryStmt("INSERT INTO conversazioni_messaggi(conversazione,mittente,testo,creato_da) VALUES (:conversazione, :mittente,:testo,:creato_da)", [
                'conversazione' => $id,
                'mittente' => $this->me_id,
                'testo' => $text,
                'creato_da' => $this->me_id,
            ]);

            if(isset($post['allegati'])){
                $message_id = DB::queryLastId();

                foreach ($post['allegati'] as $allegato) {
                    DB::queryStmt("INSERT INTO conversazioni_messaggi_allegati(messaggio,allegato,tipo,creato_da) VALUES (:messaggio, :allegato,:tipo,:creato_da)", [
                        'messaggio' => $message_id,
                        'allegato' => Filters::in($allegato['allegato']),
                        'tipo' => Filters::in($allegato['tipo']),
                        'creato_da' => $this->me_id,
                    ]);
                }
            }

            return [
                'response' => true,
                'new_messages' => $this->conversation($id),
                'new_conversations' => $this->conversationsList(),
            ];
        } else {
            return [
                'response' => false,
            ];
        }
    }

    /**
     * @fn updateRead
     * @note Aggiorna la data di lettura di una conversazione
     * @param int $id
     * @return void
     * @throws Throwable
     */
    public function updateRead(int $id): void
    {
        DB::queryStmt("UPDATE conversazioni_membri SET ultima_lettura=NOW() WHERE conversazione=:conversazione AND personaggio=:pg", [
            'conversazione' => $id,
            'pg' => $this->me_id,
        ]);
    }

    /**
     * @fn newConversation
     * @note Crea una nuova conversazione
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function newConversation(array $post): array
    {

        $title = Filters::in($post['title']);
        $img = Filters::in($post['img']);
        $members = $post['members'];

        DB::queryStmt("INSERT INTO conversazioni(nome,immagine,creato_da) VALUES (:titolo, :immagine,:creato_da)", [
            'titolo' => $title,
            'immagine' => $img,
            'creato_da' => $this->me_id,
        ]);

        $conv_id = Filters::int(DB::queryLastId());

        DB::queryStmt("INSERT INTO conversazioni_membri(conversazione,personaggio,proprietario,creato_da) VALUES (:conversazione, :pg,:owner,:creato_da)", [
            'conversazione' => $conv_id,
            'pg' => $this->me_id,
            'owner' => 1,
            'creato_da' => $this->me_id,
        ]);

        foreach ( $members as $member ) {
            if ( Filters::int($member) !== $this->me_id ) {
                DB::queryStmt("INSERT INTO conversazioni_membri(conversazione,personaggio,proprietario,creato_da) VALUES (:conversazione, :pg,:owner,:creato_da)", [
                    'conversazione' => $conv_id,
                    'pg' => Filters::int($member),
                    'owner' => 0,
                    'creato_da' => $this->me_id,
                ]);
            }
        }

        return [
            'response' => true,
            'swal_title' => 'Operazione riuscita!',
            'swal_message' => 'Conversazione creata.',
            'swal_type' => 'success',
            'conv_id' => $conv_id,
        ];
    }

    /**
     * @fn editConversation
     * @note Modifica una conversazione
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function editConversation(array $post): array
    {

        $id = Filters::int($post['id']);

        if ( $this->permissionUpdateConversation($id) ) {

            $title = Filters::in($post['title']);
            $img = Filters::in($post['img']);
            $members = $post['members'];
            $owner = Filters::int($post['owner']);

            DB::queryStmt("UPDATE conversazioni SET nome=:titolo, immagine=:immagine WHERE id=:id", [
                'titolo' => $title,
                'immagine' => $img,
                'id' => $id,
            ]);

            DB::queryStmt("DELETE FROM conversazioni_membri WHERE conversazione=:id AND proprietario=0", [
                'id' => $id,
            ]);

            foreach ( $members as $member ) {
                $member_data = $this->getConversationMember(Filters::int($member), $id);

                if ( $member_data->getNumRows() == 0 ) {
                    DB::queryStmt("INSERT INTO conversazioni_membri(conversazione,personaggio,proprietario,creato_da) VALUES (:conversazione, :pg,:proprietario,:creato_da)", [
                        'conversazione' => $id,
                        'pg' => $member,
                        'proprietario' => 0,
                        'creato_da' => $this->me_id,
                    ]);
                }
            }

            DB::queryStmt("UPDATE conversazioni_membri SET proprietario=0 WHERE conversazione=:id", [
                'id' => $id,
            ]);

            DB::queryStmt("UPDATE conversazioni_membri SET proprietario=1 WHERE conversazione=:id AND personaggio=:pg", [
                'id' => $id,
                'pg' => $owner,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Conversazione creata.',
                'swal_type' => 'success',
                'conv_id' => $id,
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Non hai i permessi per modificare questa conversazione.',
                'swal_type' => 'error',
            ];
        }
    }

    /**
     * @fn deleteConversation
     * @note Elimina una conversazione
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function deleteConversation(array $post): array
    {

        $id = Filters::int($post['id']);

        if ( $this->permissionDeleteConversation($id) ) {

            DB::queryStmt("UPDATE conversazioni SET eliminato_il=NOW(), eliminato_da=:pg WHERE id=:id", [
                'id' => $id,
                'pg' => $this->me_id,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Conversazione creata.',
                'swal_type' => 'success',
                'new_conversations' => $this->conversationsList(),
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Non hai i permessi per eliminare questa conversazione.',
                'swal_type' => 'error',
            ];
        }
    }

    /**
     * @fn addEventFromConversation
     * @note Aggiunge un evento da una conversazione
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function addEventFromConversation(array $post): array
    {
        $event_id = Filters::int($post['id']);
        return Calendario::getInstance()->copyEventToMe($event_id);
    }
}