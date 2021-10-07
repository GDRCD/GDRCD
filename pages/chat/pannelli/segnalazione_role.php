<body>
<div class="titolo_box">
    Registrazione giocate
</div>
<?php


if (isset($_SESSION['login'])) {

//Condizione per cui seleziona la facciata "principale" del pannello

$log = gdrcd_query("SELECT * FROM segnalazione_role WHERE mittente='" . gdrcd_filter('in', $_SESSION['login']) . "'
	 AND conclusa = 0 ", "result");
$row = gdrcd_query($log, 'fetch');
$num_log = gdrcd_query($log, 'num_rows');

if ($num_log > 0 && ($row['stanza'] !== $_SESSION['luogo']) && (isset($_POST['op']) === FALSE)) { ?>
    <div class="warning" style="width: auto;">Stai ancora giocando altrove</div>
    <form action="popup.php?page=chat_pannelli_index&pannello=segnalazione_role" method="post">
        <!--- Segnalazione giocate ---->
        <br>
        <div class="form_submit">
            <input type="hidden"
                   name="op"
                   value="leave"/>
            <input type="submit" style="width: auto;"
                   name="submit"
                   onclick="return confirm('Cancellando la registrazione aperta, la giocata in questione non sarà ' +
                    'salvata e non sarà conteggiata nelle segnalazioni. Sicuro di voler procedere?')"
                   value="Cancella la registrazione precedente"/>
        </div>
    </form>
<?php } else if ($num_log == 0 && (isset($_POST['op']) === FALSE)) {

    $mydate = date('Y-m-d H:i:s');
    $mesenow = date('m', strtotime($mydate));
    $giornonow = date('d', strtotime($mydate));
    $oranow = date('h', strtotime($mydate));
    $annonow = date('Y', strtotime($mydate));

    #Schermata di avvio segnalazione giocata. ?>
    <div><br>
        <div class="scheda_titolo">Avvia registrazione <b>ora</b></div>

        <div class="form_info"><b>Attenzione:</b> Ogni giocatore deve inviare una propria registrazione della role.
            La registrazione deve essere avviata all'inizio della giocata e chiusa alla fine per essere valida.
            Giocate con un numero di azioni inferiori a <?=REG_MIN_AZIONI;?>
            non saranno considerate segnalabili. La giocata sarà salvata e farà fede per eventuali segnalazioni ai
            Master.
        </div>
        <br>
        <!--- registrazione giocate ---->

        <form action="popup.php?page=chat_pannelli_index&pannello=segnalazione_role" method="post">

            <div class="form_submit">
                <input type="hidden"
                       name="op"
                       value="start_segn"/>
                <input type="submit" style="width: auto;"
                       name="submit"
                       value="Avvia registrazione"/>
            </div>
        </form>
        <div><b>Oppure</b></div>
        <br>

        <form action="popup.php?page=chat_pannelli_index&pannello=segnalazione_role" method="post">

            <div class='form_field'>
                <!-- Giorno -->
                <div class="reg_titolo">Seleziona la <b>data di inizio</b> giocata</div>
                <div class="form_info">E' possibile selezionare un'ora di inizio role diversa da quella attuale,
                    entro un massimo di 6 ore.<br>
                    Per giocate più vecchie, è consigliabile usare lo strumento di registrazione che si trova in <i>
                        Scheda > Giocate registrate</i>.
                </div>
                <br>
                Data: <select name="day" class="day">
                    <?php for ($i = 1; $i <= 31; $i++) { ?>
                        <option value="<?php echo $i; ?>" <?php if ($i == $giornonow) {
                            echo 'selected';
                        } ?> ><?php echo $i; ?></option>
                    <?php }//for ?>
                </select>
                <!-- Mese -->
                <select name="month" class="month">
                    <?php for ($i = 1; $i <= 12; $i++) { ?>
                        <option value="<?php echo $i; ?>" <?php if ($i == $mesenow) {
                            echo 'selected';
                        } ?> ><?php echo $i; ?></option>
                    <?php }//for ?>
                </select>
                <!-- Anno -->
                <select name="year" class="year">
                    <?php for ($i = 2021; $i <= strftime('%Y') + 20; $i++) { ?>
                        <option value="<?php echo $i; ?>" <?php if ($i == $annonow) {
                            echo 'selected';
                        } ?>><?php echo $i; ?></option>
                    <?php }//for ?>
                </select> <br>
                <!-- Ora -->
                Ora: <select name="hour" class="month">
                    <?php for ($i = 0; $i <= 23; $i++) { ?>
                        <option value="<?php echo $i; ?>" <?php if ($i == $oranow) {
                            echo 'selected';
                        } ?> ><?php echo sprintf('%02s', $i); ?></option>
                    <?php }//for ?>
                </select>:
                <!-- Minuto -->
                <select name="minut" class="month">
                    <?php for ($i = 0; $i <= 60; $i += 5) { ?>
                        <option value="<?php echo $i; ?>"><?php echo sprintf('%02s', $i); ?></option>
                    <?php }//for ?>
                </select>
            </div>
            <div class="form_submit">
                <input type="hidden"
                       name="op"
                       value="start_ret"/>
                <input type="submit" style="width: auto;"
                       name="submit"
                       value="Avvia registrazione"/>
            </div>
        </form>
    </div>
<?php } else if ($num_log > 0 && ($row['stanza'] == $_SESSION['luogo']) && (isset($_POST['op']) === FALSE)) {
    $chat = $_SESSION['luogo'];

    $name = gdrcd_query(" SELECT nome FROM mappa WHERE id = " . gdrcd_filter('num', $chat) . "", 'result');
    $r_nam = gdrcd_query($name, 'fetch');

    $query = gdrcd_query("	SELECT chat.id, chat.mittente, chat.destinatario, chat.tipo, chat.ora
    	FROM chat
    	INNER JOIN mappa ON mappa.id = chat.stanza
    	LEFT JOIN personaggio ON personaggio.nome = chat.mittente 
    	WHERE stanza = " . gdrcd_filter('num', $chat) . " AND ora >= '" . $row['data_inizio'] . "' AND ora <= NOW() 
    	AND (tipo = 'A' || tipo = 'P' || tipo = 'M' || tipo = 'N') GROUP BY mittente ORDER BY ora", 'result');

    $start = gdrcd_query("	SELECT chat.id
    	FROM chat
    	INNER JOIN mappa ON mappa.id = chat.stanza
    	LEFT JOIN personaggio ON personaggio.nome = chat.mittente 
    	WHERE stanza = " . gdrcd_filter('num', $chat) . " AND ora >= '" . $row['data_inizio'] . "' AND ora <= NOW() 
    	AND (tipo = 'A' || tipo = 'P' || tipo = 'M' || tipo = 'N') AND mittente = '" . gdrcd_filter('in', $_SESSION['login']) . "' 
    	ORDER BY ora ", 'result');
    $num_az = gdrcd_query($start, 'num_rows');

    $start_time = date("Y-m-d H:i:s", strtotime("+2 hours", strtotime($row['data_inizio'])));
    $mydate = date('Y-m-d H:i:s'); ?>

    <form action="popup.php?page=chat_pannelli_index&pannello=segnalazione_role" method="post">
        <div style="padding:5px;">
            <div class="form_info"><b>Attenzione:</b> Ogni giocatore deve inviare una propria registrazione della role,
                segnando eventuali note.
                In caso di mancata conclusione della registrazione, non sarà possibile inviare una segnalazione ai GM.
                E' possibile registrare una giocata anche in un secondo momento e non necessariamente per motivi di
                trama.
            </div>
            <br>
            <div class='form_field'>
                <div class="reg_titolo">Conferma i <b>Partecipanti:</b></div>
                <div class="form_info">
                    <?php
                    while ($prow = gdrcd_query($query, 'fetch')) {
                        ?>
                        &nbsp; &nbsp; &raquo; <?php echo gdrcd_filter('out', $prow['mittente']); ?>
                        <input checked type="checkbox"
                               name="parte[]"
                               value="<?php echo gdrcd_filter('out', $prow['mittente']); ?>"
                               style="width:10px;margin: 0;"/>
                    <?php } ?>
                </div>
            </div>
            <br>
            <div class='form_field'>
                <div class="reg_titolo">Inserisci dei <b>tag</b> che riassumano la giocata:</div>
                <input name="ab" type="text" style="margin: auto;" value=""/>
            </div>
            <div class="form_info">I tag possono essere utili per ritrovare rapidamente una role.</div>


            <div class="reg_titolo"> Note quest</div>
            <input name="quest" type="text" style="margin: auto;" value=""/>
        </div>
        <div class="form_info">Compilare con un brevissimo riassunto di cosa fatto in giocata, focalizzandosi sulle
            interazioni con eventuali spunti di trama.
        </div>
        <div class="form_submit">
            <input type="hidden"
                   name="op"
                   value="send_segn"/>
            <input type="submit" style="width: auto;"
                   name="submit"
                   value="Registra la giocata"/>
        </div>
    </form>

    <form action="popup.php?page=chat_pannelli_index&pannello=segnalazione_role" method="post">
        <div class="form_submit">
            <input type="hidden"
                   name="op"
                   value="leave"/>
            <input type="submit"
                   name="submit"
                   onclick="return confirm('Cancellando la registrazione aperta, la giocata in questione non sarà salvata ' +
                    'e non apparirà nelle registrazioni. Sicuro di voler procedere?')"
                   value="Cancella"/>
        </div>


    </form>

    <?php
}
#Chiusura registrazione aperta
if ($_POST['op'] == 'leave') {
    gdrcd_query("UPDATE segnalazione_role SET data_fine = NOW(), conclusa = 2 WHERE id = 
                " . gdrcd_filter('num', $row['id']) . " LIMIT 1");
    /*Confermo l'operazione*/
    echo '<div class="warning" style="width: auto;">La registrazione aperta è stata cancellata </div>

		<div class="link_back"> <a href="popup.php?page=chat_pannelli_index&pannello=segnalazione_role">Torna indietro</a></div>';

}
#Apertura nuova segnalazione
if ($_POST['op'] == 'start_segn') {
    /*Invio la segnalazione giocata */
    gdrcd_query("INSERT INTO segnalazione_role (data_inizio, mittente, stanza, conclusa ) 
	VALUES 
	( NOW(), '" . gdrcd_filter('in', $_SESSION['login']) . "', " . gdrcd_filter('num', $_SESSION['luogo']) . ", 0)");

    /*Confermo l'operazione*/
    echo '<div class="warning" style="width: auto;">La registrazione è stata aperta </div>

		<div class="link_back"> <a href="popup.php?page=chat_pannelli_index&pannello=segnalazione_role">Torna indietro</a></div>';

} #Apertura nuova segnalazione
else if ($_POST['op'] == 'start_ret') {

    $mydate = date('Y-m-d H:i:s');
    $date = gdrcd_filter('num', $_POST['year']) . '-' . sprintf('%02s', gdrcd_filter('num', $_POST['month'])) . '-' . sprintf('%02s', gdrcd_filter('num', $_POST['day'])) . ' ' . sprintf('%02s', gdrcd_filter('num', $_POST['hour'])) . ':' . sprintf('%02s', gdrcd_filter('num', $_POST['minut'])) . ':00';
    $start_time = date("Y-m-d H:i:s", strtotime("-6 hours", strtotime($mydate)));

    $query = gdrcd_query("SELECT chat.id, chat.mittente, chat.destinatario, chat.tipo, chat.ora
		FROM chat
		INNER JOIN mappa ON mappa.id = chat.stanza
		LEFT JOIN personaggio ON personaggio.nome = chat.mittente 
		WHERE stanza = " . gdrcd_filter('num', $_SESSION['luogo']) . " AND ora >= '" . $date . "' AND ora <= NOW() AND mittente = '" . gdrcd_filter('in', $_SESSION['login']) . "'  AND (tipo = 'A' || tipo = 'P' || tipo = 'M' || tipo = 'N') ORDER BY ora ", 'result');
    $record = gdrcd_query($query, 'fetch');
    $num_az = gdrcd_query($query, 'num_rows');

    $time_start = gdrcd_query("SELECT chat.id, chat.ora
		FROM chat
		INNER JOIN mappa ON mappa.id = chat.stanza
		LEFT JOIN personaggio ON personaggio.nome = chat.mittente 
		WHERE stanza = " . gdrcd_filter('num', $_SESSION['luogo']) . " AND ora >= '" . $date . "' AND ora <= NOW() 
		AND mittente = '" . gdrcd_filter('in', $_SESSION['login']) . "'  
		AND (tipo = 'A' || tipo = 'P' || tipo = 'M' || tipo = 'N') ORDER BY ora LIMIT 1 ", 'result');
    $rts = gdrcd_query($time_start, 'fetch');


    if ($date < $start_time) {
        /*Imposto il messaggio*/
        $message = 'Non è possibile selezionare un orario più lontano di sei ore';
    } else if ($num_az == 0) {
        /*Imposto il messaggio*/
        $message = 'Non hai inviato alcuna azione a partire dall\'orario segnalato';
    } else {
        /*Invio la segnalazione giocata */
        gdrcd_query("INSERT INTO segnalazione_role (data_inizio, mittente, stanza, conclusa ) 
			VALUES 
			( '" . $rts['ora'] . "', '" . gdrcd_filter('in', $_SESSION['login']) . "', 
			" . gdrcd_filter('num', $_SESSION['luogo']) . ", 0)");

        /*Imposto il messaggio*/
        $message = 'La registrazione è stata aperta';
    }
    /*Confermo l'operazione*/
    echo '<div class="warning" style="width: auto;">' . $message . '</div>

			<div class="link_back"> <a href="popup.php?page=chat_pannelli_index&pannello=segnalazione_role">Torna indietro</a></div>';


} else if ($_POST['op'] == 'send_segn') {

    $listapart = join(',', $_POST['parte']);
    $total = count($_POST['parte']);
    $singolo = substr_count($listapart, $_SESSION['login']); #conta le volte in cui il partecipante è presente in questa stringa

    $query = gdrcd_query("SELECT chat.id, chat.mittente, chat.destinatario, chat.tipo, chat.ora
        FROM chat
        INNER JOIN mappa ON mappa.id = chat.stanza
        LEFT JOIN personaggio ON personaggio.nome = chat.mittente 
        WHERE stanza = " . gdrcd_filter('num', $_SESSION['luogo']) . " AND ora >= '" . gdrcd_filter('in', $row['data_inizio']) . "' 
        AND ora <= NOW() AND mittente = '" . gdrcd_filter('in', $_SESSION['login']) . "' 
        AND (tipo = 'A' || tipo = 'P' || tipo = 'M' || tipo = 'N') ORDER BY ora ", 'result');
    $record = gdrcd_query($query, 'fetch');
    $num_az = gdrcd_query($query, 'num_rows');

    $time_end = gdrcd_query("SELECT chat.id, chat.ora
        FROM chat
        INNER JOIN mappa ON mappa.id = chat.stanza
        LEFT JOIN personaggio ON personaggio.nome = chat.mittente 
        WHERE stanza = " . gdrcd_filter('num', $_SESSION['luogo']) . " AND ora >= '" . gdrcd_filter('in', $row['data_inizio']) . "' 
        AND ora <= NOW() AND mittente = '" . gdrcd_filter('in', $_SESSION['login']) . "' 
        AND (tipo = 'A' || tipo = 'P' || tipo = 'M' || tipo = 'N') ORDER BY ora DESC LIMIT 1 ", 'result');
    $rte = gdrcd_query($time_end, 'fetch');
    $end_time = date("Y-m-d H:i:s", strtotime("+1 hours", strtotime($rte['ora'])));
    $mydate = date('Y-m-d H:i:s');

    #Condizione 1: Ci deve essere una giocata in corso
    if ($_POST['parte'] == NULL) {
        /*Imposto il messaggio*/
        $message = 'Non c\'è nessuna giocata in corso';
    } #Condizione 2: chi segnala deve essere nella giocata
    else if ($singolo == 0) {
        /*Imposto il messaggio*/
        $message = 'Non puoi segnalare questa giocata';
    } #Condizione: giocata fatta da un solo giocatore.
    else if ($total == 1) {
        /*Imposto il messaggio*/
        $message = 'Non puoi segnalare una giocata con un solo partecipante';

    } #Minimo 5 azioni
    else if ($num_az < REG_MIN_AZIONI) {
        /*Imposto il messaggio*/
        $message = 'Non hai inviato azioni sufficienti ad una registrazione';

    } #Segnalazione valida entro due ore dall'ultima azione
    else if ($mydate > $end_time) {
        /*Aggiorno e chiudo la segnalazione giocata */
        gdrcd_query("UPDATE segnalazione_role SET data_fine = '" . gdrcd_filter('in', $rte['ora']) . "', 
        conclusa = 1, partecipanti = '" . gdrcd_filter('in', $listapart) . "', tags = '" . gdrcd_filter('in', $_POST['ab']) . "', 
        quest = '" . gdrcd_filter('in', $_POST['quest']) . "' WHERE id = " . gdrcd_filter('num', $row['id']) . " ");

        /*Imposto il messaggio*/
        $message = 'La registrazione è stata salvata sulla base della tua ultima azione in chat';
    } #Procedo
    else if ($total > 1) {
        /*Aggiorno e chiudo la segnalazione giocata */
        gdrcd_query("UPDATE segnalazione_role SET data_fine = NOW(), conclusa = 1, 
                             partecipanti = '" . gdrcd_filter('in', $listapart) . "', 
                             tags = '" . gdrcd_filter('in', $_POST['ab']) . "', 
                             quest = '" . gdrcd_filter('in', $_POST['quest']) . "' 
                             WHERE id = " . gdrcd_filter('num', $row['id']) . " ");

        /*Imposto il messaggio*/
        $message = 'Registrazione inviata con successo';
    }
    /*Messaggio di avviso*/
    echo '<div class="warning" style="width: auto;">' . $message . '</div>

		<div class="link_back"> <a href="popup.php?page=chat_pannelli_index&pannello=segnalazione_role">Torna indietro</a></div>';

}
?>
</div>
</body>


<?php
} else {

    die('<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['not_allowed']) . '</div>');

}