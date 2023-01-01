<?php

class Calendario extends BaseClass
{
    private bool
        $calendar_enabled,
        $calendar_only_future_selectable;

    /**
     * @throws Throwable
     */
    public function __construct()
    {
        parent::__construct();

        # Calendario attivo?
        $this->calendar_enabled = Functions::get_constant('CALENDAR_ENABLED');

        # Solo date future selezionabili?
        $this->calendar_only_future_selectable = Functions::get_constant('CALENDAR_ONLY_FUTURE_SELECTABLE');
    }


    /*** GETTER */

    /**
     * @fn calendarEnabled
     * @note Controlla se il calendario è attivo
     * @return bool
     */
    public function calendarEnabled(): bool
    {
        return $this->calendar_enabled;
    }

    /**
     * @fn onlyFutureSelectable
     * @note Controlla se solo le date future sono selezionabili
     * @return bool
     */
    public function onlyFutureSelectable(): bool
    {
        return $this->calendar_only_future_selectable;
    }

    /*** PERMISSIONS ***/

    /**
     * @fn permissionEditEvent
     * @note Controlla se l'utente ha i permessi per modificare un evento
     * @param int $event_id
     * @return bool
     * @throws Throwable
     */
    public function permissionEditEvent(int $event_id): bool
    {
        $event_data = $this->getCalendarEvent($event_id);
        return Personaggio::isMyPg(Filters::int($event_data['personaggio']));
    }

    /*** TABLE HELPER ***/

    /**
     * @fn getCalendarEventData
     * @note Ritorna i dati di un evento
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getCalendarEvent(int $id, string $val = 'calendario_tipi.*,calendario.*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM calendario 
                LEFT JOIN calendario_tipi ON calendario.tipo = calendario_tipi.id 
                WHERE calendario.id=:id LIMIT 1", ['id' => $id]);
    }

    /**
     * @fn getCalendarPersonalEvents
     * @note Ritorna gli eventi personali dell'utente
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getCalendarPersonalEvents(int $id, string $val = 'calendario.*,calendario_tipi.colore_bg,calendario_tipi.colore_testo'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM calendario 
                LEFT JOIN calendario_tipi ON calendario.tipo = calendario_tipi.id 
                WHERE (calendario.personaggio=:id AND calendario_tipi.pubblico = 0) OR (calendario_tipi.pubblico = 1)", ['id' => $id]);
    }

    /**
     * @fn getCalendarAllTypes
     * @note Ritorna tutti i tipi di evento
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getCalendarAllTypes(string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM calendario_tipi WHERE 1 ", []);
    }

    /**
     * @fn getCalendarTypeData
     * @note Ritorna i dati di un tipo di evento
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getCalendarTypeData(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM calendario_tipi WHERE id=:id LIMIT 1", ['id' => $id]);
    }

    /*** AJAX ***/

    /**
     * @fn ajaxCalendarSettings
     * @note Ritorna le impostazioni del calendario
     * @return array
     * @throws Throwable
     */
    public function ajaxCalendarSettings(): array
    {
        return [
            'calendar_enabled' => $this->calendarEnabled(),
            'calendar_only_future_selectable' => $this->onlyFutureSelectable(),
            'buttons' => $this->calendarEventsTypesButtons(),
        ];
    }

    /**
     * @fn ajaxCalendarEvents
     * @note Ritorna gli eventi del calendario
     * @return array
     * @throws Throwable
     */
    public function ajaxCalendarEvents(): array
    {
        return [
            'events' => $this->calendarEvents($this->me_id),
        ];
    }

    /**
     * @fn ajaxCalendarFormBody
     * @note Ritorna gli eventi del calendario
     * @return array
     * @throws Throwable
     */
    public function ajaxCalendarFormBody(): array
    {
        return [
            'body' => $this->renderAddEventForm(),
        ];
    }

    /**
     * @fn ajaxCalendarEventData
     * @note Ritorna i dati di un evento
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function ajaxCalendarEventData(array $post): array
    {

        $event_id = Filters::int($post['event_id']);
        $event_data = $this->getCalendarEvent($event_id)->getData()[0];
        $edit_permission = $this->permissionEditEvent($event_id);

        if ( Filters::bool($event_data['pubblico']) && $edit_permission ) {
            return [
                'data' => $event_data,
                'action' => 'edit',
            ];
        } else if ( $edit_permission ) {
            return [
                'data' => $event_data,
                'action' => 'delete',
            ];
        } else {
            return [
                'data' => $event_data,
                'action' => 'view',
            ];
        }
    }

    /*** RENDER ***/

    /**
     * @fn renderAddEventForm
     * @note Renderizza il form per l'aggiunta di un evento
     * @return mixed
     * @throws Throwable
     */
    public function renderAddEventForm(): string
    {
        return Template::getInstance()->startTemplate()->render(
            "calendario/add_event",
            [
                "conversations" => Conversazioni::getInstance()->listConversations(),
                "conversations_active" => Conversazioni::getInstance()->conversationsEnabled()
            ]
        );
    }

    /**
     * @fn renderEventName
     * @note Renderizza il nome di un evento
     * @param int $event_id
     * @return string
     * @throws Throwable
     */
    public function renderEventName(int $event_id): string {
        $event_data = $this->getCalendarEvent($event_id)->getData()[0];
        return Filters::out($event_data['titolo']);
    }

    /**
     * @fn renderEventTooltip
     * @note Renderizza il nome di un evento
     * @param array $event_data
     * @return string
     * @throws Throwable
     */
    public function renderEventTooltip(array $event_data): string {
        $event_data['start_format'] = CarbonWrapper::format($event_data['inizio'], 'd/m/Y H:i');
        $event_data['end_format'] = CarbonWrapper::format($event_data['fine'], 'd/m/Y H:i');
        return Template::getInstance()->startTemplate()->render('calendario/event_tooltip', [
            'event_data' => $event_data,
            'edit_permission' => $this->permissionEditEvent($event_data['id']),
        ]);
    }

