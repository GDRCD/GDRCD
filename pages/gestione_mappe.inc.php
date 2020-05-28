<div class="pagina_gestione_mappe">
    <?php /*HELP: */
    /*Controllo permessi utente*/
    if($_SESSION['permessi'] < MODERATOR) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
    } else { ?>
        <!-- Titolo della pagina -->
        <div class="page_title">
            <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['page_name']); ?></h2>
        </div>
        <!-- Corpo della pagina -->
        <div class="page_body">
            <?php /*Inserimento di un nuovo record*/
            if(gdrcd_filter('get', $_POST['op']) == 'insert') {
                /*Processo le informazioni ricevute dal form*/
                $is_mobile = ((isset($_POST['mobile']) == true) && ($_POST['mobile'] == 'is_mobile')) ? 1 : 0;

                $immagine = ($_POST['immagine'] == "") ? "standard_mappa.png" : gdrcd_filter('in', $_POST['immagine']);

                /*Eseguo l'inserimento*/
                gdrcd_query("INSERT INTO mappa_click (nome, posizione, mobile, immagine, larghezza, altezza) VALUES ('".gdrcd_filter('in', $_POST['nome'])."', ".gdrcd_filter('num', $_POST['posizione']).", ".$is_mobile.", '".$immagine."', ".gdrcd_filter('num', $_POST['larghezza']).", ".gdrcd_filter('num', $_POST['altezza']).")");
                ?>
                <div class="warning">
                    <?php echo gdrcd_filter('out', $MESSAGE['warning']['inserted']); ?>
                </div>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_mappe">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['link']['back']); ?>
                    </a>
                </div>
            <?php
            }
            /* Cancellatura in un record */
            if(gdrcd_filter('get', $_POST['op']) == 'erase') {
                /*Eseguo la cancellatura*/
                gdrcd_query("DELETE FROM mappa_click WHERE id_click=".gdrcd_filter('num', $_POST['id_record'])." LIMIT 1");
                ?>
                <div class="warning">
                    <?php echo gdrcd_filter('out', $MESSAGE['warning']['deleted']); ?>
                </div>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_mappe">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['link']['back']); ?>
                    </a>
                </div>
            <?php
            }
            /*Modifica di un record*/
            if(gdrcd_filter('get', $_POST['op']) == 'modify') {
                /*Processo le informazioni ricevute dal form*/
                $is_mobile = ((isset($_POST['mobile']) == true) && ($_POST['mobile'] == 'is_mobile')) ? 1 : 0;

                $immagine = ($_POST['immagine'] == "") ? "standard_mappa.png" : gdrcd_filter('in', $_POST['immagine']);
                /*Eseguo l'aggiornamento*/
                gdrcd_query("UPDATE mappa_click SET nome ='".gdrcd_filter('in', $_POST['nome'])."', mobile = ".$is_mobile.", immagine = '".gdrcd_filter('in', $immagine)."', posizione = ".gdrcd_filter('num', $_POST['posizione']).", larghezza = ".gdrcd_filter('num', $_POST['larghezza']).", altezza = ".gdrcd_filter('num', $_POST['altezza'])."  WHERE id_click = ".gdrcd_filter('num', $_POST['id_record'])." LIMIT 1");
                ?>
                <div class="warning">
                    <?php echo gdrcd_filter('out', $MESSAGE['warning']['modified']); ?>
                </div>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_mappe">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['link']['back']); ?>
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
                    $loaded_record = gdrcd_query("SELECT * FROM mappa_click WHERE id_click=".gdrcd_filter('num', $_POST['id_record'])." LIMIT 1 ");
                    /*Cambio l'operazione in modifica*/
                    $operation = 'edit';
                } ?>
                <!-- Form di inserimento/modifica -->
                <div class="panels_box">
                    <form action="main.php?page=gestione_mappe" method="post" class="form_gestione">
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['name']); ?>
                        </div>
                        <div class='form_field'>
                            <input name="nome" value="<?php echo gdrcd_filter('out', $loaded_record['nome']); ?>" />
                        </div>
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['is_mobile']); ?>
                        </div>
                        <div class='form_field'>
                            <input type="checkbox" name="mobile" <?php if($loaded_record['mobile'] == 1) { ?>checked="checked"<?php } ?> value="is_mobile" />
                        </div>
                        <div class='form_info'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['is_mobile_info']); ?>
                        </div>
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['position']); ?>
                        </div>
                        <div class='form_field'>
                            <input name="posizione" value="<?php echo 0 + gdrcd_filter('out', $loaded_record['posizione']); ?>" />
                        </div>
                        <div class='form_info'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['position_info']); ?>
                        </div>
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['image']); ?>
                        </div>
                        <div class='form_field'>
                            <input name="immagine" value="<?php echo gdrcd_filter('out', $loaded_record['immagine']); ?>" />
                        </div>
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['width']); ?>
                        </div>
                        <div class='form_field'>
                            <input name="larghezza" value="<?php echo gdrcd_filter('out', $loaded_record['larghezza']); ?>" />
                        </div>
                        <div class='form_info'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['width_info']); ?>
                        </div>
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['height']); ?>
                        </div>
                        <div class='form_field'>
                            <input name="altezza" value="<?php echo gdrcd_filter('out', $loaded_record['altezza']); ?>" />
                        </div>
                        <div class='form_info'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['height_info']); ?>
                        </div>
                        <!-- bottoni -->
                        <div class='form_submit'>
                            <?php /* Se l'operazione è una modifica stampo i tasti modifica e annulla */
                            if($operation == "edit") { ?>
                                <input type="hidden" name="id_record" value="<?php echo gdrcd_filter('out', $loaded_record['id_click']); ?>">
                                <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['submit']['edit']); ?>" />
                                <input type="hidden" name="op" value="modify">
                            <?php
                            } else { /* Altrimenti il tasto inserisci */ ?>
                                <input type="hidden" name="op" value="insert">
                                <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['submit']['insert']); ?>" />
                            <?php
                            } ?>
                        </div>
                    </form>
                </div>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_mappe">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['link']['back']); ?>
                    </a>
                </div>
            <?php
            }//if
            if((isset($_POST['op']) === false) && (isset($_REQUEST['op']) === false)) { /*Elenco record (Visualizzaione di base della pagina)*/
                //Determinazione pagina (paginazione)
                $pagebegin = (int) gdrcd_filter('get', $_REQUEST['offset']) * $PARAMETERS['settings']['records_per_page'];
                $pageend = $PARAMETERS['settings']['records_per_page'];
                //Conteggio record totali
                $record_globale = gdrcd_query("SELECT COUNT(*) FROM mappa_click");
                $totaleresults = $record_globale['COUNT(*)'];
                //Lettura record
                $result = gdrcd_query("SELECT id_click, nome, mobile, posizione FROM mappa_click ORDER BY nome LIMIT ".$pagebegin.", ".$pageend."", 'result');
                $numresults = gdrcd_query($result, 'num_rows');

                /* Se esistono record */
                if($numresults > 0) { ?>
                    <!-- Elenco dei record paginato -->
                    <div class="elenco_record_gestione">
                        <table>
                            <!-- Intestazione tabella -->
                            <tr>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['name_col']); ?></div>
                                </td>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['position']
                                        ); ?></div>
                                </td>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['is_mobile']
                                        ); ?></div>
                                </td>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops_col']); ?></div>
                                </td>
                            </tr>
                            <!-- Record -->
                            <?php while($row = gdrcd_query($result, 'fetch')) { ?>
                                <tr class="risultati_elenco_record_gestione">
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco"><?php echo gdrcd_filter('out', $row['nome']); ?></div>
                                    </td>
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco"><?php echo gdrcd_filter('out', $row['posizione']); ?></div>
                                    </td>
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco"><?php if($row['mobile'] == 1) {
                                                echo gdrcd_filter('out', $MESSAGE['interface']['administration']['yes']);
                                            } else {
                                                echo gdrcd_filter('out', $MESSAGE['interface']['administration']['no']);
                                            } ?>
                                        </div>
                                    </td>
                                    <td class="casella_controlli"><!-- Iconcine dei controlli -->
                                                                  <!-- Modifica -->
                                        <div class="controlli_elenco">
                                            <div class="controllo_elenco">
                                                <form class="opzioni_elenco_record_gestione" action="main.php?page=gestione_mappe" method="post">
                                                    <input type="hidden" name="id_record" value="<?php echo gdrcd_filter('out', $row['id_click']) ?>" />
                                                    <input type="hidden" name="op" value="edit" />
                                                    <input type="image" src="imgs/icons/edit.png"
                                                           alt="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['edit']); ?>"
                                                           title="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['edit']); ?>" />
                                                </form>
                                            </div>
                                            <!-- Elimina -->
                                            <div class="controllo_elenco">
                                                <form class="opzioni_elenco_record_gestione" action="main.php?page=gestione_mappe" method="post">
                                                    <input type="hidden" name="id_record" value="<?php echo gdrcd_filter('out', $row['id_click']) ?>" />
                                                    <input type="hidden" name="op" value="erase" />
                                                    <input type="image" src="imgs/icons/erase.png"
                                                           alt="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['erase']); ?>"
                                                           title="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['erase']
                                                           ); ?>" />
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                            } //while
                            gdrcd_query($result, 'free');
                            ?>
                        </table>
                    </div>
                <?php
                }//if
                ?>
                <!-- Paginatore elenco -->
                <div class="pager">
                    <?php if($totaleresults > $PARAMETERS['settings']['records_per_page']) {
                        echo gdrcd_filter('out', $MESSAGE['interface']['pager']['pages_name']);
                        for($i = 0; $i <= floor($totaleresults / $PARAMETERS['settings']['records_per_page']); $i++) {
                            if($i != gdrcd_filter('num', $_REQUEST['offset'])) { ?>
                                <a href="main.php?page=gestione_mappe&offset=<?php echo $i; ?>"><?php echo $i + 1; ?></a>
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
                    <a href="main.php?page=gestione_mappe&op=new">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['link']['new']); ?>
                    </a>
                </div>
            <?php }//else ?>
        </div>
    <?php }//else (controllo permessi utente) ?>
</div><!--Pagina-->