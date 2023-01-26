class FullCalendarWrapper {

    calendar;
    form_body;
    active_type;
    settings;
    events;

    /*** AJAX LOADERS ***/

    /**
     * @fn loadSettings
     * @note Carica le impostazioni del calendario
     * @returns void
     */
    async loadSettings() {
        this.settings = await Ajax('calendario/ajax.php', {'action': 'get_calendar_settings'}, function (response) {
            return response;
        });
    }

    /**
     * @fn loadEvents
     * @note Carica gli eventi dal database
     * @returns void
     */
    async loadEvents() {
        this.events = await Ajax('calendario/ajax.php', {'action': 'get_calendar_events'}, function (response) {
            return response;
        });
    }

    /**
     * @fn loadFormBody
     * @note Carica il body del form
     * @returns void
     */
    async loadFormBody() {
        this.form_body = await Ajax('calendario/ajax.php', {
            'action': 'get_calendar_form_body'
        }, function (response) {
            return response;
        });
    }


    async getEventData(event_id) {
        return await Ajax('calendario/ajax.php', {
            action: 'get_event_data',
            event_id: event_id
        }, function (response) {
            return response;
        });
    }

    /*** FUNCTIONS ***/

    /**
     * @fn init
     * @note Inizializza il calendario
     * @param container
     * @returns void
     */
    async init(container) {
        let that = this;
        let calendarEl = document.querySelector(container);

        await this.loadSettings();
        await this.loadEvents();

        let buttons = {};
        this.settings.buttons.map((button) => {

            if (!this.active_type) {
                this.active_type = button.id;
                this.loadFormBody();
            }

            buttons[button.nome] = {
                text: button.nome,
                click: function (event) {
                    that.active_type = button.id;
                    $('#calendar_container .fc-footer-toolbar .fc-button-group button').removeClass('selected');
                    event.target.classList.add("selected");
                    that.loadFormBody();
                }
            }
        });


        this.calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'it',
            selectable: true,
            dayMaxEventRows: true,
            nowIndicator: true,
            selectAllow: function (selectInfo) {
                return (!that.settings.calendar_only_future_selectable) ? true : moment.utc(selectInfo.start.toUTCString()).isSameOrAfter(moment().utc().toISOString());
            },
            customButtons: buttons,
            select: (event) => that.addEventWrapper(event),
            eventClick: (event) => this.clickEventWrapper(event),
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            footerToolbar: {
                left: Object.keys(buttons).toString(),
            },
            eventDidMount: function (info) {
                TippyWrapper.init(info.el, info.event.extendedProps.tooltip);
            },
            events: this.events
        });
    }

    /**
     * @fn render
     * @note Renderizza il calendario
     * @returns void
     */
    async render() {
        this.calendar.render();
        $('#calendar_container .fc-footer-toolbar .fc-button-group button:first-child')?.click();
    }

    /**
     * @fn addEventWrapper
     * @note Wrapper per la funzione addEvent
     * @param event
     * @returns void
     */
    async addEventWrapper(event) {
        let that = this;

        let tooltip = $('.event_tooltip_container');

        if(tooltip.length === 0) {
            if (this.active_type) {
                SwalWrapper.customForm(this.form_body.body, 'Nuovo evento').then(async (result) => {
                    if (result.isConfirmed) {
                        if (result.value.title) {
                            await Ajax('calendario/ajax.php', {
                                action: 'add_event',
                                title: result.value.title,
                                description: result.value.description,
                                start: event.startStr,
                                end: event.endStr,
                                all_day: event.allDay === true ? 1 : 0,
                                type: this.active_type,
                                conversation: result.value?.conversation ?? null,
                                conversation_text: result.value?.conversation_text ?? null
                            }, function (response) {
                                if (response) {
                                    let datas = JSON.parse(response);
                                    if (datas.response) {
                                        that.calendar.addEvent({
                                            id: datas.event_id,
                                            title: result.value.title,
                                            description: result.value.description,
                                            start: event.startStr,
                                            end: event.endStr,
                                            allDay: event.allDay,
                                            color: datas.type_data.colore_bg,
                                            textColor: datas.type_data.colore_testo,
                                            extendedProps: {
                                                tooltip: datas.tooltip
                                            }
                                        });
                                    }
                                    SwalWrapper.fire(datas.swal_title, datas.swal_message, datas.swal_type);
                                }
                            });
                        } else {
                            await SwalWrapper.fire('Titolo mancante', 'Inserisci un titolo per l\'evento', 'error');
                            await this.addEventWrapper(event);
                        }
                    }
                });
            } else {
                await SwalWrapper.fire('Tipo evento mancante', 'Seleziona un tipo evento dalla barra inferiore, prima di continuare', 'error');
            }
        }
    }

    /**
     * @fn removeEventWrapper
     * @note Wrapper per la funzione removeEvent
     * @param event
     * @returns void
     */
    async clickEventWrapper(event) {

        let event_data = await this.getEventData(event.event.id);

        switch (event_data.action) {
            case 'edit':
                await this.editEventWrapper(event_data, event);
                break;
            case 'delete':
                await this.removeEventWrapper(event_data, event);
                break;
        }

    }

    async editEventWrapper(event_data, event) {
        // TODO: Implementare la modifica dell'evento
    }

    async removeEventWrapper(event_data, event) {
        SwalWrapper.fire('Sei sicuro di voler eliminare questo evento?', '', 'question').then((result) => {
            if (result.isConfirmed) {
                Ajax('calendario/ajax.php', {
                    action: 'remove_event',
                    event_id: event_data.data.id
                }, function (response) {
                    if (response) {
                        let datas = JSON.parse(response);
                        if (datas.response) {
                            event.event.remove();
                        }
                        SwalWrapper.fire(datas.swal_title, datas.swal_message, datas.swal_type);
                    }
                });
            }
        });
    }
}