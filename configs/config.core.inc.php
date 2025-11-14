<?php

/**
 * CONFIGURAZIONI CORE DI GDRCD
 *
 * ATTENZIONE - LEGGERE PRIMA DI MODIFICARE:
 *
 * Questo file contiene le configurazioni di base di GDRCD.
 * NON VIENE ASSOLUTAMENTE SUGGERITA LA MODIFICA DI QUESTO FILE,
 * tranne in situazioni particolari e solo da utenti esperti.
 *
 * PROCEDURA DI CONFIGURAZIONE:
 *
 * 1. Copia il file "config.sample.inc.php" dalla directory principale
 * 2. Rinominalo in "config.inc.php"
 * 3. Modifica solo i parametri che ti servono nel nuovo file
 *
 * PARAMETRI INDISPENSABILI:
 * - Connessione database (username, password, nome database, host)
 * - Informazioni sito (nome, URL, email webmaster)
 * - Tema e layout
 *
 * Il sistema carica dapprima questo file con tutti i valori di default,
 * poi legge il file config.inc.php e sovrascrive solo con ciò che è lì specificato.
 * Se non è presente un file config.inc.php, il sistema userà i valori di default.
 *
 * Ogni opzione è spiegata nei commenti HELP che trovi più avanti.
 */

error_reporting(E_ERROR | E_PARSE);

/**
 * Se il personaggio è connesso avvio la gestione dei suoi spostamenti nella land
 * Il controllo va messo qui e non in main poichè in main risulterebbe trovarsi dopo l'inclusione del config
 * dando vita ad un bug sul tastino di aggiornamento della pagina corrente.
 */
if( ! empty($_SESSION['login'])) {
    // Aggiornamento della posizione nella mappa del pg
    if(isset($_REQUEST['map_id']) && is_numeric($_REQUEST['map_id'])) {
        $_SESSION['luogo'] = -1;
        $_SESSION['mappa'] = $_REQUEST['map_id'];
    }

    if(isset($_REQUEST['dir']) && is_numeric($_REQUEST['dir'])) {
        $_SESSION['luogo'] = $_REQUEST['dir'];
    }
}


/* INFORMAZIONI SU GDRCD */
$PARAMETERS['info']['GDRCD'] = '5.6.0.6'; //versione di GDRCD


/* PARAMETRI DI CONNESSIONE */
/**
 * HELP:
 * Sostituire le diciture inserite tra le virgolette con i parametri di connessione al Database del proprio dominio.
 * Essi sono forniti al momento della registrazione. Se non si e' in possesso di tali parametri consultare le FAQ
 * della homepage dell'host che fornisce il dominio. Se non le si trovano li contattare lo staff dell'host.
 */
$PARAMETERS['database']['username'] = 'gdrcd';            //nome utente del database
$PARAMETERS['database']['password'] = 'gdrcd';            //password del database
$PARAMETERS['database']['database_name'] = 'gdrcd';    //nome del database
$PARAMETERS['database']['url'] = 'localhost';        //indirizzo ip del database


/* INFORMAZIONI SUL SITO */
/**
 * HELP: I parametri di questa voce compaiono come informazioni sulla homepage.
 */
$PARAMETERS['info']['site_name'] = 'GDRCD 5.6.0.6'; //nome del gioco
$PARAMETERS['info']['site_url'] = 'http://gdrcd.test/'; //indirizzo URL del gioco
$PARAMETERS['info']['webmaster_name'] = 'Webmaster'; //nome e cognome del responsabile del sito
$PARAMETERS['info']['webmaster_email'] = 'webmaster@gdrhost.it'; //email ufficiale del webmaster (è visibile in homepage)
$PARAMETERS['info']['homepage_name'] = 'Homepage'; //nome con il quale si indica la prima pagina visualizzata
$PARAMETERS['info']['dbadmin_name'] = 'Admin DB'; //nome del responsabile del database


/* SCELTA DELLA LINGUA */
/**
 * HELP: Per definire un diverso vocabolario creare una copia del file /vocabulary/IT-it.vocabulary.php nella cartella vocabulary.
 * Il nome del file deve essere [nome].vocabulary.php, dove la stringa [nome] può essere scelta e deve essere il valore specificato
 * in $PARAMETER['languages']['set'].
 */
$PARAMETERS['languages']['set'] = 'IT-it'; //lingua italiana

/* SCELTA DEL TEMA */
// HOMEPAGE
$PARAMETERS['themes']['homepage'] = 'advanced'; //tema in uso
// MAINPAGE
$PARAMETERS['themes']['current_theme'] = 'advanced'; //tema in uso

/**
 * Inserendo i nomi dei temi in questo elenco è possibile rendere disponibili agli utenti temi alternativi rispetto a quello di default
 * Il primo elemento di ogni riga deve corrispondere al nome della cartella in cui è contenuto il tema in themes/<nome tema>
 * Il secondo elemento è il nome che verrà presentato agli utenti durante la scelta
 *
 * Se non si vuole dare gli utenti la possibilità di scegliere temi alternativi, è sufficiente non impostare alcun tema in questa variabile
 *
 * NOTA: le pagine esterne del sito, cioè quelle visualizzate prima del login saranno sempre visualizzate con il tema di default
 */
$PARAMETERS['themes']['available'] = array(
    'advanced' => 'Tema "Advanced" GDRCD',
    //'il_mio_tema_preferito' => 'Il mio tema troppo figo'
);


/* NUOVI MESSAGGI E ATTIVITA' NEL FORUM */
/**
 * HELP:
 * ON: attiva il controllo di nuovi messaggi e nuove attività nel forum
 * OFF: disattiva il controllo di nuovi messaggi e nuove attività nel forum
 */
$PARAMETERS['mode']['check_forum'] = 'ON';
$PARAMETERS['mode']['check_messages'] = 'ON';

/**
 * HELP:
 * Testo che apparirà accanto al link del forum se sono presenti nuove attività
 * Mettendo FALSE o lasciandolo vuoto, non si vedrà il messaggio
 */
$PARAMETERS['text']['check_forum']['new'] = '(Nuovo)'; // Mettendo FALSE o lasciandolo vuoto, non si vedrà il messaggio


