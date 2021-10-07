<?php
/*Inserimento di un nuovo record*/
if ($_POST['op']=='add') {

    $load_blocco=gdrcd_query("SELECT id, pg, master, titolo, closed FROM blocco_esiti 
        WHERE id='".gdrcd_filter('num',$_POST['id'])."' AND pg = '".gdrcd_filter('in',$_SESSION['login'])."' LIMIT 1 ");

    if ( gdrcd_filter('num',$load_blocco['id'])>0) {
        if ($load_blocco['closed']==1) { ?>
            <div class="warning">
                Questa serie di esiti è al momento chiusa
            </div>
            <?php
        } else {
            if (isset($_POST['note'])===FALSE) { $note = 'Nessuna';} else { $note = gdrcd_filter('in',$_POST['note']); }
            if (isset($_POST['dice_face'])===FALSE) { $facce = 0;} else { $facce=gdrcd_filter('num',$_POST['dice_face']);}
            if (isset($_POST['dice_num'])===FALSE) { $num = 0;} else {  $num= gdrcd_filter('num',$_POST['dice_num']);}

            #Tiro il numero di dadi secondo le indicazioni
            if ($num>0 || $facce>0) {
                $tiri= [];
                $i = 0;
                for ($i =0; $i < $num; $i++) {
                    $tiri[$i] = mt_rand(1, $facce);
                }
                #Compatto l'array in una stringa
                $dice_res = join(',', $tiri);
            } else { $dice_res = '0';}


            /*Eseguo l'inserimento del singolo esito*/
            gdrcd_query("INSERT INTO esiti (titolo, pg, autore, contenuto, noteoff, id_blocco, dice_face, dice_num, dice_results,letto_pg) 
                  VALUES ('".gdrcd_filter('in',$_POST['titolo'])."', '".gdrcd_filter('in', $_SESSION['login'])."',
                  '".gdrcd_filter('in', $_SESSION['login'])."', '".gdrcd_filter('in', $_POST['contenuto'])."','".$note."', 
                  ".$_POST['id'].", ".gdrcd_filter('num', $facce).", ".gdrcd_filter('num', $num).", 
                  '".gdrcd_filter('in', $dice_res)."', 1 )");
            ?>

            <div class="warning">
                <?php echo gdrcd_filter('out',$MESSAGE['warning']['inserted']);?>
            </div>
            <!-- Link a piè di pagina -->
            <div class="link_back">
                <a href="main.php?page=servizi_esiti">Torna indietro</a>
            </div>
        <?php }
    } else {
        echo '<div class="warning">Non hai i permessi per visualizzare questa sezione</div>';
    }
} ?>