<?php

/**
 * @class Chat
 * @note Classe che gestisce la chat
 */
class Chat extends BaseClass
{

    /**
     * @note Configurazioni di db
     * @type int
     */
    protected int
        $luogo,
        $last_action,
        $chat_time,
        $chat_exp,
        $chat_pvt_exp,
        $chat_exp_master,
        $chat_exp_azione,
        $chat_exp_min,
        $chat_icone,
        $chat_avatar,
        $chat_dice,
        $chat_dice_base,
        $chat_skill_buyed,
        $chat_equip_bonus,
        $chat_equip_equipped,
        $chat_esiti,
        $chat_notify;

    /**** BASE ****/

    /**
     * @fn __construct
     * @note Chat constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->resetClass();
    }

    /**
     * @fn resetClass
     * @note Funzione per il reset delle variabili necessarie
     * @return void
     */
    public function resetClass(): void
    {
        # Variabili di sessione
        $this->luogo = Filters::int(Session::read('luogo'));
        $this->last_action = Filters::int(Session::read('last_action_id'));

        # Ore di caricamento della chat
        $this->chat_time = Functions::get_constant('CHAT_TIME');

        # Esperienza in chat, attiva?
        $this->chat_exp = Functions::get_constant('CHAT_EXP');

        # Esperienza in chat pvt, attiva?
        $this->chat_pvt_exp = Functions::get_constant('CHAT_PVT_EXP');

        # Esperienza guadagnata con azione master
        $this->chat_exp_master = Functions::get_constant('CHAT_EXP_MASTER');

        # Esperienza guadagnata con azione normale
        $this->chat_exp_azione = Functions::get_constant('CHAT_EXP_AZIONE');

        # Minimo caratteri per esperienza
        $this->chat_exp_min = Functions::get_constant('CHAT_EXP_MIN');

        # Icone in chat, attive?
        $this->chat_icone = Functions::get_constant('CHAT_ICONE');

        # Avatar in chat, attivo?
        $this->chat_avatar = Functions::get_constant('CHAT_AVATAR');

        # Suono chat per nuove azioni, attivo?
        $this->chat_notify = Functions::get_constant('CHAT_NOTIFY');

        # Dadi in chat, attivi?
        $this->chat_dice = Functions::get_constant('CHAT_DICE');

        # Facce del dado baso
        $this->chat_dice_base = Functions::get_constant('CHAT_DICE_BASE');

        # Mostra solo le abilita' acquistate?
        $this->chat_skill_buyed = Functions::get_constant('CHAT_SKILL_BUYED');

        # Aggiungere ai tiri in chat i bonus per gli equipaggiamenti indossati?
        $this->chat_equip_bonus = Functions::get_constant('CHAT_EQUIP_BONUS');

        # Mostrare in chat solo gli equipaggiamenti indossati?
        $this->chat_equip_equipped = Functions::get_constant('CHAT_EQUIP_EQUIPPED');

        # Attivare gli esiti in chat?
        $this->chat_esiti = Functions::get_constant('ESITI_CHAT');
    }


    /**** GETTER ****/

    /**
     * @fn actualChatId
     * @note Ottiene l'id della chat attuale
     * @return int
     */
    public function actualChatId(): int
    {
        return $this->luogo;
    }

    /**
     * @fn equippedOnly
     * @note Ottiene l'id della chat attuale
     * @return bool
     */
    public function equippedOnly(): bool
    {
        return $this->chat_equip_equipped;
    }

    /**
     * @fn activeNotify
     * @note Controlla se il suono di notifica è attivo
     * @return bool
     */
    public function activeNotify(): bool
    {
        return $this->chat_notify;
    }

    /* ! test

    /**
     * @fn setLast
     * @note Imposta l'ultima azione
     * @param int $id
     * @return void
     */
    public function setLast(int $id): void
    {
        Session::store('last_action_id', $id);
        $this->last_action = $id;
    }

    /**** TABLE HELPERS ***/

    /**
     * @fn getAllChats
     * @note Ottiene tutte le chat
     * @param int $private
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllChats(int $private = 0): DBQueryInterface
    {
        return DB::queryStmt("SELECT * FROM mappa WHERE privata = :privata ORDER BY nome", ['privata' => $private]);
    }

    /**
     * @fn getChatData
     * @note Estrae i dati di una chat
     * @param int $id
     * @param string $val
     * @return bool|array
     * @throws Throwable
     */
    public function getChatData(int $id, string $val = '*'): bool|array
    {
        return DB::queryStmt(
            "SELECT {$val} FROM mappa WHERE id=:id LIMIT 1",
            [
                'id' => $id,
            ]
        )->getData();
    }

    /**
     * @fn getActions
     * @note Estrai le azioni in chat che devono essere visualizzate
     * @param int $chat
     * @param string $start
     * @param string $end
     * @return array
     * @throws Throwable
     */
    public function getActionsByTime(int $chat, string $start, string $end): array
    {

        $start = Filters::out($start);
        $end = Filters::out($end);
        $chat = Filters::in($chat);

        # Estraggo le azioni n base alle condizioni indicate
        return DB::queryStmt("
        SELECT personaggio.nome,personaggio.url_img_chat,personaggio.sesso,chat.* FROM chat 
            LEFT JOIN personaggio ON (personaggio.nome = chat.mittente)
            WHERE chat.stanza = :chat AND chat.ora >= :start AND chat.ora <= :end
            ORDER BY id",
            ['chat' => $chat, 'start' => $start, 'end' => $end]
        )->getData();
    }