    /**
     * @fn calendarEvents
     * @note Ritorna gli eventi del calendario
     * @param int $id
     * @return array
     * @throws Throwable
     */
    public function calendarEvents(int $id): array
    {
        $data = [];

        // Eventi personali
        $personal_events = $this->getCalendarPersonalEvents($id)->getData();
        foreach ( $personal_events as $event ) {

            $data[] = [
                'id' => $event['id'],
                'title' => $event['titolo'],
                'start' => $event['inizio'],
                'end' => $event['fine'],
                'allDay' => $event['all_day'],
                'color' => $event['colore_bg'],
                'textColor' => $event['colore_testo'],
                'extendedProps'=> [
                    'description' => $event['descrizione'],
                    'tooltip' => $this->renderEventTooltip($event),
                ]
            ];
        }

        return $data;
    }

    /**
     * @fn calendarEventsTypesButtons
     * @note Ritorna i tipi di eventi del calendario
     * @return array
     * @throws Throwable
     */
    public function calendarEventsTypesButtons(): array
    {
        $data = [];
        $types = $this->getCalendarAllTypes();
        foreach ( $types as $type ) {
            if(!isset($type['permessi']) || Permissions::permission($type['permessi'])) {
                $data[] = $type;
            }
        }
        return $data;
    }

    /*** FUNCTIONS ***/

    /**
     * @fn addEvent
     * @note Aggiunge un evento al calendario
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function addEvent(array $post): array
    {
        $start = CarbonWrapper::format($post['start'], 'Y-m-d H:i:s');
        $end = CarbonWrapper::format($post['end'], 'Y-m-d H:i:s');
        $title = Filters::string($post['title']);
        $description = Filters::string($post['description']);
        $type = Filters::int($post['type']);
        $conversation_id = Filters::int($post['conversation']);
        $conversation_text = Filters::text($post['conversation_text']);

        if ( $type ) {
            if ( $title ) {
                DB::queryStmt("INSERT INTO calendario (inizio,fine,titolo,descrizione,personaggio,all_day,tipo) VALUES (:inizio,:fine,:titolo,:descrizione,:personaggio,:all_day,:tipo)",
                    [
                        'inizio' => $start,
                        'fine' => $end,
                        'titolo' => $title,
                        'personaggio' => $this->me_id,
                        'all_day' => Filters::int($post['all_day']),
                        'descrizione' => $description,
                        'tipo' => $type,
                    ]
                );

                $calendar_id = DB::queryLastId();

                if($conversation_id){
                    if(Conversazioni::getInstance()->permissionConversation($conversation_id)) {
                        Conversazioni::getInstance()->sendMessage([
                            'id' => $conversation_id,
                            'testo' => $conversation_text,
                            'allegati' => [
                                [
                                    'tipo' => 'calendario',
                                    'allegato' => $calendar_id,
                                ],
                            ]
                        ]);
                    } else {
                        return [
                            'response' => false,
                            'swal_title' => 'Errore!',
                            'swal_message' => 'Non hai i permessi per inviare messaggi in questa conversazione.',
                            'swal_type' => 'error',
                        ];
                    }
                }

                return [
                    'response' => true,
                    'swal_title' => 'Operazione riuscita!',
                    'swal_message' => 'Evento creato.',
                    'swal_type' => 'success',
                    'type_data' => $this->getCalendarTypeData($type)->getData()[0],
                    'event_id' => $calendar_id,
                    'tooltip' => $this->renderEventTooltip($this->getCalendarEvent($calendar_id)->getData()[0]),
                ];
            } else {
                return [
                    'response' => false,
                    'swal_title' => 'Errore!',
                    'swal_message' => 'Titolo mancante.',
                    'swal_type' => 'error',
                ];
            }
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Tipo evento mancante.',
                'swal_type' => 'error',
            ];
        }
    }

    /**
     * @fn removeEvent
     * @note Rimuove un evento dal calendario
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function removeEvent(array $post): array
    {
        $id = Filters::int($post['event_id']);

        if ( $this->permissionEditEvent($id) ) {
            DB::queryStmt("DELETE FROM calendario WHERE id=:id AND personaggio=:personaggio", ['id' => $id, 'personaggio' => $this->me_id]);

            DB::queryStmt("DELETE FROM conversazioni_messaggi_allegati WHERE tipo='calendario' AND allegato=:allegato", ['allegato' => $id]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Evento rimosso.',
                'swal_type' => 'success',
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Non hai i permessi per rimuovere questo evento.',
                'swal_type' => 'error',
            ];
        }
    }

    /**
     * @fn copyEventToMe
     * @note Copia un evento nel calendario personale
     * @param int $event_id
     * @return array
     * @throws Throwable
     */
    public function copyEventToMe(int $event_id): array
    {

        $event_data = $this->getCalendarEvent($event_id);

        DB::queryStmt("INSERT INTO calendario (inizio,fine,titolo,personaggio,all_day,tipo) VALUES (:inizio,:fine,:titolo,:personaggio,:all_day,:tipo)",
            [
                'inizio' => $event_data['inizio'],
                'fine' => $event_data['fine'],
                'titolo' =>  $event_data['titolo'],
                'personaggio' => $this->me_id,
                'all_day' => $event_data['all_day'],
                'tipo' => $event_data['tipo'],
            ]
        );

        return [
            'response' => true,
            'swal_title' => 'Operazione riuscita!',
            'swal_message' => 'Evento duplicato nel tuo calendario personale.',
            'swal_type' => 'success',
        ];
    }
}