/**
 * SCELTA DEL TIPO DI LAYOUT
 *
 * Tutti i layout sono cross-browser e compatibili con tutti i browser.
 * Il CSS di ogni singolo layout è disponibile nel file corrispondente nella cartella "layouts".
 * Se avete intenzione di modificarli, fatelo con cura.
 *
 * DESCRIZIONE DEI TIPI DI LAYOUT DISPONIBILI:
 *
 * left-right (LAYOUT DI DEFAULT)
 *   Layout a frames con colonne fisse a sinistra e destra
 *   Consente di usare left_column e right_column per decidere quali moduli caricare e dove
 *
 * left-top
 *   Layout a frames con colonna fissa a sinistra e riga fissa in alto, contenuti in basso adattabili
 *   Consente di usare left_column e top_column per decidere quali moduli caricare e dove
 *
 * left-bottom
 *   Layout a frames con colonna fissa a sinistra e riga fissa in basso, contenuti in alto adattabili
 *   Consente di usare left_column e bottom_column per decidere quali moduli caricare e dove
 *
 * top-bottom
 *   Layout a frames con riga fissa in alto e riga fissa in basso, contenuti al centro
 *   Consente di usare top_column e bottom_column per decidere quali moduli caricare e dove
 *   AVVERTENZA: ordinare il CSS dei moduli in modo che si adattino bene su una riga
 *
 * left-top-right
 *   Layout a frames con colonna fissa a sinistra e destra e riga fissa in alto, contenuti in basso al centro
 *   Consente di usare left_column, top_column e right_column per decidere quali moduli caricare e dove
 *   AVVERTENZA: ordinare il CSS dei moduli in modo che si adattino bene sulla riga superiore
 *
 * left-right-bottom
 *   Layout a frames con colonna fissa a sinistra e destra e riga fissa in basso, contenuti in alto al centro
 *   Consente di usare left_column, bottom_column e right_column per decidere quali moduli caricare e dove
 *   AVVERTENZA: ordinare il CSS dei moduli in modo che si adattino bene sulla riga inferiore
 *
 * left-top-bottom
 *   Layout a frames con colonna fissa a sinistra e righe fisse in alto e in basso, contenuti al centro
 *   Consente di usare left_column, bottom_column e top_column per decidere quali moduli caricare e dove
 *   AVVERTENZA: ordinare il CSS dei moduli in modo che si adattino bene sulle righe superiore e inferiore
 *
 * left-top-right-bottom
 *   Layout completo con colonne fisse a sinistra e destra e righe fisse in alto e in basso, contenuti al centro
 *   Consente di usare left_column, bottom_column, top_column e right_column per decidere quali moduli caricare e dove
 *   AVVERTENZA: ordinare il CSS dei moduli in modo che si adattino bene su tutte le righe
 *
 * left
 *   Layout semplice con una colonna di sinistra fissa e contenuti adattabili
 *   Consente di usare solo left_column per caricare i moduli (menu, messaggi, presenti, etc.)
 *
 * right
 *   Layout semplice con una colonna di destra fissa e contenuti adattabili
 *   Consente di usare solo right_column per caricare i moduli
 *
 * top
 *   Layout semplice con una riga in alto fissa e contenuti adattabili in basso
 *   Consente di usare solo top_column per caricare i moduli
 *   AVVERTENZA: ordinare il CSS dei moduli in modo che si adattino bene su una riga
 *
 * bottom
 *   Layout semplice con una riga in basso fissa e contenuti adattabili in alto
 *   Consente di usare solo bottom_column per caricare i moduli
 *   AVVERTENZA: ordinare il CSS dei moduli in modo che si adattino bene su una riga
 */
$PARAMETERS['themes']['kind_of_layout'] = 'left-right';


/*CONFIGURAZIONE DELLE COLONNE*/
/**
 * HELP:
 * Ogni colonna o riga può essere attivata o disattivata a seconda del layout scelto.
 * ON: la colonna/riga è attiva
 * OFF: la colonna/riga è disattivata
 */
$PARAMETERS['top_column']['activate'] = 'OFF';
$PARAMETERS['bottom_column']['activate'] = 'OFF';
$PARAMETERS['left_column']['activate'] = 'ON';
$PARAMETERS['right_column']['activate'] = 'ON';

/*COLONNA SINISTRA */
$PARAMETERS['left_column']['box']['info_location']['class'] = 'info';
$PARAMETERS['left_column']['box']['info_location']['page'] = 'info_location'; //Meteo e informazioni sul luogo.
$PARAMETERS['left_column']['box']['frame_messages']['class'] = 'messages';
$PARAMETERS['left_column']['box']['frame_messages']['page'] = 'frame_messages'; //Link ai messaggi
$PARAMETERS['left_column']['box']['frame_forum']['class'] = 'forums';
$PARAMETERS['left_column']['box']['frame_forum']['page'] = 'frame_forums'; //Link al forum
$PARAMETERS['left_column']['box']['link_menu']['class'] = 'menu';
$PARAMETERS['left_column']['box']['link_menu']['page'] = 'link_menu'; //Menu' del gioco.

/*COLONNA DESTRA*/
$PARAMETERS['right_column']['box']['frame_presenti']['class'] = 'presenti';
$PARAMETERS['right_column']['box']['frame_presenti']['page'] = 'frame_presenti'; //Presenti.


/* NOMI CHIAVE DEL GIOCO */
/**
 * HELP:
 * I nomi chiave sono i termini con i quali ci si riferisce, all'interno del gioco, ad alcune figure ricorrenti, come il forum interno o lo staff del sito.
 */
$PARAMETERS['names']['users_name']['sing'] = 'Utente'; //nome singolare degli utenti
$PARAMETERS['names']['users_name']['plur'] = 'Utenti'; //nome plurale degli utenti
$PARAMETERS['names']['currency']['sing'] = 'Moneta'; //nome singolare della valuta nel gioco
$PARAMETERS['names']['currency']['plur'] = 'Monete'; //nome plurale della valuta nel gioco
$PARAMETERS['names']['currency']['short'] = 'MO'; //nome breve della valuta nel gioco
$PARAMETERS['names']['private_message']['sing'] = 'Messaggio'; //nome dei messaggi privati tra utenti (singolare)
$PARAMETERS['names']['private_message']['plur'] = 'Messaggi'; //nome dei messaggi privati tra utenti (plurale)
$PARAMETERS['names']['private_message']['image_file'] = ''; //immagine del link ai messaggi
$PARAMETERS['names']['private_message']['image_file_onclick'] = ''; //immagine al passaggio del mouse dei messaggi
$PARAMETERS['names']['private_message']['image_file_new'] = ''; //immagine nuovi messaggi
$PARAMETERS['names']['forum']['sing'] = 'Bacheca'; //nome dei forum (singolare)
$PARAMETERS['names']['forum']['plur'] = 'Bacheche'; //nome dei forum (plurale)
$PARAMETERS['names']['forum']['image_file'] = ''; //immagine del forum
$PARAMETERS['names']['forum']['image_file_onclick'] = ''; //immagine al passaggio del mouse del forum
$PARAMETERS['names']['forum']['image_file_new'] = ''; //immagine nuove attività forum
$PARAMETERS['names']['guild_name']['sing'] = 'Gilda'; //nome delle gilde nel gioco (singolare)
$PARAMETERS['names']['guild_name']['plur'] = 'Gilde'; //nome delle gilde nel gioco (plurale)
$PARAMETERS['names']['guild_name']['lead'] = 'Capogilda'; //nome del capo gilda nel gioco (plurale)
$PARAMETERS['names']['guild_name']['members'] = 'Affiliati'; //componenti della gilda (plurale)
$PARAMETERS['names']['guild_name']['type'] = 'Allineamento'; //nome del tipo gilda nel gioco (singolare)
$PARAMETERS['names']['race']['sing'] = 'Razza'; //nome delle razze nel gioco (singolare)
$PARAMETERS['names']['race']['plur'] = 'Razze'; //nome delle razze nel gioco (plurale)
$PARAMETERS['names']['race']['lead'] = 'Caporazza'; //nome del capo razza nel gioco (plurale)
$PARAMETERS['names']['master']['sing'] = 'Master'; //titolo degli arbitri (singolare)
$PARAMETERS['names']['master']['plur'] = 'Master'; //titolo degli arbitri (plurale)
$PARAMETERS['names']['moderators']['sing'] = 'Admin'; //titolo dei moderatori (singolare)
$PARAMETERS['names']['moderators']['plur'] = 'Admin'; //titolo dei moderatori (plurale)
$PARAMETERS['names']['administrator']['sing'] = 'Gestore'; //titolo del super user (singolare)
$PARAMETERS['names']['administrator']['plur'] = 'Gestori'; //titolo del super user (plurale)
$PARAMETERS['names']['gamemenu']['menu'] = 'Menu'; //Nome in calce al menu del gioco
$PARAMETERS['names']['market_name'] = 'Mercato'; //Nome del mercato
$PARAMETERS['names']['maps_location'] = 'Alle mappe'; //Appare se il PG si trova su una mappa
$PARAMETERS['names']['base_location'] = 'In giro'; //Appare nei presenti se non è possibile localizzare il pg
$PARAMETERS['names']['stats']['car0'] = 'Forza'; //Caratteristiche del personaggio nella scheda
$PARAMETERS['names']['stats']['car1'] = 'Robustezza';
$PARAMETERS['names']['stats']['car2'] = 'Destrezza';
$PARAMETERS['names']['stats']['car3'] = 'Intelligenza';
$PARAMETERS['names']['stats']['car4'] = 'Saggezza';
$PARAMETERS['names']['stats']['car5'] = 'Percezioni';
$PARAMETERS['names']['stats']['hitpoints'] = 'Punti ferita';


