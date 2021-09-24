<div class="gestione_bacheche">
    <?php /*HELP: */
    /*Controllo permessi utente*/
    if($_SESSION['permessi'] < MODERATOR) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
    } else { ?>
        <!-- Titolo della pagina -->
        <div class="page_title">
            <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['forums']['page_name']); ?></h2>
        </div>
        <!-- Corpo della pagina -->
        <div class="page_body">
            <?php /*Inserimento di un nuovo record*/
            if(gdrcd_filter('get', $_POST['op']) == $MESSAGE['interface']['administration']['forums']['submit']['insert']) { ?>
                <?php /*Eseguo l'inserimento*/
                gdrcd_query("INSERT INTO araldo (nome, tipo, proprietari) VALUES ('".gdrcd_filter('in', $_POST['nome'])."', ".gdrcd_filter('num', $_POST['tipo']).", ".gdrcd_filter('num', $_POST['owner']).")"); ?>
                <div class="warning">
                    <?php echo gdrcd_filter('out', $MESSAGE['warning']['inserted']); ?>
                </div>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_bacheche">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['forums']['link']['back']); ?>
                    </a>
                </div>
            <?php
            }
            /*Cancellatura in un record*/
            if($_POST['op'] == 'erase') { /*Eseguo la cancellatura*/
                gdrcd_query("DELETE FROM messaggioaraldo WHERE id_araldo=".gdrcd_filter('num', $_POST['id_record'])."");
                gdrcd_query("DELETE FROM araldo WHERE id_araldo=".gdrcd_filter('num', $_POST['id_record'])." LIMIT 1");
                ?>
                <div class="warning">
                    <?php echo gdrcd_filter('out', $MESSAGE['warning']['deleted']); ?>
                </div>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_bacheche">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['forums']['link']['back']); ?>
                    </a>
                </div>
            <?php }
            /*Modifica di un record*/
            if($_POST['op'] == $MESSAGE['interface']['administration']['forums']['submit']['edit']) {
                /*Eseguo l'aggiornamento*/
                gdrcd_query("UPDATE araldo SET nome ='".gdrcd_filter('in', $_POST['nome'])."', tipo = ".gdrcd_filter('num', $_POST['tipo']).", proprietari = ".gdrcd_filter('num', $_POST['owner'])." WHERE id_araldo = ".gdrcd_filter('num', $_POST['id_record'])." LIMIT 1");
                ?>
                <div class="warning">
                    <?php echo gdrcd_filter('out', $MESSAGE['warning']['modified']); ?>
                </div>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_bacheche">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['forums']['link']['back']); ?>
                    </a>
                </div>
            <?php
            }
            /*Form di inserimento/modifica*/
            if((gdrcd_filter('get', $_POST['op']) == 'edit') || (gdrcd_filter('get', $_REQUEST['op']) == 'new')) {
                /*Preseleziono l'operazione di inserimento*/
                $operation = 'insert';
                /*Se è stata richiesta una modifica*/
                if(gdrcd_filter('get', $_POST['op']) == 'edit') {
                    /*Carico il record da modificare*/
                    $loaded_record = gdrcd_query("SELECT * FROM araldo WHERE id_araldo=".gdrcd_filter('num', $_POST['id_record'])." LIMIT 1 ");
                    /*Cambio l'operazione in modifica*/
                    $operation = 'edit';
                }
                ?>
                <!-- Form di inserimento/modifica -->
                <div class="panels_box">
                    <form action="main.php?page=gestione_bacheche" method="post" class="form_gestione">
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['forums']['name']); ?>
                        </div>
                        <div class='form_field'>
                            <input name="nome" value="<?php echo $loaded_record['nome']; ?>" />
                        </div>
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['forums']['type']['name']); ?>
                        </div>
                        <div class='form_field'>
                            <!-- Elenco dei tipi -->
                            <select name="tipo">
                                <option value="<?php echo INGIOCO; ?>"
                                    <?php if($loaded_record['tipo'] == INGIOCO) {echo "selected";} ?>>
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['type'][INGIOCO]); ?>
                                </option>
                                <option value="<?php echo PERTUTTI; ?>"
                                    <?php if($loaded_record['tipo'] == PERTUTTI) {echo "selected";} ?>>
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['type'][PERTUTTI]); ?>
                                </option>
                                <option value="<?php echo SOLORAZZA; ?>"
                                    <?php if($loaded_record['tipo'] == SOLORAZZA) {echo "selected";} ?>>
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['type'][SOLORAZZA]); ?>
                                </option>
                                <option value="<?php echo SOLOGILDA; ?>"
                                    <?php if($loaded_record['tipo'] == SOLOGILDA) {echo "selected";} ?>>
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['type'][SOLOGILDA]); ?>
                                </option>
                                <option value="<?php echo SOLOMASTERS; ?>"
                                    <?php if($loaded_record['tipo'] == SOLOMASTERS) {echo "selected";} ?>>
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['type'][SOLOMASTERS]); ?>
                                </option>
                                <option value="<?php echo SOLOMODERATORS; ?>"
                                    <?php if($loaded_record['tipo'] == SOLOMODERATORS) {echo "selected";} ?>>
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['type'][SOLOMODERATORS]); ?>
                                </option>
                            </select>
                        </div>
                        <div class='form_info'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['type']['info']); ?>
                        </div>
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['forums']['owner']); ?>
                        </div>
                        <div class='form_field'>
                            <?php /* Carico l'elenco delle mappe inserite */
                            $razze = gdrcd_query("SELECT id_razza, nome_razza FROM razza", 'result');
                            $gilde = gdrcd_query("SELECT id_gilda, nome FROM gilda", 'result'); ?>
                            <!-- Elenco delle mappe -->
                            <select name="owner">
                                <!-- Opzione "Nessuna" -->
                                <option value="-1"><!-- Opzione "Nessuno" -->
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['forums']['no_owner']); ?>
                                </option>
                                <?php
                                while($option = gdrcd_query($razze, 'fetch')) { ?>
                                    <option value="<?php echo gdrcd_filter('out', $option['id_razza']); ?>"
                                        <?php if(($loaded_record['proprietari'] == $option['id_razza']) && ($loaded_record['tipo'] == SOLORAZZA)) {echo 'SELECTED';} ?>>
                                        <?php echo gdrcd_filter('out', $option['nome_razza']); ?>
                                    </option>
                                <?php
                                }
                                gdrcd_query($razze, 'free');

                                while($option = gdrcd_query($gilde, 'fetch')) { ?>
                                    <option value="<?php echo gdrcd_filter('out', $option['id_gilda']); ?>"
                                        <?php if(($loaded_record['proprietari'] == $option['id_gilda']) && ($loaded_record['tipo'] == SOLOGILDA)) {
                                            echo 'SELECTED';
                                        } ?>>
                                        <?php echo gdrcd_filter('out', $option['nome']); ?>
                                    </option>
                                <?php
                                }
                                gdrcd_query($gilde, 'free');
                                ?>
                            </select>
                        </div>
                        <!-- bottoni -->
                        <div class='form_submit'>
                            <?php /* Se l'operazione è una modifica stampo i tasti modifica e annulla */
                            if($operation == "edit") { ?>
                                <input type="hidden" name="id_record" value="<?php echo $loaded_record['id_araldo']; ?>">
                                <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['forums']['submit']['edit']); ?>" name="op" />
                                <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['forums']['submit']['undo']); ?>" name="cancel" />
                            <?php
                            } else { /* Altrimenti il tasto inserisci */ ?>
                                <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['forums']['submit']['insert']); ?>" name="op" />
                            <?php
                            } ?>
                        </div>
                    </form>
                </div>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_bacheche"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['forums']['link']['back']); ?></a>
                </div>
            <?php
            }
            /*Elenco record (Visualizzaione di base della pagina)*/
            if((isset($_POST['op']) === false) && (isset($_REQUEST['op']) === false)) {
                //Determinazione pagina (paginazione)
                $pagebegin = (int) $_REQUEST['offset'] * $PARAMETERS['settings']['records_per_page'];
                $pageend = $PARAMETERS['settings']['records_per_page'];
                //Conteggio record totali
                $record_globale = gdrcd_query("SELECT COUNT(*) FROM araldo");
                $totaleresults = $record_globale['COUNT(*)'];

                //Lettura record
                $result = gdrcd_query("SELECT id_araldo, nome, tipo FROM araldo ORDER BY nome LIMIT ".$pagebegin.", ".$pageend."", 'result');
                $numresults = gdrcd_query($result, 'num_rows');

                /* Se esistono record */
                if($numresults > 0) { ?>
                    <!-- Elenco dei record paginato -->
                    <div class="elenco_record_gestione">
                        <table>
                            <!-- Intestazione tabella -->
                            <tr>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['forums']['name']); ?></div>
                                </td>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['forums']['type']['name']); ?></div>
                                </td>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops_col']); ?></div>
                                </td>
                            </tr>
                            <!-- Record -->
                            <?php while($row = gdrcd_query($result, 'fetch')) { ?>
                                <tr>
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco">
                                            <?php echo gdrcd_filter('out', $row['nome']); ?>
                                        </div>
                                    </td>
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco">
                                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['type'][$row['tipo']]); ?>
                                        </div>
                                    </td>
                                    <!-- Icone dei controlli -->
                                    <td class="casella_controlli">
                                        <!-- Modifica -->
                                        <div class="controlli_elenco">
                                            <div class="controllo_elenco">
                                                <form action="main.php?page=gestione_bacheche" method="post">
                                                    <input type="hidden" name="id_record" value="<?php echo $row['id_araldo'] ?>" />
                                                    <input type="hidden" name="op" value="edit" />
                                                    <input type="image" src="imgs/icons/edit.png" alt="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['edit']); ?>" title="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['edit']); ?>" />
                                                </form>
                                            </div>
                                            <!-- Elimina -->
                                            <div class="controllo_elenco">
                                                <form action="main.php?page=gestione_bacheche" method="post">
                                                    <input type="hidden" name="id_record" value="<?php echo $row['id_araldo'] ?>" />
                                                    <input type="hidden" name="op" value="erase" />
                                                    <input type="image" src="imgs/icons/erase.png" alt="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['erase']); ?>" title="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['erase']); ?>" />
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
                                <a href="main.php?page=gestione_bacheche&offset=<?php echo $i; ?>">
                                    <?php echo $i + 1; ?>
                                </a>
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
                    <a href="main.php?page=gestione_bacheche&op=new">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['forums']['link']['new']); ?>
                    </a>
                </div>
            <?php
            }//else
            ?>
        </div><!-- panels_box -->
    <?php
    }//else (controllo permessi utente) ?>
</div><!-- pagina -->
