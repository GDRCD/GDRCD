<?php


/**
 * @class Chat
 * @note Classe che gestisce la chat
 */
class Chat extends BaseClass
{

    /**
     * @note Inizializzo le variabili private della classe, per evitare di doverle richiamare ogni volta dalla sessione
     *          ed evitando possibili imbrogli
     * @var int $luogo
     */
    private
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
        $chat_esiti;

    public
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
    public function resetClass()
    {
        # Variabili di sessione
        $this->luogo = Filters::int($_SESSION['luogo']);
        $this->last_action = Filters::int($_SESSION['last_action_id']);

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

    public function controllaChat($post)
    {

        $dir = Filters::int($post['dir']);

        $luogo = DB::query("SELECT ultimo_luogo FROM personaggio WHERE nome='{$this->me}' LIMIT 1");
        $luogo_id = Filters::int($luogo['ultimo_luogo']);

        $response = ($dir == $luogo_id);

        return ['response' => $response, 'newdir' => $luogo_id];
    }

    /**
     * @fn setLast
     * @note Imposta l'ultima azione
     * @param int $id
     * @return void
     */
    public function setLast($id)
    {
        $_SESSION['last_action_id'] = $id;
        $this->last_action = $id;
    }

    /**
     * @fn assignExp
     * @note Assegna l'esperienza indicata al personaggio che ha inviato l'azione
     * @param string $tipo
     * @return void
     * # TODO globalizzare i valori
     */
    private function assignExp($azione)
    {
        # Filtro i valori passati
        $tipo = Filters::in($azione['tipo']);
        $testo = Filters::in($azione['testo']);

        # Calcolo la lunghezza del testo
        $lunghezza_testo = strlen($testo);

        # Controllo se la chat e' privata
        $luogo_pvt = DB::query("SELECT privata FROM mappa WHERE id='{$this->luogo}' LIMIT 1")['privata'];

        # Se e' privata e l'assegnazione privata e' attivo, oppure non e' privata
        if (($luogo_pvt && $this->chat_pvt_exp) || (!$luogo_pvt)) {

            # Se la lunghezza del testo supera il minimo caratteri neccesari all'assegnazione exp
            if ($lunghezza_testo >= $this->chat_exp_min) {

                # In base al tipo scelgo quanta exp assegnare
                switch ($tipo) {
                    case 'A':
                    case 'P':
                        $exp = $this->chat_exp_azione;
                        break;
                    case 'M':
                        $exp = $this->chat_exp_master;
                        break;
                    default:
                        $exp = 0;
                        break;
                }

                # Se per quel tipo e' segnato un valore di exp, lo assegno, altrimenti no
                if ($exp > 0) {
                    DB::query("UPDATE personaggio SET esperienza=esperienza+'{$exp}' WHERE nome='{$this->me}' LIMIT 1");
                }
            }
        }
    }

    /**
     * @fn audioActivated
     * @note Controlla se nella scheda pg l'audio e' segnato attivato o meno
     * @return bool
     */
    public function audioActivated()
    {
        return DB::query("SELECT blocca_media FROM personaggio WHERE nome='{$this->me}' LIMIT 1")['blocca_media'];
    }

    /**
     * @fn chatAccess
     * @note Si occupa di controllare che la chat sia accessibile (non pvt o pvt accessibile dal pg)
     * @return bool
     */
    public function chatAccess()
    {
        # Inizializzo le variabili necessarie
        $resp = false;

        # Filtro i dati passati
        $id = Filters::int($this->luogo);

        # Estraggo i dati della chat
        $data = DB::query("SELECT privata,proprietario,invitati,scadenza FROM mappa WHERE id='{$id}' LIMIT 1");

        # Filtro i valori estratti
        $privata = Filters::int($data['privata']);
        $proprietario = Filters::out($data['proprietario']);
        $invitati = Filters::out($data['invitati']);
        $scadenza = Filters::out($data['scadenza']);

        # Se non e' privata, allora di default e' accessibile
        if (!$privata) {
            $resp = true;
        } # Se e' privata
        else {

            # Se faccio parte degli invitat o sono il proprietario  e la stanza non e' scaduta
            if ($this->pvtInvited($proprietario, $invitati) && $this->pvtClosed($scadenza)) {

                # Allora e' accessibile
                $resp = true;
            }
        }

        # Ritorno il responso del controllo
        return $resp;
    }

    /**
     * @fn pvtInvited
     * @note Controlla se si fa parte degli invitati o se si e' il proprietario
     * @param string $proprietario
     * @param string $invitati
     * @return bool
     */
    private function pvtInvited($proprietario, $invitati)
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
     * @fn pvtClosed
     * @note Controlla se la chat e' scaduta
     * @param string $scadenza
     * @return bool
     */
    private function pvtClosed($scadenza)
    {
        # Filtro i dati passati
        $scadenza = Filters::out($scadenza);

        # Estraggo la data attuale
        $now = date('Y-m-d H:i:s');

        # Controllo se la chat non e' ancora scaduta
        return ($now < $scadenza);
    }

    /**** STAMP AZIONE ****/

    /**
     * @fn printChat
     * @note Stampi le azioni in chat, funzione pubblica da richiamare per lo stamp
     * @return string
     */
    public function printChat()
    {
        if ($this->chatAccess()) {
            # Inizializzo le variabili necessarie
            $html = '';

            # Estraggo le azioni della chat
            $azioni = $this->getActions();

            # Per ogni azione creo il suo html in base al tipo
            foreach ($azioni as $azione) {
                $html .= $this->Filter($azione);
            }

            # Ritorno l'html creato
            return $html;
        }
    }

    /**
     * @fn getActions
     * @note Estrai le azioni in chat che devono essere visualizzate
     * @return mixed
     */
    private function getActions()
    {

        # Se l'ultima azione non e' 0 e quindi non sono appena entrato in chat
        $extra_query = ($this->last_action > 0) ? " AND chat.id > '{$this->last_action}' " : '';

        # Estraggo le azioni n base alle condizioni indicate
        return DB::query("SELECT personaggio.nome,personaggio.url_img_chat,personaggio.sesso,chat.*
                                    FROM chat 
                                    LEFT JOIN personaggio
                                    ON (personaggio.nome = chat.mittente)
                                    WHERE chat.stanza ='{$this->luogo}' 
                                      AND chat.ora > DATE_SUB(NOW(),INTERVAL {$this->chat_time} HOUR) {$extra_query} 
                                      ORDER BY id", 'result');
    }

    /**
     * @fn Filter
     * @ntoe Filtra il tipo dell'azione passata per decidere che html stampare
     * @param array $azione
     * @return string
     */
    private function Filter($azione)
    {
        # Filtro i dati passati
        $tipo = Filters::out($azione['tipo']);
        $id = Filters::out($azione['id']);

        # Inizio l'html con una classe comune ed una di tipologia, uguale per tutte
        $html = "<div class='singola_azione chat_row_{$tipo}'>";

        # Creo l'html in base al tipo indicato
        switch ($tipo) {
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
            case 'MOD':
                $html .= $this->htmlMaster($azione);
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

        # Ritorno l'html creato
        return $html;
    }

    /**
     * @fn formattedText
     * @note Formatta il testo con le giuste funzioni di gdrcd
     * @param string $testo
     * @return string
     */
    private function formattedText($testo)
    {
        # Evidenzio il mio nome e cambio il testo tra le variabili, ritorno il risultato
        return gdrcd_chatme($this->me, gdrcd_chatcolor(Filters::out($testo)));
    }

    /**
     * @fn getIcons
     * @note Funzione che si occupa dell'estrazione delle icone chat
     * @param string $mittente
     * @return string
     */
    private function getIcons($mittente)
    {
        # Filtro il mittente passato
        $mittente = Filters::in($mittente);

        # Ritorno le sue icone estratte
        return DB::query("SELECT razza.icon,razza.nome_razza,personaggio.sesso
                                FROM personaggio 
                                    
                                    LEFT JOIN razza 
                                    ON (razza.id_razza = personaggio.id_razza)
                                    
                                    WHERE personaggio.nome = '{$mittente}'
                                    ");
    }

    /***** TIPOLOGIE HTML STAMP *****/

    /**
     * @fn htmlAzione
     * @note Stampa html per il tipo 'A'.Azione ed il tipo 'P'.Parlato (per retrocompatibilita')
     * @param array $azione
     * @return string
     *  # TODO aggiungere icone chat
     */
    private function htmlAzione($azione)
    {

        # Filtro le variabili necessarie
        $mittente = Filters::out($azione['mittente']);
        $tag = Filters::out($azione['destinatario']);
        $img_chat = Filters::out($azione['url_img_chat']);
        # Formo i testi necessari
        $ora = gdrcd_format_time($azione['ora']);
        $testo = $this->formattedText($azione['testo']);

        # Se le icone in chat sono abilitate
        if ($this->chat_icone) {

            # Estraggo le icone
            $data_imgs = $this->getIcons($mittente);
            $sesso = Filters::out($data_imgs['sesso']);
            $razza_nome = Filters::out($data_imgs['nome_razza']);
            $razza_icon = Filters::out($data_imgs['icon']);

            # Aggiungo l'html delle icone
            $html = "<span class='chat_icons'>";

            # Se l'avatar e' abilitato, aggiungo l'html dell'avatar
            if ($this->chat_avatar) {
                $html .= " <img class='chat_ico' src='{$img_chat}' class='chat_avatar'> ";
            }

            $html .= "<img class='chat_ico' src='themes/{$this->parameters['themes']['current_theme']}/imgs/races/{$razza_icon}' title='{$razza_nome}'> ";
            $html .= "<img class='chat_ico' src='imgs/icons/testamini{$sesso}.png' title='{$razza_nome}'> ";
            $html .= "</span>";
        }


        # Creo il corpo azione
        $html .= "<span class='chat_time'>{$ora}</span> ";
        $html .= "<span class='chat_name'><a href='#'>{$mittente}</a></span> ";
        $html .= "<span class='chat_tag'> [{$tag}]</span> ";
        $html .= "<span class='chat_msg'>{$testo}</span> ";
        $html .= '<br style="clear:both;" /> ';

        # Ritorno l'html creato
        return $html;
    }

    /**
     * @fn htmlSussurro
     * @note Stampa html per il tipo 'S'.Sussurro
     * @param array $azione
     * @return string
     */
    private function htmlSussurro($azione)
    {
        # Inizializzo le variabili necessarie
        $intestazione = '';
        $html = '';

        # Filtro le variabili necessarie
        $mittente = Filters::out($azione['mittente']);
        $destinatario = Filters::out($azione['destinatario']);

        # Formo i testi necessari
        $ora = gdrcd_format_time($azione['ora']);
        $testo = $this->formattedText($azione['testo']);

        # Se sono io il mittente
        if ($this->me == $mittente) {
            $intestazione = "Hai sussurrato a {$destinatario}: ";
        } # Se sono io il destinatario
        else if ($this->me == $destinatario) {
            $intestazione = "{$mittente} ti ha sussurrato: ";
        } # Se non sono nessuno di entrambi ma sono uno staffer
        else if ($this->permission > MODERATOR) {
            $intestazione = "{$mittente} ha sussurrato a {$destinatario}";
        }

        # Se l'intestazione non e' vuota, significa che posso leggere il messaggio e lo stampo
        if ($intestazione != '') {
            $html .= "<span class='chat_time'>{$ora}</span> ";
            $html .= "<span class='chat_name'>{$intestazione}</span> ";
            $html .= "<span class='chat_msg'>{$testo}</span> ";
        }

        # Ritorno l'html stampato
        return $html;
    }

    /**
     * @fn htmlSussurroGlobale
     * @note Stampa html per il tipo 'F'.Sussurro Globale
     * @param array $azione
     * @return string
     */
    private function htmlSussurroGlobale($azione)
    {
        # Filtro i dati necessari
        $mittente = Filters::out($azione['mittente']);
        $ora = gdrcd_format_time($azione['ora']);

        # Formo i testi necessari
        $testo = $this->formattedText($azione['testo']);

        # Creo il corpo azione
        $html = "<span class='chat_time'>{$ora}</span> ";
        $html .= "<span class='chat_name'>{$mittente} sussurra a tutti: </span> ";
        $html .= "<span class='chat_msg'>{$testo}</span> ";

        # Ritorno l'html dell'azione
        return $html;
    }

    /**
     * @fn htmlPNG
     * @note Stampa html per il tipo 'N'.PNG
     * @param array $azione
     * @return string
     */
    private function htmlPNG($azione)
    {
        # Filtro i dati necessari
        $destinatario = Filters::out($azione['destinatario']);

        # Formo i testi necessari
        $ora = gdrcd_format_time($azione['ora']);
        $testo = $this->formattedText($azione['testo']);

        # Creo il corpo azione
        $html = "<span class='chat_time'>{$ora}</span> ";
        $html .= "<span class='chat_name'>{$destinatario}</span> ";
        $html .= "<span class='chat_msg'>{$testo}</span> ";

        # Ritorno l'html dell'azione
        return $html;
    }

    /**
     * @fn htmlMaster
     * @note Stampa html per il tipo 'M'.Master
     * @param array $azione
     * @return string
     */
    private function htmlMaster($azione)
    {
        # Formo i testi necessari
        $ora = gdrcd_format_time($azione['ora']);
        $testo = $this->formattedText($azione['testo']);

        # Creo il corpo azione
        $html = "<span class='chat_time'>{$ora}</span> ";
        $html .= "<span class='chat_master'>{$testo}</span>";

        # Ritorno l'html dell'azione
        return $html;
    }

    /**
     * @fn htmlOnlyText
     * @note Stampa html per le tipologie che necessitano solo di ora e messaggio (Dado,Oggetto,Caccia/Invita)
     * @param array $azione
     * @return string
     */
    private function htmlOnlyText($azione)
    {
        # Formo i testi necessari
        $ora = gdrcd_format_time($azione['ora']);
        $testo = $this->formattedText($azione['testo']);

        # Creo il corpo azione
        $html = "<span class='chat_time'>{$ora}</span> ";
        $html .= "<span class='chat_msg'>{$testo}</span> ";

        # Ritorno l'html dell'azione
        return $html;
    }

    /**
     * @fn htmlImage
     * @note Stampa il testo passato come immagine
     * @param array $azione
     * @return string
     */
    private function htmlImage($azione)
    {
        # Formo i testi necessari
        $ora = gdrcd_format_time($azione['ora']);
        $url = Filters::url($azione['testo']);

        # Creo il corpo azione
        $html = "<span class='chat_time'>{$ora}</span> ";
        $html .= "<span class='chat_msg'><img src='{$url}'></span> ";

        # Ritorno l'html dell'azione
        return $html;
    }


    /******* INVIO AZIONE *******/

    /**
     * @fn send
     * @note Funzione contenitore per smistamento tipi di invio in chat
     * @param array $post
     * @return array
     */
    public function send($post)
    {
        if ($this->chatAccess()) {
            # Inizializzo le variabili necessarie
            $resp = [];

            # Filtro i dati necessari
            $tipo = Filters::in($post['tipo']);

            # In base al tipo mando l'azione con diversi permessi
            switch ($tipo) {
                case 'A':
                case 'P':
                case 'F':
                    $resp = $this->sendAction($post, USER);
                    break;
                case 'N':
                case 'M':
                    $resp = $this->sendAction($post, GAMEMASTER);
                    break;
                case 'MOD':
                    $resp = $this->sendAction($post, MODERATOR);
                    break;
                case 'S':
                    $resp = $this->sendSussurro($post);
                    break;
            }

            # Se l'exp e' attiva assegno l'exp
            if ($this->chat_exp) {
                $this->assignExp($post);
            }

            # Ritorno il risultato dell'invio ed eventuale messaggio di errore
            return $resp;
        }
    }

    /**
     * @fn sendAction
     * @note Manda le azioni generiche che non hanno il permesso come unico controllo
     * @param array $post
     * @param int $permission
     * @return array
     */
    private function sendAction($post, $permission)
    {
        # Filtro le variabili necessarie
        $permission = Filters::int($permission);

        # Se i permessi sono superiori a quelli necessari
        if ($this->permission >= $permission) {

            # Salvo l'azione in db
            $this->saveAction($post);

            # Setto il responso riuscito
            $response = ['response' => true, 'error' => ''];

        } # Se non ho i permessi per quel tipo di azione
        else {

            # Setto il responso come fallito
            $response = [
                'response' => false,
                'error' => 'Non hai i permessi per questo tipo di azione.'
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
     */
    private function sendSussurro($post)
    {
        # Filtro le variabili necessarie
        $tag = Filters::in($post['tag']);
        $luogo = $this->luogo;

        # Controllo che il personaggio a cui sussurro sia nella mia stessa chat
        $count = DB::query("SELECT count(nome) AS total FROM personaggio WHERE nome='{$tag}' AND ultimo_luogo='{$luogo}' LIMIT 1");

        # Se e' nella mia stessa chat
        if (Filters::int($count['total']) > 0) {

            # Salvo l'azione in DB
            $this->saveAction($post);

            # Setto il responso riuscito
            $response = ['response' => true, 'error' => ''];

        } # Se il personaggio non e' presente in chat, setto il responso come fallito
        else {
            $response = [
                'response' => false,
                'error' => 'Personaggio non presente in chat.'
            ];
        }

        # Ritorno il responso
        return $response;
    }

    /**
     * @fn saveAction
     * @note Funzione che si occupa del salvataggio dell'azione in DB
     * @param array $post
     */
    private function saveAction($post)
    {
        # Filtro le variabili necessarie
        $tag = Filters::in($post['tag']);
        $tipo = Filters::in($post['tipo']);
        $testo = Filters::in($post['testo']);

        # Salvo l'azione in DB
        DB::query("INSERT INTO chat(stanza,imgs,mittente,destinatario,tipo,testo)
                              VALUE('{$this->luogo}','test','{$this->me}','{$tag}','{$tipo}','{$testo}')");
    }


    /******** ABILITA CHAT *******/

    /**
     * @fn abilityList
     * @note Genera la lista delle abilita'
     * @return string
     */
    public function abilityList()
    {
        # Estraggo le abilita' generiche
        $ids = $this->allAbility();

        # Estraggo le abilita' di razza
        $ids = $this->raceAbility($ids);

        # Metto in ordine la lista per nome e ne creo le option per la select
        $html = $this->getList($ids);

        # Ritorno la lista formattata
        return $html;
    }

    /**
     * @fn allAbility
     * @note Estrae id e nome delle abilita' generali che non dipendono dalla razza
     * @return array
     */
    private function allAbility()
    {
        # Se devono essere estratte solo le abilita' gia' acquistate, ne aggiungo la clausola
        if ($this->chat_skill_buyed) {
            $extra_query = ' AND clgpersonaggioabilita.grado > 0 ';
        }

        # Estraggo le abilita secondo i parametri
        $abilita = DB::query("
                            SELECT abilita.nome,abilita.id_abilita,clgpersonaggioabilita.grado 
                            FROM abilita 
                            
                            LEFT JOIN clgpersonaggioabilita 
                            ON (clgpersonaggioabilita.id_abilita = abilita.id_abilita)
                            
                            WHERE abilita.id_razza = -1 {$extra_query} ORDER BY abilita.nome
                            ", 'result');

        # Per ogni abilita' aggiungo il suo id all'array globale
        foreach ($abilita as $abi) {
            $id_abilita = Filters::int($abi['id_abilita']);
            $nome_abilita = Filters::out($abi['nome']);

            $ids[$id_abilita] = $nome_abilita;
        }

        # Ritorno l'insieme di id
        return $ids;
    }

    /**
     * @fn raceAbility
     * @note aggiunge le abilita' relative alla razza
     * @param array $ids
     * @return array
     */
    private function raceAbility($ids)
    {
        # Estaggo la razza del pg
        $race = DB::query("SELECT id_razza FROM personaggio WHERE nome='{$this->me}' LIMIT 1")['id_razza'];

        # Se devono essere estratte solo le abilita' gia' acquistate, ne aggiungo la clausola
        if ($this->chat_skill_buyed) {
            $extra_query = ' AND clgpersonaggioabilita.grado > 0 ';
        }

        # Estraggo le abilita secondo i parametri
        $abilita = DB::query("
                            SELECT abilita.nome,abilita.id_abilita,clgpersonaggioabilita.grado 
                            FROM abilita 
                            
                            LEFT JOIN clgpersonaggioabilita 
                            ON (clgpersonaggioabilita.id_abilita = abilita.id_abilita)
                            
                            WHERE abilita.id_razza = {$race} {$extra_query} ORDER BY abilita.nome
                            ", 'result');

        # Per ogni abilita' aggiungo il suo id all'array globale
        foreach ($abilita as $abi) {
            $id_abilita = Filters::int($abi['id_abilita']);
            $nome_abilita = Filters::out($abi['nome']);

            $ids[$id_abilita] = $nome_abilita;
        }

        # Ritorno l'insieme di id
        return $ids;
    }

    /**
     * @fn getList
     * @note Trasforma le coppie id/abilita' nelle option necessarie
     * @param array $ids
     * @return string
     */
    private function getList($ids)
    {
        # Inizializzo le variabili necessarie
        $html = '';

        # Riordino gli id in base al nome
        asort($ids, SORT_ASC);

        # Per ogni id creo una option per la select
        foreach ($ids as $index => $value) {
            $id = Filters::int($index);
            $nome = Filters::out($value);

            $html .= "<option value='{$id}'>{$nome}</option>";
        }

        # Ritorno le option per la select
        return $html;
    }

    /********** LISTE ********/

    /**
     * @fn objectsList
     * @note Crea la lista di oggetti utilizzabili in chat
     * @return string
     */
    public function objectsList()
    {

        # Se devono essere estratti solo gli oggetti equipaggiati, ne aggiungo la clausola
        if ($this->chat_equip_equipped) {
            $extra_query = ' AND clgpersonaggiooggetto.posizione != 0 AND oggetto.ubicabile = 1 ';
        }

        # Estraggo gli oggetti secondo i parametri
        $objects = DB::query("SELECT clgpersonaggiooggetto.id,oggetto.nome
                                        FROM clgpersonaggiooggetto 
                                        LEFT JOIN oggetto 
                                        ON (clgpersonaggiooggetto.id_oggetto = oggetto.id_oggetto)
                                        
                                        WHERE clgpersonaggiooggetto.nome ='{$this->me}' {$extra_query}
                                        ", 'result');

        # Per ogni oggetto creo una option per la select
        foreach ($objects as $object) {
            $id = Filters::int($object['id']);
            $nome = Filters::out($object['nome']);

            $html .= "<option value='{$id}'>{$nome}</option>";
        }

        # Ritorno le option per la select
        return $html;
    }

    /**
     * @fn chatList
     * @note Crea la lista delle chat
     * @return string
     */
    public function chatList()
    {
        $html = '';

        # Estraggo gli oggetti secondo i parametri
        $chats = DB::query("SELECT * FROM mappa WHERE privata = 0 ORDER BY nome", 'result');

        # Per ogni oggetto creo una option per la select
        foreach ($chats as $chat) {
            $id = Filters::int($chat['id']);
            $nome = Filters::out($chat['nome']);

            $html .= "<option value='{$id}'>{$nome}</option>";
        }

        # Ritorno le option per la select
        return $html;
    }


    /**** LANCIO DADI ****/

    /**
     * @fn roll
     * @note Funzione contenitore del lancio dadi/abi/stat
     * @param array $post
     * @return array
     */
    public function roll($post)
    {
        if ($this->chatAccess()) {
            # Inizializzo le variabili necessarie
            $abi_nome = '';
            $abi_dice = '';
            $car_dice = '';
            $car_bonus = '';
            $obj_nome = '';
            $obj_dice = '';

            # Filtro le variabili necessarie
            $abi = Filters::int($post['abilita']);
            $car = Filters::in($post['caratteristica']);
            $obj = Filters::int($post['oggetto']);

            # Se ho selezionato l'abilita'
            if ($abi != 0) {

                # Estraggo i dati dell'abilita' scelta
                $abi_roll = $this->rollAbility($abi);

                # Filtro i dati ricevuti
                $abi_dice = Filters::int($abi_roll['abi_dice']);
                $abi_nome = Filters::in($abi_roll['nome']);

                # Se non ho selezionato nessuna caratteristica utilizzo quella base dell'abilita'
                if ($car === '') {
                    # Imposto la stat dell'abilita' come caratteristica richiesta in caso di utilizzo oggetti
                    $car = Filters::int($abi_roll['car']);
                }
            }


            # Se ho selezionato una caratteristica (abbinata o meno ad un'abilita')
            if ($car !== '') {

                # Estraggo i dati riguardo la statistica
                $car_roll = $this->rollCar($car, $obj);

                # Setto il valore della caratteristica
                $car_dice = Filters::int($car_roll['car_dice']);

                # Se devo aggiungere anche il valore dell'equipaggiamento alla stat
                if ($this->chat_equip_bonus) {

                    # Valorizzo il campo bonus equip stat per la stampa del testo
                    $car_bonus = Filters::int($car_roll['car_bonus']);
                }
            }

            # Se ho selezionato l'utilizzo di un oggetto e di una tra abilita' o stat
            if (($obj != 0) && ($car !== '')) {

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
                'car' => $car,
                'car_dice' => $car_dice,
                'obj_nome' => $obj_nome,
                'obj_dice' => $obj_dice,
                'car_bonus' => $car_bonus
            ];

            # Formo il testo e lo salvo in database
            $this->saveDice($data);

            # Ritorno il responso positivo
            return ['response' => true, 'error' => ''];
        }
    }

    /**
     * @fn rollAbility
     * @note Funzione che si occupa del calcolo dell'abilita'
     * @param int $id
     * @return array
     */
    public function rollAbility($id)
    {
        # Filtro le variabili necessarie
        $id = Filters::int($id);

        # Estraggo i dati relativi l'abilita'
        $abi_data = DB::query("SELECT clgpersonaggioabilita.grado, abilita.car,abilita.nome
                                    FROM abilita 
    
                                    LEFT JOIN clgpersonaggioabilita
                                    ON (clgpersonaggioabilita.nome='{$this->me}' AND clgpersonaggioabilita.id_abilita='{$id}')

                                    WHERE abilita.id_abilita ='{$id}' LIMIT 1 ");

        # Filtro i dati necessari
        $car = Filters::int($abi_data['car']);
        $abi_dice = Filters::int($abi_data['abi_dice']);
        $nome = Filters::in($abi_data['nome']);

        # Ritorno i dati necessari
        return ['nome' => $nome, 'abi_dice' => $abi_dice, 'car' => $car];
    }

    /**
     * @fn rollCar
     * @note Funzione che si occupa del calcolo della statistica
     * @param int $car
     * @param int $obj
     * @return array
     */
    private function rollCar($car, $obj)
    {
        # Filtro i dati passati
        $car = Filters::int($car);
        $obj = Filters::int($obj);

        # Inizializzo il totale
        $total_bonus = 0;

        # Se nel conto caratteristica va aggiunto anche il totale degli oggetti equipaggiati
        if ($this->chat_equip_bonus) {

            # Estraggo i bonus di tutti gli oggetti equipaggiati
            $objects = DB::query("SELECT oggetto.bonus_car{$car},clgpersonaggiooggetto.id
                                        FROM clgpersonaggiooggetto 
                                        LEFT JOIN oggetto 
                                        ON (clgpersonaggiooggetto.id_oggetto = oggetto.id_oggetto)
                                         
                                        WHERE clgpersonaggiooggetto.nome ='{$this->me}' AND clgpersonaggiooggetto.id != '{$obj}' 
                                        AND clgpersonaggiooggetto.posizione > 0 AND oggetto.ubicabile = 1

                                        ", 'result');

            # Per ogni oggetto equipaggiato
            foreach ($objects as $object) {

                # Aggiungo il suo bonus al totale
                $total_bonus += Filters::int($object["bonus_car{$car}"]);
            }
        }

        # Seleziono la caratteristica interessata e ritorno il suo valore
        $query = DB::query("SELECT personaggio.car{$car} FROM personaggio WHERE nome='{$this->me}' LIMIT 1");

        # Ritorno un array contenente i vari valori
        return ['car_dice' => Filters::int($query["car{$car}"]), 'car_bonus' => $total_bonus];
    }

    /**
     * @fn rollObj
     * @note Funzione che si occupa del calcolo dell'oggetto
     * @param int $obj
     * @param int $car
     * @return array
     */
    private function rollObj($obj, $car)
    {
        # Filtro le variabili necessarie
        $obj = Filters::int($obj);
        $car = Filters::int($car);

        # Estraggo i dati dell'oggetto
        $obj_data = DB::query("SELECT oggetto.nome,oggetto.bonus_car{$car} FROM clgpersonaggiooggetto 
                                        LEFT JOIN oggetto ON (oggetto.id_oggetto = clgpersonaggiooggetto.id_oggetto) 
                                        WHERE clgpersonaggiooggetto.id='{$obj}' LIMIT 1");

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
    private function rollDice()
    {
        # Lancio il dado
        return rand(1, $this->chat_dice_base);
    }

    /**
     * @fn rollCustomDice
     * @note Funzione che si occupa del lancio di dadi diversi da quelli base
     * @return int
     */
    public function rollCustomDice($num, $face)
    {
        # Lancio il dado
        $total = 0;
        $diced = 0;

        while ($diced < $num) {
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
     */
    private function saveDice($array)
    {
        # Filtro le variabili necessarie
        $dice = Filters::in($array['dice']);
        $abi_nome = Filters::in($array['abi_nome']);
        $abi_dice = Filters::in($array['abi_dice']);
        $car = Filters::int($array['car']);
        $car_dice = Filters::in($array['car_dice']);
        $car_bonus = Filters::in($array['car_bonus']);
        $obj_nome = Filters::in($array['obj_nome']);
        $obj_dice = Filters::in($array['obj_dice']);
        $total = 0;

        # Inizializzo il testo con una parte comune
        $html = "{$this->me} ha lanciato:";

        # Se ho lanciato un'abilita' ne aggiungo il testo
        if ($abi_nome != '') {
            $html .= "{$abi_nome} {$abi_dice},";
            $total += $abi_dice;
        }

        # Se ho lanciato una caratteristica ne aggiungo il testo
        if ($car_dice != '') {
            $html .= "{$this->parameters['names']['stats']['car'.$car]} {$car_dice},";
            $total += $car_dice;
        }

        # Se ho selezionato la possibilita' di aggiungere i bonus equipaggiamento al totale della stat
        if ($car_bonus !== '') {
            $html .= "Bonus Equip Stat {$car_bonus},";
            $total += $car_bonus;
        }

        # Se ho lanciato un oggetto ne aggiungo il testo
        if ($obj_nome != '') {
            $html .= "{$obj_nome} {$obj_dice},";
            $total += $obj_dice;
        }

        # Aggiungo il valore del dado al testo
        if ($this->chat_dice) {
            $html .= "Dado: {$dice},";
            $total += $dice;
        }

        # Aggiungo il totale al dado
        $html .= " Totale : {$total}";

        # Salvo il risultato in DB
        DB::query("INSERT INTO chat(stanza,mittente,destinatario,tipo,testo)
                            VALUE('{$this->luogo}','{$this->me}','','C','{$html}')");
    }

    /*******  ESITI *******/

    /**
     * @fn rollEsito
     * @note Utilizzo di un esito in chat
     * @param array $post
     * @return array
     */
    public function rollEsito(array $post): array
    {

        if ($this->chatAccess()) {


            $esiti = Esiti::getInstance();
            $esiti->rollEsito($post);

            return ['response' => true, 'error' => ''];
        } else {
            $error = 'Non hai accesso all\'esito selezionato.';

            # Ritorno il risultato dell'invio ed eventuale messaggio di errore
            return ['response' => false, 'error' => $error];
        }

    }


}