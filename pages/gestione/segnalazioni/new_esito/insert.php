<?php
/*Inserimento di un nuovo blocco*/
if ($_POST['op']=='insert') {

    if (isset($_POST['note'])===FALSE) { $note = 'Nessuna';} else { $note = gdrcd_filter('in',$_POST['note']); }

    if ($_POST['pg']!==$_SESSION['login']) {
        /*Eseguo l'inserimento del blocco*/
        gdrcd_query("INSERT INTO blocco_esiti (titolo, pg, autore,master) 
            VALUES 
            ('".gdrcd_filter('in',$_POST['titolo'])."','".gdrcd_filter('in', $_POST['pg'])."', 
            '".gdrcd_filter('in',$_SESSION['login'])."','".gdrcd_filter('in',$_SESSION['login'])."') ");

    } else {
        /*Eseguo l'inserimento del blocco*/
        gdrcd_query("INSERT INTO blocco_esiti (titolo, pg, autore) 
            VALUES 
            ('".gdrcd_filter('in',$_POST['titolo'])."','".gdrcd_filter('in', $_POST['pg'])."', 
            '".gdrcd_filter('in',$_SESSION['login'])."') ");
    }
    $load_blocco=gdrcd_query("SELECT id, master FROM blocco_esiti WHERE titolo='".gdrcd_filter('in',$_POST['titolo'])."' 
    ORDER BY id DESC LIMIT 1 ");

    /*Eseguo l'inserimento del singolo esito*/
    gdrcd_query("INSERT INTO esiti (titolo, pg, autore, contenuto, noteoff, master, id_blocco, letto_master) 
            VALUES 
            ('".gdrcd_filter('in',$_POST['titolo'])."','".gdrcd_filter('in', $_POST['pg'])."',
            '".gdrcd_filter('in', $_SESSION['login'])."','".gdrcd_filter('in', $_POST['contenuto'])."',
            '".$note."', '".gdrcd_filter('in', $load_blocco['master'])."',  ".$load_blocco['id'].", 1) ");

    if ($_POST['pg']!==$_SESSION['login']) {
        #Invio messaggio di avviso al giocatore
        $text ='Hai ricevuto un nuovo esito dal Master '.gdrcd_filter('out', $_SESSION['login']).' per la serie di 
        esiti intitolata: "'.gdrcd_filter('out', $_POST['titolo']).'" ';
        $dest = $_POST['pg'];

        #Invio
        #gdrcd_query("INSERT INTO messaggi (mittente, destinatario, spedito, testo) VALUES ('Sistema esiti', '" . gdrcd_filter('in', $dest) . "', NOW(), '" . gdrcd_filter('in', $text). "')");
    }
    ?>
    <div class="warning">
        <?php echo gdrcd_filter('out',$MESSAGE['warning']['inserted']);?>
    </div>
    <br><a href="main.php?page=gestione_segnalazioni&segn=esiti_master">Torna indietro</a>

<?php }