    /**
     * @fn getActions
     * @note Estrai le azioni in chat che devono essere visualizzate
     * @return DBQueryInterface
     * @throws Throwable
     */
    private function getActions(): DBQueryInterface
    {
        # Se l'ultima azione non e' 0 e quindi non sono appena entrato in chat
        $extra_query = ($this->last_action > 0) ? " AND chat.id > '{$this->last_action}' " : '';

        # Estraggo le azioni n base alle condizioni indicate
        # ! Lasciare il now in questo modo per essere sicuri che la data sia giusta
        return DB::queryStmt("
            SELECT personaggio.nome,personaggio.url_img_chat,personaggio.sesso,chat.*  
            FROM chat LEFT JOIN personaggio ON (personaggio.nome = chat.mittente)
            WHERE chat.stanza = :stanza AND chat.ora > DATE_SUB(:now,INTERVAL :hours HOUR) {$extra_query}
            ORDER BY id",
            [
                "now" => CarbonWrapper::getNow('Y-m-d H:i:s'),
                "hours" => $this->chat_time,
                "stanza" => $this->luogo,
            ]
        );
    }

    /**
     * @fn isPgInChat
     * @note Controlla se un pg è in chat
     * @param int $pg
     * @param int $luogo
     * @return bool
     * @throws Throwable
     */
    private function isPgInChat(int $pg, int $luogo): bool
    {
        $count = DB::queryStmt(
            "SELECT count(id) AS total FROM personaggio WHERE id=:pg AND ultimo_luogo=:luogo LIMIT 1",
            [
                'pg' => $pg,
                'luogo' => $luogo,
            ]
        )->getData();

        return Filters::int($count['total']);
    }

    /**
     * TODO Adattare con nuove statistiche oggetti
     * @fn calcAllObjsBonus
     * @note Calcola i bonus statistiche dell'oggetto
     * @param int $pg
     * @param int $car
     * @param array $excluded
     * @return int
     * @throws Throwable
     */
    public static function calcAllObjsBonus(int $pg, int $car, array $excluded = []): int
    {
        # ! Terminare quando ci saranno statistiche oggetto, non funziona al momento
        $extra_query = '';
        $total_bonus = 0;

        if ( !empty($excluded) ) {
            $implode = implode(',', $excluded);
            $extra_query = " AND personaggio_oggetto.id NOT IN ({$implode})";
        }

        # Estraggo i bonus di tutti gli oggetti equipaggiati
        $objects = DB::query("SELECT oggetto.bonus_car{$car},personaggio_oggetto.id
                                        FROM personaggio_oggetto 
                                        LEFT JOIN oggetto 
                                        ON (personaggio_oggetto.oggetto = oggetto.id)
                                         
                                        WHERE personaggio_oggetto.personaggio ='{$pg}' {$extra_query}
                                        AND personaggio_oggetto.indossato > 0 AND oggetto.indossabile = 1

                                        ", 'result');

        # Per ogni oggetto equipaggiato
        foreach ( $objects as $object ) {
            # Aggiungo il suo bonus al totale
            $total_bonus += Filters::int($object["bonus_car{$car}"]);
        }

        return $total_bonus;
    }


    /**** CONTROLS ****/

    /**
     * @fn controllaChat
     * @note Controllo chat
     * @param $post
     * @return array
     * @throws Throwable
     */
    public function chatPositionTrue($post): array
    {
        // ID che arriva dalla chat in cui ci si trova
        $dir = Filters::int($post['dir']);

        // Controllo la chat dove si trova da bg
        $luogo = Personaggio::getPgData($this->me, 'ultimo_luogo');
        $luogo_id = Filters::int($luogo['ultimo_luogo']);

        // Ritorno il risultato ed eventuale nuova direzione per redirect
        return ['response' => ($dir == $luogo_id), 'newdir' => $luogo_id];
    }

    /**
     * @fn audioActivated
     * @note Controlla se nella scheda pg l'audio è segnato attivato o meno
     * @return bool
     * @throws Throwable
     */
    public function audioActivated(): bool
    {
        $blocca_media = Personaggio::getPgData($this->me_id, 'blocca_media');
        return Filters::bool($blocca_media['blocca_media']);
    }

    /**
     * @fn chatAccess
     * @note Si occupa di controllare che la chat sia accessibile (non pvt o pvt accessibile dal pg)
     * @return bool
     * @throws Throwable
     * TODO Implementare controlli per eventuali chat di gruppo o altri tipi di chat
     */
    public function chatAccess(): bool
    {
        # Inizializzo le variabili necessarie
        $resp = false;

        # Filtro i dati passati
        $id = Filters::int($this->luogo);

        # Estraggo i dati della chat
        $data = $this->getChatData($id, 'privata,proprietario,invitati,scadenza');

        # Filtro i valori estratti
        $privata = Filters::int($data['privata']);
        $proprietario = Filters::out($data['proprietario']);
        $invitati = Filters::out($data['invitati']);
        $scadenza = Filters::out($data['scadenza']);

        # Se non è privata di default è accessibile
        if ( !$privata ) {
            $resp = true;
        } # Se e' privata - TODO da testare per bene
        else {
            # Se faccio parte degli invitati o sono il proprietario e la stanza non è scaduta
            if ( $this->pvtExpired($scadenza) && $this->pvtInvited($proprietario, $invitati) ) {
                $resp = true;
            }
        }

        # Ritorno il risultato
        return $resp;
    }

    /**
     * @fn pvtInvited
     * @note Controlla se si fa parte degli invitati o se si è il proprietario
     * @param string $proprietario
     * @param string $invitati
     * @return bool
     */
    private function pvtInvited(string $proprietario, string $invitati): bool
    {
        # Filtro i dati passati
        $proprietario = Filters::out($proprietario);
        $invitati = Filters::out($invitati);

        # Trasformo la riga invitati in un array
        $inv_array = explode(',', $invitati);

        # Controllo se sono invitato
        $invitato = in_array($this->me, $inv_array);

        # Se sono invitato o sono il proprietario, posso accedere
        return (($proprietario == $this->me) || ($invitato));
    }

    /**
     * @fn pvtExpired
     * @note Controlla se la chat è scaduta
     * @param string $scadenza
     * @return bool
     */
    private function pvtExpired(string $scadenza): bool
    {
        return CarbonWrapper::lowerThan($scadenza, CarbonWrapper::getNow());
    }


    /**** LISTS ****/

    /**
     * @fn chatList
     * @note Crea la lista delle chat
     * @return string
     * @throws Throwable
     */
    public function chatList(): string
    {
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $this->getAllChats(), 'Chat');
    }


