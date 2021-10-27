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
        $last_action;



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
        $this->luogo = gdrcd_filter('num', $_SESSION['luogo']);
        $this->last_action = gdrcd_filter('num', $_SESSION['last_action_id']);
    }

    public function controllaChat($post)
    {

        $dir = gdrcd_filter('num', $post['dir']);

        $luogo = gdrcd_query("SELECT ultimo_luogo FROM personaggio WHERE nome='{$this->me}' LIMIT 1");
        $luogo_id = gdrcd_filter('num', $luogo['ultimo_luogo']);

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
        $tipo = gdrcd_filter('in', $azione['tipo']);
        $testo = gdrcd_filter('in', $azione['testo']);

        # Calcolo la lunghezza del testo
        $lunghezza_testo = strlen($testo);

        # Controllo se la chat e' privata
        $luogo_pvt = gdrcd_query("SELECT privata FROM mappa WHERE id='{$this->luogo}' LIMIT 1")['privata'];

        # Se e' privata e l'assegnazione privata e' attivo, oppure non e' privata
        if (($luogo_pvt && CHAT_PVT_EXP) || (!$luogo_pvt)) {

            # Se la lunghezza del testo supera il minimo caratteri neccesari all'assegnazione exp
            if ($lunghezza_testo >= CHAT_EXP_MIN) {

                # In base al tipo scelgo quanta exp assegnare
                switch ($tipo) {
                    case 'A':
                    case 'P':
                        $exp = CHAT_EXP_AZIONE;
                        break;
                    case 'M':
                        $exp = CHAT_EXP_MASTER;
                        break;
                    default:
                        $exp = 0;
                        break;
                }

                # Se per quel tipo e' segnato un valore di exp, lo assegno, altrimenti no
                if ($exp > 0) {
                    gdrcd_query("UPDATE personaggio SET esperienza=esperienza+'{$exp}' WHERE nome='{$this->me}' LIMIT 1");
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
        return gdrcd_query("SELECT blocca_media FROM personaggio WHERE nome='{$this->me}' LIMIT 1")['blocca_media'];
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
        $id = gdrcd_filter('num', $this->luogo);

        # Estraggo i dati della chat
        $data = gdrcd_query("SELECT privata,proprietario,invitati,scadenza FROM mappa WHERE id='{$id}' LIMIT 1");

        # Filtro i valori estratti
        $privata = gdrcd_filter('num', $data['privata']);
        $proprietario = gdrcd_filter('out', $data['proprietario']);
        $invitati = gdrcd_filter('out', $data['invitati']);
        $scadenza = gdrcd_filter('out', $data['scadenza']);

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
        $proprietario = gdrcd_filter('out', $proprietario);
        $invitati = gdrcd_filter('out', $invitati);

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
        $scadenza = gdrcd_filter('out', $scadenza);

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
        $interval = CHAT_TIME;

        # Se l'ultima azione non e' 0 e quindi non sono appena entrato in chat
        if ($this->last_action > 0) {
            $extra_query = " AND chat.id > '{$this->last_action}' ";
        }

        # Estraggo le azioni n base alle condizioni indicate
        return gdrcd_query("SELECT personaggio.nome,personaggio.url_img_chat,personaggio.sesso,chat.*
                                    FROM chat 
                                    LEFT JOIN personaggio
                                    ON (personaggio.nome = chat.mittente)
                                    WHERE chat.stanza ='{$this->luogo}' 
                                      AND chat.ora > DATE_SUB(NOW(),INTERVAL {$interval} HOUR) {$extra_query} 
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
        $tipo = gdrcd_filter('out', $azione['tipo']);
        $id = gdrcd_filter('out', $azione['id']);

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
        return gdrcd_chatme($this->me, gdrcd_chatcolor(gdrcd_filter('out', $testo)));
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
        $mittente = gdrcd_filter('in', $mittente);

        # Ritorno le sue icone estratte
        return gdrcd_query("SELECT razza.icon,razza.nome_razza,personaggio.sesso
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
        $mittente = gdrcd_filter('out', $azione['mittente']);
        $tag = gdrcd_filter('out', $azione['destinatario']);
        $img_chat = gdrcd_filter('out', $azione['url_img_chat']);

        # Formo i testi necessari
        $ora = gdrcd_format_time($azione['ora']);
        $testo = $this->formattedText($azione['testo']);

        # Se le icone in chat sono abilitate
        if (CHAT_ICONE) {

            # Estraggo le icone
            $data_imgs = $this->getIcons($mittente);
            $sesso = gdrcd_filter('out', $data_imgs['sesso']);
            $razza_nome = gdrcd_filter('out', $data_imgs['nome_razza']);
            $razza_icon = gdrcd_filter('out', $data_imgs['icon']);

            # Aggiungo l'html delle icone
            $html = "<span class='chat_icons'>";

            # Se l'avatar e' abilitato, aggiungo l'html dell'avatar
            if (CHAT_AVATAR) {
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
        $mittente = gdrcd_filter('out', $azione['mittente']);
        $destinatario = gdrcd_filter('out', $azione['destinatario']);

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
        $mittente = gdrcd_filter('out', $azione['mittente']);
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
        $destinatario = gdrcd_filter('out', $azione['destinatario']);

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
        $testo = gdrcd_filter('fullurl', $azione['testo']);

        # Creo il corpo azione
        $html = "<span class='chat_time'>{$ora}</span> ";
        $html .= "<span class='chat_msg'><img src='{$testo}'></span> ";

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
            $tipo = gdrcd_filter('in', $post['tipo']);

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
            if (CHAT_EXP) {
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
        $permission = gdrcd_filter('num', $permission);

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
        $tag = gdrcd_filter('in', $post['tag']);
        $luogo = $this->luogo;

        # Controllo che il personaggio a cui sussurro sia nella mia stessa chat
        $count = gdrcd_query("SELECT count(nome) AS total FROM personaggio WHERE nome='{$tag}' AND ultimo_luogo='{$luogo}' LIMIT 1");

        # Se e' nella mia stessa chat
        if (gdrcd_filter('num', $count['total']) > 0) {

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
        $tag = gdrcd_filter('in', $post['tag']);
        $tipo = gdrcd_filter('in', $post['tipo']);
        $testo = gdrcd_filter('in', $post['testo']);

        # Salvo l'azione in DB
        gdrcd_query("INSERT INTO chat(stanza,imgs,mittente,destinatario,tipo,testo)
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
        if (CHAT_SKILL_BUYED) {
            $extra_query = ' AND clgpersonaggioabilita.grado > 0 ';
        }

        # Estraggo le abilita secondo i parametri
        $abilita = gdrcd_query("
                            SELECT abilita.nome,abilita.id_abilita,clgpersonaggioabilita.grado 
                            FROM abilita 
                            
                            LEFT JOIN clgpersonaggioabilita 
                            ON (clgpersonaggioabilita.id_abilita = abilita.id_abilita)
                            
                            WHERE abilita.id_razza = -1 {$extra_query} ORDER BY abilita.nome
                            ", 'result');

        # Per ogni abilita' aggiungo il suo id all'array globale
        foreach ($abilita as $abi) {
            $id_abilita = gdrcd_filter('num', $abi['id_abilita']);
            $nome_abilita = gdrcd_filter('out', $abi['nome']);

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
        $race = gdrcd_query("SELECT id_razza FROM personaggio WHERE nome='{$this->me}' LIMIT 1")['id_razza'];

        # Se devono essere estratte solo le abilita' gia' acquistate, ne aggiungo la clausola
        if (CHAT_SKILL_BUYED) {
            $extra_query = ' AND clgpersonaggioabilita.grado > 0 ';
        }

        # Estraggo le abilita secondo i parametri
        $abilita = gdrcd_query("
                            SELECT abilita.nome,abilita.id_abilita,clgpersonaggioabilita.grado 
                            FROM abilita 
                            
                            LEFT JOIN clgpersonaggioabilita 
                            ON (clgpersonaggioabilita.id_abilita = abilita.id_abilita)
                            
                            WHERE abilita.id_razza = {$race} {$extra_query} ORDER BY abilita.nome
                            ", 'result');

        # Per ogni abilita' aggiungo il suo id all'array globale
        foreach ($abilita as $abi) {
            $id_abilita = gdrcd_filter('num', $abi['id_abilita']);
            $nome_abilita = gdrcd_filter('out', $abi['nome']);

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
            $id = gdrcd_filter('num', $index);
            $nome = gdrcd_filter('out', $value);

            $html .= "<option value='{$id}'>{$nome}</option>";
        }

        # Ritorno le option per la select
        return $html;
    }

    /********** OGGETTI ********/

    /**
     * @fn objectsList
     * @note Crea la lista di oggetti utilizzabili in chat
     * @return string
     */
    public function objectsList()
    {

        # Se devono essere estratti solo gli oggetti equipaggiati, ne aggiungo la clausola
        if (CHAT_EQUIP_EQUIPPED) {
            $extra_query = ' AND clgpersonaggiooggetto.posizione != 0 AND oggetto.ubicabile = 1 ';
        }

        # Estraggo gli oggetti secondo i parametri
        $objects = gdrcd_query("SELECT clgpersonaggiooggetto.id,oggetto.nome
                                        FROM clgpersonaggiooggetto 
                                        LEFT JOIN oggetto 
                                        ON (clgpersonaggiooggetto.id_oggetto = oggetto.id_oggetto)
                                        
                                        WHERE clgpersonaggiooggetto.nome ='{$this->me}' {$extra_query}
                                        ", 'result');

        # Per ogni oggetto creo una option per la select
        foreach ($objects as $object) {
            $id = gdrcd_filter('num', $object['id']);
            $nome = gdrcd_filter('out', $object['nome']);

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
            $abi = gdrcd_filter('num', $post['abilita']);
            $car = gdrcd_filter('in', $post['caratteristica']);
            $obj = gdrcd_filter('num', $post['oggetto']);

            # Se ho selezionato l'abilita'
            if ($abi != 0) {

                # Estraggo i dati dell'abilita' scelta
                $abi_roll = $this->rollAbility($abi);

                # Filtro i dati ricevuti
                $abi_dice = gdrcd_filter('num', $abi_roll['abi_dice']);
                $abi_nome = gdrcd_filter('in', $abi_roll['nome']);

                # Se non ho selezionato nessuna caratteristica utilizzo quella base dell'abilita'
                if ($car === '') {
                    # Imposto la stat dell'abilita' come caratteristica richiesta in caso di utilizzo oggetti
                    $car = gdrcd_filter('num', $abi_roll['car']);
                }
            }


            # Se ho selezionato una caratteristica (abbinata o meno ad un'abilita')
            if ($car !== '') {

                # Estraggo i dati riguardo la statistica
                $car_roll = $this->rollCar($car, $obj);

                # Setto il valore della caratteristica
                $car_dice = gdrcd_filter('num', $car_roll['car_dice']);

                # Se devo aggiungere anche il valore dell'equipaggiamento alla stat
                if (CHAT_EQUIP_BONUS) {

                    # Valorizzo il campo bonus equip stat per la stampa del testo
                    $car_bonus = gdrcd_filter('num', $car_roll['car_bonus']);
                }
            }

            # Se ho selezionato l'utilizzo di un oggetto e di una tra abilita' o stat
            if (($obj != 0) && ($car !== '')) {

                # Estraggo i dati dell'oggetto
                $obj_roll = $this->rollObj($obj, $car);

                # Filtro e setto i dati necessari all'aggiunta del valore al lancio
                $obj_nome = gdrcd_filter('out', $obj_roll['nome']);
                $obj_dice = gdrcd_filter('num', $obj_roll['val']);
            }

            # Lancio il dado
            $dice = (CHAT_DICE) ? $this->rollDice() : 0;

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
    private function rollAbility($id)
    {
        # Filtro le variabili necessarie
        $id = gdrcd_filter('num', $id);

        # Estraggo i dati relativi l'abilita'
        $abi_data = gdrcd_query("SELECT clgpersonaggioabilita.grado, abilita.car,abilita.nome
                                    FROM abilita 
    
                                    LEFT JOIN clgpersonaggioabilita
                                    ON (clgpersonaggioabilita.nome='{$this->me}' AND clgpersonaggioabilita.id_abilita='{$id}')

                                    WHERE abilita.id_abilita ='{$id}' LIMIT 1 ");

        # Filtro i dati necessari
        $car = gdrcd_filter('num', $abi_data['car']);
        $abi_dice = gdrcd_filter('num', $abi_data['abi_dice']);
        $nome = gdrcd_filter('in', $abi_data['nome']);

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
        $car = gdrcd_filter('num', $car);
        $obj = gdrcd_filter('num', $obj);

        # Inizializzo il totale
        $total_bonus = 0;

        # Se nel conto caratteristica va aggiunto anche il totale degli oggetti equipaggiati
        if (CHAT_EQUIP_BONUS) {

            # Estraggo i bonus di tutti gli oggetti equipaggiati
            $objects = gdrcd_query("SELECT oggetto.bonus_car{$car},clgpersonaggiooggetto.id
                                        FROM clgpersonaggiooggetto 
                                        LEFT JOIN oggetto 
                                        ON (clgpersonaggiooggetto.id_oggetto = oggetto.id_oggetto)
                                         
                                        WHERE clgpersonaggiooggetto.nome ='{$this->me}' AND clgpersonaggiooggetto.id != '{$obj}' 
                                        AND clgpersonaggiooggetto.posizione > 0 AND oggetto.ubicabile = 1

                                        ", 'result');

            # Per ogni oggetto equipaggiato
            foreach ($objects as $object) {

                # Aggiungo il suo bonus al totale
                $total_bonus += gdrcd_filter('num', $object["bonus_car{$car}"]);
            }
        }

        # Seleziono la caratteristica interessata e ritorno il suo valore
        $query = gdrcd_query("SELECT personaggio.car{$car} FROM personaggio WHERE nome='{$this->me}' LIMIT 1");

        # Ritorno un array contenente i vari valori
        return ['car_dice' => gdrcd_filter('num', $query["car{$car}"]), 'car_bonus' => $total_bonus];
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
        $obj = gdrcd_filter('num', $obj);
        $car = gdrcd_filter('num', $car);

        # Estraggo i dati dell'oggetto
        $obj_data = gdrcd_query("SELECT oggetto.nome,oggetto.bonus_car{$car} FROM clgpersonaggiooggetto 
                                        LEFT JOIN oggetto ON (oggetto.id_oggetto = clgpersonaggiooggetto.id_oggetto) 
                                        WHERE clgpersonaggiooggetto.id='{$obj}' LIMIT 1");

        # Filtro i dati estratti
        $obj_name = gdrcd_filter('out', $obj_data['nome']);
        $obj_bonus = gdrcd_filter('num', $obj_data["bonus_car{$car}"]);

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
        # Estraggo il dado da lanciare, lo lancio e ritorno il risultato
        $dice = CHAT_DICE_BASE;
        return rand(1, $dice);
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
        $dice = gdrcd_filter('in', $array['dice']);
        $abi_nome = gdrcd_filter('in', $array['abi_nome']);
        $abi_dice = gdrcd_filter('in', $array['abi_dice']);
        $car = gdrcd_filter('num', $array['car']);
        $car_dice = gdrcd_filter('in', $array['car_dice']);
        $car_bonus = gdrcd_filter('in', $array['car_bonus']);
        $obj_nome = gdrcd_filter('in', $array['obj_nome']);
        $obj_dice = gdrcd_filter('in', $array['obj_dice']);
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
        if(CHAT_DICE) {
            $html .= "Dado: {$dice},";
            $total += $dice;
        }

        # Aggiungo il totale al dado
        $html .= " Totale :{$total}";

        # Salvo il risultato in DB
        gdrcd_query("INSERT INTO chat(stanza,mittente,destinatario,tipo,testo)
                            VALUE('{$this->luogo}','{$this->me}','','C','{$html}')");
    }
}