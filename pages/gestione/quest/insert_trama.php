<?php /*Inserimento di un nuovo record*/
if ($_POST['op']=='insert_trama' && Functions::get_constant('TRAME_ENABLED')) {

    /*Eseguo l'inserimento*/
    gdrcd_query("INSERT INTO quest_trama (titolo, data, descrizione, autore, stato) VALUES 
            ('".gdrcd_filter('in',$_POST['titolo'])."', NOW(), '".gdrcd_filter('in',$_POST['descrizione'])."', 
            '".gdrcd_filter('in', $_SESSION['login'])."', ".gdrcd_filter('num',$_POST['stato']).")");
    ?>
    <div class="warning">
        <?php echo gdrcd_filter('out',$MESSAGE['warning']['inserted']);?>
    </div>
    <!-- Link di ritorno alla visualizzazione di base -->
    <div class="link_back">
        <a href="main.php?page=gestione_quest&op=lista_trame">
            Torna alla gestione delle trame
        </a>
    </div>
<?php } ?>