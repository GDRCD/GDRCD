<?php
    /*HELP: */
    /*Controllo permessi utente*/
    if(!gdrcd_controllo_permessi($PARAMETERS['administration']['maps']['access_level'])) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
        die();
    }

    //Determinazione pagina (paginazione)
    $pagebegin = (int) gdrcd_filter('get', $_REQUEST['offset']) * $PARAMETERS['settings']['records_per_page'];
    $pageend = $PARAMETERS['settings']['records_per_page'];
    // Costruisco la query delle mappe
    $sqlMappe = "
        SELECT id_click, nome, mobile, posizione, principale
        FROM mappa_click
    ";
    $result = gdrcd_query($sqlMappe." ORDER BY nome LIMIT ".$pagebegin.", ".$pageend, 'result');
    $numresults = gdrcd_query($result, 'num_rows');

    // Conteggio i record totali per l'impaginazione
    $totaleresults = gdrcd_query(gdrcd_query($sqlMappe, 'result'), 'num_rows');

    // Conteggio i record aventi posizione principale
    $mainMaps = gdrcd_query(gdrcd_query($sqlMappe." WHERE principale = 1", 'result'), 'num_rows');

    ?>
    <div id="GestioneMappeView" class="elenco_record_gestione">
        <?php
            // Se sono presenti record, avvio la costruzione della tabella
            if($numresults > 0) {

                // Se non è presente almeno una mappa principale, mostro un messaggio di avviso
                if(!$mainMaps) {
                    echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['no_main']).'</div>';
                }

                ?>
                <!-- Elenco dei record paginato -->
                <table>
                    <!-- Intestazione tabella -->
                    <tr>
                        <td class="casella_titolo">
                            <div class="titoli_elenco"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['name_col']);?></div>
                        </td>
                        <td class="casella_titolo">
                            <div class="titoli_elenco"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['position']); ?></div>
                        </td>
                        <td class="casella_titolo">
                            <div class="titoli_elenco"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['is_mobile']); ?></div>
                        </td>
                        <td class="casella_titolo">
                            <div class="titoli_elenco"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['is_main']); ?></div>
                        </td>
                        <td class="casella_titolo">
                            <div class="titoli_elenco"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['ops_col']); ?></div>
                        </td>
                    </tr>
                    <!-- Record -->
                    <?php while($row = gdrcd_query($result, 'fetch')) { ?>
                        <tr class="risultati_elenco_record_gestione">
                            <td class="casella_elemento">
                                <div class="elementi_elenco"><?=gdrcd_filter('out', $row['nome']); ?></div>
                            </td>
                            <td class="casella_elemento">
                                <div class="elementi_elenco"><?=gdrcd_filter('out', $row['posizione']); ?></div>
                            </td>
                            <td class="casella_elemento">
                                <div class="elementi_elenco">
                                    <div class="elementi_elenco"><?=gdrcd_filter('out', $MESSAGE['interface']['administration'][$row['mobile'] == 1 ? 'yes' : 'no']); ?></div>
                                </div>
                            </td>
                            <td class="casella_elemento">
                                <div class="elementi_elenco">
                                    <div class="elementi_elenco"><?=gdrcd_filter('out', $MESSAGE['interface']['administration'][$row['principale'] == 1 ? 'yes' : 'no']); ?></div>
                                </div>
                            </td>
                            <td class="casella_controlli"><!-- Iconcine dei controlli -->
                                <div class="controlli_elenco">
                                    <div class="controllo_elenco">
                                        <!-- Modifica -->
                                        <form class="opzioni_elenco_record_gestione" action="main.php?page=gestione/mappe&op=edit" method="post">
                                            <input type="hidden" name="id_click" value="<?php echo gdrcd_filter('out', $row['id_click']) ?>" />
                                            <input type="image" src="imgs/icons/edit.png"
                                                   alt="<?=gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['edit']); ?>"
                                                   title="<?=gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['edit']); ?>" />
                                        </form>
                                    </div>
                                    <div class="controllo_elenco">
                                        <!-- Elimina -->
                                        <form class="opzioni_elenco_record_gestione" action="main.php?page=gestione/mappe" method="post">
                                            <input type="hidden" name="id_click" value="<?=gdrcd_filter('out', $row['id_click']) ?>" />
                                            <input type="hidden" name="op" value="erase" />
                                            <input type="image" src="imgs/icons/erase.png"
                                                   alt="<?=gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['erase']); ?>"
                                                   title="<?=gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['erase']); ?>" />
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php }//while

                    gdrcd_query($result, 'free');

                    ?>
                </table>
            <?php } ?>
    </div>

    <!-- Paginatore elenco -->
    <div class="pager">
        <?php if($totaleresults > $PARAMETERS['settings']['records_per_page']) {
            echo gdrcd_filter('out', $MESSAGE['interface']['pager']['pages_name']);
            for($i = 0; $i <= ceil($totaleresults / $PARAMETERS['settings']['messages_per_page']) - 1; $i++) {
                if($i != gdrcd_filter('num', $_REQUEST['offset'])) { ?>
                    <a href="main.php?page=gestione/mappe&offset=<?php echo $i; ?>"><?php echo ($i+1); ?></a>
                <?php } else {
                    echo ' '.($i+1).' ';
                }
            } //for
        }//if
        ?>
    </div>
    <!-- link crea nuovo -->
    <div class="link_back">
        <a href="main.php?page=gestione/mappe&op=create">
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['link']['create']); ?>
        </a>
    </div>