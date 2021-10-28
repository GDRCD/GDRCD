<?php /*Inserimento di un nuovo record*/
if ($_POST['op']=='insert_quest') {

    $i=0;
    for ($i=0; $i<10; $i++) {
        $a = $i+1;

        #Caricamento lista presenti
        if ($_POST['part'.$a.'']!=='' && $a==1) {
            $pglist[$i] = $_POST['part'.$a.''];
        }
        else if ($_POST['part'.$a.'']!=='') {
            $pglist[$i] = ', '.$_POST['part'.$a.''];
        }

    }

    $partecipanti = $pglist[0].''.$pglist[1].''.$pglist[2].''.$pglist[3].''.$pglist[4].''.$pglist[5].''.$pglist[6].''.$pglist[7].''.$pglist[8].''.$pglist[9].''.$pglist[10];

    /*Eseguo l'inserimento*/
    gdrcd_query("INSERT INTO quest (titolo, data, descrizione, autore, trama, partecipanti) VALUES 
            ('".gdrcd_filter('in',$_POST['titolo'])."', NOW(), '".gdrcd_filter('in',$_POST['descrizione'])."', 
            '".gdrcd_filter('in', $_SESSION['login'])."', ".gdrcd_filter('num',$_POST['trama']).", 
            '".gdrcd_filter('in', $partecipanti)."'  )");

    #Recupero l'id di quest
    $id="SELECT * FROM quest WHERE titolo = '".$_POST['titolo']."' AND autore ='".$_SESSION['login']."' 
        ORDER BY data DESC LIMIT 1";
    $res_id=gdrcd_query($id, 'result');
    $rec_id=gdrcd_query($res_id, 'fetch');

    #Ciclo l'assegnazione px e resoconti
    $i=0;
    for ($i=0; $i<10; $i++) {
        $a = $i+1;
        if ($_POST['part'.$a.'']!=='') {

            #Notifica
            if (Functions::get_constant('QUEST_NOTIFY')) {
                $text = 'Il resoconto quest relativo alla Quest: <b>' . $_POST['titolo'] . '</b> è stato inserito. 
                    Puoi consultarlo andando su Scheda > Esperienza > Resoconti quest';
                gdrcd_query("INSERT INTO messaggi (destinatario, mittente, spedito, oggetto, testo) VALUES 
                                    ('" . gdrcd_filter('in', $_POST['part' . $a . '']) . "',
                                    'Resoconti Quest',  NOW(), 'Inserimento resoconto quest', 
                                    '" . $text . "')");

            }

            #inserisco il record in clgpgquest
            gdrcd_query("INSERT INTO clgpgquest (id_quest, nome, commento, autore, nome_pg, px_assegnati) 
                VALUES 
                    (".$rec_id['id'].",'".gdrcd_filter('in',$_POST['titolo'])."', '".gdrcd_filter('in',$_POST['comm'.$a.''])."', 
                    '".gdrcd_filter('in', $_SESSION['login'])."', '".gdrcd_filter('in',$_POST['part'.$a.''])."', 
                    ".gdrcd_filter('num',$_POST['px'.$a.''])."  )");

            #Do l'esperienza
            if(is_numeric($_POST['px'.$a.''])===TRUE) {
                gdrcd_query("UPDATE personaggio SET esperienza = esperienza + ".gdrcd_filter('num',$_POST['px'.$a.''])." 
                WHERE nome = '".gdrcd_filter('in',$_POST['part'.$a.''])."' LIMIT 1 ");

                /*Registro l'operazione*/
                $cause = 'Quest: '.gdrcd_filter('in',$_POST['titolo']);
                gdrcd_query("INSERT INTO log (nome_interessato, autore, data_evento, codice_evento ,descrizione_evento) 
                    VALUES ('".gdrcd_filter('in',$_POST['part'.$a.''])."', '".$_SESSION['login']."', NOW(), ".PX.", 
                    '(".gdrcd_filter('num',$_POST['px'.$a.'']).' px) '.gdrcd_filter('in',$cause)."')");

            }


        }
    }

    ?>
    <div class="warning">
        <?php echo gdrcd_filter('out',$MESSAGE['warning']['inserted']);?>
    </div>
    <!-- Link di ritorno alla visualizzazione di base -->
    <div class="link_back">
        <a href="main.php?page=gestione_quest">
            Torna a gestione quest
        </a>
    </div>
<?php } ?>