/* DATA DELL'AMBIENTAZIONE */
/**
 * HELP:
 * L'offset della data viene sommato all'anno corrente per ottenere l'anno desiderato per il gioco.
 * Es: Se il gioco si svolge nel 1290 e l'anno corrente e' il 2010 allora l'offset necessario è 1290-2010= -720, nel caso del 2120 l'offset e' +110.
 *
 * Il sistema potrebbe risultare incoerente per gli anni bisestili, e' consiglibile che lo sfasamento tenga conto della posizione dell'anno corrente nel corrente quadriennio.
 */
$PARAMETERS['date']['offset'] = 0;
$PARAMETERS['date']['base_temperature'] = -4;//temperatura minima assoluta in gradi.


/* OPZIONI DEL GIOCO */
$PARAMETERS['settings']['first_map'] = -1;//ID della mappa corrispondente al primo login
$PARAMETERS['settings']['first_money'] = 50;//Quantita' di denaro iniziale per i PG
$PARAMETERS['settings']['posts_per_page'] = 15;//Numero di post per pagina visualizzati nei forum
$PARAMETERS['settings']['records_per_page'] = 15;//Numero di record per pagina visualizzati nei pannelli gestione
$PARAMETERS['settings']['messages_per_page'] = 40;//Numero di messaggi visualizzati per pagina nel sistema di messaggistica privata
$PARAMETERS['settings']['messages_limit'] = 50;//Numero di messaggi privati oltre il quale appare il suggerimento di cancellarli
$PARAMETERS['settings']['minimum_employment'] = 10;//Numero di giorni entro i quali non è possibile scegliere un'altro lavoro o essere esclusi da una gilda.
$PARAMETERS['settings']['guilds_limit'] = 2;//Numero massimo di gilde a cui si può essere affiliati. Il numero tiene conto delle gilde di cui un personaggio è membro e dell'eventuale lavoro indipendente che svolte. In ogni caso il sistema permette di svolgere un unico lavoro indipendente. La paga giornaliera del personaggio e' la somma degli introiti di tutti i ruoli di gilda e dell'eventuale lavoro che riveste.
$PARAMETERS['settings']['resell_price'] = 30; //Percentuale di svalutazione degli oggetti rivenduti al mercato.
$PARAMETERS['settings']['first_px'] = 100;//Esperienza iniziale. Se il gioco non prevede abilità dovrebbe essere 0.
$PARAMETERS['settings']['max_hp'] = 100;//Punti ferita.
$PARAMETERS['settings']['px_x_rank'] = 10; //Costo in px per rango di abilità. Il valore di questo campo viene moltiplicato al rango successivo dell'abità per determinarne il costo. Es: Se il valore è 10 e il rango da aquisite è 5 il suo costo è 5x10=50px.
$PARAMETERS['settings']['skills_cap'] = 10;//Punteggio massimo per un'abilità.
$PARAMETERS['settings']['initial_cars_cap'] = 10;//Punteggio massimo iniziale per una caratteristica.
$PARAMETERS['settings']['cars_cap'] = 10;//Punteggio massimo per una caratteristica.
$PARAMETERS['settings']['cars_sum'] = 40;//Punteggio totale da distribuire tra le caratteristiche in fase di iscrizione.
$PARAMETERS['settings']['view_logs'] = 10; //Numero di log visualizzato.
$PARAMETERS['settings']['auto_salary'] = 'OFF'; //ON per attivare l'accredito automatico dello stipendio al primo login


/* ATTESA DI RICONNESSIONE */
/**
 * HELP:
 * Imposta l'intervallo di attesa per la riconessione, in modo da evitare i doppi login.
 * Il valore è espresso in secondi (300 = 5 minuti)
 * Se il valore è impostato a 0, il sistema non effettua alcun controllo
 */
$PARAMETERS['settings']['reconnection_cooldown'] = 300;


/* ABILITAZIONE AUDIO IN LAND */
/**
 * HELP:
 * L'abilitazione dell'audio in land consente di riprodurre suoni in determinate occasioni, come l'arrivo di un nuovo messaggio privato o di un nuovo messaggio in chat.
 * ON: abilita l'uso dell'audio
 * OFF: disabilita l'uso dell'audio
 */
$PARAMETERS['mode']['allow_audio'] = 'ON';

/**
 * HELP:
 * Tipi di file audio concessi in land, la lista è semplice: [estensione_file] = mimetype
 * Per disabilitare i suoni sugli utenti mettere su OFF la voce relativa nei gruppi di abilitazione poco più sotto
 */
$PARAMETERS['settings']['audiotype']['.mp3'] = 'audio/mpeg';
$PARAMETERS['settings']['audiotype']['.mid'] = 'audio/x-mid';
$PARAMETERS['settings']['audiotype']['.midi'] = 'audio/x-mid';
$PARAMETERS['settings']['audiotype']['.wav'] = 'audio/x-wav';

/**
 * HELP:
 * nome del file audio usato per il suono dei nuovi messaggi in arrivo
 * - il file DEVE trovarsi nella cartella sounds
 * - il file DEVE essere in uno dei formati concessi per l'uso
 * - per non usare file audio per le nuove missive, semplicemente lasciare vuoto il campo
 *
 * AudioController
 * è possibile inserire suoni diversi per le varie tipologia di notifica
 * nel caso si volesse introdurre una nuova tipologia di notifica, è sufficiente aggiungere un nuovo parametro in coda
 * e impostargli come nome 'audio_new_LABELNOTIFICA', dove LABELNOTIFICA corrisponde a quello passato in AudioController
 */
$PARAMETERS['settings']['audio_new_messages'] = 'beep.wav';
$PARAMETERS['settings']['audio_new_chat'] = 'beep.wav';


