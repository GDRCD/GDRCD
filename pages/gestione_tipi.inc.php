<div class="pagina_gestione_tipi">
    <?php /*HELP: */
    /*Controllo permessi utente*/
    if($_SESSION['permessi'] < MODERATOR) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
    } else { ?>
        <!-- Titolo della pagina -->
        <?php $types = (isset($_REQUEST['types']) === false) ? 'items' : gdrcd_filter('get', $_REQUEST['types']); ?>
        <div class="page_title">
            <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['types']['page_name'][$types]); ?></h2>
        </div>
        <!-- Corpo della pagina -->
        <div class="page_body">
            <?php /*Inserimento di un nuovo record*/
            if($_POST['op'] == 'insert') {
                /*Eseguo l'inserimento*/
                if($types == "items") {
                    $query = "INSERT INTO codtipooggetto (descrizione) VALUES ('".gdrcd_filter('in', $_POST['nome'])."')";
                }
                if($types == "guilds") {
                    $query = "INSERT INTO codtipogilda (descrizione) VALUES ('".gdrcd_filter('in', $_POST['nome'])."')";
                }
                gdrcd_query($query); ?>
                <div class="warning">
                    <?php echo gdrcd_filter('out', $MESSAGE['warning']['inserted']); ?>
                </div>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_tipi&types=<?php echo $types; ?>">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['types']['link']['back']); ?>
                    </a>
                </div>
            <?php
            }
            /*Cancellatura in un record*/
            if($_POST['op'] == 'erase') {
                /*Eseguo la cancellatura*/
                if($types == "items") {
                    $query = "DELETE FROM codtipooggetto WHERE cod_tipo=".gdrcd_filter('num', $_POST['id_record'])." LIMIT 1";
                    $query_update = "UPDATE oggetto SET tipo = 0 WHERE tipo = ".gdrcd_filter('num', $_POST['id_record'])."";
                }
                if($types == "guilds") {
                    $query = "DELETE FROM codtipogilda WHERE cod_tipo=".gdrcd_filter('num', $_POST['id_record'])." LIMIT 1";
                    $query_update = "UPDATE gilda SET tipo = 0 WHERE tipo = ".gdrcd_filter('num', $_POST['id_record'])."";
                }
                gdrcd_query($query);
                gdrcd_query($query_update);
                ?>
                <div class="warning">
                    <?php echo gdrcd_filter('out', $MESSAGE['warning']['deleted']); ?>
                </div>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_tipi&types=<?php echo $types; ?>">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['types']['link']['back']); ?>
                    </a>
                </div>
            <?php
            }
            /*Modifica di un record*/
            if($_POST['op'] == 'modify') {
                /*Eseguo l'aggiornamento*/
                if($types == "items") {
                    $query = "UPDATE codtipooggetto SET descrizione ='".gdrcd_filter('in', $_POST['nome'])."' WHERE cod_tipo = ".gdrcd_filter('num', $_POST['id_record']
                        )." LIMIT 1";
                }
                if($types == "guilds") {
                    $query = "UPDATE codtipogilda SET descrizione ='".gdrcd_filter('in', $_POST['nome'])."' WHERE cod_tipo = ".gdrcd_filter('num', $_POST['id_record'])." LIMIT 1";
                }
                gdrcd_query($query); ?>
                <div class="warning">
                    <?php echo gdrcd_filter('out', $MESSAGE['warning']['modified']); ?>
                </div>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_tipi&types=<?php echo $types; ?>">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['types']['link']['back']); ?>
                    </a>
                </div>
            <?php
            }/*Form di inserimento/modifica*/
            if((gdrcd_filter('get', $_POST['op']) == 'edit') || (gdrcd_filter('get', $_REQUEST['op']) == 'new')) {
                /*Preseleziono l'operazione di inserimento*/
                $operation = 'insert';
                /*Se è stata richiesta una modifica*/
                if(gdrcd_filter('get', $_POST['op']) == 'edit') {
                    /*Carico il record da modificare*/
                    if($types == "items") {
                        $query = "SELECT * FROM codtipooggetto WHERE cod_tipo=".gdrcd_filter('in', $_POST['id_record'])." LIMIT 1 ";
                    }
                    if($types == "guilds") {
                        $query = "SELECT * FROM codtipogilda WHERE cod_tipo=".gdrcd_filter('in', $_POST['id_record'])." LIMIT 1 ";
                    }

                    $loaded_record = gdrcd_query($query);
                    /*Cambio l'operazione in modifica*/
                    $operation = 'edit';
                }
                ?>
                <!-- Form di inserimento/modifica -->
                <div class="panels_box">
                    <form action="main.php?page=gestione_tipi&types=<?php echo $types; ?>"
                          method="post"
                          class="form_gestione">

                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['types']['name']); ?>
                        </div>
                        <div class='form_field'>
                            <input name="nome"
                                   value="<?php echo gdrcd_filter('out', $loaded_record['descrizione']); ?>" />
                        </div>

                        <!-- bottoni -->
                        <div class='form_submit'>
                            <?php /* Se l'operazione è una modifica stampo i tasti modifica e annulla */
                            if($operation == "edit") {
                                ?>
                                <input type="hidden" name="id_record"
                                       value="<?php echo $loaded_record['cod_tipo']; ?>" />
                                <input type="submit"
                                       value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['types']['submit']['edit']); ?>" />
                                <input type="hidden" name="op" value="modify" />
                            <?php } /* Altrimenti il tasto inserisci */ else { ?>
                                <input type="submit"
                                       value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['types']['submit']['insert']); ?>" />
                                <input type="hidden" name="op" value="insert" />
                            <?php } ?>

                        </div>

                    </form>
                </div>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_tipi&types=<?php echo gdrcd_filter('out', $types); ?>">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['types']['link']['back']); ?>
                    </a>
                </div>
            <?php
            }//if
            if((isset($_POST['op']) === false) && (isset($_REQUEST['op']) === false)) {
                /*Elenco record (Visualizzaione di base della pagina)*/

                //Determinazione pagina (paginazione)
                $pagebegin = (int) gdrcd_filter('get', $_REQUEST['offset']) * $PARAMETERS['settings']['records_per_page'];
                $pageend = $PARAMETERS['settings']['records_per_page'];
                //Conteggio record totali
                if($types == 'items') {
                    $query = "SELECT COUNT(*) FROM codtipooggetto";
                }
                if($types == 'guilds') {
                    $query = "SELECT COUNT(*) FROM codtipogilda";
                }
                $record_globale = gdrcd_query($query);
                $totaleresults = $record_globale['COUNT(*)'];
                //Lettura record
                if($types == 'items') {
                    $query = "SELECT cod_tipo, descrizione FROM codtipooggetto ORDER BY descrizione LIMIT ".$pagebegin.", ".$pageend."";
                }
                if($types == 'guilds') {
                    $query = "SELECT cod_tipo, descrizione FROM codtipogilda ORDER BY descrizione LIMIT ".$pagebegin.", ".$pageend."";
                }
                $result = gdrcd_query($query, 'result');
                $numresults = gdrcd_query($result, 'num_rows');

                /* Se esistono record */
                if($numresults > 0) { ?>
                    <!-- Elenco dei record paginato -->
                    <div class="elenco_record_gestione">
                        <table>
                            <!-- Intestazione tabella -->
                            <tr>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['types']['name']); ?></div>
                                </td>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['types']['code']); ?></div>
                                </td>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops_col']); ?></div>
                                </td>
                            </tr>
                            <!-- Record -->
                            <?php while($row = gdrcd_query($result, 'fetch')) { ?>
                                <tr class="risultati_elenco_record_gestione">
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco"><?php echo gdrcd_filter('out', $row['descrizione']); ?></div>
                                    </td>
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco"><?php echo gdrcd_filter('out', $$row['cod_tipo']); ?></div>
                                    </td>
                                    <td class="casella_controlli"><!-- Iconcine dei controlli -->
                                                                  <!-- Modifica -->
                                        <div class="controlli_elenco">
                                            <div class="controllo_elenco">
                                                <form class="opzioni_elenco_record_gestione"
                                                      action="main.php?page=gestione_tipi&types=<?php echo $types; ?>"
                                                      method="post">
                                                    <input type="hidden" name="id_record"
                                                           value="<?php echo $row['cod_tipo'] ?>" />
                                                    <input type="hidden" name="op" value="edit" />
                                                    <input type="image"
                                                           src="imgs/icons/edit.png"
                                                           alt="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['edit']); ?>"
                                                           title="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['edit']); ?>" />
                                                </form>
                                            </div>
                                            <!-- Elimina -->
                                            <div class="controllo_elenco">
                                                <form class="opzioni_elenco_record_gestione"
                                                      action="main.php?page=gestione_tipi&types=<?php echo $types; ?>"
                                                      method="post">
                                                    <input type="hidden" name="id_record"
                                                           value="<?php echo $row['cod_tipo'] ?>" />
                                                    <input type="hidden" name="op" value="erase" />
                                                    <input type="image"
                                                           src="imgs/icons/erase.png"
                                                           alt="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['erase']); ?>"
                                                           title="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['erase']); ?>" />
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
                <?php }//if ?>
                <!-- Paginatore elenco -->
                <div class="pager">
                    <?php if($totaleresults > $PARAMETERS['settings']['records_per_page']) {
                        echo gdrcd_filter('out', $MESSAGE['interface']['pager']['pages_name']);
                        for($i = 0; $i <= floor($totaleresults / $PARAMETERS['settings']['records_per_page']); $i++) {
                            if($i != gdrcd_filter('num', $_REQUEST['offset'])) { ?>
                                <a href="main.php?page=gestione_tipi&types=<?php echo $types; ?>&offset=<?php echo $i; ?>"><?php echo $i + 1; ?></a>
                            <?php } else {
                                echo ' '.($i + 1).' ';
                            }
                        } //for
                    }//if
                    ?>
                </div>
                <!-- link crea nuovo -->
                <div class="link_back">
                    <a href="main.php?page=gestione_tipi&types=<?php echo $types; ?>&op=new">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['types']['link']['new']); ?>
                    </a>
                </div>
                <?php if($types == 'guilds') { ?>
                    <div class="link_back">
                        <a href="main.php?page=gestione_gilde">
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['types']['link']['guilds']); ?>
                        </a>
                    </div>
                <?php } ?>
                <?php if($types == 'items') { ?>
                    <div class="link_back">
                        <a href="main.php?page=gestione_mercato">
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['types']['link']['items']); ?>
                        </a>
                    </div>
                <?php } ?>
            <?php }//else ?>
        </div>
    <?php }//else (controllo permessi utente) ?>
</div> <!-- Pagina -->