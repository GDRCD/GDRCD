<?php
/*Inserimento di un nuovo blocco*/
if ($_POST['op']=='insert') {

    if (isset($_POST['note'])===FALSE) { $note = 'Nessuna';} else { $note = gdrcd_filter('in',$_POST['note']); }

    /* Estraggo id_personaggio per il destinatario */
    $dest_pg_data = gdrcd_query("SELECT id_personaggio FROM personaggio WHERE nome = '".gdrcd_filter('in', $_POST['pg'])."'");
    
    if ($dest_pg_data['id_personaggio'] != $_SESSION['id_personaggio']) {
        /*Eseguo l'inserimento del blocco*/
        gdrcd_query("INSERT INTO blocco_esiti (titolo, id_personaggio_destinatario, id_personaggio_autore, id_personaggio_master) 
            VALUES 
            ('".gdrcd_filter('in',$_POST['titolo'])."','".$dest_pg_data['id_personaggio']."', 
            '".$_SESSION['id_personaggio']."','".$_SESSION['id_personaggio']."') ");

    } else {
        /*Eseguo l'inserimento del blocco*/
        gdrcd_query("INSERT INTO blocco_esiti (titolo, id_personaggio_destinatario, id_personaggio_autore) 
            VALUES 
            ('".gdrcd_filter('in',$_POST['titolo'])."','".$dest_pg_data['id_personaggio']."', 
            '".$_SESSION['id_personaggio']."') ");
    }
    $load_blocco=gdrcd_query("SELECT id, id_personaggio_master FROM blocco_esiti WHERE titolo='".gdrcd_filter('in',$_POST['titolo'])."' 
    ORDER BY id DESC LIMIT 1 ");

    /*Eseguo l'inserimento del singolo esito*/
    gdrcd_query("INSERT INTO esiti (titolo, id_personaggio_destinatario, id_personaggio_autore, contenuto, noteoff, id_personaggio_master, id_blocco, letto_master) 
            VALUES 
            ('".gdrcd_filter('in',$_POST['titolo'])."','".$dest_pg_data['id_personaggio']."',
            '".$_SESSION['id_personaggio']."','".gdrcd_filter('in', $_POST['contenuto'])."',
            '".$note."', '".$load_blocco['id_personaggio_master']."',  ".$load_blocco['id'].", 1) ");

    if ($dest_pg_data['id_personaggio'] != $_SESSION['id_personaggio']) {
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