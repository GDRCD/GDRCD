<?php
/*Inserimento di un nuovo blocco*/
if ($_POST['op']=='insert') {

    if (isset($_POST['note'])===FALSE) { $note = 'Nessuna';} else { $note = gdrcd_filter('in',$_POST['note']); }

        /*Eseguo l'inserimento del blocco*/
        gdrcd_query("INSERT INTO blocco_esiti (titolo, pg, autore) 
            VALUES 
            ('".gdrcd_filter('in',$_POST['titolo'])."','".gdrcd_filter('in',$_SESSION['login'])."', 
            '".gdrcd_filter('in',$_SESSION['login'])."') ");

    $load_blocco=gdrcd_query("SELECT id FROM blocco_esiti WHERE titolo='".gdrcd_filter('in',$_POST['titolo'])."' 
    ORDER BY id DESC LIMIT 1 ");

    /*Eseguo l'inserimento del singolo esito*/
    gdrcd_query("INSERT INTO esiti (titolo, pg, autore, contenuto, noteoff, id_blocco, letto_pg) 
            VALUES 
            ('".gdrcd_filter('in',$_POST['titolo'])."','".gdrcd_filter('in',$_SESSION['login'])."',
            '".gdrcd_filter('in', $_SESSION['login'])."','".gdrcd_filter('in', $_POST['contenuto'])."',
            '".$note."',  ".$load_blocco['id'].", 1) ");

    ?>
    <div class="warning">
        <?php echo gdrcd_filter('out',$MESSAGE['warning']['inserted']);?>
    </div>
    <!-- Link a piè di pagina -->
    <div class="link_back">
        <a href="main.php?page=servizi_esiti">Torna indietro</a>
    </div>
<?php }
?>