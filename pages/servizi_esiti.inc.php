<div class="pagina_scheda_esiti">
    <!-- Titolo della pagina -->
    <div class="page_title">
        <h2>
            Pannello esiti personali
        </h2>
    </div>
    <!-- Box principale -->
    <div class="page_body">
        <div class="form_info">
            <?php echo $MESSAGE['interface']['esiti']['pg_page']; ?>
        </div>
            <?php
            # Lista di tutti i blocchi di esiti
            if ($_POST['op']=='listpg') {
                    $id = gdrcd_filter('num', $_POST['id']);

                    $query=gdrcd_query("SELECT * FROM blocco_esiti WHERE id = ".$id." 
                        AND pg = '".gdrcd_filter('in',$_SESSION['login'])."' ORDER BY id ", 'result');
                    $result=gdrcd_query($query, 'fetch');


                    $tit = gdrcd_filter('out', $result['titolo']);

                    $pg = gdrcd_filter('out', $result['pg']);

                    gdrcd_query("UPDATE esiti SET letto_pg = 1 WHERE id_blocco = ".$id." ");

                    ?>
                <div class="fate_frame" >
                    <div class="titolo_box">
                        <h2 >
                            <?php echo $tit;  ?>
                        </h2>
                    </div>

                    <?php
                    $quer="SELECT * FROM esiti WHERE id_blocco = ".$id." AND chat = 0 
                        AND pg = '".gdrcd_filter('in',$_SESSION['login'])."' ORDER BY data DESC";


                    $res=gdrcd_query($quer, 'result');


                    while  ($row=gdrcd_query($res, 'fetch')) {
                        $chat=gdrcd_query("SELECT nome FROM mappa WHERE id = ".$row['chat']." ");	?>

                        <div class="title_esi">Autore:<b><?php echo $row['autore'].'</b> | 
                            Creato il: '.gdrcd_format_date($row['data']).' alle '.gdrcd_format_time($row['data']);?></div>

                        <div class="fate_title">Titolo: <b><?php echo $row['titolo'];?></b>
                            <?php if ($row['dice_face']>0 && $row['dice_num']>0 && TIRI_ESITO) { ?>
                               <br> Risultato tiro di <?php echo $row['dice_num'].'d'.$row['dice_face'];?>: <b><?php echo $row['dice_results'] ?></b>
                            <?php } ?>
                        </div>
                        <div class="fate_cont">
                            <?php echo $row['contenuto']; ?>
                        </div>

                        <b>Note OFF:</b> <?php echo $row['noteoff']; ?>
                    <?php } # Singolo esito ?>
                </div>
                <!-- Link a piè di pagina -->
                <?php if ($result['closed']==0) { ?>
                <div class="link_back">
                    <a href='main.php?page=servizi_esitinew&op=new&blocco=<?php echo gdrcd_filter('num',$id);?>'>
                        Invia una nuova richiesta di esito
                    </a>
                </div>
                <?php } ?>
                <div class="link_back">
                    <a href='main.php?page=servizi_esiti'>
                        Torna indietro
                    </a>
                </div>
            <?php }
            else if (isset($_POST['op'])===FALSE) {
            /** VISUALIZZAZIONE BASE (LATO PG) **/

            //Determinazione pagina (paginazione)
                $pagebegin = (int) $_REQUEST['offset'] * $PARAMETERS['settings']['posts_per_page'];
                $pageend = $pagebegin + $PARAMETERS['settings']['posts_per_page'];

                //Conteggio record totali
                $record_globale = gdrcd_query("SELECT COUNT(*) FROM blocco_esiti WHERE pg = '".$_SESSION['login']."' ");
                $totaleresults = $record_globale['COUNT(*)'];

                $query="SELECT * FROM blocco_esiti WHERE pg = '".$_SESSION['login']."' 
                    ORDER BY id DESC LIMIT ".$pagebegin.", ".$PARAMETERS['settings']['posts_per_page']."";
                $result=gdrcd_query($query, 'result');

                if (gdrcd_query($result, 'num_rows')==0) {
                    echo '<div class="fate_frame">';
                    echo 'Nessuna serie di esiti aperta';
                    echo '</div>';
                } else { ?>
                    <!-- Paginatore elenco -->
                    <div class="pager">
                        <?php
                        if($totaleresults > $PARAMETERS['settings']['posts_per_page']) {
                            echo gdrcd_filter('out', $MESSAGE['interface']['pager']['pages_name']);
                            for($i = 0; $i <= floor($totaleresults / $PARAMETERS['settings']['posts_per_page']); $i++) {
                                if($i != $_REQUEST['offset']) {
                                    ?>
                                    <a href="main.php?page=servizi_esiti&offset=<?php echo $i; ?>"><?php echo $i + 1; ?></a>
                                    <?php
                                } else {
                                    echo ' '.($i + 1).' ';
                                }
                            } //for
                        }//if
                        ?>
                    </div>
                    <div class="elenco_record_gioco">
                        <table>
                            <tr class="titles_table">
                                <td class="casella_titolo">
                                    <div class="titoli_elenco">
                                        <?php echo 'Data'; ?>
                                    </div>
                                </td>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco">
                                        <?php echo 'Nome master'; ?>
                                    </div>
                                </td>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco">
                                        <?php echo 'Titolo'; ?>
                                    </div>
                                </td>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco">
                                        <?php echo 'Numero esiti'; ?>
                                    </div>
                                </td>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco">

                                    </div>
                                </td>
                            </tr>
                            <?php while($rec=gdrcd_query($result, 'fetch')) {
                                $num=gdrcd_query(gdrcd_query("SELECT * FROM esiti 
                                    WHERE id_blocco = ".gdrcd_filter('num',$rec['id'])." AND autore != '".$_SESSION['login']."' 
                                    ORDER BY data DESC", 'result'), 'num_rows');
                                $new=gdrcd_query(gdrcd_query("SELECT * FROM esiti 
                                    WHERE id_blocco = ".gdrcd_filter('num',$rec['id'])." AND autore != '".$_SESSION['login']."' 
                                    AND letto_pg = 0 ORDER BY data DESC", 'result'), 'num_rows');
                                ?>

                                <tr>
                                    <td class="casella_titolo">
                                        <div class="elementi_elenco">
                                            <?php echo gdrcd_filter('out',gdrcd_format_date($rec['data'])); ?>
                                        </div>
                                    </td>
                                    <td class="casella_titolo">
                                        <div class="elementi_elenco">
                                            <?php if ($rec['master']=='0') { echo 'In attesa di risposta';}
                                            else { echo gdrcd_filter('out',$rec['master']); } ?>
                                        </div>
                                    </td>
                                    <td class="casella_titolo">
                                        <div class="elementi_elenco">
                                            <?php echo gdrcd_filter('out',$rec['titolo']); ?>
                                        </div>
                                    </td>
                                    <td class="casella_titolo">
                                        <div class="elementi_elenco">
                                            <?php echo gdrcd_filter('num',$num);
                                            if ($new>0) { echo ' - Nuovi messaggi';}
                                            ?>
                                        </div>
                                    </td>
                                    <td>
                                        <form action="main.php?page=servizi_esiti" method="post">
                                            <input type="hidden"
                                                   name="op"
                                                   value="listpg" />
                                            <input type="hidden"
                                                   name="id"
                                                   value="<?php echo $rec['id'];?>" />
                                            <input type="submit" name="submit" class="submitroles" value="Apri serie" />
                                        </form>
                                    </td>
                                </tr>
                            <?php } #Fine blocco  ?>
                        </table>
                    </div>
                    <!-- link nuova serie esiti -->
                    <div class="link_back">
                        <a href='main.php?page=servizi_esitinew&op=first'>
                            Apri una nuova serie di esiti
                        </a>
                    </div>
                    <?php
                }
            }
        ?>
    </div>
    <!-- Box principale -->
</div><!-- Pagina -->