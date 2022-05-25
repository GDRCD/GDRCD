<?php
    /** VISUALIZZAZIONE BASE (LATO PG) **/

    //Determinazione pagina (paginazione)
    $pagebegin = (int) $_REQUEST['offset'] * $PARAMETERS['settings']['posts_per_page'];
    $pageend = $pagebegin + $PARAMETERS['settings']['posts_per_page'];

    //Conteggio record totali
    $record_globale = gdrcd_query("SELECT COUNT(*) FROM blocco_esiti WHERE pg = '".$_SESSION['login']."' ");
    $totaleresults = $record_globale['COUNT(*)'];

    // Ottengo tutte le serie di esiti associate al PG
    $query="SELECT * FROM blocco_esiti WHERE pg = '".$_SESSION['login']."' 
            ORDER BY id DESC LIMIT ".$pagebegin.", ".$PARAMETERS['settings']['posts_per_page']."";
    $result=gdrcd_query($query, 'result');

    // Se non sono presenti serie di esiti, mostro messaggio
    if (gdrcd_query($result, 'num_rows') == 0) {
        echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['interface']['esiti']['no_esiti']).'</div>';
    }
    // Altrimenti, mostro l'elenco
    else { ?>
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
                    <tr class="titoli_elenco">
                        <td class="casella_titolo">
                            <div class="titoli_elenco">
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['esiti']['data']); ?>
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
                                           value="view" />
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
            <?php
    }
?>
<!-- link scrivi messaggio -->
<div class="link_back">
    <a href="main.php?page=servizi_esitinew&op=first">
        <?php echo $MESSAGE['interface']['esiti']['link']['new']; ?>
    </a>
</div>