/* ABILITAZIONE DADI IN CHAT */
/**
 * HELP:
 * Abilita o disabilita l'uso dei dadi in chat
 * ON: abilita l'uso dei dadi in chat
 * OFF: disabilita l'uso dei dadi in chat
 */
$PARAMETERS['mode']['dices'] = 'ON';

/**
 * HELP:
 * Numero di facce del dado da usare per il "Tiro su Caratteristica" in chat.
 * E' possibile configurare NULL per disattivare l'uso del dado nel tiro caratteristica
 */
$PARAMETERS['settings']['stats_dice'] = 20;

/**
 * HELP:
 * Numero di facce del dado da usare per il "Tiro su Abilità" in chat
 * E' possibile configurare NULL per disattivare l'uso del dado nel tiro abilità
 */
$PARAMETERS['settings']['skill_dice'] = 20;

/**
 * HELP:
 * E' possibile aggiungere la possibilità di usare altri tipi di dado implementando questa sezione.
 * E' possibile anche ridurre la scelta dei possibili tipi di dado semplicemente rimuovendo una riga qui.
 */
$PARAMETERS['settings']['skills_dices']['faces']['d4'] = 4;
$PARAMETERS['settings']['skills_dices']['faces']['d6'] = 6;
$PARAMETERS['settings']['skills_dices']['faces']['d8'] = 8;
$PARAMETERS['settings']['skills_dices']['faces']['d10'] = 10;
$PARAMETERS['settings']['skills_dices']['faces']['d12'] = 12;
$PARAMETERS['settings']['skills_dices']['faces']['d20'] = 20;
$PARAMETERS['settings']['skills_dices']['faces']['d100'] = 100;

// Massimo numero di dadi che possono essere lanciati in chat con un singolo tiro
$PARAMETERS['settings']['skills_dices']['max_number'] = 100;

// Minimo modificatore consentito da applicare alla somma dei dadi lanciati
$PARAMETERS['settings']['skills_dices']['min_modifier'] = -100;

// Massimo modificatore consentito da applicare alla somma dei dadi lanciati
$PARAMETERS['settings']['skills_dices']['max_modifier'] = 100;


/* CONFIGURAZIONE AVATAR CHAT */
/**
 * HELP:
 * Abilita o disabilita l'uso degli avatar in chat
 * ON: abilita l'uso degli avatar in chat
 * OFF: disabilita l'uso degli avatar in chat
 */
$PARAMETERS['mode']['chat_avatar'] = 'ON';

$PARAMETERS['settings']['chat_avatar']['width'] = 50;    # Dimensione in pixel della larghezza dell'immagine consentita
$PARAMETERS['settings']['chat_avatar']['height'] = 50;    # Dimensione in pixel dell'altezza dell'imagine consentita

/**
 * HELP:
 * Permette di rendere cliccabile l'avatar da chat ed aprire così la scheda del personaggio in questione (!! non funziona nel chat_save)
 * ON: abilita l'apertura della scheda del personaggio tramite click dell'avatar da chat
 * OFF: disabilita l'apertura della scheda del personaggio tramite click dell'avatar da chat
 */
$PARAMETERS['settings']['chat_avatar']['link']['mode'] = 'ON';

/**
 * HELP:
 * Se è abilitata l'apertura della scheda del personaggio dall'avatar da chat, permette di scegliere se aprire la pagina in un pop up o sulla pagina corrente
 * ON: la scheda del personaggio viene aperta in una modale
 * OFF: la scheda del personaggio viene aperta sulla pagina corrente
 */
$PARAMETERS['settings']['chat_avatar']['link']['popup'] = 'OFF';


/* CONFIGURAZIONE TOOLTIP MAPPA */
/**
 * HELP:
 * Uso di una tooltip di preview per le descrizioni sulla mappa
 * ON: abilita delle tooltip sui link in mappa che mostrano la descrizione del luogo in anteprima
 * OFF: non abilita le tooltip
 */
$PARAMETERS['mode']['map_tooltip'] = 'ON';

$PARAMETERS['settings']['map_tooltip']['offset_y'] = 20;    # offset verticale della tip dalla posizione del cursore
$PARAMETERS['settings']['map_tooltip']['offset_x'] = 20;    # offset orizzontale della tip dalla posizione del cursore


/* PARAMETRI PER L'INCREMENTO DELL'ESPERIENZA TRAMITE CARATTERI SCRITTI */
/**
 * HELP:
 * ON: abilita l'incremento dei punti esperienza tramite i caratteri scritti in chat.
 * OFF: disabilita l'incremento dei punti esperienza tramite i caratteri scritti in chat.
 */
$PARAMETERS['mode']['exp_by_chat'] = 'OFF';

/**
 * HELP:
 * Numero di caratteri necessari al fine di aggiungere punti esperienza.
 * Se l'incremento dell'esperienza è abilitato e il numero caratteri è 0 allora si aggiungono punti esperienza ogni volta che si scrive un carattere.
 */
$PARAMETERS['settings']['exp_by_chat']['number'] = '1000';

/**
 * HELP:
 * Numero di punti da assegnare quando si superano i caratteri necessari.
 * Impostare 0 nel caso si vuole dare 1 punto ogni volta che si raggiungono i caratteri dichiarati prima.
 */
$PARAMETERS['settings']['exp_by_chat']['value'] = '0';

/**
 * HELP:
 * ON: abilita l'incremento dei punti esperienza tramite i caratteri scritti in chat pubblica.
 * OFF: disabilita l'incremento dei punti esperienza tramite i caratteri scritti in chat pubblica.
 */
$PARAMETERS['mode']['exp_in_private'] = 'ON';


/* CONFIGURAZIONE BBCode */
/**
 * HELP:
 * ON: abilita l'uso del bbcode obbligatorio per la formattazione delle aree del forum
 * OFF: consente di usare html, filtrato delle sue componenti nocive, per la modifica dei campi del forum
 */
$PARAMETERS['mode']['forum_bbcode'] = 'ON';
$PARAMETERS['mode']['user_bbcode'] = 'ON';

/**
 * HELP:
 * Le seguenti impostazioni sono valide solo se il bbcode viene abilitato.
 * bbd : viene abilitato l'uso del plugin "BBDecoder" per la funzionalità del bbcode
 * native : viene usato la scarna formattazione bbcode di base del gdrcd
 *
 * NOTA: nel forum o si sceglie il bbd o la funzione nativa di GDRCD5.1. Non è possibile includere html filtrato
 */
$PARAMETERS['settings']['forum_bbcode']['type'] = 'bbd';
$PARAMETERS['settings']['user_bbcode']['type'] = 'bbd';

/**
 * HELP:
 * ON: abilita l'uso del plugin "BBDecoder" per la funzionalità del bbcode
 * OFF: disabilita l'uso del plugin "BBDecoder"
 */
$PARAMETERS['settings']['bbd']['free_html'] = 'ON';

/**
 * HELP:
 * ON: accetta SOLO i link per le immagini derivanti da imageshack
 * OFF: accetta link per le immagini da tutti gli indirizzi
 */
$PARAMETERS['settings']['bbd']['imageshack'] = 'OFF';

