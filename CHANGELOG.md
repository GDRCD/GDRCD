
# Change Log v5.6
Tutti i cambiamenti dalla versione 5.5 verranno inseriti qui.

## 2021-10-15

### Added
- [Main] Ora è possibile inserire una immagine in caso di nuovo messaggio nel forum, attraverso il parametro $PARAMETERS['names']['forum']['image_file_new'];
- [GDRCD] Aggiunti metodi per il debug delle variabili;
- [GDRCD] Integrato metodo gdrcd_stmt per i prepared statements;
- [GDRCD] Ora è possibile inserire più di un tema su GDRCD, la scelta del tema viene fatta in homepage al momento del login dell'utente;
- [GDRCD] Aggiunti nuovi stili per i pannelli di gestione e servizi
- [CHAT] Aggiunto pannello per la Registrazione delle giocate
- [SCHEDA] Aggiunto nuovo menu per la consultazione delle giocate registrate del personaggio, realizzata da @Jansna
- [GESTIONE] Aggiunto nuovo menù per la gestione delle giocate registrate
- [MESSAGGI] Aggiunta la funzionalità di 'risposta' per i Messaggi, ora non più integrata nella creazione del messaggio
- [MESSAGGI] Aggiunti i campi 'Oggetto' e 'Tipo' ai messaggi, adottando il sistema del pacchetto 'Messaggi 2.0' di @Seralia e i suggerimenti di @Jansna
- [GDRCD] Aggiunti nuovi stili per tabelle generiche fatte con \<div> al posto di \<table>
- [CHAT] Aggiunto pannello per la Registrazione delle giocate
- [SERVIZI] Aggiunti nuovi menù per la gestione e richiesta Esiti Master
- [GDRCD] Aggiunto case assoc in gdrcd_query
- [LUOGO] Aggiunte le fasi lunari tra le informazioni del luogo, adottando il sistema del pacchetto 'Fasi lunari' di @Haruka
- [SCHEDA] Aggiunta la sezione Diari nella scheda
- [GDRCD] Aggiunta possibilita' di creare un file override dei config per sistemi locali

### Changed
- [Main] Allineata gestione nuovo messaggio nei forum con nuovo messaggio;
- [GDRCD] I metodi per la creazione delle Modali sono stati spostati in un file a parte, in modo da migliorare la loro gestione;
- [Chat] Ora è possibile aprire la scheda del personaggio dalla chat cliccando sull'avatar_chat;
- [Messaggi] Aggiornato il metodo per il salvataggio dell'ultimo messaggio visualizzato;
- [Messaggi] Ripristinata la funzione "Cancella tutti i messaggi letti", ora li cancella solo per chi visualizza il messaggio e vale sia per gli inviati che per i ricevuti;
- [SERVIZI] Aggiornata la funzionalità dell'Anagrafe, adottando il sistema del pacchetto 'Anagrafe & Prestavolti' di @anneth
- [MESSAGGI] È stato rimosso il controllo 'last_instant_message' per la generazione delle notifiche di lettura dei messaggi, preferendo un controllo diretto
- [MESSAGGI] Ricostruite le operazioni di invio del messaggio, ora tutti gli utenti possono inviare messaggi multipli e viene eseguito sempre un controllo sull'esistenza dei personaggi indicati
- [GESTIONE] Migliorata fruibilita' dei Log Chat in gestione
- [SERVIZI] Ripristinata la possibilità di invio messaggio ai personaggi direttamente dai risultati della ricerca
- [LAYOUT] Divisi frame messaggi e bacheche sulla colonna si sinistra ed evitati doppioni
- [GDPR] Oscurate le ultime cifre di tutti gli ip in ogni pagina di gestione


### Fixed
- [Gestione > Manutenzione] Nel processo di cancellazione del personaggio, sono stati allineati i nomi dei campi con quelli presenti in Database;
- [Menu utente > Cambio nome] Corretto un errore che impediva l'operazione di cambio nome di un personaggio;
- [Menu utente > Cambio password] Corretto il controllo sulla email di registrazione inserita, non conforme alla criptazione impostata su GDRCD;
- [Menu utente > Cancella account] Correzioni minori sui commenti;
- [GDRCD] Corretto errore che impediva il caricamento corretto della chat in caso di aggiornamento della pagina;
- [GDRCD] Quando viene chiusa una modale, ora viene cancellato anche il suo precedente contenuto;
- [Forum] Ora gli utenti possono visualizzare solo i Forum a cui sono abilitati;
- [Chat] Risolto invio lancio dado sensa skillsystem attivo;
- [Chat] Risolto problema scalo degli oggetti in chat dopo l'utilizzo;
- [GDRCD] Inibito l'autofocus alla prima apertura di una modale;
- [Scheda] Corretto metodo di salvataggio della email del personaggio dal pannello di amministrazione della scheda;
- [Presenti] I personaggi invisibili sono stati rimossi dal conteggio dei presenti;
- [Messaggi] Rimosse colonne duplicate;
- [Messaggi] Rimossa apertura tag non necessaria nell'invio di un messaggio;
- [GDRCD] Aggiunto un ulteriore controllo sullo stato online del personaggio, per evitare personaggi invisibili.
- [GDRCD] Aggiornato il riferimento al database e tolti auto_increment già iniziati;
- [GDRCD] Corretto il controllo email per il recupero della password;
- [GDRCD] Aggiornate le email fittizie per gli utenti di default;
- [GDRCD] Spostati o rimissi session_start() dove necessario, per spostarlo in includes/required.php;
- [GDRCD] Spostato controllo posizione del personaggio in config.inc.php;
- [CHAT] Corretta errata apertura tag php
- [MESSAGGI] Correzione estesa sull'intero modulo dei Messaggi, correggendo la maggior parte dei bug presenti e ripristinando funzionalità disabilitate
- [MESSAGGI] Rimossi tutti i riferimenti a last_instant_message , per evitare sporcizia;
- [CHAT] Correzioni minori nel pannello Registrazione giocate;
- [GDRCD] Aggiornati i controlli per la rimozione dell'attributo "on" in gdrcd_html_filter
- [GILDE] Inibita la possibilita' per i personaggi di assegnarsi in autonomia le cariche attraverso la pagina di amministrazione corporazioni
- [GDRCD] Correzioni minori


