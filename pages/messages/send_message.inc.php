<?php
if (gdrcd_filter('get', $_POST['multipli']) == 'singolo') {
    $check_dest = explode(',', gdrcd_filter('get', $_POST['destinatario']));
    $destinat = trim($check_dest[0]);
    if (gdrcd_filter('get', $_POST['url']) != "") {
        $_POST['testo'] = $_SESSION['login'] . ' ti ha segnalato questo [url=' . $_POST['url'] . ']link[/url].';
    }//if

    $result = gdrcd_query("SELECT nome FROM personaggio WHERE nome = '" . $destinat . "'", 'result');
    if ((gdrcd_query($result, 'num_rows') > 0) && (empty($destinat) === false)) {
        gdrcd_query("INSERT INTO messaggi (mittente, destinatario, spedito, testo) VALUES ('" . $_SESSION['login'] . "', '" . gdrcd_capital_letter(gdrcd_filter('in', $destinat)) . "', NOW(), '" . gdrcd_filter('in', $_POST['testo']) . "')");
        gdrcd_query("INSERT INTO backmessaggi (mittente, destinatario, spedito, testo) VALUES ('" . $_SESSION['login'] . "', '" . gdrcd_capital_letter(gdrcd_filter('in', $destinat)) . "', NOW(), '" . gdrcd_filter('in', $_POST['testo']) . "')");
    }//if
} else {
    if ($_POST['multipli'] == 'presenti') {
        $query = "SELECT personaggio.nome, personaggio.cognome, personaggio.permessi, personaggio.sesso, personaggio.id_razza, razza.sing_m, razza.sing_f, razza.icon, personaggio.disponibile, personaggio.online_status, personaggio.is_invisible, personaggio.ultima_mappa, personaggio.ultimo_luogo, personaggio.posizione, personaggio.ora_entrata, personaggio.ora_uscita, personaggio.ultimo_refresh, mappa.stanza_apparente, mappa.nome as luogo, mappa_click.nome as mappa FROM personaggio LEFT JOIN mappa ON personaggio.ultimo_luogo = mappa.id LEFT JOIN mappa_click ON personaggio.ultima_mappa = mappa_click.id_click LEFT JOIN razza ON personaggio.id_razza = razza.id_razza WHERE personaggio.ora_entrata > personaggio.ora_uscita AND DATE_ADD(personaggio.ultimo_refresh, INTERVAL 4 MINUTE) > NOW() ORDER BY personaggio.is_invisible, personaggio.ultima_mappa, personaggio.ultimo_luogo, personaggio.nome";
        $result = gdrcd_query($query, 'result');

        while ($record = gdrcd_query($result, 'fetch')) {
            gdrcd_query("INSERT INTO messaggi (mittente, destinatario, spedito, testo) VALUES ('" . $_SESSION['login'] . "', '" . $record['nome'] . "', NOW(), '" . gdrcd_filter('in', $_POST['testo']) . "')");
        }
    } elseif ($_POST['multipli'] == 'multiplo') {
        $check_dest = explode(',', $_POST['destinatario']);
        $query = "INSERT INTO messaggi (mittente, destinatario, spedito, testo) VALUES";
        foreach ($check_dest as $destinat) {
            $destinat = trim($destinat);

            $result = gdrcd_query("SELECT nome FROM personaggio WHERE nome = '" . gdrcd_filter('in', $destinat) . "'", 'result');
            if (gdrcd_query($result, 'num_rows') > 0) {
                $query .= " ('" . $_SESSION['login'] . "', '" . gdrcd_capital_letter(gdrcd_filter('in', $destinat)) . "', NOW(), '" . gdrcd_filter('in', $_POST['testo']) . "'),";
            }
        }
        $query = substr($query, 0, -1);
        gdrcd_query($query);
        /** * Bugfix: commentato la stampa della variabile $query. In caso di messaggio multiplo stampava
         * l'ultima query eseguita.
         * @author Rhllor
         */
    } elseif ($_POST['multipli'] == "broadcast") {
        $personaggio = gdrcd_query("SELECT * FROM personaggio where nome = '" . $_SESSION['login'] . "'");

        if($personaggio['permessi'] >= MODERATOR) {
            $query = gdrcd_query("SELECT nome FROM personaggio", 'result');
            while ($row = gdrcd_query($query, 'fetch'))
            {
                gdrcd_query("INSERT INTO messaggi (mittente, destinatario, spedito, testo) VALUES ('" . $_SESSION['login'] . "', '" . $row['nome'] . "' , NOW(), '" . gdrcd_filter('in', $_POST['testo']) . "')");
            }
        }
        gdrcd_query($query, 'free');
    } elseif (is_numeric($_POST['multipli']) === true) {
        gdrcd_query("INSERT INTO messaggi (mittente, destinatario, spedito, testo) VALUES ('" . $_SESSION['login'] . "', '" . $_POST['multipli'] . "', NOW(), '" . gdrcd_filter('in', $_POST['testo']) . "')");
    } elseif (empty($_POST['destinatario']) === false) {
        gdrcd_query("INSERT INTO messaggi (mittente, destinatario, spedito, testo) VALUES ('" . $_SESSION['login'] . "', '" . gdrcd_capital_letter(gdrcd_filter('in', $_POST['destinatario'])) . "', NOW(), '" . gdrcd_filter('in', $_POST['testo']) . "')");
        gdrcd_query("INSERT INTO backmessaggi (mittente, destinatario, spedito, testo) VALUES ('" . $_SESSION['login'] . "', '" . gdrcd_capital_letter(gdrcd_filter('in', $_POST['destinatario'])) . "', NOW(), '" . gdrcd_filter('in', $_POST['testo']) . "')");
    }
}//else
?>
    <div class="warning">
        <?php echo $PARAMETERS['names']['private_message']['sing'] . $MESSAGE['interface']['messages']['sent']; ?>
    </div>
<?php