/**
 * HELP:
 * Rimodimensionamento automatico delle immagini caricate tramite bbcode.
 * Le immagini che superano i limiti di altezza o larghezza vengono ridimensionate automaticamente.
 *
 * NON TUTTI I SERVIZI DI HOSTING SUPPORTANO IL RIDIMENSIONAMENTO DELLE IMMAGINI.
 * IN QUESTO CASO IL RIDIMENSIONAMENTO NON SARA' APPLICATO.
 */
$PARAMETERS['settings']['bbd']['image_max_width'] = 400; // Larghezza massima
$PARAMETERS['settings']['bbd']['image_max_height'] = 600; // Altezza massima

/**
 * HELP:
 * ON: consente l'uso del bbtag [youtube][/youtube] per l'inclusione di video da youtube.
 * OFF: non consente l'uso
 */
$PARAMETERS['settings']['bbd']['youtube'] = 'ON';

/**
 * HELP:
 * Tipi di estensioni consentiti per le immagini che i giocatori possono caricare in scheda o nel forum tramite il bbd.
 */
$PARAMETERS['settings']['bbd']['images_ext'][] = 'jpg';
$PARAMETERS['settings']['bbd']['images_ext'][] = 'jpeg';
$PARAMETERS['settings']['bbd']['images_ext'][] = 'png';
$PARAMETERS['settings']['bbd']['images_ext'][] = 'gif';

/* PARAMETRI PER HTML FILTRATO */
/**
 * HELP:
 * Controlla quanto pesantemente filtrare il codice HTML per ragioni di sicurezza. Ci sono 2 livelli:
 * HTML_FILTER_BASE: filtra solo le cose più pericolose, come gli iframe, gli object e javascript
 * HTML_FILTER_HIGH: filtra anche tutte le immagini
 */
$PARAMETERS['settings']['html'] = HTML_FILTER_BASE;

/* AVVISO PERIODO DI CAMBIO PASSWORD */
/**
 * HELP:
 * ON: abilita l'avviso di cambio password ogni tot periodo
 * OFF: disabilita l'avviso di cambio password ogni tot periodo
 */
$PARAMETERS['mode']['alert_password_change'] = 'ON';

/**
 * HELP:
 * Solo se 'alert_password_change' è impostato su ON:
 * ON: fa comparire l'avviso di cambio password anche se appena iscritti, così da invitare a modificare la password generata autonomamente per l'iscrizione
 * OFF: non avverte l'utente appena iscritto di cambiare la password di default assegnatagli
 */
$PARAMETERS['settings']['alert_password_change']['alert_from_signup'] = 'OFF';


/* OPZIONI TECNICHE DEL GIOCO */

/**
 * HELP:
 * ON: i messaggi nuovi della chat compaiono dal basso verso l'alto
 * OFF: i messaggi nuovi della chat compaiono dall'alto verso il basso
 */
$PARAMETERS['mode']['chat_from_bottom'] = 'OFF';

/**
 * HELP:
 * ON: gli oggetti dei personaggi possono essere ceduti fra loro solo se entrambi online e nella stessa locazione
 * OFF: gli oggetti dei personaggi possono essere ceduti fra loro anche se uno dei due non è online e indipendentemente dalla locazione
 */
$PARAMETERS['mode']['give_only_if_online'] = 'ON';

/**
 * HELP:
 * ON: nel modulo di login nella land mostra un checkbox che consente a scelta dell'utente di aprire il gdr in una pagina popup
 * OFF: il gdr si apre nella pagina completa del browser
 */
$PARAMETERS['mode']['popup_choise'] = 'ON';

/**
 * HELP:
 * ON: il titolo della pagina annuncerà l'arrivo di nuovi messaggi privati
 * OFF: il titolo della pagina non subirà alcun cambiamento
 */
$PARAMETERS['mode']['alert_pm_via_pagetitle'] = 'ON';

/**
 * HELP:
 * ON: abilita la modifica per gli utenti di un campo che sarà poi visualizzato nell'elenco dei presenti mediante tooltip, se abilitato si applicano i parametri di configurazione dell'offset usati per il tooltip della mappa
 * OFF: nega tale possibilità
 */
$PARAMETERS['mode']['user_online_state'] = 'ON';

/**
 * HELP:
 * ON: abilita un menù a tendina che trasporta velocemente fra mappe o locazioni
 * OFF: lo disabilita
 */
$PARAMETERS['mode']['gotomap_list'] = 'ON';

/**
 * HELP:
 * ON: Il personaggio viene automaticamente loggato nel luogo dove si trovava al momento del logout
 * OFF: Il personaggio non viene loggato nel luogo dove si trovava al momento del logout
 */
$PARAMETERS['mode']['log_back_location'] = 'OFF';

/**
 * HELP:
 * ON: I link dell'elenco presenti non permettono al personaggio di saltare da una mappa all'altra
 * OFF: I link dell'elenco presenti permettono al personaggio di saltare da una mappa all'altra
 */
$PARAMETERS['mode']['mapwise_links'] = 'OFF';

/**
 * HELP:
 * ON: Il meteo è generato automaticamente
 * OFF: Il meteo è inserito manualmente, mappa per mappa. Se le mappe sono significativamente distanti, geograficamente, questa opzione dovrebbe essere impostata ad OFF
 */
$PARAMETERS['mode']['auto_meteo'] = 'ON';

/**
 * HELP:
 * ON: È attivato il sistema di gioco con punteggi, abilità e tiri di dado
 * OFF: È attivato il sistema di gioco solo interpretativo
 */
$PARAMETERS['mode']['skillsystem'] = 'ON';

/**
 * HELP:
 * ON: Non è permesso l'uso di codici html nella documentazione del gioco
 * OFF: È permesso l'uso di codici html nella documentazione del gioco (questa opzione potrebbe compromettere la sicurezza del sito)
 */
$PARAMETERS['mode']['filterdocuments'] = 'OFF';

/**
 * HELP:
 * ON: In fase di registrazione la password viene inviata per email
 * OFF: In fase di registrazione la password viene visualizzata nella pagina di conferma
 */
$PARAMETERS['mode']['emailconfirmation'] = 'ON';

/**
 * HELP:
 * ON: In fase di iscrizione è presente un link alla descrizione della razza
 * OFF: In fase di iscrizione non è presente un link alla descrizione della razza (Se il gioco non prevede razze e la funzione razza è utilizzata in altro modo, come professione, nazionalità o altro scegliere OFF)
 */
$PARAMETERS['mode']['racialinfo'] = 'ON';

/**
 * HELP:
 * ON: In chat sono visualizzate le icone relative a sesso e razza del pg
 * OFF: In chat non sono visualizzate le icone relative a sesso, razza e gilda del pg
 */
$PARAMETERS['mode']['chaticons'] = 'ON';

/**
 * HELP:
 * ON: I messaggi istantanei tra giocatori appaiono nei log della scheda
 * OFF: I messaggi istantanei tra giocatori non appaiono nei log della scheda
 */
$PARAMETERS['mode']['spymessages'] = 'ON';

/**
 * HELP:
 * ON: Il gioco prevede stanze private temporanee prenotabili dai giocatori
 * OFF: Il gioco non prevede stanze private prenotabili dai giocatori
 */
$PARAMETERS['mode']['privaterooms'] = 'ON';

