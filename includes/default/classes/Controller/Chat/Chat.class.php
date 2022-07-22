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
    protected
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
     * @fn getChatData
     * @note Estrae i dati di una chat
     * @param int $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getChatData(int $id, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM mappa WHERE id='{$id}' LIMIT 1");
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

    /**
     * @fn controllaChat
     * @note Controllo chat
     * @param $post
     * @return array
     */
    public function controllaChat($post): array
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
    public function setLast(int $id): void
    {
        $_SESSION['last_action_id'] = $id;
        $this->last_action = $id;
    }

    /**
     * @fn assignExp
     * @note Assegna l'esperienza indicata al personaggio che ha inviato l'azione
     * @param mixed $azione
     * @return void
     * # TODO globalizzare i valori
     */
    private function assignExp($azione): void
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
    public function audioActivated(): bool
    {
        return DB::query("SELECT blocca_media FROM personaggio WHERE nome='{$this->me}' LIMIT 1")['blocca_media'];
    }

    /**
     * @fn chatAccess
     * @note Si occupa di controllare che la chat sia accessibile (non pvt o pvt accessibile dal pg)
     * @return bool
     */
    public function chatAccess(): bool
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
     * @fn pvtClosed
     * @note Controlla se la chat e' scaduta
     * @param string $scadenza
     * @return bool
     */
    private function pvtClosed(string $scadenza): bool
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
    public function printChat(): string
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
        } else {
            return '';
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
    private function Filter(array $azione): string
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

        # Ritorno l'html creato
        return $html;
    }

    /**
     * @fn formattedText
     * @note Formatta il testo con le giuste funzioni di gdrcd
     * @param string $testo
     * @param string $colore_parlato
     * @return string
     */
    private function formattedText(string $testo, string $colore_parlato = ''): string
    {
        # Evidenzio il mio nome e cambio il testo tra le variabili, ritorno il risultato
        return gdrcd_chatme($this->me, gdrcd_chatcolor($testo, $colore_parlato));
    }

    /**
     * @fn getIcons
     * @note Funzione che si occupa dell'estrazione delle icone chat
     * @param string $mittente
     * @return array
     */
    private function getIcons(string $mittente): array
    {
        # Filtro il mittente passato
        $mittente = Filters::int($mittente);


        $icons = [];

        # Ritorno le sue icone estratte
        $races = DB::query("
                SELECT razza.icon,razza.nome_razza,personaggio.sesso
                FROM personaggio 
                LEFT JOIN razza ON (razza.id_razza = personaggio.id_razza)
                WHERE personaggio.id = '{$mittente}'", 'result');

        foreach ($races as $race){
            $icons[] = [
                "Title" => Filters::out($race['nome_razza']),
                "Img" => 'races/'.Filters::out($race['icon'])
            ];
        }

        if(Gruppi::getInstance()->activeGroupIconChat()){
            $roles = PersonaggioRuolo::getInstance()->getAllCharacterRolesWithRoleData($mittente);

            foreach ($roles as $role){
                $icons[] = [
                    "Title" => Filters::out($role['nome']),
                    "Img" => 'groups/'.Filters::out($role['immagine'])
                ];
            }
        }

        return $icons;
    }

    /***** TIPOLOGIE HTML STAMP *****/

    /**
     * @fn htmlAzione
     * @note Stampa html per il tipo 'A'.Azione ed il tipo 'P'.Parlato (per retrocompatibilita')
     * @param array $azione
     * @return string
     *  # TODO aggiungere icone chat
     */
    private function htmlAzione(array $azione): string
    {

        # Filtro le variabili necessarie
        $mittente = Filters::int($azione['mittente']);
        $mittente_data = Personaggio::getPgData($mittente,'nome');
        $mittente_nome = Filters::out($mittente_data['nome']);
        $tag = Filters::out($azione['destinatario']);
        $img_chat = Filters::out($azione['url_img_chat']);
        $testo = Filters::string($azione['testo']);

        # Customizzazioni
        $colore_testo_parlato = PersonaggioChatOpzioni::getInstance()->getOptionValue('action_color_talk', $this->me_id);
        $colore_parlato = Filters::out($colore_testo_parlato['valore']);

        $colore_testo_descr = PersonaggioChatOpzioni::getInstance()->getOptionValue('action_color_descr', $this->me_id);
        $colore_descr = Filters::out($colore_testo_descr['valore']);

        $testo_size = PersonaggioChatOpzioni::getInstance()->getOptionValue('action_size', $this->me_id);
        $size = Filters::out($testo_size['valore']) ? Filters::out($testo_size['valore']) . 'px' : '';

        # Formo i testi necessari
        $ora = gdrcd_format_time($azione['ora']);
        $testo = $this->formattedText($testo, $colore_parlato);

        # Se le icone in chat sono abilitate
        if ($this->chat_icone) {

            # Estraggo le icone
            $data_imgs = $this->getIcons($mittente);

            # Aggiungo l'html delle icone
            $html = "<span class='chat_icons'>";

            # Se l'avatar e' abilitato, aggiungo l'html dell'avatar
            if ($this->chat_avatar) {
                $html .= " <img class='chat_ico' src='{$img_chat}' class='chat_avatar'> ";
            }

            foreach ($data_imgs as $data_img) {
                $link = Router::getImgsDir().$data_img['Img'];
                $html .= "<img class='chat_ico' src='{$link}' title='{$data_img['Title']}'> ";
            }

            $html .= "</span>";
        }


        # Creo il corpo azione
        $html .= "<span class='chat_time'>{$ora}</span> ";
        $html .= "<span class='chat_name'><a href='#'>{$mittente_nome}</a></span> ";
        $html .= "<span class='chat_tag'> [{$tag}]</span> ";
        $html .= "<span class='chat_msg' style='color:{$colore_descr}; font-size:{$size};'>{$testo}</span> ";
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
    private function htmlSussurro(array $azione): string
    {
        # Inizializzo le variabili necessarie
        $intestazione = '';
        $html = '';

        # Filtro le variabili necessarie
        $mittente = Filters::int($azione['mittente']);
        $destinatario = Filters::int($azione['destinatario']);
        $testo = Filters::string($azione['testo']);


        $mittente_data = Personaggio::getPgData($mittente,'nome');
        $mittente_nome = Filters::out($mittente_data['nome']);

        // TODO Cambiare campo libero destinatario sussurro con select personaggi
        $destinatario_data = Personaggio::getPgData($destinatario,'nome');
        $destinatario_nome = Filters::out($destinatario_data['nome']);

        # Customizzazioni
        $colore_testo_parlato = PersonaggioChatOpzioni::getInstance()->getOptionValue('sussurro_color', $this->me_id);
        $colore_parlato = Filters::out($colore_testo_parlato['valore']);

        $testo_size = PersonaggioChatOpzioni::getInstance()->getOptionValue('sussurro_size', $this->me_id);
        $size = Filters::out($testo_size['valore']) ? Filters::out($testo_size['valore']) . 'px' : '';

        # Formo i testi necessari
        $ora = gdrcd_format_time($azione['ora']);
        $testo = $this->formattedText($testo, '');

        # Se sono io il mittente
        if ($this->me_id == $mittente) {
            $intestazione = "Hai sussurrato a {$destinatario_nome}: ";
        } # Se sono io il destinatario
        else if ($this->me_id == $destinatario) {
            $intestazione = "{$mittente_nome} ti ha sussurrato: ";
        } # Se non sono nessuno di entrambi ma sono uno staffer
        else if ($this->permission > MODERATOR) {
            $intestazione = "{$mittente_nome} ha sussurrato a {$destinatario_nome}";
        }

        # Se l'intestazione non e' vuota, significa che posso leggere il messaggio e lo stampo
        if ($intestazione != '') {
            $html .= "<span class='chat_time'>{$ora}</span> ";
            $html .= "<span class='chat_name'>{$intestazione}</span> ";
            $html .= "<span class='chat_msg' style='color:{$colore_parlato};font-size:{$size};'>{$testo}</span> ";
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
    private function htmlSussurroGlobale(array $azione): string
    {
        # Filtro i dati necessari
        $mittente = Filters::int($azione['mittente']);
        $ora = gdrcd_format_time($azione['ora']);
        $testo = Filters::string($azione['testo']);

        $mittente_data = Personaggio::getPgData($mittente,'nome');
        $mittente_nome = Filters::out($mittente_data['nome']);

        # Customizzazioni
        $colore_testo_parlato = PersonaggioChatOpzioni::getInstance()->getOptionValue('sussurro_globale_color', $this->me_id);
        $colore_parlato = Filters::out($colore_testo_parlato['valore']);

        $testo_size = PersonaggioChatOpzioni::getInstance()->getOptionValue('sussurro_globale_size', $this->me_id);
        $size = Filters::out($testo_size['valore']) ? Filters::out($testo_size['valore']) . 'px' : '';

        # Formo i testi necessari
        $testo = $this->formattedText($testo);

        # Creo il corpo azione
        $html = "<span class='chat_time'>{$ora}</span> ";
        $html .= "<span class='chat_name'>{$mittente_nome} sussurra a tutti: </span> ";
        $html .= "<span class='chat_msg' style='color:{$colore_parlato};font-size:{$size};'>{$testo}</span> ";

        # Ritorno l'html dell'azione
        return $html;
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
        $ora = gdrcd_format_time($azione['ora']);
        $testo = $this->formattedText($testo, $colore_parlato);

        # Creo il corpo azione
        $html = "<span class='chat_time'>{$ora}</span> ";
        $html .= "<span class='chat_name'>{$destinatario}</span> ";
        $html .= "<span class='chat_msg' style='color:{$colore_descr};font-size:{$size};'>{$testo}</span> ";


        # Ritorno l'html dell'azione
        return $html;
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
        $ora = gdrcd_format_time($azione['ora']);
        $testo = $this->formattedText($testo, $colore_parlato);

        # Creo il corpo azione
        $html = "<span class='chat_time'>{$ora}</span> ";
        $html .= "<span class='chat_msg' style='color:{$colore_descr};font-size:{$size};'>{$testo}</span> ";

        # Ritorno l'html dell'azione
        return $html;
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
        $ora = gdrcd_format_time($azione['ora']);
        $testo = $this->formattedText($testo);

        # Creo il corpo azione
        $html = "<span class='chat_time'>{$ora}</span> ";
        $html .= "<span class='chat_msg' style='color:{$colore_parlato};font-size:{$size};'>{$testo}</span> ";

        # Ritorno l'html dell'azione
        return $html;
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
        $ora = gdrcd_format_time($azione['ora']);
        $testo = $this->formattedText($testo);

        # Creo il corpo azione
        $html = "<span class='chat_time'>{$ora}</span> ";
        $html .= "<span class='chat_msg' style='color:{$colore_parlato};font-size:{$size};'>{$testo}</span> ";

        # Ritorno l'html dell'azione
        return $html;
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
    public function send(array $post): array
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
        } else {
            return [
                'response' => false,
                'error' => 'Permesso negato.'
            ];
        }
    }

    /**
     * @fn sendAction
     * @note Manda le azioni generiche che non hanno il permesso come unico controllo
     * @param array $post
     * @param int $permission
     * @return array
     */
    private function sendAction(array $post, int $permission): array
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
    private function sendSussurro(array $post): array
    {
        # Filtro le variabili necessarie
        $luogo = $this->luogo;
        $sussurraA = Filters::int($post['whispTo']);


        # Controllo che il personaggio a cui sussurro sia nella mia stessa chat
        $count = DB::query("SELECT count(id) AS total FROM personaggio WHERE id='{$sussurraA}' AND ultimo_luogo='{$luogo}' LIMIT 1");


        # Se e' nella mia stessa chat
        if (Filters::int($count['total']) > 0) {

            $post['tag'] = $sussurraA;

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
    private function saveAction(array $post)
    {
        # Filtro le variabili necessarie
        $tag = Filters::in($post['tag']);
        $tipo = Filters::in($post['tipo']);
        $testo = Filters::in($post['testo']);

        # Salvo l'azione in DB
        DB::query("INSERT INTO chat(stanza,imgs,mittente,destinatario,tipo,testo)
                              VALUE('{$this->luogo}','test','{$this->me_id}','{$tag}','{$tipo}','{$testo}')");
    }

    /*** TABLE HELPERS ***/

    /**
     * @fn getPgAllObjects
     * @note Estrae tutti gli oggetti di un personaggio
     * @param int $pg
     * @param bool $only_equipped
     * @param string $val
     * @return bool|int|mixed|string
     */
    public static function getPgAllObjects(int $pg, bool $only_equipped, $val = 'personaggio_oggetto.*,oggetto.*')
    {

        $pg = Filters::int($pg);

        $extra_query = ($only_equipped) ? ' AND personaggio_oggetto.indossato != 0 AND oggetto.indossabile = 1 ' : '';

        return DB::query("SELECT {$val}
                                        FROM personaggio_oggetto 
                                        LEFT JOIN oggetto 
                                        ON (personaggio_oggetto.oggetto = oggetto.id)                         
                                        WHERE personaggio_oggetto.personaggio ='{$pg}' {$extra_query}
                                        ", 'result');
    }


    /**
     * # TODO Modificare con nuovo sistema oggetti
     * @fn calcAllObjsBonus
     * @note Calcola i bonus statistiche dell'oggetto
     * @param int $pg
     * @param int $car
     * @param array $excluded
     * @return int
     */
    public static function calcAllObjsBonus(int $pg, int $car, array $excluded = []): int
    {

        //#TODO Adattare con le stat oggetto

        $extra_query = '';
        $total_bonus = 0;

        if (!empty($excluded)) {
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
        foreach ($objects as $object) {
            # Aggiungo il suo bonus al totale
            $total_bonus += Filters::int($object["bonus_car{$car}"]);
        }

        return $total_bonus;
    }

    /********** LISTE ********/

    /**
     * @fn objectsList
     * @note Crea la lista di oggetti utilizzabili in chat
     * @return string
     */
    public function objectsList(): string
    {
        $html = '';
        $obj_class = Oggetti::getInstance();

        # Estraggo gli oggetti posseduti
        $objects = $this->getPgAllObjects($this->me_id, $this->chat_equip_equipped, 'personaggio_oggetto.id,oggetto.id AS obj_id,oggetto.nome');

        # Per ogni oggetto creo una option per la select
        foreach ($objects as $object) {
            $id = Filters::int($object['id']);
            $id_obj = Filters::int($object['obj_id']);
            $nome = Filters::out($object['nome']);

            if ($obj_class->existObject($id_obj)) {
                $html .= "<option value='{$id}'>{$nome}</option>";
            }
        }

        # Ritorno le option per la select
        return $html;
    }

    /**
     * @fn chatList
     * @note Crea la lista delle chat
     * @return string
     */
    public function chatList(): string
    {
        $html = '<option value=""></option>';

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
    public function roll(array $post): array
    {
        if ($this->chatAccess()) {
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
            if ($abi != 0) {

                # Estraggo i dati dell'abilita' scelta
                $abi_roll = $this->rollAbility($abi);

                # Filtro i dati ricevuti
                $abi_dice = Filters::int($abi_roll['abi_dice']);
                $abi_nome = Filters::in($abi_roll['abi_nome']);

                # Se non ho selezionato nessuna caratteristica utilizzo quella base dell'abilita'
                if ($car == 0) {
                    # Imposto la stat dell'abilita' come caratteristica richiesta in caso di utilizzo oggetti
                    $car_name = Filters::out($abi_roll['car_name']);
                    $car = Filters::int($abi_roll['car']);
                }
            }


            # Se ho selezionato una caratteristica (abbinata o meno ad un'abilita')
            if ($car != 0) {

                # Estraggo i dati riguardo la statistica
                $car_roll = $this->rollCar($car, $obj);

                # Setto il valore della caratteristica
                $car_dice = Filters::int($car_roll['car_dice']);
                $car_name = Filters::out($car_roll['car_name']);

                # Se devo aggiungere anche il valore dell'equipaggiamento alla stat
                if ($this->chat_equip_bonus) {

                    # Valorizzo il campo bonus equip stat per la stampa del testo
                    $car_bonus = Filters::int($car_roll['car_bonus']);
                }
            }

            # Se ho selezionato l'utilizzo di un oggetto e di una tra abilita' o stat
            if (($obj != 0) && ($car != 0)) {

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
                'car_bonus' => $car_bonus
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
            'car_name' => $stat_name
        ];
    }

    /**
     * @fn rollObj
     * @note Funzione che si occupa del calcolo dell'oggetto
     * @param int $obj
     * @param int $car
     * @return array
     * #TODO Adattare statistiche oggetto
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
    private function saveDice(array $array): void
    {
        # Filtro le variabili necessarie
        $dice = Filters::in($array['dice']);
        $abi_nome = Filters::in($array['abi_nome']);
        $abi_dice = Filters::in($array['abi_dice']);
        $car_dice = Filters::in($array['car_dice']);
        $car_name = Filters::in($array['car_name']);
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

        # Aggiungo la caratteristica
        $html .= "{$car_name} {$car_dice},";
        $total += $car_dice;

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