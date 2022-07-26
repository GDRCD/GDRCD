<?php
if ($_SESSION['permessi'] >= ESITI_PERM && ESITI) {
    ?>
    <div class="page_title">
        <h2>Gestione esiti</h2>
    </div>

    <div class="form_info">
        <?=$MESSAGE['interface']['esiti']['gm_page'];?>
    </div>
    <?php
    # Lista di tutti i blocchi di esiti
     if ($_POST['op']=='list') {
        $id = gdrcd_filter('num', $_POST['id']);
        if ($_SESSION['permessi'] < FULL_PERM) {
            $query = gdrcd_query("SELECT * FROM blocco_esiti WHERE id = " . $id . " 
            AND (master = '0' || master ='" . gdrcd_filter('in', $_SESSION['login']) . "') 
            ORDER BY id ", 'result');
        } else {
            $query = gdrcd_query("SELECT * FROM blocco_esiti WHERE id = " . $id . " 
            ORDER BY id ", 'result');
        }
        $blocco=gdrcd_query($query, 'fetch');

        $tit = gdrcd_filter('out', $blocco['titolo']);
        $pg = gdrcd_filter('out', $blocco['pg']);

        gdrcd_query("UPDATE esiti SET letto_master = 1 WHERE id_blocco = ".gdrcd_filter('num',$blocco['id'])." ");

        ?>
        <div class="fate_frame">
            <div class="titolo_box">
                <h2 style="margin-top:3px;">
                    <b><?=$tit;?> - <?=$pg;?></b>
                </h2>
            </div>

            <?php
            //
            $quer="SELECT * FROM esiti WHERE id_blocco = ".gdrcd_filter('num',$blocco['id'])." ORDER BY data DESC";
            $res=gdrcd_query($quer, 'result');

            if (!isset($tit['closed'])) { ?>
                <div class="titolo_box">
                    <a class="link_new" href='main.php?page=gestione_segnalazioni&segn=esito_index&op=edit&id=<?=gdrcd_filter('num',$blocco['id']);?>'>
                        [ Modifica ]
                    </a>
                </div>
            <?php 	}

            //
            while  ($row=gdrcd_query($res, 'fetch')) {
                $abilita=gdrcd_query("SELECT nome FROM abilita WHERE id_abilita = ".$row['id_ab']." ");
                $chat=gdrcd_query("SELECT nome FROM mappa WHERE id = ".$row['chat']." ");	?>
                <div class="title_esi">
                    Autore:<b><?=$row['autore'].'</b> | Creato il: '.gdrcd_format_date($row['data']).' alle
                     '.gdrcd_format_time($row['data']);?>
                </div>

                <div class="fate_title">
                    Titolo: <b><?php echo $row['titolo'];?></b>
                    <?php if ($row['chat']>0) { echo '- <b><u>Esito in chat</u></b>
                         (Chat: '.$chat['nome'].' | Skill: '.gdrcd_filter('out',$abilita['nome']).')';
                    }?>
                    <br>
                    <?php if ($row['dice_face']>0 && $row['dice_num']>0 && TIRI_ESITO) { ?>
                    Risultato tiro di <?php echo $row['dice_num'].'d'.$row['dice_face'];?>: <b><?php echo $row['dice_results'] ?></b>
                    <?php } ?>
                </div>
                <div class="fate_cont">
                <?php if ($row['chat']>0) {
                        echo 'Esito per il Fallimento critico: '.$row['CD_1'].'<br> 
                        Esito per il Fallimento: '.$row['CD_2'].'<br> Esito per il Successo: '.$row['CD_3'].'<br>
                        Esito per il Successo critico: '.$row['CD_4'];
                    } else {
                        echo $row['contenuto'];
                    } ?>
                </div>
                <b>Note OFF:</b> <?php echo $row['noteoff'];?>
            <?php } # Singolo esito ?>
        </div><br>
         <?php if (!isset($tit['closed'])) { ?>
             <div class="link_back">
                 <a href='main.php?page=gestione_segnalazioni&segn=esito_index&op=new&blocco=<?=gdrcd_filter('num',$blocco['id']);?>'>
                 Invia un nuovo esito
                </a>
             </div>
             <?php
             if (ESITI_CHAT){
                 ?>
                 | <a class="link_new"

                      href='main.php?page=gestione_segnalazioni&segn=esito_index&op=newchat&blocco=<?=gdrcd_filter('num',$blocco['id']);?>'
                 >
                     Invia un esito in chat
                 </a>
             <?php  }
         } // Fine if !isset?>
         <!-- Link a piè di pagina -->
         <div class="link_back">
             <a href="main.php?page=gestione_segnalazioni&segn=esiti_master">Torna alla lista</a>
         </div>
    <?php } // Fine lista esiti
    else if (isset($_POST['op'])===FALSE) {
    //Determinazione pagina (paginazione)
    $pagebegin = (int)$_REQUEST['offset'] * $PARAMETERS['settings']['posts_per_page'];
    $pageend = $pagebegin + $PARAMETERS['settings']['posts_per_page'];

    //Conteggio record totali
    $record_globale = gdrcd_query("SELECT COUNT(*) FROM blocco_esiti ");
    $totaleresults = $record_globale['COUNT(*)'];

    if ($_SESSION['permessi'] < FULL_PERM) {    #seleziono la lista di esiti sulla base dei permessi
        $query = "SELECT * FROM blocco_esiti WHERE (master = '0' || master ='" . gdrcd_filter('in', $_SESSION['login']) . "') 
          ORDER BY closed, data DESC, pg LIMIT " . $pagebegin . ", " . $PARAMETERS['settings']['posts_per_page'] . "";
    } else {
        $query = "SELECT * FROM blocco_esiti ORDER BY closed, data DESC, pg 
          LIMIT " . $pagebegin . ", " . $PARAMETERS['settings']['posts_per_page'] . "";
    }

    $blocco = gdrcd_query($query, 'result');

    if (gdrcd_query($blocco, 'num_rows') == 0) {
        echo '<div class="fate_frame">';
        echo 'Nessuna serie di esiti aperta';
        echo '</div>';
    } else { ?>

        <!-- Paginatore elenco -->
        <div class="pager">
            <?php
            if ($totaleresults > $PARAMETERS['settings']['posts_per_page']) {
                echo gdrcd_filter('out', $MESSAGE['interface']['pager']['pages_name']);
                for ($i = 0; $i <= floor($totaleresults / $PARAMETERS['settings']['posts_per_page']); $i++) {
                    if ($i != $_REQUEST['offset']) {
                        ?>
                        <a href="main.php?page=gestione_segnalazioni&segn=esiti_master&offset=<?=$i;?>"><?=$i + 1;?></a>
                        <?php
                    } else {
                        echo ' ' . ($i + 1) . ' ';
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
                            Data
                        </div>
                    </td>
                    <td class="casella_titolo">
                        <div class="titoli_elenco">
                            Nome pg richiedente
                        </div>
                    </td>
                    <td class="casella_titolo">
                        <div class="titoli_elenco">
                            Stato
                        </div>
                    </td>
                    <td class="casella_titolo">
                        <div class="titoli_elenco">
                            Titolo
                        </div>
                    </td>
                    <td class="casella_titolo">
                        <div class="titoli_elenco">
                            Numero esiti
                        </div>
                    </td>
                    <td class="casella_titolo" style="width: 100px;">
                        <div class="titoli_elenco">

                        </div>
                    </td>
                </tr>
                <?php while ($rec = gdrcd_query($blocco, 'fetch')) {
                    $num = gdrcd_query(gdrcd_query("SELECT * FROM esiti WHERE id_blocco = " . gdrcd_filter('num', $rec['id']) . " 
                    AND autore != '" . $rec['pg'] . "' ORDER BY master, data DESC", 'result'), 'num_rows');
                    $new = gdrcd_query(gdrcd_query("SELECT * FROM esiti WHERE id_blocco = " . gdrcd_filter('num', $rec['id']) . " 
                    AND letto_master = 0 ", 'result'), 'num_rows');
                    ?>

                    <tr>
                        <td class="casella_titolo">
                            <div class="elementi_elenco">
                                <?php echo gdrcd_filter('out', gdrcd_format_date($rec['data'])); ?>
                            </div>
                        </td>
                        <td class="casella_titolo">
                            <div class="elementi_elenco">
                                <?php echo gdrcd_filter('out', $rec['pg']); ?>
                            </div>
                        </td>
                        <td class="casella_titolo">
                            <div class="elementi_elenco">
                                <?php if ($rec['master'] == '0') {
                                    echo '<u>In attesa di risposta</u>';
                                } else {
                                    echo 'Presa in carico';
                                } ?>
                            </div>
                        </td>
                        <td class="casella_titolo">
                            <div class="elementi_elenco">
                                <?php echo gdrcd_filter('out', $rec['titolo']); ?>
                            </div>
                        </td>
                        <td class="casella_titolo">
                            <div class="elementi_elenco">
                                <?php echo gdrcd_filter('num', $num);
                                if ($new > 0) {
                                    echo ' - Nuovo messaggio';
                                }
                                ?>
                            </div>
                        </td>
                        <td>
                            <form action="main.php?page=gestione_segnalazioni&segn=esiti_master" method="post">
                                <input type="hidden"
                                       name="op"
                                       value="list"/>
                                <input type="hidden"
                                       name="id"
                                       value="<?php echo $rec['id']; ?>"/>
                                <input type="submit" name="submit" class="submitroles" value="Apri serie"/>
                            </form>
                        </td>
                    </tr>
                <?php } #Fine blocco  ?>
            </table>
        </div>
        <div class="link_back">
            <a href='main.php?page=gestione_segnalazioni&segn=esito_index&op=first'>
                Apri una nuova serie di esiti
            </a>
        </div>
    <?php
        }
    }
} else {
    echo '<div class="warning">Non hai i permessi per visualizzare questa sezione</div>';
}
?>