/**
 * HELP:
 * ON: Gli amministratori possono leggere e scrivere nelle stanze private altrui
 * OFF: Gli amministratori non possono leggere e scrivere nelle stanze private altrui
 */
$PARAMETERS['mode']['spyprivaterooms'] = 'OFF';

/**
 * HELP:
 * ON: È abilitato il pulsante Salva chat
 * OFF: È disabilitato il pulsante Salva chat
 */
$PARAMETERS['mode']['chatsave'] = 'ON';

/**
 * HELP:
 * ON: È abilitato il pulsante Salva chat per le chat private, SCONSIGLIATO
 * OFF: È disabilitato il pulsante Salva chat per le chat private
 */
$PARAMETERS['mode']['chatsavepvt'] = 'OFF';

/**
 * HELP:
 * ON: Salva le chat automaticamente sulla land e fornisce il link per visualizzarla
 * OFF: Non salva le chat in land
 */
$PARAMETERS['mode']['chatsave_link'] = 'ON';

/**
 * HELP:
 * ON: Crea un file html e lo fa scaricare dall'utente
 * OFF: Non crea nessun file html che viene scaricato
 */
$PARAMETERS['mode']['chatsave_download'] = 'OFF';

/* HELP: Le voci di questa categoria abilitano o disabilitano funzioni presenti nel gioco. Ad esempio, se non si desidera che il personaggio si riconnetta nello stesso luogo di gioco in cui si è disconnesso, bensi' nella mappa, occorre impostare in OFF la relativa voce */


/* CLASSIFICAZIONE PEGI */
/**
 * HELP:
 * Per visualizzare le icone stile pegi in homepage rimuovere i commenti davanti alle voci ( il  // )
 * Il PEGI non è una risorsa gratuita, le icone sono disabilitate di base perchè per adoperarle è necessaria una licenza che si ACQUISTA.
 *
 * La funzionalità rimane pertanto per consentire il facile inserimento delle vostre iconcine personalizzate.
 */
//$PARAMETERS['pegi']['violenza']['image_file']='117.gif';
//$PARAMETERS['pegi']['violenza']['text']='Gioco che contiene scene di violenza';
//$PARAMETERS['pegi']['droghe']['image_file']='112.gif';
//$PARAMETERS['pegi']['droghe']['text']='Gioco che fa riferimento a o rappresenta l’uso di droghe';
//$PARAMETERS['pegi']['discriminazione']['image_file']='111.gif';
//$PARAMETERS['pegi']['discriminazione']['text']='Gioco che contiene scene di discriminazione o materiale che possa incoraggiarla';
//$PARAMETERS['pegi']['paura']['image_file']='113.gif';
//$PARAMETERS['pegi']['paura']['text']='Gioco che può allarmare o spaventare i bambini ';
//$PARAMETERS['pegi']['azzardo']['image_file']='114.gif';
//$PARAMETERS['pegi']['azzardo']['text']='Gioco che incoraggia o insegna a giocare d’azzardo ';
//$PARAMETERS['pegi']['sesso']['image_file']='116.gif';
//$PARAMETERS['pegi']['sesso']['text']='Gioco che contiene scene di nudo e/o comportamenti sessuali o riferimenti sessuali ';
//$PARAMETERS['pegi']['volgarita']['image_file']='115.gif';
//$PARAMETERS['pegi']['volgarita']['text']='Gioco che contiene espressioni volgari';
//$PARAMETERS['pegi']['online']['image_file']='237.gif';
//$PARAMETERS['pegi']['online']['text']='Gioco online';
//$PARAMETERS['pegi']['3+']['image_file']='149.gif';
//$PARAMETERS['pegi']['3+']['text']='Gioco adatto ai bambini';
//$PARAMETERS['pegi']['7+']['image_file']='151.gif';
//$PARAMETERS['pegi']['7+']['text']='Gioco adatto ai bambini';
//$PARAMETERS['pegi']['12+']['image_file']='154.gif';
//$PARAMETERS['pegi']['12+']['text']='Gioco adatto ai ragazzi';
//$PARAMETERS['pegi']['16+']['image_file']='155.gif';
//$PARAMETERS['pegi']['16+']['text']='Gioco adatto ai ragazzi';
//$PARAMETERS['pegi']['18+']['image_file']='156.gif';
//$PARAMETERS['pegi']['18+']['text']='Per un pubblico adulto';


/* COSTRUZIONE OPZIONI MENU */
/**
 * HELP [-- IMPORTANTE!!! --]:
 * Le seguenti voci configurano i menu' opzioni interni al gioco. Specificare un diverso testo visualizzato (text)
 * o un immagine (image_file) modifica l'aspetto del menu, ma alterare l'indirizzo di riferimento (url)
 * o cancellare voci potrebbe pregiudicare il funzionamento di parte del gioco.
 * Le immagini specificate in image_file e image_file_onclick devono essere nella cartella imgs/menu del tema
 * e sono, rispettivamente, l'immagine di base del tasto e l'immagine al passaggio del mouse, nel caso
 * la seconda mancasse viene visualizzata la prima in entrambi i casi.
 *
 * Alternativamente per gli utenti esperti: al posto delle immagini image_file e image_file_onclick (che
 * vengono modificare con javascript) è possibile utilizzare un'immagine sola come sprite
 * ( https://css-tricks.com/css-sprites/ ). Per impostare la sprite inserire la chiave 'sprite' nella
 * configurazione della voce di menù e dargli un valore true. Per impostare l'immagine usare solo la chiave
 * 'image_file'. Ricordarsi però di personalizzare correttamente le dimensioni delle immagini nel css (main
 * .css): di default sono impostate a 50x50px
 */

/**
 * MENU MULTIPLI: è possibile configurare più menù nella pagina principale del gioco. Per farlo è sufficiente
 * richiamare più volte la pagina link_menu nella configurazione del layout, specificando però una classe
 * differente e aggiungendo il parametro menu_key per indicare al sistema dove recuperare le informazioni sul
 * secondo menù. Per impedire la comparsa della lista di mappe su ogni menù è necessario aggiungere il
 * parametro 'no_gotomap_list' e impostarlo a true per ogni menù che NON deve visualizzare le mappe
 * Per esempio:
 *
 * $PARAMETERS['bottom_column']['box']['link_menu']['class'] = 'menu_bottom';
 * $PARAMETERS['bottom_column']['box']['link_menu']['page'] = 'link_menu';
 * $PARAMETERS['bottom_column']['box']['link_menu']['menu_key'] = 'secondo_menu';
 * $PARAMETERS['bottom_column']['box']['link_menu']['no_gotomap_list'] = true;
 *
 * Per modificare il titolo visualizzato in cima al nuovo menù è necessario aggiungere una nuova voce alla
 * lista dei nomi impostata più indietro in questo file:
 * $PARAMETERS['names']['gamemenu']['secondo_menu'] = 'Menu 2';
 *
 * Dopo di che basta aggiungere la configurazione del nuovo menù, con una struttura simile a
 * quella usata per il menù principale, ma cambiando la chiave:
 *
 * $PARAMETERS['secondo_menu']['refresh']['text'] = 'Aggiorna';
 * $PARAMETERS['secondo_menu']['refresh']['url'] = 'main.php?dir=' . $_SESSION['luogo'];
 * $PARAMETERS['secondo_menu']['refresh']['image_file'] = '';
 * $PARAMETERS['secondo_menu']['refresh']['image_file_onclick'] = '';
 *
 * $PARAMETERS['secondo_menu']['map']['text'] = 'Mappa';
 * $PARAMETERS['secondo_menu']['map']['url'] = 'main.php?page=mappaclick&map_id=' . $_SESSION['mappa'];
 * $PARAMETERS['secondo_menu']['map']['image_file'] = '';
 * $PARAMETERS['secondo_menu']['map']['image_file_onclick'] = '';
 *
 * Ogni voce di menù riceve un attributo id personalizzato in automatico, utilizzabile in css e javascript.
 *
 * Volendo, a ogni voce di menù è possibile aggiungere qualunque attributo html, aggiungendolo alla
 * configurazione nel menù, nel seguende modo:
 * $PARAMETERS['secondo_menu']['map']['class'] = 'custom_class'; //Aggiunge una classe
 */

