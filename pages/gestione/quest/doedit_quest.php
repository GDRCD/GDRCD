<?php /*Modifica di un record*/
if ($_POST['op']=='doedit_quest'){
    /*Processo le informazioni ricevute dal form*/

    #Verifico che l'id passato sia valido
    if ($_SESSION['permessi']<Functions::get_constant('QUEST_SUPER_PERMISSION')){
        $id=gdrcd_query("SELECT * FROM quest WHERE id = '".$_POST['id_record']."' AND autore ='".$_SESSION['login']."'", 'result');
    } else {
        $id=gdrcd_query("SELECT * FROM quest WHERE id = '".$_POST['id_record']."' ", 'result');
    }
    $quest_num = gdrcd_query($id, 'num_rows');

    if ($quest_num>0){
            #Caricamento lista presenti
            $i=0;
            for ($i=0; $i<10; $i++) {
                $a = $i+1;

                if ($_POST['part'.$a.'']!=='' && $a==1) {
                    $pglist[$i] = $_POST['part'.$a.''];
                }
                else if ($_POST['part'.$a.'']!=='') {
                    $pglist[$i] = ', '.$_POST['part'.$a.''];
                }

                if ($_POST['part'.$a.'']!=='') {

                    #Recupero i pg coinvolti
                    $pg="SELECT * FROM clgpgquest WHERE id_quest = ".gdrcd_filter('num',$_POST['id_record'])." 
                    AND nome_pg= '".gdrcd_filter('in',$_POST['part'.$a.''])."' ";
                    $res_pg=gdrcd_query($pg, 'result');
                    $rec_pg=gdrcd_query($res_pg, 'fetch');

                    $numresults=gdrcd_query($res_pg, 'num_rows');


                    if ($numresults>0) {

                        #Correggo l'esperienza
                            #Calcolo la differenza dei px da assegnare. Se vanno ridotti, il valore sarà negativo.
                            $newpx = $_POST['px'.$a.''] - $rec_pg['px_assegnati'];

                            if ($newpx !== 0) {
                                gdrcd_query("UPDATE personaggio SET esperienza = esperienza + " . gdrcd_filter('num', $newpx) . " 
                                WHERE nome = '" . gdrcd_filter('in', $_POST['part' . $a . '']) . "' LIMIT 1 ");

                                /*Registro l'operazione*/
                                $cause = 'Modifica Quest: ' . gdrcd_filter('in', $_POST['titolo']);
                                gdrcd_query("INSERT INTO log (nome_interessato, autore, data_evento, codice_evento ,descrizione_evento) 
                                        VALUES 
                                        ('" . gdrcd_filter('in', $_POST['part' . $a . '']) . "', '" . $_SESSION['login'] . "', NOW(), 
                                        " . PX . ", '(" . gdrcd_filter('num', $newpx) . ' px) ' . gdrcd_filter('in', $cause) . "')");
                            }
                                #Modifico il record in clgpgquest
                                gdrcd_query("UPDATE clgpgquest SET nome = '" . gdrcd_filter('in', $_POST['titolo']) . "', 
                                    autore = '" . gdrcd_filter('in', $_SESSION['login']) . "', 
                                    commento = '" . gdrcd_filter('in', $_POST['comm' . $a . '']) . "', 
                                    px_assegnati = " . gdrcd_filter('num', $_POST['px' . $a . '']) . " 
                                    WHERE id_quest = " . gdrcd_filter('num', $_POST['id_record']) . " 
                                    AND nome_pg = '" . gdrcd_filter('in', $_POST['part' . $a . '']) . "' ");

                        #Notifica
                        if (Functions::get_constant('QUEST_NOTIFY')) {
                            $text = 'Il resoconto quest relativo alla Quest: <b>' . $_POST['titolo'] . '</b> è stato modificato da 
                                ' . gdrcd_filter('in', $_SESSION['login']) . '. Puoi consultarlo andando su Scheda > 
                                Esperienza > Resoconti quest';
                            gdrcd_query("INSERT INTO messaggi (destinatario, mittente, spedito, oggetto, testo) VALUES 
                                    ('" . gdrcd_filter('in', $_POST['part' . $a . '']) . "',
                                    'Resoconti Quest',  NOW(), 'Modifica resoconto quest', 
                                    '" . $text . "')");

                        }
                    } else {

                        #inserisco il record in clgpgquest
                        gdrcd_query("INSERT INTO clgpgquest (id_quest, nome, commento, autore, nome_pg, px_assegnati) VALUES 
                                (".$rec_id['id'].",'".gdrcd_filter('in',$_POST['titolo'])."', '".gdrcd_filter('in',$_POST['comm'.$a.''])."',
                                 '".gdrcd_filter('in', $_SESSION['login'])."', '".gdrcd_filter('in',$_POST['part'.$a.''])."', 
                                 ".gdrcd_filter('num',$_POST['px'.$a.''])."  )");

                        #Do l'esperienza
                        if($_POST['px'.$a.'']!==0) {
                            gdrcd_query("UPDATE personaggio SET esperienza = esperienza + ".gdrcd_filter('num',$_POST['px'.$a.''])." 
                            WHERE nome = '".gdrcd_filter('in',$_POST['part'.$a.''])."' LIMIT 1 ");

                            /*Registro l'operazione*/
                            $cause = 'Quest: '.gdrcd_filter('in',$_POST['titolo']);
                            gdrcd_query("INSERT INTO log (nome_interessato, autore, data_evento, codice_evento ,descrizione_evento) 
                            VALUES ('".gdrcd_filter('in',$_POST['part'.$a.''])."', '".$_SESSION['login']."', NOW(), ".PX.", 
                            '(".gdrcd_filter('num',$_POST['px'.$a.'']).' px) '.gdrcd_filter('in',$cause)."')");

                        }
                        #Notifica
                        if (Functions::get_constant('QUEST_NOTIFY')) {
                            $text = 'Il resoconto quest relativo alla Quest: <b>' . $_POST['titolo'] . '</b> è stato inserito. 
                            Puoi consultarlo andando su Scheda > Esperienza > Resoconti quest';
                            gdrcd_query("INSERT INTO messaggi (destinatario, mittente, spedito, oggetto, testo) VALUES 
                                    ('" . gdrcd_filter('in', $_POST['part' . $a . '']) . "',
                                    'Resoconti Quest',  NOW(), 'Inserimento resoconto quest', 
                                    '" . $text . "')");

                            }

                    }
                }
            }

            $partecipanti = $pglist[0].''.$pglist[1].''.$pglist[2].''.$pglist[3].''.$pglist[4].''.$pglist[5].''.$pglist[6].''.$pglist[7].''.$pglist[8].''.$pglist[9].''.$pglist[10];

            /*Eseguo l'aggiornamento*/
            gdrcd_query("UPDATE quest SET titolo ='".gdrcd_filter('in',$_POST['titolo'])."', 
            ultima_modifica ='".gdrcd_filter('in',$_SESSION['login'])."', partecipanti = '".$partecipanti."', 
            descrizione ='".gdrcd_filter('in',$_POST['descrizione'])."', trama = ".gdrcd_filter('num',$_POST['trama'])." 
            WHERE id = ".gdrcd_filter('num',$_POST['id_record'])." LIMIT 1");

            ?>
            <div class="warning">
                <?php echo gdrcd_filter('out',$MESSAGE['warning']['modified']);?>
            </div>
    <?php } else { ?>
            <div class="warning">
                Impossibile modificare questa quest
            </div>
    <?php   } ?>
    <!-- Link di ritorno alla visualizzazione di base -->
    <div class="link_back">
        <a href="main.php?page=gestione_quest">
            Torna a gestione quest
        </a>
    </div>
<?php } ?>