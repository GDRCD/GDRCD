<div class="pagina_gestione_abilita">
    <?php /*HELP: */
    /*Controllo permessi utente*/
    if($_SESSION['permessi'] < MODERATOR) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
    } else { ?>
        <!-- Titolo della pagina -->
        <div class="page_title">
            <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['plot']['page_name']); ?></h2>
        </div>
        <!-- Corpo della pagina -->
        <div class="page_body">
            <?php /*Inserimento di un nuovo record*/
            if($_POST['op'] == 'insert') {
                /*Eseguo l'inserimento*/
                gdrcd_query("INSERT INTO ambientazione (capitolo, titolo, testo) VALUES (".gdrcd_filter('num', $_POST['articolo']).", '".gdrcd_filter('in', $_POST['titolo'])."', '".gdrcd_filter('in', $_POST['testo'])."')");
                ?>
                <div class="warning">
                    <?php echo gdrcd_filter('out', $MESSAGE['warning']['inserted']); ?>
                </div>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_ambientazione">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['plot']['link']['back']); ?>
                    </a>
                </div>
            <?php
            }
            /* Cancellatura in un record */
            if($_POST['op'] == 'erase') {
                /*Eseguo la cancellatura*/
                gdrcd_query("DELETE FROM ambientazione WHERE capitolo=".gdrcd_filter('num', $_POST['id_record'])." LIMIT 1");
                ?>
                <div class="warning">
                    <?php echo gdrcd_filter('out', $MESSAGE['warning']['deleted']); ?>
                </div>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_ambientazione">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['plot']['link']['back']); ?>
                    </a>
                </div>
            <?php
            }
            /*Modifica di un record*/
            if(gdrcd_filter('get', $_POST['op']) == 'doedit') {
                /*Eseguo l'aggiornamento*/
                gdrcd_query("UPDATE ambientazione SET titolo ='".gdrcd_filter('in', $_POST['titolo'])."', testo ='".gdrcd_filter('in', $_POST['testo'])."', capitolo = ".gdrcd_filter('in', $_POST['articolo'])." WHERE capitolo = ".gdrcd_filter('num', $_POST['art'])." LIMIT 1");
                ?>
                <div class="warning">
                    <?php echo gdrcd_filter('out', $MESSAGE['warning']['modified']); ?>
                </div>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_ambientazione">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['plot']['link']['back']); ?>
                    </a>
                </div>
            <?php
            }
            /*Form di inserimento/modifica*/
            if((gdrcd_filter('get', $_POST['op']) == 'edit') || (gdrcd_filter('get', $_REQUEST['op']) == 'new')) {
                /*Preseleziono l'operazione di inserimento*/
                $operation = 'insert';
                /*Se è stata richiesta una modifica*/
                if($_POST['op'] == 'edit') {
                    /*Carico il record da modificare*/
                    $loaded_record = gdrcd_query("SELECT * FROM ambientazione WHERE capitolo=".gdrcd_filter('num', $_POST['id_record'])." LIMIT 1 ");
                    /*Cambio l'operazione in modifica*/
                    $operation = 'edit';
                } ?>
                <!-- Form di inserimento/modifica -->
                <div class="panels_box">
                    <form action="main.php?page=gestione_ambientazione" method="post" class="form_gestione">
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['plot']['art']); ?>
                        </div>
                        <div class='form_field'>
                            <input name="articolo" value="<?php echo 0 + $loaded_record['capitolo']; ?>" />
                        </div>
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['plot']['title']); ?>
                        </div>
                        <div class='form_field'>
                            <input name="titolo" value="<?php echo gdrcd_filter('out', $loaded_record['titolo']); ?>" />
                        </div>
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['plot']['infos']); ?>
                        </div>
                        <div class='form_field'>
                            <textarea name="testo"><?php echo gdrcd_filter('out', $loaded_record['testo']); ?></textarea>
                        </div>
                        <div class="form_info">
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['help']['bbcode']); ?>
                        </div>
                        <!-- bottoni -->
                        <div class='form_submit'>
                            <?php /* Se l'operazione è una modifica stampo i tasti modifica*/
                            if($operation == "edit") { ?>
                                <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['modify']); ?>" />
                                <input type="hidden" name="art" value="<?php echo 0 + $loaded_record['capitolo']; ?>">
                                <input type="hidden" name="op" value="doedit">
                            <?php
                            } else { /* Altrimenti il tasto inserisci */ ?>
                                <input type="hidden" name="op" value="insert">
                                <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>" />
                            <?php
                            } ?>
                        </div>
                    </form>
                </div>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_ambientazione">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['plot']['link']['back']); ?>
                    </a>
                </div>
            <?php
            }//if
            if(isset($_REQUEST['op']) === false) { /*Elenco record (Visualizzaione di base della pagina)*/
                //Determinazione pagina (paginazione)
                $pagebegin = (int) gdrcd_filter('get', $_REQUEST['offset']) * $PARAMETERS['settings']['records_per_page'];
                $pageend = $PARAMETERS['settings']['records_per_page'];
                //Conteggio record totali

                $record_globale = gdrcd_query("SELECT COUNT(*) FROM ambientazione");
                $totaleresults = $record_globale['COUNT(*)'];
                //Lettura record
                $result = gdrcd_query("SELECT capitolo, titolo, testo FROM ambientazione ORDER BY capitolo LIMIT ".$pagebegin.", ".$pageend."", 'result');
                $numresults = gdrcd_query($result, 'num_rows');

                /* Se esistono record */
                if($numresults > 0) { ?>
                    <!-- Elenco dei record paginato -->
                    <div class="elenco_record_gestione">
                        <table>
                            <!-- Intestazione tabella -->
                            <tr>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['plot']['art']); ?></div>
                                </td>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['plot']['titolo']); ?></div>
                                </td>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops_col']); ?></div>
                                </td>
                            </tr>
                            <!-- Record -->
                            <?php while($row = gdrcd_query($result, 'fetch')) { ?>
                                <tr class="risultati_elenco_record_gestione">
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco">
                                            <?php echo $row['capitolo']; ?>
                                        </div>
                                    </td>
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco">
                                            <?php echo gdrcd_filter('out', $row['titolo']); ?>
                                        </div>
                                    </td>
                                    <td class="casella_controlli"><!-- Iconcine dei controlli -->
                                                                  <!-- Modifica -->
                                        <div class="controlli_elenco">
                                            <div class="controllo_elenco">
                                                <form class="opzioni_elenco_record_gestione" action="main.php?page=gestione_ambientazione" method="post">
                                                    <input type="hidden" name="id_record" value="<?php echo $row['capitolo'] ?>" />
                                                    <input type="hidden" name="op" value="edit" />
                                                    <input type="image" src="imgs/icons/edit.png" alt="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['edit']); ?>" title="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['edit']); ?>" />
                                                </form>
                                            </div>
                                            <!-- Elimina -->
                                            <div class="controllo_elenco">
                                                <form class="opzioni_elenco_record_gestione" action="main.php?page=gestione_ambientazione" method="post">
                                                    <input type="hidden" name="id_record" value="<?php echo $row['capitolo'] ?>" />
                                                    <input type="hidden" name="op" value="erase" />
                                                    <input type="image" src="imgs/icons/erase.png" alt="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['erase']); ?>" title="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['erase']); ?>" />
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php } //while
                            gdrcd_query($result, 'free');
                            ?>
                        </table>
                    </div>
                <?php
                }//if
                ?>
                <!-- Paginatore elenco -->
                <div class="pager">
                    <?php
                    if($totaleresults > $PARAMETERS['settings']['records_per_page']) {
                        echo gdrcd_filter('out', $MESSAGE['interface']['pager']['pages_name']);
                        for($i = 0; $i <= floor($totaleresults / $PARAMETERS['settings']['records_per_page']); $i++) {
                            if($i != gdrcd_filter('num', $_REQUEST['offset'])) {
                                ?>
                                <a href="main.php?page=gestione_ambientazione&offset=<?php echo $i; ?>"><?php echo $i + 1; ?></a>
                            <?php
                            } else {
                                echo ' '.($i + 1).' ';
                            }
                        } //for
                    }//if
                    ?>
                </div>
                <!-- link crea nuovo -->
                <div class="link_back">
                    <a href="main.php?page=gestione_ambientazione&op=new">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['plot']['link']['new']); ?>
                    </a>
                </div>
            <?php
            }//else
            ?>
        </div>
    <?php
    }//else (controllo permessi utente) ?>
</div><!--Pagina-->