/**
 * HELP:
 * Queste voci compariranno nel menu di gioco. E' possibile scegliere se farle comparire come immagini o semplice testo. Se, per ciascuna voce, è specificato il campo image_file allora la voce di menu compare come immagine e text viene interpretato come testo alternativo all'immagine.
 * text - Testo visualizzato come voce di menu o testo alternativo dell'immagine
 * url - Destinazione del link. E' sconsigliato modificare questa voce.
 * image_file - File dell'immagine che appare come voce di menu. L'immagine deve trovarsi nella cartella themes/[nome tema]/img/menu (per il template preimpostato themes/extreme/img/menu)
 */

/* VOCI DEL MENU */
$PARAMETERS['menu']['refresh']['text'] = 'Aggiorna';
$PARAMETERS['menu']['refresh']['url'] = 'main.php?dir=' . $_SESSION['luogo'];
$PARAMETERS['menu']['refresh']['image_file'] = '';
$PARAMETERS['menu']['refresh']['image_file_onclick'] = '';

$PARAMETERS['menu']['map']['text'] = 'Mappa';
$PARAMETERS['menu']['map']['url'] = 'main.php?page=mappaclick&map_id=' . $_SESSION['mappa'];
$PARAMETERS['menu']['map']['image_file'] = '';
$PARAMETERS['menu']['map']['image_file_onclick'] = '';

$PARAMETERS['menu']['profile']['text'] = 'Scheda';
$PARAMETERS['menu']['profile']['url'] = 'main.php?page=scheda&pg=' . $_SESSION['login'];
/**
 * HELP:
 * Esempio di link nel caso si volesse aprire come scheda modale:
 * $PARAMETERS['menu']['profile']['url']="javascript:modalWindow('scheda', 'Scheda di ". $_SESSION['login'] ."', 'popup.php?page=scheda&pg=". $_SESSION['login'] ."');";
 */
$PARAMETERS['menu']['profile']['image_file'] = '';
$PARAMETERS['menu']['profile']['image_file_onclick'] = '';

if ($_SESSION['permessi'] >= MODERATOR)
{
    $PARAMETERS['menu']['backend']['text'] = 'Gestione';
    $PARAMETERS['menu']['backend']['url'] = 'main.php?page=gestione';
    $PARAMETERS['menu']['backend']['image_file'] = '';
    $PARAMETERS['menu']['backend']['image_file_onclick'] = '';
}

$PARAMETERS['menu']['services']['text'] = 'Servizi';
$PARAMETERS['menu']['services']['url'] = 'main.php?page=uffici';
$PARAMETERS['menu']['services']['image_file'] = '';
$PARAMETERS['menu']['services']['image_file_onclick'] = '';

$PARAMETERS['menu']['user_services']['text'] = 'Menu utente';
$PARAMETERS['menu']['user_services']['url'] = 'main.php?page=utenti';
$PARAMETERS['menu']['user_services']['image_file'] = '';
$PARAMETERS['menu']['user_services']['image_file_onclick'] = '';

$PARAMETERS['menu']['quit']['text'] = 'Esci';
$PARAMETERS['menu']['quit']['url'] = 'logout.php';
$PARAMETERS['menu']['quit']['image_file'] = '';
$PARAMETERS['menu']['quit']['image_file_onclick'] = '';

/* PANNELLO SERVIZI */
$PARAMETERS['office_page_name'] = 'Servizi';
$PARAMETERS['office']['image_file'] = '';
$PARAMETERS['office']['pg_list']['text'] = 'Anagrafe';
$PARAMETERS['office']['pg_list']['url'] = 'main.php?page=servizi_anagrafe';
$PARAMETERS['office']['pg_list']['access_level'] = USER;
$PARAMETERS['office']['guilds_adm']['text'] = "Amministrazione " . strtolower($PARAMETERS['names']['guild_name']['sing']);
$PARAMETERS['office']['guilds_adm']['url'] = 'main.php?page=servizi_adm_gilde';
$PARAMETERS['office']['guilds_adm']['access_level'] = USER;
$PARAMETERS['office']['job']['text'] = 'Lavoro';
$PARAMETERS['office']['job']['url'] = 'main.php?page=servizi_lavoro';
$PARAMETERS['office']['job']['access_level'] = USER;
$PARAMETERS['office']['guilds']['text'] = $PARAMETERS['names']['guild_name']['plur'];
$PARAMETERS['office']['guilds']['url'] = 'main.php?page=servizi_gilde';
$PARAMETERS['office']['guilds']['access_level'] = USER;
$PARAMETERS['office']['market']['text'] = 'Mercato';
$PARAMETERS['office']['market']['url'] = 'main.php?page=servizi_mercato';
$PARAMETERS['office']['market']['access_level'] = USER;
if (ESITI)
{
    $PARAMETERS['office']['esiti']['text'] = 'Pannello esiti';
    $PARAMETERS['office']['esiti']['url'] = 'main.php?page=servizi_esiti';
    $PARAMETERS['office']['esiti']['access_level'] = USER;
}
if ($PARAMETERS['mode']['privaterooms'] == 'ON')
{
    $PARAMETERS['office']['hotel']['text'] = 'Prenotazione stanze';
    $PARAMETERS['office']['hotel']['url'] = 'main.php?page=servizi_prenotazioni';
    $PARAMETERS['office']['hotel']['access_level'] = USER;
}
$PARAMETERS['office']['bank']['text'] = 'Servizi bancari';
$PARAMETERS['office']['bank']['url'] = 'main.php?page=servizi_banca';
$PARAMETERS['office']['bank']['access_level'] = USER;

