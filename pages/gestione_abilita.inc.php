<div class="pagina_gestione_abilita">
    <?php /*HELP: */
    /*Controllo permessi utente*/
    if($_SESSION['permessi'] < MODERATOR) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
    } elseif($PARAMETERS['mode']['skillsystem'] == 'OFF') {
        echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['warning']['unactive']).'</div>';
    } else { ?>
        <!-- Titolo della pagina -->
        <div class="page_title">
            <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['skills']['page_name']); ?></h2>
        </div>
        <!-- Corpo della pagina -->
        <div class="page_body">
            <?php /*Inserimento di un nuovo record*/
            if($_POST['op'] == 'insert') {
                /*Eseguo l'inserimento*/
                gdrcd_query("INSERT INTO abilita (nome, descrizione, car, id_razza) VALUES ('".gdrcd_filter('in', $_POST['nome'])."', '".gdrcd_filter('in', $_POST['descrizione'])."', ".gdrcd_filter('num', $_POST['car']).", ".gdrcd_filter('num', $_POST['id_razza']).")");
                ?>
                <div class="warning">
                    <?php echo gdrcd_filter('out', $MESSAGE['warning']['inserted']); ?>
                </div>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_abilita">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['skills']['link']['back']); ?>
                    </a>
                </div>
            <?php
            }
            /* Cancellatura in un record */
            if(gdrcd_filter('get', $_POST['op']) == 'erase') {
                /*Eseguo la cancellatura*/
                gdrcd_query("DELETE FROM abilita WHERE id_abilita=".gdrcd_filter('num', $_POST['id_record'])." LIMIT 1");

                /*Aggiorno i personaggi*/
                gdrcd_query("DELETE FROM clgpersonaggioabilita WHERE id_abilita=".gdrcd_filter('num', $_POST['id_record'])."");
                ?>
                <div class="warning">
                    <?php echo gdrcd_filter('out', $MESSAGE['warning']['deleted']); ?>
                </div>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_abilita">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['skills']['link']['back']); ?>
                    </a>
                </div>
            <?php
            }
            /*Modifica di un record*/
            if(gdrcd_filter('get', $_POST['op']) == 'modify') {
                /*Eseguo l'aggiornamento*/
                gdrcd_query("UPDATE abilita SET nome ='".gdrcd_filter('in', $_POST['nome'])."', descrizione ='".gdrcd_filter('in', $_POST['descrizione'])."', car = ".gdrcd_filter('num', $_POST['car']).", id_razza = ".gdrcd_filter('num', $_POST['id_razza'])." WHERE id_abilita = ".gdrcd_filter('num', $_POST['id_record'])." LIMIT 1"); ?>
                <div class="warning">
                    <?php echo gdrcd_filter('out', $MESSAGE['warning']['modified']); ?>
                </div>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_abilita">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['skills']['link']['back']); ?>
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
                    $loaded_record = gdrcd_query("SELECT * FROM abilita WHERE id_abilita=".gdrcd_filter('num', $_POST['id_record'])." LIMIT 1 ");
                    /*Cambio l'operazione in modifica*/
                    $operation = 'edit';
                } ?>
                <!-- Form di inserimento/modifica -->
                <div class="panels_box">
                    <form action="main.php?page=gestione_abilita" method="post" class="form_gestione">
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['skills']['name']); ?>
                        </div>
                        <div class='form_field'>
                            <input name="nome" value="<?php echo $loaded_record['nome']; ?>" />
                        </div>
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['skills']['infos']); ?>
                        </div>
                        <div class='form_field'>
                            <textarea name="descrizione"><?php echo $loaded_record['descrizione']; ?></textarea>
                        </div>
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['skills']['car']); ?>
                        </div>
                        <div class='form_field'>
                            <select name='car'>
                                <option value="0" <?php if($loaded_record['car'] == 0) { echo 'SELECTED'; } ?>>
                                    <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car0']); ?></option>
                                <option value="1" <?php if($loaded_record['car'] == 1) { echo 'SELECTED'; } ?>>
                                    <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car1']); ?></option>
                                <option value="2" <?php if($loaded_record['car'] == 2) { echo 'SELECTED'; } ?>>
                                    <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car2']); ?></option>
                                <option value="3" <?php if($loaded_record['car'] == 3) { echo 'SELECTED'; } ?>>
                                    <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car3']); ?></option>
                                <option value="4" <?php if($loaded_record['car'] == 4) { echo 'SELECTED'; } ?>>
                                    <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car4']); ?></option>
                                <option value="5" <?php if($loaded_record['car'] == 5) { echo 'SELECTED'; } ?>>
                                    <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car5']); ?></option>
                            </select>
                        </div>
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['skills']['race']); ?>
                        </div>
                        <div class='form_field'>
                            <select name='id_razza'>
                                <option value="-1" <?php if($loaded_record['id_razza'] == -1) { echo 'SELECTED'; } ?>>
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['skills']['no_race']); ?>
                                </option>
                                <?php
                                $result = gdrcd_query("SELECT id_razza, nome_razza FROM razza ORDER BY nome_razza", 'result');
                                while($raz = gdrcd_query($result, 'fetch')) {
                                    ?>
                                    <option value="<?php echo $raz['id_razza']; ?>" <?php if($loaded_record['id_razza'] == $raz['id_razza']) { echo 'SELECTED'; } ?> >
                                        <?php echo gdrcd_filter('out', $raz['nome_razza']); ?>
                                    </option>
                                <?php
                                }
                                gdrcd_query($result, 'free');
                                ?>
                            </select>
                        </div>
                        <!-- bottoni -->
                        <div class='form_submit'>
                            <?php /* Se l'operazione è una modifica stampo i tasti modifica e annulla */
                            if($operation == "edit") { ?>
                                <input type="hidden" name="id_record" value="<?php echo $loaded_record['id_abilita']; ?>">
                                <input type="hidden" name="op" value="modify" />
                                <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['skills']['submit']['edit']); ?>" />
                            <?php
                            }  else {  /* Altrimenti il tasto inserisci */ ?>
                                <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['skills']['submit']['insert']); ?>" />
                                <input type="hidden" name="op" value="insert" />
                            <?php
                            } ?>
                        </div>
                    </form>
                </div>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_abilita">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['skills']['link']['back']); ?>
                    </a>
                </div>
            <?php }
            //if
            if(isset($_REQUEST['op']) === false) { /*Elenco record (Visualizzaione di base della pagina)*/
                //Determinazione pagina (paginazione)
                $pagebegin = (int) gdrcd_filter('get', $_REQUEST['offset']) * $PARAMETERS['settings']['records_per_page'];
                $pageend = $PARAMETERS['settings']['records_per_page'];
                //Conteggio record totali
                $record_globale = gdrcd_query("SELECT COUNT(*) FROM abilita");
                $totaleresults = $record_globale['COUNT(*)'];

                //Lettura record
                $result = gdrcd_query("SELECT id_abilita, nome, car FROM abilita ORDER BY nome LIMIT ".$pagebegin.", ".$pageend."", 'result');
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
                                    <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['skills']['car']
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
                                        <div class="elementi_elenco">
                                            <?php echo $row['nome']; ?>
                                        </div>
                                    </td>
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco">
                                            <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car'.$row['car'].'']); ?>
                                        </div>
                                    </td>
                                    <td class="casella_controlli"><!-- Iconcine dei controlli -->
                                                                  <!-- Modifica -->
                                        <div class="controlli_elenco">
                                            <div class="controllo_elenco">
                                                <form class="opzioni_elenco_record_gestione" action="main.php?page=gestione_abilita" method="post">
                                                    <input type="hidden" name="id_record" value="<?php echo $row['id_abilita'] ?>" />
                                                    <input type="hidden" name="op" value="edit" />
                                                    <input type="image" alt="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['edit']); ?>" title="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['edit']); ?>" src="imgs/icons/edit.png" />
                                                </form>
                                            </div>
                                            <!-- Elimina -->
                                            <div class="controllo_elenco">
                                                <form class="opzioni_elenco_record_gestione" action="main.php?page=gestione_abilita" method="post">
                                                    <input type="hidden" name="id_record" value="<?php echo $row['id_abilita'] ?>" />
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
                    <?php if($totaleresults > $PARAMETERS['settings']['records_per_page']) {
                        echo gdrcd_filter('out', $MESSAGE['interface']['pager']['pages_name']);
                        for($i = 0; $i <= floor($totaleresults / $PARAMETERS['settings']['records_per_page']); $i++) {
                            if($i != gdrcd_filter('num', $_REQUEST['offset'])) {
                                ?>
                                <a href="main.php?page=gestione_abilita&offset=<?php echo $i; ?>"><?php echo $i + 1; ?></a>
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
                    <a href="main.php?page=gestione_abilita&op=new">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['skills']['link']['new']); ?>
                    </a>
                </div>
            <?php
            }//else
            ?>
        </div>
    <?php
    }//else (controllo permessi utente) ?>
</div><!--Pagina-->