    /**** FUNCTIONS ****/

    /**
     * @fn assignExp
     * @note Assegna l'esperienza indicata al personaggio che ha inviato l'azione
     * @param array $azione
     * @return void
     * @throws Throwable
     */
    private function assignExp(array $azione): void
    {
        # Filtro i valori passati
        $tipo = Filters::in($azione['tipo']);
        $testo = Filters::in($azione['testo']);

        # Calcolo la lunghezza del testo
        $lunghezza_testo = strlen($testo);

        # Controllo se la chat è privata
        $luogo_pvt = $this->getChatData($this->luogo, 'privata');

        # Se è privata e l'assegnazione privata è attivo, oppure non è privata
        if ( ($luogo_pvt && $this->chat_pvt_exp) || (!$luogo_pvt) ) {

            # Se la lunghezza del testo supera il minimo caratteri necessari all'assegnazione esperienza
            if ( $lunghezza_testo >= $this->chat_exp_min ) {

                # In base al tipo scelgo quanta esperienza assegnare
                $exp = match ($tipo) {
                    'A', 'P' => $this->chat_exp_azione,
                    'M' => $this->chat_exp_master,
                    default => 0,
                };

                # Assegno l'esperienza se è maggiore di zero
                if ( $exp > 0 ) {
                    Personaggio::updatePgData($this->me_id, "esperienza = esperienza + '{$exp}'");
                }
            }
        }
    }

    /**
     * @fn formattedText
     * @note Formatta il testo con le giuste funzioni di gdrcd
     * @param string $testo
     * @param string $colore_parlato
     * @param bool $master
     * @return string
     */
    private function formattedText(string $testo, string $colore_parlato = '', bool $master = false): string
    {
        # Evidenzio il mio nome e cambio il testo tra le variabili, ritorno il risultato
        return $this->chatMe($this->me, $this->chatColor($testo, $colore_parlato), $master);
    }

    /**
     * @fn chatMe
     * @note Evidenzia il nome del pg che ha parlato
     * @param string $user
     * @param string $str
     * @param bool $master
     * @return string
     */
    private function chatMe(string $user, string $str, bool $master = false): string
    {
        $search = "|\\b" . preg_quote($user, "|") . "\\b|si";
        if ( !$master ) {
            $replace = '<span class="chat_me">' . Filters::out($user) . '</span>';
        } else {
            $replace = '<span class="chat_me_master">' . Filters::out($user) . '</span>';
        }

        return preg_replace($search, $replace, $str);
    }

    /**
     * @fn chatColor
     * @note Cambia il colore del testo per il parlato
     * @param string $str
     * @param string $colore_parlato
     * @return string
     */
    private function chatColor(string $str, string $colore_parlato): string
    {

        $colore_parlato = ($colore_parlato) ?? '';

        $search = [
            '#\&lt;(.+?)\&gt;#is',
            '#\[(.+?)\]#is',
        ];
        $replace = [
            "<span class='color2' style='color:{$colore_parlato}'>&lt;$1&gt;</span>",
            "<span class='color2' style='color:{$colore_parlato}'>&lt;$1&gt;</span>",
        ];

        return preg_replace($search, $replace, $str);
    }