/* PANNELLO UTENTE */
$PARAMETERS['user_page_name'] = 'Menù utente';
if ($PARAMETERS['mode']['skillsystem'] == 'ON')
{
    $PARAMETERS['user']['skill_list']['text'] = 'Abilità';
    $PARAMETERS['user']['skill_list']['url'] = 'main.php?page=user_abilita';
    $PARAMETERS['user']['skill_list']['access_level'] = USER;
}
$PARAMETERS['user']['plot']['text'] = 'Ambientazione';
$PARAMETERS['user']['plot']['url'] = 'main.php?page=user_ambientazione';
$PARAMETERS['user']['plot']['access_level'] = USER;
$PARAMETERS['user']['name']['text'] = 'Cambio nome';
$PARAMETERS['user']['name']['url'] = 'main.php?page=user_cambio_nome';
$PARAMETERS['user']['name']['access_level'] = MODERATOR;
$PARAMETERS['user']['pass']['text'] = 'Cambio password';
$PARAMETERS['user']['pass']['url'] = 'main.php?page=user_cambio_pass';
$PARAMETERS['user']['pass']['access_level'] = USER;
$PARAMETERS['user']['delete']['text'] = 'Cancella account';
$PARAMETERS['user']['delete']['url'] = 'main.php?page=user_cancella_pg';
$PARAMETERS['user']['delete']['access_level'] = SUPERUSER;
$PARAMETERS['user']['races']['text'] = 'Elenco razze';
$PARAMETERS['user']['races']['url'] = 'main.php?page=user_razze';
$PARAMETERS['user']['races']['access_level'] = USER;
$PARAMETERS['user']['rules']['text'] = 'Regolamento';
$PARAMETERS['user']['rules']['url'] = 'main.php?page=user_regolamento';
$PARAMETERS['user']['rules']['access_level'] = USER;
$PARAMETERS['user']['stats']['text'] = 'Statistiche del sito';
$PARAMETERS['user']['stats']['url'] = 'main.php?page=user_stats&links=yes';
$PARAMETERS['user']['stats']['access_level'] = USER;

/* PANNELLO GESTIONE */
/**
 * HELP: Elenco delle voci dei menu' dei servizi e di gestione. E' sconsigliato operare modifiche. Le opzioni sono disponibili solo agli account con il livello d'accesso specificato o superiore.
 * Livelli di accesso utente:
 * USER: Utente normale.
 * SUPERUSER: Amministratore.
 * MODERATOR: Admin.
 * GAMEMASTER: Master.
 * GUILDMODERATOR: Master di gilda.
 */
$PARAMETERS['administration_page_name'] = 'Gestione';
$PARAMETERS['administration']['image_file'] = '';
$PARAMETERS['administration']['log_chat']['text'] = 'Log chat';
$PARAMETERS['administration']['log_chat']['url'] = 'main.php?page=log_chat';
$PARAMETERS['administration']['log_chat']['access_level'] = MODERATOR;
$PARAMETERS['administration']['log_eventi']['text'] = 'Log eventi';
$PARAMETERS['administration']['log_eventi']['url'] = 'main.php?page=log_eventi';
$PARAMETERS['administration']['log_eventi']['access_level'] = MODERATOR;

if ($PARAMETERS['mode']['spymessages'] == 'ON')
{
    $PARAMETERS['administration']['log_messaggi']['text'] = 'Log messaggi';
    $PARAMETERS['administration']['log_messaggi']['url'] = 'main.php?page=log_messaggi';
    $PARAMETERS['administration']['log_messaggi']['access_level'] = MODERATOR;
}
if (REG_ROLE && SEND_GM)
{
    $PARAMETERS['administration']['send_GM']['text'] = 'Giocate segnalate';
    $PARAMETERS['administration']['send_GM']['url'] = 'main.php?page=gestione_segnalazioni&segn=roles_gm';
    $PARAMETERS['administration']['send_GM']['access_level'] = ROLE_PERM;
}
if (ESITI)
{
    $PARAMETERS['administration']['esiti']['text'] = 'Pannello esiti Master';
    $PARAMETERS['administration']['esiti']['url'] = 'main.php?page=gestione_segnalazioni&segn=esiti_master';
    $PARAMETERS['administration']['esiti']['access_level'] = ESITI_PERM;
}

$PARAMETERS['administration']['email']['text'] = 'Gestione email';
$PARAMETERS['administration']['email']['url'] = 'main.php?page=gestione_cambio_email';
$PARAMETERS['administration']['email']['access_level'] = MODERATOR;
$PARAMETERS['administration']['skills']['text'] = 'Gestione abilità';
$PARAMETERS['administration']['skills']['url'] = 'main.php?page=gestione_abilita';
$PARAMETERS['administration']['skills']['access_level'] = SUPERUSER;
$PARAMETERS['administration']['plot']['text'] = 'Gestione ambientazione';
$PARAMETERS['administration']['plot']['url'] = 'main.php?page=gestione_ambientazione';
$PARAMETERS['administration']['plot']['access_level'] = SUPERUSER;
$PARAMETERS['administration']['forums']['text'] = 'Gestione bacheche';
$PARAMETERS['administration']['forums']['url'] = 'main.php?page=gestione_bacheche';
$PARAMETERS['administration']['forums']['access_level'] = SUPERUSER;
$PARAMETERS['administration']['guilds']['text'] = 'Gestione gilde e ruoli';
$PARAMETERS['administration']['guilds']['url'] = 'main.php?page=gestione_gilde';
$PARAMETERS['administration']['guilds']['access_level'] = SUPERUSER;
$PARAMETERS['administration']['locations']['text'] = 'Gestione luoghi';
$PARAMETERS['administration']['locations']['url'] = 'main.php?page=gestione_luoghi';
$PARAMETERS['administration']['locations']['access_level'] = SUPERUSER;
$PARAMETERS['administration']['maps']['text'] = 'Gestione mappe';
$PARAMETERS['administration']['maps']['url'] = 'main.php?page=gestione/mappe';
$PARAMETERS['administration']['maps']['access_level'] = SUPERUSER;
$PARAMETERS['administration']['items']['text'] = 'Gestione oggetti';
$PARAMETERS['administration']['items']['url'] = 'main.php?page=gestione_mercato';
$PARAMETERS['administration']['items']['access_level'] = SUPERUSER;
$PARAMETERS['administration']['levels']['text'] = 'Gestione permessi';
$PARAMETERS['administration']['levels']['url'] = 'main.php?page=gestione_permessi';
$PARAMETERS['administration']['levels']['access_level'] = SUPERUSER;
$PARAMETERS['administration']['races']['text'] = 'Gestione razze';
$PARAMETERS['administration']['races']['url'] = 'main.php?page=gestione_razze';
$PARAMETERS['administration']['races']['access_level'] = SUPERUSER;
$PARAMETERS['administration']['rules']['text'] = 'Gestione regolamento';
$PARAMETERS['administration']['rules']['url'] = 'main.php?page=gestione_regolamento';
$PARAMETERS['administration']['rules']['access_level'] = SUPERUSER;
$PARAMETERS['administration']['maintenance']['text'] = 'Manutenzione';
$PARAMETERS['administration']['maintenance']['url'] = 'main.php?page=gestione/manutenzione';
$PARAMETERS['administration']['maintenance']['access_level'] = SUPERUSER;
$PARAMETERS['administration']['config']['text'] = 'Configurazioni';
$PARAMETERS['administration']['config']['url'] = 'main.php?page=gestione_config';
$PARAMETERS['administration']['config']['access_level'] = SUPERUSER;
$PARAMETERS['administration']['token']['text'] = 'Token iscrizione';
$PARAMETERS['administration']['token']['url'] = 'main.php?page=gestione_token';
$PARAMETERS['administration']['token']['access_level'] = SUPERUSER;
