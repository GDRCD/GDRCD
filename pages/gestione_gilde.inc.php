<div class="pagina_gestione_gilde">
    <?php /*HELP: */
    /*Controllo permessi utente*/
    if($_SESSION['permessi'] < MODERATOR) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
    } else { ?>
        <!-- Titolo della pagina -->
        <div class="page_title">
            <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['page_name']); ?></h2>
        </div>
        <!-- Corpo della pagina -->
        <div class="page_body">
            <?php /*Inserimento di un nuovo ruolo nella gilda corrente*/
            if(gdrcd_filter('get', $_POST['op']) == 'nuovo_ruolo') {
                /*Processo le informazioni ricevute dal form*/
                $is_capo = (isset($_POST['capo']) == true) && ($_POST['capo'] == 'is_capo') ? 1 : 0;

                $immagine = ($_POST['immagine'] == '') ? "standard_gilda.png" : gdrcd_filter('in', $_POST['immagine']);

                /*Eseguo l'inserimento*/
                gdrcd_query("INSERT INTO ruolo (nome_ruolo, gilda, immagine, stipendio, capo) VALUES ('".gdrcd_filter('in', $_POST['nome'])."', ".gdrcd_filter('num', $_POST['gilda']).", '".gdrcd_filter('in', $immagine)."', '".gdrcd_filter('num', $_POST['stipendio'])."', '".$is_capo."')");
                ?>
                <!-- Conferma -->
                <div class="warning">
                    <?php echo gdrcd_filter('out', $MESSAGE['warning']['inserted']); ?>
                </div>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_gilde&op=edit&id_record=<?php echo gdrcd_filter('num', $_POST['gilda']); ?>">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['link']['back']); ?>
                    </a>
                </div>
            <?php
            }
            /*Inserimento di un nuovo record*/
            if(gdrcd_filter('get', $_POST['op']) == $MESSAGE['interface']['administration']['guilds']['submit']['insert']) {
                /*Processo le informazioni ricevute dal form*/
                $is_visible = ((isset($_POST['visible']) == true) && ($_POST['visible'] == 'is_visible')) ? 1 : 0;

                $url_sito = ((isset($_POST['url_sito']) == true) && ($_POST['url_sito'] == 'http://')) ? '' :  $_POST['url_sito'];

                $immagine = ($_POST['immagine'] == '') ? "standard_gilda.png" : gdrcd_filter('in', $_POST['immagine']);

                /*Eseguo l'inserimento*/
                gdrcd_query("INSERT INTO gilda (nome, tipo, immagine, url_sito, visibile, statuto) VALUES ('".gdrcd_filter('in', $_POST['nome'])."', ".gdrcd_filter('in', $_POST['tipo']).", '".gdrcd_filter('in', $immagine)."', '".gdrcd_filter('in', $_POST['url_sito'])."', '".$is_visible."', '".gdrcd_filter('in', $_POST['statuto'])."')");
                ?>
                <!-- Conferma -->
                <div class="warning">
                    <?php echo gdrcd_filter('out', $MESSAGE['warning']['inserted']); ?>
                </div>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_gilde">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['link']['back']); ?>
                    </a>
                </div>
            <?php
            }
            /* Cancellatura in un record */
            if(gdrcd_filter('get', $_POST['op']) == 'erase') {
                /*Eseguo la cancellatura*/
                $result = gdrcd_query("SELECT id_ruolo FROM ruolo WHERE gilda = ".gdrcd_filter('num', $_POST['id_record'])."", 'result');

                while($row = gdrcd_query($result, 'fetch')) {
                    gdrcd_query("DELETE FROM clgpersonaggioruolo WHERE id_ruolo=".gdrcd_filter('num', $row['id_ruolo'])."");
                }
                gdrcd_query($result, 'free');
                gdrcd_query("DELETE FROM ruolo WHERE gilda = ".gdrcd_filter('num', $_POST['id_record'])."");
                gdrcd_query("DELETE FROM gilda WHERE id_gilda=".gdrcd_filter('num', $_POST['id_record'])." LIMIT 1");
                ?>
                <!-- Conferma -->
                <div class="warning">
                    <?php echo gdrcd_filter('out', $MESSAGE['warning']['deleted']); ?>
                </div>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_gilde">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['link']['back']); ?>
                    </a>
                </div>
            <?php
            }
            /* Cancellatura in un ruolo */
            if((gdrcd_filter('get', $_POST['op']) == $MESSAGE['interface']['administration']['guilds']['role']['submit']['delete']) && ($_POST['provenienza'] == 'ruolo')) { /*Eseguo la cancellatura*/
                gdrcd_query("DELETE FROM clgpersonaggioruolo WHERE id_ruolo=".gdrcd_filter('num', $_POST['id_ruolo'])."");
                gdrcd_query("DELETE FROM ruolo WHERE id_ruolo=".gdrcd_filter('num', $_POST['id_ruolo'])." LIMIT 1");
                ?>
                <!-- Conferma -->
                <div class="warning">
                    <?php echo gdrcd_filter('out', $MESSAGE['warning']['deleted']); ?>
                </div>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_gilde&op=edit&id_record=<?php echo gdrcd_filter('num', $_POST['gilda']) ?>"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['link']['back']); ?></a>
                </div>
            <?php
            }
            /*Modifica di un record*/
            if((gdrcd_filter('get', $_POST['op']) == $MESSAGE['interface']['administration']['guilds']['submit']['edit']) && (isset($_POST['provenienza']) == false)) {
                /*Processo le informazioni ricevute dal form*/
                $is_visible = ((isset($_POST['visible']) == true) && ($_POST['visible'] == 'is_visible')) ? 1 : 0;

                $url_sito = ((isset($_POST['url_sito']) == true) && ($_POST['url_sito'] == 'http://')) ? '' :  $_POST['url_sito'];

                $immagine = ($_POST['immagine'] == '') ? "standard_gilda.png" : gdrcd_filter('in', $_POST['immagine']);

                /*Eseguo l'aggiornamento*/
                gdrcd_query("UPDATE gilda SET nome ='".gdrcd_filter('in', $_POST['nome'])."', visibile = ".$is_visible.", immagine = '".gdrcd_filter('in', $immagine)."', tipo = ".gdrcd_filter('in', $_POST['tipo']).", url_sito = '".gdrcd_filter('in', $url_sito)."', statuto='".gdrcd_filter('in', $_POST['statuto'])."' WHERE id_gilda = ".gdrcd_filter('num', $_POST['id_record'])." LIMIT 1");
                ?>
                <!-- Conferma -->
                <div class="warning">
                    <?php echo gdrcd_filter('out', $MESSAGE['warning']['modified']); ?>
                </div>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_gilde">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['link']['back']); ?>
                    </a>
                </div>
            <?php
            }
            /*Modifica di un ruolo*/
            if((gdrcd_filter('get', $_POST['op']) == $MESSAGE['interface']['administration']['guilds']['role']['submit']['edit']) && ($_POST['provenienza'] == 'ruolo')) {
                /*Processo le informazioni ricevute dal form*/
                $is_capo = (isset($_POST['capo']) == true) && ($_POST['capo'] == 'is_capo') ? 1 : 0;

                $immagine = ($_POST['immagine'] == '') ? "standard_gilda.png" : gdrcd_filter('in', $_POST['immagine']);
                /*Eseguo l'aggiornamento*/
                gdrcd_query("UPDATE ruolo SET nome_ruolo ='".gdrcd_filter('in', $_POST['nome'])."', capo = ".$is_capo.", immagine = '".gdrcd_filter('in', $immagine)."', gilda = ".gdrcd_filter('num', $_POST['gilda']).", stipendio = ".gdrcd_filter('num', $_POST['stipendio'])." WHERE id_ruolo = ".gdrcd_filter('num', $_POST['id_ruolo'])." LIMIT 1");
                ?>
                <!-- Conferma -->
                <div class="warning">
                    <?php echo gdrcd_filter('out', $MESSAGE['warning']['modified']); ?>
                </div>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_gilde&op=edit&id_record=<?php echo $_POST['gilda'] ?>">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['link']['back']); ?>
                    </a>
                </div>
            <?php
            }
            /*Form di inserimento/modifica*/
            if((gdrcd_filter('get', $_REQUEST['op']) == 'edit') || (gdrcd_filter('get', $_REQUEST['op']) == 'new')) {
                /*Preseleziono l'operazione di inserimento*/
                $operation = 'insert';
                /*Se è stata richiesta una modifica*/
                if((gdrcd_filter('get', $_REQUEST['op']) == 'edit') && (gdrcd_filter('get', $_REQUEST['id_record'] > -1))) {
                    /*Carico il record da modificare*/
                    $loaded_record = gdrcd_query("SELECT * FROM gilda WHERE id_gilda=".gdrcd_filter('get', $_REQUEST['id_record'])." LIMIT 1 ");
                    /*Cambio l'operazione in modifica*/
                    $operation = 'edit';
                }//if

                if((isset($_REQUEST['id_record']) === false) || (gdrcd_filter('get', $_REQUEST['id_record'] > -1))) { ?>
                    <!-- Form di inserimento/modifica -->
                    <div class="panels_box">
                        <form action="main.php?page=gestione_gilde" method="post" class="form_gestione">
                            <div class='form_label'>
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['name']); ?>
                            </div>
                            <div class='form_field'>
                                <input name="nome" value="<?php echo gdrcd_filter('out', $loaded_record['nome']); ?>" />
                            </div>
                            <div class='form_label'>
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['type']); ?>
                            </div>
                            <div class='form_field'>
                                <?php /* Carico l'elenco dei tipi di gilda */
                                $tipi = gdrcd_query("SELECT cod_tipo, descrizione FROM codtipogilda", 'result');
                                /*Se sono presenti tipi sul database*/
                                if(gdrcd_query($tipi, 'num_rows') > 0) { ?>
                                    <!-- Elenco dei tipi -->
                                    <select name="tipo">
                                        <?php while($option = gdrcd_query($tipi, 'fetch')) { ?>
                                            <option value="<?php echo $option['cod_tipo']; ?>" <?php if($loaded_record['tipo'] == $option['cod_tipo']) {echo 'SELECTED';} ?>>
                                                <?php echo gdrcd_filter('out', $option['descrizione']); ?>
                                            </option>
                                        <?php }
                                        gdrcd_query($tipi, 'free');
                                        ?>
                                    </select>
                                <?php
                                } else { /*Altrimenti segnalo l'assenza di tipi*/
                                    echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['type_err']);
                                } ?>
                            </div>
                            <div class="link_back">
                                <a href="main.php?page=gestione_tipi&types=guilds">
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['link']['menage_types']); ?>
                                </a>
                            </div>

                            <div class='form_label'>
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['image']); ?>
                            </div>
                            <div class='form_field'>
                                <input name="immagine" value="<?php echo gdrcd_filter('out', $loaded_record['immagine']); ?>" />
                            </div>

                            <div class='form_label'>
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['site']); ?>
                            </div>
                            <div class='form_field'>
                                <input name="url_sito" value="<?php if(isset($loaded_record['url_sito']) === true) {
                                           echo gdrcd_filter('out', $loaded_record['url_sito']);
                                       } else {
                                           echo "http://";
                                       } ?>" />
                            </div>
                            <div class='form_label'>
                                Statuto
                            </div>
                            <div class='form_field'><textarea name="statuto"><?php echo gdrcd_filter('out', $loaded_record['statuto']); ?></textarea>
                            </div>
                            <div class='form_label'>
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['visible']); ?>
                            </div>
                            <div class='form_field'>
                                <input type="checkbox" name="visible"
                                    <?php if(gdrcd_filter('out', $loaded_record['visibile']) == 1) { ?>
                                        checked="checked"
                                    <?php } ?>
                                       value="is_visible" />
                            </div>
                            <div class='form_info'>
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['visible_info']); ?>
                            </div>
                            <!-- bottoni -->
                            <div class='form_submit'>
                                <?php /* Se l'operazione è una modifica stampo i tasti modifica e annulla */
                                if($operation == "edit") {  ?>
                                    <input type="hidden" name="id_record" value="<?php echo gdrcd_filter('out', $loaded_record['id_gilda']); ?>">
                                    <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['submit']['edit']); ?>" name="op" />
                                    <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['submit']['undo']); ?>" name="cancel" />
                                <?php
                                } else { /* Altrimenti il tasto inserisci */ ?>
                                    <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['submit']['insert']); ?>" name="op" />
                                <?php
                                } ?>
                            </div>
                        </form>
                    </div>
                <?php
                }//if
                if((gdrcd_filter('get', $_REQUEST['op']) == 'edit') && (isset($_REQUEST['id_record']) === true)) { ?>
                    <!-- Titolo della pagina -->
                    <div class="page_title">
                        <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['role']['page_name']); ?></h2>
                    </div>
                    <div class="page_body">
                        <?php $id_gilda_padre = (0) ? -1 : gdrcd_filter('get', $_REQUEST['id_record']); ?>
                        <!-- Nuovo ruolo -->
                        <form action="main.php?page=gestione_gilde" method="post" class="form_gestione">
                            <div class='form_label'>
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['role']['name_new']); ?>
                            </div>
                            <div class='form_field'>
                                <input name="nome" value="" />
                            </div>
                            <div class='form_label'>
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['image']); ?>
                            </div>
                            <div class='form_field'>
                                <input name="immagine" value="" />
                            </div>
                            <div class='form_label'>
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['role']['pay']); ?>
                            </div>
                            <div class='form_field'>
                                <input name="stipendio" value="0" />
                            </div>
                            <?php if(gdrcd_filter('get', $_REQUEST['id_record'] > -1)) { ?>
                                <div class='form_label'>
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['role']['head']); ?>
                                </div>
                                <div class='form_field'>
                                    <input type="checkbox" name="capo" value="is_capo" />
                                </div>
                            <?php } else { ?>
                                <div class='form_field'>
                                    <input type="hidden" name="capo" value="is_not_capo" />
                                </div>
                            <?php } ?>
                            <div class='form_info'>
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['role']['head_info']); ?>
                            </div>
                            <div class='form_submit'>
                                <input type="hidden" name="gilda" value="<?php echo gdrcd_filter('out', $id_gilda_padre); ?>" />
                                <input type="hidden" name="op" value="nuovo_ruolo" />
                                <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['submit']['insert']); ?>" name="submit" />
                            </div>
                        </form>
                        <?php /*Carico i ruoli della gilda corrende*/
                        $result = gdrcd_query("SELECT * FROM ruolo WHERE gilda=".gdrcd_filter('num', $id_gilda_padre)." ORDER BY capo DESC, stipendio DESC", 'result');
                        /*Elenco ruoli*/
                        while($row = gdrcd_query($result, 'fetch')) { ?>
                            <form action="main.php?page=gestione_gilde" method="post" class="form_gestione">
                                <div class="elenco_record_gestione">
                                    <table>
                                        <tr>
                                            <td>
                                                <div class='titoli_elenco'>
                                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['role']['name']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class='titoli_elenco'>
                                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['image']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class='titoli_elenco'>
                                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['role']['pay']); ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class='form_field'>
                                                    <input name="nome" value="<?php echo gdrcd_filter('out', $row['nome_ruolo']); ?>" />
                                                </div>
                                            </td>
                                            <td>
                                                <div class='form_field'>
                                                    <input name="immagine" value="<?php echo gdrcd_filter('out', $row['immagine']); ?>" />
                                                </div>
                                            </td>
                                            <td>
                                                <div class='form_field'>
                                                    <input name="stipendio" value="<?php echo 0 + gdrcd_filter('out', $row['stipendio']); ?>" />
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <?php if(gdrcd_filter('get', $_REQUEST['id_record'] > -1)) { ?>
                                                    <div class='form_label'>
                                                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['role']['head']); ?>
                                                    </div>
                                                <?php } else { ?>
                                                    &nbsp;
                                                <?php } ?>
                                            </td>
                                            <td>
                                            </td>
                                            <td>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <?php if(gdrcd_filter('get', $_REQUEST['id_record'] > -1)) { ?>
                                                    <div class='form_field'>
                                                        <input type="checkbox" name="capo" <?php if($row['capo'] == 1) {echo 'checked';} ?> value="is_capo" />
                                                    </div>
                                                <?php } else { ?>
                                                    <div class='form_field'>
                                                        <input type="hidden" name="capo" value="is_not_capo" />
                                                    </div>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <div class='form_submit'>
                                                    <input type="hidden" name="provenienza" value="ruolo" />
                                                    <input type="hidden" name="id_ruolo" value="<?php echo gdrcd_filter('out', $row['id_ruolo']); ?>" />
                                                    <input type="hidden" name="gilda" value="<?php echo gdrcd_filter('out', $id_gilda_padre); ?>" />
                                                    <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['role']['submit']['edit']); ?>" name="op" />
                                                    <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['role']['submit']['delete']); ?>" name="op" />
                                                </div>
                                            </td>
                                            <td>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <!-- elenco_record_gestione -->
                            </form>
                        <?php
                        }//while
                        gdrcd_query($result, 'free');
                        ?>
                    </div>
                <?php
                }//if
                ?>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_gilde">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['link']['back']); ?>
                    </a>
                </div>
            <?php
            }//if
            if((isset($_POST['op']) === false) && (isset($_REQUEST['op']) === false)) { /*Elenco record (Visualizzaione di base della pagina)*/
                //Determinazione pagina (paginazione)
                $pagebegin = (int) gdrcd_filter('get', $_REQUEST['offset']) * $PARAMETERS['settings']['records_per_page'];
                $pageend = $PARAMETERS['settings']['records_per_page'];
                //Conteggio record totali
                $record_globale = gdrcd_query("SELECT COUNT(*) FROM gilda");
                $totaleresults = $record_globale['COUNT(*)'];
                //Lettura record
                $result = gdrcd_query("SELECT gilda.id_gilda, gilda.nome, gilda.visibile, codtipogilda.descrizione FROM gilda LEFT JOIN codtipogilda ON gilda.tipo = codtipogilda.cod_tipo ORDER BY nome LIMIT ".$pagebegin.", ".$pageend."", 'result');
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
                                    <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['type']); ?></div>
                                </td>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['visible']); ?></div>
                                </td>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops_col']); ?></div>
                                </td>
                            </tr>
                            <!-- Record -->
                            <?php while($row = gdrcd_query($result, 'fetch')) { ?>
                                <tr>
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco"><?php echo gdrcd_filter('out', $row['nome']); ?></div>
                                    </td>
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco"><?php echo gdrcd_filter('out', $row['descrizione']); ?></div>
                                    </td>
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco"><?php if($row['visibile'] == 1) {
                                                echo gdrcd_filter('out', $MESSAGE['interface']['administration']['yes']);
                                            } else {
                                                echo gdrcd_filter('out', $MESSAGE['interface']['administration']['no']);
                                            } ?></div>
                                    </td>
                                    <td class="casella_controlli"><!-- Iconcine dei controlli -->
                                                                  <!-- Modifica -->
                                        <div class="controlli_elenco">
                                            <div class="controllo_elenco">
                                                <form class="opzioni_elenco_record_gestione"
                                                      action="main.php?page=gestione_gilde" method="post">
                                                    <input type="hidden" name="id_record"
                                                           value="<?php echo gdrcd_filter('out', $row['id_gilda']) ?>" />
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
                                                      action="main.php?page=gestione_gilde" method="post">
                                                    <input type="hidden" name="id_record"
                                                           value="<?php echo gdrcd_filter('out', $row['id_gilda']) ?>" />
                                                    <input type="hidden" name="op" value="erase" />
                                                    <input type="image"
                                                           src="imgs/icons/erase.png"
                                                           alt="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['erase']); ?>"
                                                           title="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['erase']
                                                           ); ?>" />
                                                </form>
                                            </div>
                                            <div class="controlli_elenco">
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
                                <a href="main.php?page=gestione_gilde&offset=<?php echo $i; ?>"><?php echo $i + 1; ?></a>
                            <?php } else {
                                echo ' '.($i + 1).' ';
                            }
                        } //for
                    }//if
                    ?>
                </div>
                <!-- link crea nuovo -->
                <div class="link_back">
                    <a href="main.php?page=gestione_gilde&op=new">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['link']['new']); ?>
                    </a><br />
                    <a href="main.php?page=gestione_gilde&op=edit&id_record=-1">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['link']['new_role']); ?>
                    </a><br />
                    <a href="main.php?page=gestione_tipi&types=guilds">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['guilds']['link']['menage_types']); ?>
                    </a>
                </div>
            <?php
            }//else
            ?>
        </div><!-- page_body -->
    <?php
    }//else (controllo permessi utente) ?>
</div><!-- pagina -->