    /**
     * @fn chatIcons
     * @note Funzione che si occupa dell'estrazione delle icone chat
     * @param string $mittente
     * @return array
     * @throws Throwable
     */
    private function chatIcons(string $mittente): array
    {
        # Filtro il mittente passato
        $mittente = Filters::int($mittente);

        $icons = [];

        # TODO Aggiustare quando le razze saranno multiple con l'apposita classe
        # Ritorno le sue icone estratte
        $races = DB::query("
                SELECT razze.icon,razze.nome AS nome_razza,personaggio.sesso
                FROM personaggio 
                LEFT JOIN razze ON (razze.id = personaggio.razza)
                WHERE personaggio.id = '{$mittente}'", 'result');

        foreach ( $races as $race ) {
            $icons[] = [
                "Title" => Filters::out($race['nome_razza']),
                "Img" => 'races/' . Filters::out($race['icon']),
            ];
        }

        if ( Gruppi::getInstance()->activeGroupIconChat() ) {
            $roles = PersonaggioRuolo::getInstance()->getAllCharacterRolesWithRoleData($mittente);

            foreach ( $roles as $role ) {
                $icons[] = [
                    "Title" => Filters::out($role['nome']),
                    "Img" => 'groups/' . Filters::out($role['immagine']),
                ];
            }
        }

        return $icons;
    }


    /***** TIPOLOGIE HTML STAMP *****/

    /**
     * @fn printChat
     * @note Stampi le azioni in chat, funzione pubblica da richiamare per lo stamp
     * @return string
     * @throws Throwable
     */
    public function printChat(): string
    {
        if ( $this->chatAccess() ) {
            return $this->Filter($this->getActions()->getData());
        } else {
            return '';
        }
    }

    /**
     * @fn printChatByTime
     * @note Stampa le azioni in chat, ma solo quelle nel tempo indicato
     * @param int $chat
     * @param string $start
     * @param string $end
     * @return string
     * @throws Throwable
     */
    public function printChatByTime(int $chat, string $start, string $end)
    {
        # Inizializzo le variabili necessarie
        $html = '';

        # Estraggo le azioni della chat
        $azioni = $this->getActionsByTime($chat, $start, $end);

        # Per ogni azione creo il suo html in base al tipo
        foreach ( $azioni as $azione ) {
            $html .= $this->Filter($azione);
        }

        # Ritorno html creato
        return $html;
    }

    /**
     * @fn Filter
     * @ntoe Filtra il tipo dell'azione passata per decidere che html stampare
     * @param array $azioni
     * @return string
     * @throws Throwable
     */
    public function Filter(array $azioni): string
    {
        $html = '';

        foreach ( $azioni as $azione ) {
            # Filtro i dati passati
            $tipo = Filters::out($azione['tipo']);
            $id = Filters::out($azione['id']);

            # Inizio l'html con una classe comune ed una di tipologia, uguale per tutte
            $html .= "<div class='singola_azione chat_row_{$tipo}'>";

            # Creo l'html in base al tipo indicato
            switch ( $tipo ) {
                case 'A':
                case 'P':
                    $html .= $this->htmlAzione($azione);
                    break;
                case 'S':
                    $html .= $this->htmlSussurro($azione);
                    break;
                case 'F':
                    $html .= $this->htmlSussurroGlobale($azione);
                    break;
                case 'N':
                    $html .= $this->htmlPNG($azione);
                    break;
                case 'M':
                    $html .= $this->htmlMaster($azione);
                    break;
                case 'MOD':
                    $html .= $this->htmlMod($azione);
                    break;
                case 'I':
                    $html .= $this->htmlImage($azione);
                    break;
                case 'C':
                case 'D':
                case 'O':
                    $html .= $this->htmlOnlyText($azione);
                    break;
                default:
                    $html = '';
                    break;
            }

            $html .= "</div>";

            # Setto quella analizzata come ultima azione analizzata
            $this->setLast($id);
        }

        # Ritorno html creato
        return $html;
    }

    /**
     * @fn htmlAzione
     * @note Stampa html per il tipo 'A'.Azione ed il tipo 'P'.Parlato (per retrocompatibilita')
     * @param array $azione
     * @return string
     * @throws Throwable
     */
    private function htmlAzione(array $azione): string
    {
        # Filtro le variabili necessarie
        $mittente = Filters::int($azione['mittente']);
        $mittente_data = Personaggio::getPgData($mittente, 'nome,url_img_chat');
        $testo = Filters::string($azione['testo']);

        # Customization
        $colore_testo_parlato = PersonaggioChatOpzioni::getInstance()->getOptionValue('action_color_talk', $this->me_id);
        $colore_parlato = Filters::out($colore_testo_parlato['valore']);
        $colore_testo_descr = PersonaggioChatOpzioni::getInstance()->getOptionValue('action_color_descr', $this->me_id);
        $testo_size = PersonaggioChatOpzioni::getInstance()->getOptionValue('action_size', $this->me_id);

        $array = [
            "img_chat" => Filters::out($mittente_data['url_img_chat']),
            "chat_icone" => $this->chat_icone,
            "data_imgs" => $this->chatIcons($mittente),
            "chat_avatar" => $this->chat_avatar,
            "img_link" => Router::getImgsDir(),
            "ora" => CarbonWrapper::format($azione['ora'], 'H:i'),
            "testo" => $this->formattedText($testo, $colore_parlato),
            "size" => Filters::out($testo_size['valore']) ? Filters::out($testo_size['valore']) . 'px' : '',
            "colore_descr" => Filters::out($colore_testo_descr['valore']),
            "mittente_nome" => Filters::out($mittente_data['nome']),
            "tag" => Filters::out($azione['destinatario']),
        ];

        # Ritorno html creato
        return Template::getInstance()->startTemplate()->render('chat/A', $array);
    }

    /**
     * @fn htmlSussurro
     * @note Stampa html per il tipo 'S'.Sussurro
     * @param array $azione
     * @return string
     */
    private function htmlSussurro(array $azione): string
    {
        # Inizializzo le variabili necessarie
        $intestazione = '';

        # Filtro le variabili necessarie
        $mittente = Filters::int($azione['mittente']);
        $destinatario = Filters::int($azione['destinatario']);
        $testo = Filters::string($azione['testo']);

        $mittente_data = Personaggio::getPgData($mittente, 'nome');
        $mittente_nome = Filters::out($mittente_data['nome']);

        $destinatario_data = Personaggio::getPgData($destinatario, 'nome');
        $destinatario_nome = Filters::out($destinatario_data['nome']);

        # Customizzazioni
        $colore_testo_parlato = PersonaggioChatOpzioni::getInstance()->getOptionValue('sussurro_color', $this->me_id);
        $colore_parlato = Filters::out($colore_testo_parlato['valore']);

        $testo_size = PersonaggioChatOpzioni::getInstance()->getOptionValue('sussurro_size', $this->me_id);
        $size = Filters::out($testo_size['valore']) ? Filters::out($testo_size['valore']) . 'px' : '';

        # Formo i testi necessari
        $ora = CarbonWrapper::format($azione['ora'], 'H:i');
        $testo = $this->formattedText($testo, '');

        # Se sono io il mittente
        if ( $this->me_id == $mittente ) {
            $intestazione = "Hai sussurrato a {$destinatario_nome}: ";
        } # Se sono io il destinatario
        else if ( $this->me_id == $destinatario ) {
            $intestazione = "{$mittente_nome} ti ha sussurrato: ";
        } # Se non sono nessuno di entrambi ma sono uno staffer
        else if ( $this->permission > MODERATOR ) {
            $intestazione = "{$mittente_nome} ha sussurrato a {$destinatario_nome}";
        }

        # Se l'intestazione non e' vuota, significa che posso leggere il messaggio e lo stampo
        $array = [
            "intestazione" => $intestazione,
            "ora" => $ora,
            "colore_parlato" => $colore_parlato,
            "size" => $size,
            "testo" => $testo,
        ];

        # Ritorno l'html stampato
        return Template::getInstance()->startTemplate()->render('chat/S', $array);
    }

    /**
     * @fn htmlSussurroGlobale
     * @note Stampa html per il tipo 'F'.Sussurro Globale
     * @param array $azione
     * @return string
     */
    private function htmlSussurroGlobale(array $azione): string
    {
        # Filtro i dati necessari
        $mittente = Filters::int($azione['mittente']);
        $ora = CarbonWrapper::format($azione['ora'], 'H:i');
        $testo = Filters::string($azione['testo']);

        $mittente_data = Personaggio::getPgData($mittente, 'nome');
        $mittente_nome = Filters::out($mittente_data['nome']);

        # Customizzazioni
        $colore_testo_parlato = PersonaggioChatOpzioni::getInstance()->getOptionValue('sussurro_globale_color', $this->me_id);
        $colore_parlato = Filters::out($colore_testo_parlato['valore']);

        $testo_size = PersonaggioChatOpzioni::getInstance()->getOptionValue('sussurro_globale_size', $this->me_id);
        $size = Filters::out($testo_size['valore']) ? Filters::out($testo_size['valore']) . 'px' : '';

        # Formo i testi necessari
        $testo = $this->formattedText($testo);

        # Se l'intestazione non e' vuota, significa che posso leggere il messaggio e lo stampo
        $array = [
            "ora" => $ora,
            "colore_parlato" => $colore_parlato,
            "size" => $size,
            "testo" => $testo,
            "mittente_nome" => $mittente_nome,
        ];

        # Ritorno l'html dell'azione
        return Template::getInstance()->startTemplate()->render('chat/F', $array);
    }

    /**
     * @fn htmlPNG
     * @note Stampa html per il tipo 'N'.PNG
     * @param array $azione
     * @return string
     */
    private function htmlPNG(array $azione): string
    {
        # Filtro i dati necessari
        $destinatario = Filters::out($azione['destinatario']);
        $testo = Filters::string($azione['testo']);

        # Customizzazioni
        $colore_testo_parlato = PersonaggioChatOpzioni::getInstance()->getOptionValue('png_color_talk', $this->me_id);
        $colore_parlato = Filters::out($colore_testo_parlato['valore']);

        $colore_testo_descr = PersonaggioChatOpzioni::getInstance()->getOptionValue('png_color_descr', $this->me_id);
        $colore_descr = Filters::out($colore_testo_descr['valore']);

        $testo_size = PersonaggioChatOpzioni::getInstance()->getOptionValue('png_size', $this->me_id);
        $size = Filters::out($testo_size['valore']) ? Filters::out($testo_size['valore']) . 'px' : '';

        # Formo i testi necessari
        $ora = CarbonWrapper::format($azione['ora'], 'H:i');
        $testo = $this->formattedText($testo, $colore_parlato);

        $array = [
            "ora" => $ora,
            "destinatario" => $destinatario,
            "colore_descr" => $colore_descr,
            "size" => $size,
            "testo" => $testo,
        ];

        # Ritorno l'html dell'azione
        return Template::getInstance()->startTemplate()->render('chat/N', $array);
    }

    /**
     * @fn htmlMaster
     * @note Stampa html per il tipo 'M'.Master
     * @param array $azione
     * @return string
     */
    private function htmlMaster(array $azione): string
    {
        $testo = Filters::string($azione['testo']);

        # Customizzazioni
        $colore_testo_parlato = PersonaggioChatOpzioni::getInstance()->getOptionValue('master_color_talk', $this->me_id);
        $colore_parlato = Filters::out($colore_testo_parlato['valore']);

        $colore_testo_descr = PersonaggioChatOpzioni::getInstance()->getOptionValue('master_color_descr', $this->me_id);
        $colore_descr = Filters::out($colore_testo_descr['valore']);

        $testo_size = PersonaggioChatOpzioni::getInstance()->getOptionValue('master_size', $this->me_id);
        $size = Filters::out($testo_size['valore']) ? Filters::out($testo_size['valore']) . 'px' : '';

        # Formo i testi necessari
        $ora = CarbonWrapper::format($azione['ora'], 'H:i');
        $testo = $this->formattedText($testo, $colore_parlato, true);

        $array = [
            "ora" => $ora,
            "colore_descr" => $colore_descr,
            "size" => $size,
            "testo" => $testo,
        ];

        # Ritorno l'html dell'azione
        return Template::getInstance()->startTemplate()->render('chat/M', $array);
    }

    /**
     * @fn htmlMod
     * @note Stampa html per il tipo 'MOD'.Moderazione
     * @param array $azione
     * @return string
     */
    private function htmlMod(array $azione): string
    {
        $testo = Filters::string($azione['testo']);

        # Customizzazioni
        $colore_testo_parlato = PersonaggioChatOpzioni::getInstance()->getOptionValue('mod_color', $this->me_id);
        $colore_parlato = Filters::out($colore_testo_parlato['valore']);

        $testo_size = PersonaggioChatOpzioni::getInstance()->getOptionValue('mod_size', $this->me_id);
        $size = Filters::out($testo_size['valore']) ? Filters::out($testo_size['valore']) . 'px' : '';

        # Formo i testi necessari
        $ora = CarbonWrapper::format($azione['ora'], 'H:i');
        $testo = $this->formattedText($testo);

        $array = [
            "ora" => $ora,
            "colore_parlato" => $colore_parlato,
            "size" => $size,
            "testo" => $testo,
        ];

        # Ritorno l'html dell'azione
        return Template::getInstance()->startTemplate()->render('chat/MOD', $array);
    }

    /**
     * @fn htmlOnlyText
     * @note Stampa html per le tipologie che necessitano solo di ora e messaggio (Dado,Oggetto,Caccia/Invita)
     * @param array $azione
     * @return string
     */
    private function htmlOnlyText(array $azione): string
    {
        $testo = Filters::string($azione['testo']);

        # Customizzazioni
        $colore_testo_parlato = PersonaggioChatOpzioni::getInstance()->getOptionValue('other_color', $this->me_id);
        $colore_parlato = Filters::out($colore_testo_parlato['valore']);

        $testo_size = PersonaggioChatOpzioni::getInstance()->getOptionValue('other_size', $this->me_id);
        $size = Filters::out($testo_size['valore']) ? Filters::out($testo_size['valore']) . 'px' : '';

        # Formo i testi necessari
        $ora = CarbonWrapper::format($azione['ora'], 'H:i');
        $testo = $this->formattedText($testo);

        $array = [
            "ora" => $ora,
            "colore_parlato" => $colore_parlato,
            "size" => $size,
            "testo" => $testo,
        ];

        # Ritorno l'html dell'azione
        return Template::getInstance()->startTemplate()->render('chat/text', $array);
    }

    /**
     * @fn htmlImage
     * @note Stampa il testo passato come immagine
     * @param array $azione
     * @return string
     */
    private function htmlImage(array $azione): string
    {
        # Formo i testi necessari
        $ora = CarbonWrapper::format($azione['ora'], 'H:i');
        $url = Filters::out($azione['testo']);

        $array = [
            "ora" => $ora,
            "url" => $url,
        ];

        # Ritorno l'html dell'azione
        return Template::getInstance()->startTemplate()->render('chat/I', $array);
    }


    /******* INVIO AZIONE *******/

    /**
     * @fn send
     * @note Funzione contenitore per smistamento tipi d'invio in chat
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function send(array $post): array
    {
        if ( $this->chatAccess() ) {
            # Inizializzo le variabili necessarie
            $resp = [];

            # Filtro i dati necessari
            $tipo = Filters::in($post['tipo']);

            # In base al tipo mando l'azione con diversi permessi
            switch ( $tipo ) {
                case 'A':
                case 'P':
                case 'F':
                    $resp = $this->sendAction($post, USER);
                    break;
                case 'N':
                case 'M':
                case 'I':
                    $resp = $this->sendAction($post, GAMEMASTER);
                    break;
                case 'MOD':
                    $resp = $this->sendAction($post, MODERATOR);
                    break;
                case 'S':
                    $resp = $this->sendSussurro($post);
                    break;
            }

            # Se l'esperienza è attiva assegno l'esperienza
            if ( $this->chat_exp ) {
                $this->assignExp($post);
            }

            # Ritorno il risultato dell'invio ed eventuale messaggio di errore
            return $resp;
        } else {
            return [
                'response' => false,
                'error' => 'Permesso negato.',
            ];
        }
    }

    /**
     * @fn sendAction
     * @note Manda le azioni generiche che non hanno il permesso come unico controllo
     * @param array $post
     * @param int $permission
     * @return array
     * @throws Throwable
     */
    private function sendAction(array $post, int $permission): array
    {
        # Filtro le variabili necessarie
        $permission = Filters::int($permission);

        # Se i permessi sono superiori a quelli necessari
        if ( $this->permission >= $permission ) {

            # Salvo l'azione in db
            $this->saveAction($post);

            # Setto il responso riuscito
            $response = ['response' => true, 'error' => ''];

        } # Se non ho i permessi per quel tipo di azione
        else {

            # Setto il responso come fallito
            $response = [
                'response' => false,
                'error' => 'Non hai i permessi per questo tipo di azione.',
            ];
        }

        # Ritorno il responso dell'invio azione
        return $response;
    }

    /**
     * @fn sendSussurro
     * @note Manda le azioni di tipo sussurro e relativi controlli
     * @param array $post
     * @return array
     * @throws Throwable
     */
    private function sendSussurro(array $post): array
    {
        # Filtro le variabili necessarie
        $sussurraA = Filters::int($post['whispTo']);

        # Controllo che il personaggio a cui sussurro sia nella mia stessa chat
        $count = $this->isPgInChat($sussurraA, $this->luogo);

        # Se è nella mia stessa chat
        if ( $count > 0 ) {

            $post['tag'] = $sussurraA;

            # Salvo l'azione in DB
            $this->saveAction($post);

            # Setto il responso riuscito
            $response = ['response' => true, 'error' => ''];

        } # Se il personaggio non è presente in chat, setto il responso come fallito
        else {
            $response = [
                'response' => false,
                'error' => 'Personaggio non presente in chat.',
            ];
        }

        # Ritorno il responso
        return $response;
    }

    /**
     * @fn saveAction
     * @note Funzione che si occupa del salvataggio dell'azione in DB
     * @param array $post
     * @throws Throwable
     */
    private function saveAction(array $post)
    {
        # Filtro le variabili necessarie
        $tag = Filters::in($post['tag']);
        $tipo = Filters::in($post['tipo']);
        $testo = Filters::in($post['testo']);

        # Salvo l'azione in DB
        DB::queryStmt(
            "INSERT INTO chat(stanza,imgs,mittente,destinatario,tipo,testo)
                    VALUE(:luogo,'test',:mittente,:tag,:tipo,:testo)",
            [
                "luogo" => $this->luogo,
                'mittente' => $this->me_id,
                "tag" => $tag,
                "tipo" => $tipo,
                "testo" => $testo,
            ]
        );
    }


    /**** LANCIO DADI ****/

    /**
     * @fn roll
     * @note Funzione contenitore del lancio dadi/abi/stat
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function roll(array $post): array
    {
        if ( $this->chatAccess() ) {
            # Inizializzo le variabili necessarie
            $abi_nome = '';
            $abi_dice = '';
            $car_dice = '';
            $car_bonus = '';
            $car_name = '';
            $obj_nome = '';
            $obj_dice = '';

            # Filtro le variabili necessarie
            $abi = Filters::int($post['abilita']);
            $car = Filters::int($post['caratteristica']);
            $obj = Filters::int($post['oggetto']);

            # Se ho selezionato l'abilita'
            if ( $abi != 0 ) {

                # Estraggo i dati dell'abilita' scelta
                $abi_roll = $this->rollAbility($abi);

                # Filtro i dati ricevuti
                $abi_dice = Filters::int($abi_roll['abi_dice']);
                $abi_nome = Filters::in($abi_roll['abi_nome']);

                # Se non ho selezionato nessuna caratteristica utilizzo quella base dell'abilita'
                if ( $car == 0 ) {
                    # Imposto la stat dell'abilita' come caratteristica richiesta in caso di utilizzo oggetti
                    $car_name = Filters::out($abi_roll['car_name']);
                    $car = Filters::int($abi_roll['car']);
                }
            }

            # Se ho selezionato una caratteristica (abbinata o meno ad un'abilita')
            if ( $car != 0 ) {

                # Estraggo i dati riguardo la statistica
                $car_roll = $this->rollCar($car, $obj);

                # Setto il valore della caratteristica
                $car_dice = Filters::int($car_roll['car_dice']);
                $car_name = Filters::out($car_roll['car_name']);

                # Se devo aggiungere anche il valore dell'equipaggiamento alla stat
                if ( $this->chat_equip_bonus ) {

                    # Valorizzo il campo bonus equip stat per la stampa del testo
                    $car_bonus = Filters::int($car_roll['car_bonus']);
                }
            }

            # Se ho selezionato l'utilizzo di un oggetto e di una tra abilita' o stat
            if ( ($obj != 0) && ($car != 0) ) {

                # Estraggo i dati dell'oggetto
                $obj_roll = $this->rollObj($obj, $car);

                # Filtro e setto i dati necessari all'aggiunta del valore al lancio
                $obj_nome = Filters::out($obj_roll['nome']);
                $obj_dice = Filters::int($obj_roll['val']);
            }

            # Lancio il dado
            $dice = ($this->chat_dice) ? $this->rollDice() : 0;

            # Formo l'array necessario per il rendering del testo # TODO DA MIGLIORARE
            $data = [
                'dice' => $dice,
                'abi_dice' => $abi_dice,
                'abi_nome' => $abi_nome,
                'car_dice' => $car_dice,
                'car_name' => $car_name,
                'obj_nome' => $obj_nome,
                'obj_dice' => $obj_dice,
                'car_bonus' => $car_bonus,
            ];

            # Formo il testo e lo salvo in database
            $this->saveDice($data);

            # Ritorno il responso positivo
            return ['response' => true, 'error' => ''];
        } else {
            return ['response' => false, 'error' => 'Permesso negato.'];

        }
    }

    /**
     * @fn rollAbility
     * @note Funzione che si occupa del calcolo dell'abilita'
     * @param int $id
     * @return array
     */
    public function rollAbility(int $id): array
    {
        # Filtro le variabili necessarie
        $id = Filters::int($id);
        $pg_class = PersonaggioAbilita::getInstance();
        $abi_class = Abilita::getInstance();

        # Estraggo i dati relativi l'abilita'
        $abi_data_pg = $pg_class->getPgAbility($id, $this->me_id, 'personaggio_abilita.grado,abilita.nome,abilita.statistica');
        $stat = Filters::int($abi_data_pg['statistica']);

        # $Dati Stat
        $stat_class = Statistiche::getInstance();
        $stat_data = $stat_class->getStat($stat, 'nome');
        $stat_name = Filters::in($stat_data['nome']);

        # Dati Abi
        $abi_data = $abi_class->getAbilita($id, 'nome');
        $abi_dice = !empty($abi_data_pg['grado']) ? Filters::int($abi_data_pg['grado']) : 0;
        $nome = Filters::in($abi_data['nome']);

        # Ritorno i dati necessari
        return ['abi_nome' => $nome, 'abi_dice' => $abi_dice, 'car_name' => $stat_name, 'car' => $stat];
    }

    /**
     * @fn rollCar
     * @note Funzione che si occupa del calcolo della statistica
     * @param int $car
     * @param int $obj
     * @return array
     * @throws Throwable
     */
    private function rollCar(int $car, int $obj): array
    {
        # Filtro i dati passati
        $car = Filters::int($car);
        $obj = Filters::int($obj);
        $stat_class = Statistiche::getInstance();

        # Inizializzo il totale
        $total_bonus = ($this->chat_equip_bonus) ? $this->calcAllObjsBonus($this->me_id, $car, [$obj]) : 0;

        # Seleziono la caratteristica interessata e ritorno il suo valore
        $stat_data_pg = PersonaggioStats::getPgStat($car, $this->me_id, 'personaggio_statistiche.valore');
        $stat_val = !empty($stat_data_pg['valore']) ? Filters::int($stat_data_pg['valore']) : 0;

        $stat_data = $stat_class->getStat($car);
        $stat_name = Filters::in($stat_data['nome']);

        # Ritorno un array contenente i vari valori
        return [
            'car_dice' => $stat_val,
            'car_bonus' => $total_bonus,
            'car_name' => $stat_name,
        ];
    }

    /**
     * @fn rollObj
     * @note Funzione che si occupa del calcolo dell'oggetto
     * @param int $obj
     * @param int $car
     * @return array
     * @throws Throwable
     * TODO Adattare statistiche oggetto
     */
    private function rollObj(int $obj, int $car): array
    {
        # Filtro le variabili necessarie
        $obj = Filters::int($obj);
        $car = Filters::int($car);

        # Estraggo i dati dell'oggetto
        $obj_data = DB::query("SELECT oggetto.nome,oggetto.bonus_car{$car} FROM personaggio_oggetto 
                                        LEFT JOIN oggetto ON (oggetto.id = personaggio_oggetto.oggetto) 
                                        WHERE personaggio_oggetto.id='{$obj}' LIMIT 1");

        # Filtro i dati estratti
        $obj_name = Filters::out($obj_data['nome']);
        $obj_bonus = Filters::int($obj_data["bonus_car{$car}"]);

        # Ritorno l'array necessario
        return ['nome' => $obj_name, 'val' => $obj_bonus];
    }

    /**
     * @fn rollDice
     * @note Funzione che si occupa del lancio del dado
     * @return int
     */
    private function rollDice(): int
    {
        # Lancio il dado
        return rand(1, $this->chat_dice_base);
    }

    /**
     * @fn rollCustomDice
     * @note Funzione che si occupa del lancio di dadi diversi da quelli base
     * @param int $num
     * @param int $face
     * @return int
     */
    public function rollCustomDice(int $num, int $face): int
    {
        # Lancio il dado
        $total = 0;
        $diced = 0;

        while ( $diced < $num ) {
            $total += rand(1, $face);
            $diced++;
        }

        return Filters::int($total);
    }

    /**
     * @fn saveDice
     * @note Funzione che si occupa di creare il testo e salvarlo in db
     * @param array $array
     * @return void
     * @throws Throwable
     */
    private function saveDice(array $array): void
    {
        # Filtro le variabili necessarie
        $dice = Filters::int($array['dice']);
        $abi_nome = Filters::in($array['abi_nome']);
        $abi_dice = Filters::int($array['abi_dice']);
        $car_dice = Filters::int($array['car_dice']);
        $car_name = Filters::in($array['car_name']);
        $car_bonus = Filters::int($array['car_bonus']);
        $obj_nome = Filters::in($array['obj_nome']);
        $obj_dice = Filters::int($array['obj_dice']);
        $total = 0;

        # Inizializzo il testo con una parte comune
        $html = "{$this->me} ha lanciato:";

        # Se ho lanciato un'abilita' ne aggiungo il testo
        if ( $abi_nome != '' ) {
            $html .= "{$abi_nome} {$abi_dice},";
            $total += $abi_dice;
        }

        # Aggiungo la caratteristica
        $html .= "{$car_name} {$car_dice},";
        $total += $car_dice;

        # Se ho selezionato la possibilita' di aggiungere i bonus equipaggiamento al totale della stat
        if ( $car_bonus ) {
            $html .= "Bonus Equip Stat {$car_bonus},";
            $total += $car_bonus;
        }

        # Se ho lanciato un oggetto ne aggiungo il testo
        if ( $obj_nome != '' ) {
            $html .= "{$obj_nome} {$obj_dice},";
            $total += $obj_dice;
        }

        # Aggiungo il valore del dado al testo
        if ( $this->chat_dice ) {
            $html .= "Dado: {$dice},";
            $total += $dice;
        }

        # Aggiungo il totale al dado
        $html .= " Totale : {$total}";

        # Salvo il risultato in DB
        DB::queryStmt('INSERT INTO chat(stanza,mittente,destinatario,tipo,testo)
                            VALUE(:stanza,:mittente,:destinatario,:tipo,:testo)', [
            'stanza' => $this->luogo,
            'mittente' => $this->me_id,
            'destinatario' => '',
            'tipo' => 'C',
            'testo' => $html,
        ]);
    }

    /*******  ESITI *******/

    /**
     * @fn rollEsito
     * @note Utilizzo di un esito in chat
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function rollEsito(array $post): array
    {

        if ( $this->chatAccess() ) {

            $esiti = Esiti::getInstance();
            $esiti->rollEsito($post);

            return ['response' => true, 'error' => ''];
        } else {
            $error = "Non hai accesso all'esito selezionato.";

            # Ritorno il risultato dell'invio ed eventuale messaggio di errore
            return ['response' => false, 'error' => $error];
        }

    }

}