----------------- 

# Change Log v5.5.x
Tutti i cambiamenti dalla versione 5.5 verranno inseriti qui.


## 2020-06-01
  
Elenco delle modifiche al codice
 
### Aggiunto

- __$PARAMETERS['mode']['check_forum'] = 'OFF';__ serve per abilitare la notifica di nuovi messaggiaraldo (thread) nel box messaggi.inc.php.
- __$PARAMETERS['mode']['check_messages'] = 'ON';__ serve per abilitare la notifica di nuovi messaggi privati nel box messaggi.inc.php.
- __$PARAMETERS['text']['check_forum']['new'] = '(Nuovo)';__ permette di personalizzare il testo da far comparire nel caso ci siano nuovi messaggiaraldo (thread)
- __$PARAMETERS['info']['GDRCD'] = '5.5';__ serve per indicare la versione di GDRCD.
- __$PARAMETERS['mode']['exp_in_private'] == 'ON')__ serve per abilitare l'esperienza nelle chat private.
- __$PARAMETERS['settings']['auto_salary'] = 'OFF'__ serve per abilitare l'accredito automatico dello stipendio al primo login.
- __gdrcd_list('personaggi')__ come datalist per aiutare a trovare il nome dei personaggi durante la creazione di un messaggio.
- __$PARAMETERS['settings']['exp_by_chat']['value'] = '0';__ serve per impostare quanti punti experienza assegnare nelle azioni.
- __$PARAMETERS['mode']['allow_new_chat_audio'] = 'ON';__ serve per abilitare l'avviso sonoro quando ci sono nuovi messaggi in chat.
- __COME-FARE-LA-LAND---LEGGIMI!.txt__ aggiunto in home page al fine di ripristinare il link.
### Modificato
  
-  Impostato come unico sistema di criptaggio password BCRYPT
- Refactoring di tutto il codice 
- __forum.inc.php__ e' stato diviso in piu' parti per migliorne la leggibilita'. E' stato creato un controllo delle richieste che verranno inoltrate alla pagina richiesta (/pages/forum).
- __gestione_manutenzione.inc.php__ e' stato divisa in piu' parti per migliorne la leggibilita'. E' stato creato un controllo delle richieste che verranno inoltrate alla pagina richiesta (/pages/gestione/manutenzione).
- __messages_center.inc.php__ e' stato diviso in piu' parti per migliorne la leggibilita'. E' stato creato un controllo delle richieste che verranno inoltrate alla pagina richiesta (/pages/messages).
- __scheda.inc.php__ e' stato divisa in piu' parti per migliorne la leggibilita'. E' stato creato un controllo delle richieste che verranno inoltrate alla pagina richiesta (/pages/scheda).
- __main.css__ l'altezza di .iframe.iframe-messaggi e' stata portata da 20px a 33px.
- __PasswordHash.php__ aggiornato dalla versione 0.3 alla versione 0.5.
- __< audio >__ aggiornato allo standard HTML5 in scheda.inc.php
- __visit.inc.php__ i thread vengono visualizzati in base all'ultima risposta e non piu' in base alla creazione.
- __jQuery__ aggiornato alla versione 3.5.1.
- __jQueryUI__ aggiornato alla versione 1.12.1.
- __HTML5__ modificato il doctype in accordo con lo standard HTML5.

### Rimosso

- Rimosso il sistema di salvataggio delle password in chiaro.
- Rimosso il sistema di criptaggio password SHA-1.
- Rimosso il sistema di criptaggio password MD5.
 
### Corretto
 
- [BugFix] conflitti con merge precedenti.
- [BugFix] __$PARAMETERS['settings']['protection_password'] = 'gdrcd';__ non era stato implementato correttamente.
- [BugFix] Elimina messaggi selezionati.