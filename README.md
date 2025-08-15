# GDRCD 5.6.0.6 - Users Manual

## VERSIONE STABILE

Le corrente versione è stabile, ma ancora soggetta ed ampliamenti,

tuttavia dalla fase di testing della release beta è stata
comprovato il corretto funzionamento del software.
Le eventuali correzioni saranno rilasciate come nuove versioni
dei file correnti e sarà poissibile applicarle su un sito già pubblicato
semplicemente sostituendo i file con i corrispettivi aggiornati.

Saranno auspicabilmente anche introdotte nuove funzioni ed
ampliate le correnti su suggerimento dell'utenza.


## COMMUNITY DI SVILUPPO

GDRCD è un progetto Open Source e come tale è aperto ai contributi.
Il [core team](https://github.com/orgs/GDRCD/people) di GDRCD si riserva di vagliare personalmente eventuali
modifiche da inserire direttamente nel core ufficiale del programma,
ma nulla vieta di rilasciarle come MODs o di produrre release alternative.

Rispetto alle precedenti versioni di GDRCD è introdotta la
possibilità di rilasciare skin grafiche per il prodotto, che,
senza andare ad alterare il codice PHP o l'HTML, possono essere
installate sul proprio sito ed attivate cambiando immediatamente
tutta la veste grafica, senza ulteriori interventi.

Invito quanti desiderino cimentarsi nello sviluppo di MODs di
rispettare la struttura base del sistema, che prevede l'utilizzo
del file di vocabolario (`IT-it.vocabulary.php`) per contenere le
i messaggi di output, piuttosto che inserirli direttamente nel codice,
l'utilizzo dei css per la formattazione della pagina, e che il mod sia
un frammento di codice da includere in fase di load della pagina
in uno dei div preposti di main.php come le pagine contenute nella
cartella `pages`. Le modifiche proposte al core che non rispettino
questi standard non verranno prese in considerazione.



## PROCEDURA DI INSTALLAZIONE

1. Scaricare il file zip della [release](https://github.com/GDRCD/GDRCD/releases) più recente e decomprimerlo.

2. Prendere tutti i file e tutte le cartelle contenute nell'archivio.

3. Fare l'upload di tutti i file e tutte le cartelle sul tuo spazio
    web. Potresti aver bisogno di un programma come Filezilla.

    > **ATTENZIONE:** La struttura delle cartelle deve rimanere invariata rispetto al contenuto del file zip scaricato, altrimenti il sito non funzionerà.

    La struttura delle cartelle è la seguente:
    ```
    docs
    imgs
    ├─ avatars
    ├─ icons
    ├─ pegi
    ├─ images
    ├─ locations
    includes
    pages
    layouts
    sounds
    themes
    ├─ advanced
    |  ├─ home
    |  ├─ imgs
    |     ├─ guilds
    |     ├─ items
    |     ├─ locations
    |     ├─ maps
    |     ├─ menus
    |     ├─ races
    vocabulary
    ```

    ---

    `/imgs`: La cartella contiene alcune immagini utilizzate dal sistema, in
    particolare icone di sistrma. Se si desidera modificarle è possibile
    agire sul contenuto di questa cartella anche se non è parte dei temi.
    La cartella pegi contiene i simboli pegi per la homepage, la cartella
    avatar contiene l'immagine base dell'avatar nelle schede e la figura
    su cui vengono ubicati gli oggetti indossati.

    ---

    `/includes`: La cartella includes contiene file essenziali al funzionamento
    del programma, che vengono inclusi ad ogni visualizzazione delle pagine.

    ---

    `/pages`: La cartella pages contiene i blocchi di codice da richiamare
    nella finestra principale con le singole funzioni. Vengono richiamati
    da `main.php` attraverso il parametro page, nella barra degli indirizzi.
    Ad esempio, l'indirizzo `http://www.sito.it/main.php?page=uffici`
    richiama i servizi della pagina uffici.inc.php presente in pages.
    Per aggiungee nuove funzioni al sito è consigliabile produrre nuovi
    blocchi di codice richiamabili con questo meccanismo da posizione in
    questa cartella. A prescindere dal contenuto, tali file dovranno avere
    l'estensione `.inc.php`.

    ---

    `/themes`: La cartella themes contiene i temi per la grafica del sito.
    Ciascun tema ha il proprio nome che corrisponde al nome della cartella
    relativa, che contiene i file css con le impostazioni e la cartella imgs.
    Nella cartella `imgs` sono posizionati, solitamente gli sfondi del sito,
    mentre, nelle sue sottocartelle sono posizionati, risperttivamente, le
    icone di gilda `guilds`, le immagini degli oggetti `items`, le immagini
    dei luoghi `locations`, le immagini delle mappe `maps` e le immagini e
    iconde di razza `races`. Il programma cerca automaticamente le immagini
    specificate per queste aree del sito in tali cartelle del tema
    selezionato. Ad esempio, se specifichiamo l'immagine `razza01.jpg` come
    icona per una razza e abbiamo selezionato il tema `sfumature_blu` ogni
    volta che visualizziamo nel sito l'icona di tale razza, l'immagine
    verrà automaticamente cercata in
    `http://www.sito.it/themes/sfumature_blu/imgs/races`.

4. Modificare il file `config.inc.php`. Questo passo è cruciale.
    A prima vista il file è lungo e complicato, ma in realtà è molto
    semplice. E' soltanto un elenco di parametri che puoi impostare a tuo
    piacimento per modificare le impostazioni del tuo gioco e per attivare
    o disattivare funzioni che ti interessano o che non vuoi.

    Assicurati di aver letto attentamente tutto il testo di aiuto che il file
    contiene perché ti spiegherà, parametro per parametro, la sua funzione.

    > **ATTENZIONE**: Molto spesso la modifica che vuoi ottenere può essere
    fatta modificando il file **config.inc.php**, oppure il file **IT-it.vocabulary.php**
    (se vuoi cambiare un testo all'interno del sito), oppure mediante i file css
    della cartella **themes/nome-tema**, se è una modifica di tipo grafico.

    > **IMPORTANTE**: La prima cosa che devi modificare sono i parametri di
    connessione al database mysql, in quanto essenziali al funzionamento:

    ```php
    $PARAMETERS['database']['username'] = 'username';
    $PARAMETERS['database']['password'] = 'password';
    $PARAMETERS['database']['database_name'] = 'nomedatabase';
    $PARAMETERS['database']['url'] = 'indirizzo';
    ```

    > **CONSIGLIO**: Molti servizi di hosting consentono di lasciare la password vuota.
    Se possibile, sarebbe consigliabile procedere in questo modo.

    Al posto di `username`, `password`, `nomedatabase` e `indirizzo`, tra le virgolette,
    bisognerà inserire i parametri che ti sono stati comunicati quando hai registrato
    il tuo spazio web.

    Subito sotto ci sono le informazioni relative a sito e webmaster. Sei invitato
    a compilarli interamente riportando il tuo nome e cognome reali per correttezza.

5. A questo punto prova a visitare il tuo sito. Dovrebbe apparirti un
messaggio di errore. Se ti dice che il database è vuoto e che dovresti fare
l'installazione procedi cliccando sul link o visita la pagina all'indirizzo
`http://miosito.it/installer.php`. Questo dovrebbe installare il database.
Se invece il messaggio ti informa che non è possibile trovare il database
allora qualcosa è andato storto. Probabilmente non hai scritto correttamente
i parametri di connessione. Sono corretti? Sicuro di avere un database
attivato nel tuo spazio web?

6. Adesso dovresti avere il database installato. Prova ad accedere di nuovo
alla homepage. Se si visualizza la homepage con il form di login e tutto
allora l'installazione è completa. Prova ad effettuare il login come
amministratore del sito:

    > **username**: Super \
    **password**: super

    Se il login viene effettuato tutto è andato liscio, altrimenti c'è qualcosa
    che non va. Prova ad installare manualmente il database con il servizio di
    phpmyadmin offerto dal tuo spazio web, scegliendo l'opzione "importa" e caricando
    il file `gdrcd_db.sql`.

    > **ATTENZIONE**: Dopo aver importato manualmente il database sarà comunque
    necessario visitare manualmente la pagina **installer.php** per finalizzare
    correttamente l'installazione del database.

7. Finalmente dovresti essere loggato nel tuo sito. Sei l'amministratore ed
hai accesso al pannello di gestione. Adesso dovresti creare il tuo mondo,
compilando un regolamento, un ambientazione, creando razze, luoghi, mappe,
oggetti, ecc. Tutto questo è possibile farlo dal menu' `Gestione`. Dovrebbe
essere abbastanza intuitivo ed in questa breve guida non saranno pertanto
illustrati i vari pannelli.

8. Adesso manca soltanto la grafica e qui ti è richiesta almeno un poco di
competenza con il CSS. Non ce l'hai? Non allarmarti, c'è una scorciatoia.
GDRCD ti mette a disposizione di default la skin `Advanced`, realizzata da [`blancks`](https://github.com/blancks),
che fornisce un ottimo punto di partenza per realizzare la tua personale interfaccia,
giocando con CSS.
Altrimenti potresti scaricare e installare skin realizzate da altri utenti,
semplicemente facendo l'upload della cartella della skin nella cartella
`themes` ed andando ad attivarla nel file `config.inc.php` alla voce `Temi`.
Se sei particolarmente bravo con i Temi, puoi permetterti anche di averne più di uno,
fornendo al giocatore la possibilità di scegliere il suo preferito!


## BUON LAVORO E BUON DIVERTIMENTO

Questa dovrebbe essere davvero facile fare la tua land, confidiamo che
ne verrai a capo. Buon Divertimento!


Il Team di GDRCD.

*Questo file è stato originariamente redatto da Fabrizio Pedani e in seguito revisionato da: blancks, breaker, gianni10049 e kasui92*
