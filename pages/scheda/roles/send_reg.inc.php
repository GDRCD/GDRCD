<?php
if ($_REQUEST['pg'] == $_SESSION['login']) {
#Date
    $date_b = gdrcd_filter('num', $_POST['anno']) . '-'
        . sprintf('%02s', gdrcd_filter('num', $_POST['month_b'])) . '-'
        . sprintf('%02s', gdrcd_filter('num', $_POST['day_b'])) . ' '
        . sprintf('%02s', gdrcd_filter('num', $_POST['hour_b'])) . ':'
        . sprintf('%02s', gdrcd_filter('num', $_POST['minut_b'])) . ':00';
    $date_a = gdrcd_filter('num', $_POST['anno']) . '-'
        . sprintf('%02s', gdrcd_filter('num', $_POST['month_a'])) . '-'
        . sprintf('%02s', gdrcd_filter('num', $_POST['day_a'])) . ' '
        . sprintf('%02s', gdrcd_filter('num', $_POST['hour_a'])) . ':'
        . sprintf('%02s', gdrcd_filter('num', $_POST['minut_a'])) . ':00';


#Recupero le azioni e faccio i controlli

#recupero azioni nell'intervallo

    $query = gdrcd_query("	SELECT chat.id, chat.mittente
						FROM chat
						INNER JOIN mappa ON mappa.id = chat.stanza
						LEFT JOIN personaggio ON personaggio.nome = chat.mittente 
						WHERE stanza = " . $_POST['luogo'] . " AND ora >= '" . gdrcd_filter('in', $date_a) . "' 
						AND ora <= '" . gdrcd_filter('in', $date_b) . "' 
						AND (tipo = 'A' || tipo = 'P' || tipo = 'M' || tipo = 'N') 
						GROUP BY mittente ORDER BY ora", 'result');

    $tot= gdrcd_query($query, 'num_rows');
    echo $tot;


    switch ($tot){
        case '0':
            $message = 'Non puoi segnalare questa giocata';
            break;
        default:
            $i = 0;
            while ($prow = gdrcd_query($query, 'fetch')) {
                $part[$i] = $prow['mittente'];
                $i++;
            }
            $listapart = implode(',', $part);
            $total = count($part);
            $singolo = substr_count($listapart, $_REQUEST['pg']); #conta le volte in cui il partecipante è presente in questa stringa

            #azioni del pg dalla partenza registrazione
            $start = gdrcd_query("SELECT chat.id, chat.mittente, chat.destinatario, chat.tipo, chat.ora
                            FROM chat
                            INNER JOIN mappa ON mappa.id = chat.stanza
                            LEFT JOIN personaggio ON personaggio.nome = chat.mittente 
                            WHERE stanza = " . $_POST['luogo'] . " AND ora >= '" . gdrcd_filter('in', $date_a) . "' 
                            AND ora <= '" . gdrcd_filter('in', $date_b) . "' 
                            AND mittente = '" . gdrcd_filter('in', $_REQUEST['pg']) . "'  
                            AND (tipo = 'A' || tipo = 'P' || tipo = 'M' || tipo = 'N') ORDER BY ora ", 'result');
            $record = gdrcd_query($start, 'fetch');
            $num_az = gdrcd_query($start, 'num_rows');
            $time_end = gdrcd_query("SELECT chat.id, chat.ora
						FROM chat
						INNER JOIN mappa ON mappa.id = chat.stanza
						LEFT JOIN personaggio ON personaggio.nome = chat.mittente 
						WHERE stanza = " . $_POST['luogo'] . " AND ora >= '" . gdrcd_filter('in', $date_a) . "' 
						AND ora <= '" . gdrcd_filter('in', $date_b) . "' 
						AND mittente = '" . gdrcd_filter('in', $_REQUEST['pg']) . "' 
						AND (tipo = 'A' || tipo = 'P' || tipo = 'M' || tipo = 'N') ORDER BY ora DESC LIMIT 1 ", 'result');
            $rte = gdrcd_query($time_end, 'fetch');
            $end_time = date("Y-m-d H:i:s", strtotime("+1 hours", strtotime($rte['ora'])));

            $time_start = gdrcd_query("SELECT chat.id, chat.ora
						FROM chat
						INNER JOIN mappa ON mappa.id = chat.stanza
						LEFT JOIN personaggio ON personaggio.nome = chat.mittente 
						WHERE stanza = " . $_POST['luogo'] . " AND ora >= '" . gdrcd_filter('in', $date_a) . "' 
						AND ora <= '" . gdrcd_filter('in', $date_b) . "' 
						AND mittente = '" . gdrcd_filter('in', $_REQUEST['pg']) . "' 
						AND (tipo = 'A' || tipo = 'P' || tipo = 'M' || tipo = 'N') ORDER BY ora ASC LIMIT 1 ", 'result');
            $rts = gdrcd_query($time_start, 'fetch');
            $start_time = date("Y-m-d H:i:s", strtotime("-1 hours", strtotime($rts['ora'])));

#Conto le ore dell'intervallo
            $diff = abs((strtotime($date_b)) - (strtotime($date_a))) / 3600;

#Verifico l'assenza di altre segnalazioni in questo orario
            $segnal = gdrcd_query("SELECT id FROM segnalazione_role WHERE data_inizio >= '" . gdrcd_filter('in', $date_a) . "' 
    AND data_fine <= '" . gdrcd_filter('in', $date_b) . "' AND mittente = '" . gdrcd_filter('in', $_REQUEST['pg']) . "' 
    AND (conclusa = 1 || conclusa = 0) ORDER BY data_inizio ASC ", 'result');
            $r_seg = gdrcd_query($segnal, 'num_rows');

            if ($total == 1) {
                #Imposta messaggio
                $message = 'Non puoi segnalare una giocata con un solo partecipante';
            } #Minimo 3 azioni
            else if ($num_az < REG_MIN_AZIONI) {
                #Imposta messaggio
                $message = 'Non hai inviato azioni sufficienti ad una registrazione';
            } #Controllo che l'orario non si sovrapponga ad altre segnalazioni fatte (doppia segnalazione)
            else if ($r_seg > 0) {
                #Imposta messaggio
                $message = 'C\'è già una registrazione che combacia con queste date';
            } #Verifico che l'arco di tempo di registrazione sia <= di 12h
            else if ($diff > 12) {
                #Imposta messaggio
                $message = 'Hai scelto un range di tempo troppo ampio';
            } #Se la data di fine inserita è prima di quella di inizio
            else if ($date_b < $date_a) {
                #Imposta messaggio
                $message = 'Seleziona le date correttamente';
            }
            else if (($date_b > $end_time) && ($date_a < $start_time)) {
                /*Invio la segnalazione giocata */
                gdrcd_query("INSERT INTO segnalazione_role (data_inizio, data_fine, mittente, partecipanti, stanza, conclusa, tags, quest ) 
						VALUES ( '" . gdrcd_filter('in', $rts['ora']) . "', '" . gdrcd_filter('in', $rte['ora']) . "', 
						'" . gdrcd_filter('in', $_REQUEST['pg']) . "','" . gdrcd_filter('in', $listapart) . "', 
						" . gdrcd_filter('num', $_POST['luogo']) . ", 1, '" . gdrcd_filter('in', $_POST['ab']) . "', 
						'" . gdrcd_filter('in', $_POST['quest']) . "' )");

                #Imposta messaggio
                $message = 'La registrazione è stata salvata sulla base della tua prima ed ultima azione in chat';

            } #Segnalazione valida entro due ore dall'ultima azione
            else if ($date_b > $end_time) {
                /*Invio la segnalazione giocata */
                gdrcd_query("INSERT INTO segnalazione_role (data_inizio, data_fine, mittente, partecipanti, stanza, conclusa, tags, quest ) 
					    VALUES ( '" . gdrcd_filter('in', $date_a) . "', '" . gdrcd_filter('in', $rte['ora']) . "', 
					    '" . gdrcd_filter('in', $_REQUEST['pg']) . "','" . gdrcd_filter('in', $listapart) . "', 
					    " . gdrcd_filter('num', $_POST['luogo']) . ", 1, '" . gdrcd_filter('in', $_POST['ab']) . "', 
					    '" . gdrcd_filter('in', $_POST['quest']) . "' )");

                #Imposta messaggio
                $message = 'La registrazione è stata salvata sulla base della tua ultima azione in chat';

            } #Segnalazione in caso di margine di inizio troppo largo
            else if ($date_a < $start_time) {
                /*Invio la segnalazione giocata */
                gdrcd_query("INSERT INTO segnalazione_role (data_inizio, data_fine, mittente, partecipanti, stanza, conclusa, tags, quest ) 
					    VALUES ( '" . gdrcd_filter('in', $rts['ora']) . "', '" . gdrcd_filter('in', $date_b) . "', 
					    '" . gdrcd_filter('in', $_REQUEST['pg']) . "','" . gdrcd_filter('in', $listapart) . "', 
					    " . gdrcd_filter('num', $_POST['luogo']) . ", 1, '" . gdrcd_filter('in', $_POST['ab']) . "', 
					    '" . gdrcd_filter('in', $_POST['quest']) . "' )");


                #Imposta messaggio
                $message = 'La registrazione è stata salvata sulla base della tua prima azione in chat';
            } #Procedo
            else if ($total > 1) {

                /*Invio la segnalazione giocata */
                gdrcd_query("INSERT INTO segnalazione_role (data_inizio, data_fine, mittente, partecipanti, stanza, conclusa, tags, quest ) 
						VALUES ( '" . gdrcd_filter('in', $date_a) . "', '" . gdrcd_filter('in', $date_b) . "', 
						'" . gdrcd_filter('in', $_REQUEST['pg']) . "','" . gdrcd_filter('in', $listapart) . "', 
						" . gdrcd_filter('num', $_POST['luogo']) . ", 1, '" . gdrcd_filter('in', $_POST['ab']) . "', 
						'" . gdrcd_filter('in', $_POST['quest']) . "' )");

                #Imposta messaggio
                $message = 'Registrazione inviata con successo';
            }





    }



    /*Confermo l'operazione*/
    echo '<div class="warning">' . $message . '</div>
		<div class="link_back">
		    <a href="main.php?page=scheda_roles&pg=' . gdrcd_filter('in', $_REQUEST['pg']) . '">'. gdrcd_filter('out',
            $MESSAGE['interface']['sheet']['link']['back_roles']).'
		    </a>
		</div>';


} else {
    echo '<div class="warning">Non puoi inserire registrazioni nella scheda altrui</div>';
}
?>
