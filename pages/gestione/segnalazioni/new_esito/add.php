<?php
/*Inserimento di un nuovo record*/
if ($_POST['op']=='add') {

    $load_blocco=gdrcd_query("SELECT id_personaggio_destinatario, id_personaggio_master, titolo, closed FROM blocco_esiti 
        WHERE id='".gdrcd_filter('num',$_POST['id'])."'  LIMIT 1 ");

    if ($load_blocco['closed']==1) { ?>
        <div class="warning">
            Questa serie di esiti è al momento chiusa
        </div>
    <?php 	} else {
        if (isset($_POST['note'])===FALSE) { $note = 'Nessuna';} else { $note = gdrcd_filter('in',$_POST['note']); }
        if (isset($_POST['chat'])===FALSE) { $chat = 0;} else { $chat = gdrcd_filter('num',$_POST['chat']); }
        if (isset($_POST['id_ab'])===FALSE) { $ab = 0;} else { $ab = gdrcd_filter('num',$_POST['id_ab']); }
        if (isset($_POST['dice_face'])===FALSE) { $facce = 0;} else { $facce=gdrcd_filter('num',$_POST['dice_face']);}
        if (isset($_POST['dice_num'])===FALSE) { $num = 0;} else {  $num= gdrcd_filter('num',$_POST['dice_num']);}

        #Tiro il numero di dadi secondo le indicazioni
        if ($num>0 || $facce>0){
            $tiri= [];
            $i = 0;
            for ($i =0; $i < $num; $i++) {
                $tiri[$i] = mt_rand(1, $facce);
            }
            #Compatto l'array in una stringa
            $dice_res = join(',', $tiri);
        } else { $dice_res = '0';}


        //Invio di un messaggio di avviso al pg
        $master = gdrcd_filter('in', $_SESSION['login']);

        #Invio messaggio di avviso al giocatore
        $text ='Hai ricevuto un nuovo esito dal Master '.gdrcd_filter('out', $_SESSION['login']).' per la 
				serie di esiti intitolata: "'.gdrcd_filter('out', $load_blocco['titolo']).'" ';
        $dest_data = gdrcd_query("SELECT nome FROM personaggio WHERE id_personaggio = " . $load_blocco['id_personaggio_destinatario']);
        $dest = $dest_data['nome'];

        #Invio
        #gdrcd_query("INSERT INTO messaggi (mittente, destinatario, spedito, testo) VALUES ('Sistema esiti',
        # '" . gdrcd_filter('in', $dest) . "', NOW(), '" . gdrcd_filter('in', $text). "')");

        /*Eseguo l'inserimento del singolo esito*/
        gdrcd_query("INSERT INTO esiti (titolo, id_personaggio_destinatario, id_personaggio_autore, contenuto, noteoff, id_ab, chat, CD_1, CD_2, 
                   CD_3, CD_4, id_blocco, id_personaggio_master, dice_face, dice_num, dice_results,letto_master) 
                   VALUES 
                          ('".gdrcd_filter('in',$_POST['titolo'])."',
                   '".$load_blocco['id_personaggio_destinatario']."','".$_SESSION['id_personaggio']."',
                   '".gdrcd_filter('in', $_POST['contenuto'])."','".$note."', ".$ab.", ".$chat.", 
                   '".gdrcd_filter('in', $_POST['CD_1'])."', '".gdrcd_filter('in', $_POST['CD_2'])."', 
                   '".gdrcd_filter('in', $_POST['CD_3'])."', '".gdrcd_filter('in', $_POST['CD_4'])."', 
                   ".$_POST['id'].", '".$_SESSION['id_personaggio']."', ".gdrcd_filter('num', $facce).",
                   ".gdrcd_filter('num', $num).", '".gdrcd_filter('in', $dice_res)."', 1 )");

        #Aggiorno il master se non presente
        if ($load_blocco['id_personaggio_master'] === NULL && $_SESSION['permessi']>=ESITI_PERM && $load_blocco['id_personaggio_destinatario']!=$_SESSION['id_personaggio']) {
            gdrcd_query("UPDATE blocco_esiti SET id_personaggio_master = '".$_SESSION['id_personaggio']."' 
					WHERE id = ".gdrcd_filter('num', $_POST['id'] )." ");
        }

        ?>

        <div class="warning">
            <?php echo gdrcd_filter('out',$MESSAGE['warning']['inserted']);?>
        </div>
        <br><a href="main.php?page=gestione_segnalazioni&segn=esiti_master">Torna indietro</a>
    <?php  	   }
} ?>