<div class="pagina_gestione_luoghi">
    <?php /*HELP: */
    /*Controllo permessi utente*/
    if($_SESSION['permessi'] < MODERATOR) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
    } else { ?>
        <!-- Titolo della pagina -->
        <div class="page_title">
            <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['page_name']); ?></h2>
        </div>
        <!-- Corpo della pagina -->
        <div class="page_body">
            <?php /*Inserimento di un nuovo record*/
            if($_POST['op'] == 'insert') {
                /*Processo le informazioni ricevute dal form*/
                $is_chat = ((isset($_POST['chat']) == true) && ($_POST['chat'] == 'is_chat')) ? 1 :0;

                $is_privat = ((isset($_POST['privata']) == true) && ($_POST['privata'] == 'is_privat')) ? 1 : 0;

                $immagine = ($_POST['immagine'] == "") ? "standard_luogo.png" : gdrcd_filter('in', $_POST['immagine']);
                /*Eseguo l'inserimento*/
                gdrcd_query("INSERT INTO mappa (nome, descrizione, stato, pagina, chat, immagine, stanza_apparente, id_mappa, link_immagine, link_immagine_hover, id_mappa_collegata, x_cord, y_cord, privata, proprietario, scadenza, costo, invitati) 
                    VALUES ('".gdrcd_filter('in', $_POST['nome'])."', '".gdrcd_filter('in', $_POST['descrizione'])."', '".gdrcd_filter('in', $_POST['stato'])."', '".gdrcd_filter('in', $_POST['pagina'])."', ".$is_chat.", '".$immagine."', '".gdrcd_filter('in', $_POST['stanza_apparente'])."', ".gdrcd_filter('in', $_POST['mappa']).", '".gdrcd_filter('in', $_POST['image_button'])."', '".gdrcd_filter('in', $_POST['image_button_hover'])."', ".gdrcd_filter('in', $_POST['mappa_linked']).", ".gdrcd_filter('num', $_POST['x_cord']).", ".gdrcd_filter('num', $_POST['y_cord']).", ".$is_privat.", '".gdrcd_filter('in', $_POST['proprietario'])."', '".gdrcd_filter('num', $_POST['year'])."-".gdrcd_filter('num', $_POST['month'])."-".gdrcd_filter('num', $_POST['day'])." 00:00:00'".", ".gdrcd_filter('num', $_POST['costo']).", '')");
                ?>
                <div class="warning">
                    <?php echo gdrcd_filter('out', $MESSAGE['warning']['inserted']); ?>
                </div>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_luoghi">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['link']['back']); ?>
                    </a>
                </div>
            <?php
            }
            /* Cancellatura in un record */
            if(gdrcd_filter('get', $_POST['op']) == 'erase') {
                /*Eseguo la cancellatura*/
                gdrcd_query("DELETE FROM mappa WHERE id=".gdrcd_filter('num', $_POST['id_record'])." LIMIT 1");
                ?>
                <div class="warning">
                    <?php echo gdrcd_filter('out', $MESSAGE['warning']['deleted']); ?>
                </div>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_luoghi">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['link']['back']); ?>
                    </a>
                </div>
            <?php }
            /*Modifica di un record*/
            if(gdrcd_filter('get', $_POST['op']) == 'modify') {
                /*Processo le informazioni ricevute dal form*/
                $is_chat = ((isset($_POST['chat']) == true) && ($_POST['chat'] == 'is_chat')) ? 1 :0;

                $is_privat = ((isset($_POST['privata']) == true) && ($_POST['privata'] == 'is_privat')) ? 1 : 0;
                /*Eseguo l'aggiornamento*/
                gdrcd_query("UPDATE mappa SET nome ='".gdrcd_filter('in', $_POST['nome'])."', descrizione = '".gdrcd_filter('in', $_POST['descrizione'])."', stato = '".gdrcd_filter('in', $_POST['stato'])."', chat = ".$is_chat.", immagine = '".gdrcd_filter('in', $_POST['immagine'])."', stanza_apparente = '".gdrcd_filter('in', $_POST['stanza_apparente'])."', pagina = '".gdrcd_filter('in', $_POST['pagina'])."', id_mappa =  ".gdrcd_filter('in', $_POST['mappa']).", link_immagine = '".gdrcd_filter('in', $_POST['image_button'])."', link_immagine_hover = '".gdrcd_filter('in', $_POST['image_button_hover'])."', id_mappa_collegata = ".gdrcd_filter('in', $_POST['mappa_linked']).", x_cord = ".gdrcd_filter('num', $_POST['x_cord']).", y_cord = ".gdrcd_filter('num', $_POST['y_cord']).", privata = ".$is_privat.", proprietario = '".gdrcd_filter('in', $_POST['proprietario'])."', scadenza = '".gdrcd_filter('num', $_POST['year'])."-".gdrcd_filter('num', $_POST['month'])."-".gdrcd_filter('num', $_POST['day'])." 00:00:00"."', costo = ".gdrcd_filter('num', $_POST['costo'])." WHERE id = ".gdrcd_filter('num', $_POST['id_mappa'])." LIMIT 1");
                ?>
                <div class="warning">
                    <?php echo gdrcd_filter('out', $MESSAGE['warning']['modified']); ?>
                </div>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_luoghi">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['link']['back']); ?>
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
                    $loaded_location = gdrcd_query("SELECT * FROM mappa WHERE id=".gdrcd_filter('num', $_POST['id_record'])." LIMIT 1 ");
                    /*Cambio l'operazione in modifica*/
                    $operation = 'edit';
                } ?>
                <!-- Form di inserimento/modifica -->
                <div class="panels_box">
                    <form action="main.php?page=gestione_luoghi" method="post" class="form_gestione">
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['name']); ?>
                        </div>
                        <div class='form_field'>
                            <input name="nome" value="<?php echo gdrcd_filter('out', $loaded_location['nome']); ?>" />
                        </div>
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['description']); ?>
                        </div>
                        <div class='form_field'>
                            <textarea name="descrizione"><?php echo gdrcd_filter('out', $loaded_location['descrizione']); ?></textarea>
                        </div>
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['status']); ?>
                        </div>
                        <div class='form_field'>
                            <textarea name="stato"><?php echo gdrcd_filter('out', $loaded_location['stato']); ?></textarea>
                        </div>
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['is_chat']); ?>
                        </div>
                        <div class='form_field'>
                            <input type="checkbox" name="chat"
                                <?php if(($loaded_location['chat'] == 1) || (isset($loaded_location) === false)) { ?>checked="checked"
                                <?php } ?>
                                   value="is_chat" />
                        </div>
                        <div class='form_info'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['is_chat_info']); ?>
                        </div>
                        <!--
                            /** * Funzionalità che permette di sostituire il link testuale con un immagine
                                * @author Blancks
                            */
                        -->
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['image_button']); ?>
                        </div>
                        <div class='form_field'>
                            <input name="image_button" value="<?php echo gdrcd_filter('out', $loaded_location['link_immagine']); ?>" />
                        </div>
                        <div class="form_info">
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['image_button_info']); ?>
                        </div>
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['image_button_hover']); ?>
                        </div>
                        <div class='form_field'>
                            <input name="image_button_hover" value="<?php echo gdrcd_filter('out', $loaded_location['link_immagine_hover']); ?>" />
                        </div>
                        <div class="form_info">
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['image_button_hover_info']); ?>
                        </div>
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['page']); ?>
                        </div>
                        <div class='form_field'>
                            <input name="pagina" value="<?php echo gdrcd_filter('out', $loaded_location['pagina']); ?>" />
                        </div>
                        <!--
                              /** * Funzionalità che permette di collegare il link ad un altra mappa
                                  * @author Blancks
                              */
                          -->
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['map_related']); ?>
                        </div>
                        <div class='form_field'>
                            <?php /* Carico l'elenco delle mappe inserite */
                            $mappe = gdrcd_query("SELECT id_click, nome FROM mappa_click", 'result');
                            /*Se sono presenti mappe sul database*/
                            if(gdrcd_query($mappe, 'num_rows') > 0) { ?>
                                <!-- Elenco delle mappe -->
                                <select name="mappa_linked">
                                    <!-- Opzione "Nessuna" -->
                                    <option value="0"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['map_id_default']); ?></option>
                                    <!-- Opzione "Nessuno" -->
                                    <?php while($option = gdrcd_query($mappe, 'fetch')) { ?>
                                        <option value="<?php echo gdrcd_filter('out', $option['id_click']); ?>" <?php if($loaded_location['id_mappa_collegata'] == $option['id_click']) {echo 'SELECTED';} ?>>
                                            <?php echo gdrcd_filter('out', $option['nome']); ?>
                                        </option>
                                    <?php }
                                    gdrcd_query($mappe, 'free');
                                    ?>
                                </select>
                            <?php
                            } else { /*Altrimenti segnalo l'assenza di mappe*/
                                echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['map_id_err']);
                            } ?>
                        </div>
                        <div class="form_info">
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['map_related_info']); ?>
                        </div>
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['image']); ?>
                        </div>
                        <div class='form_field'>
                            <input name="immagine" value="<?php echo gdrcd_filter('out', $loaded_location['immagine']); ?>" />
                        </div>
                        <div class='form_info'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['image_info']); ?>
                        </div>
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['screen_name']); ?>
                        </div>
                        <div class='form_field'>
                            <input name="stanza_apparente" value="<?php echo gdrcd_filter('out', $loaded_location['stanza_apparente']); ?>" />
                        </div>
                        <div class='form_info'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['screen_name_info']); ?>
                        </div>
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['map_id']); ?>
                        </div>
                        <div class='form_field'>
                            <?php /* Carico l'elenco delle mappe inserite */
                            $mappe = gdrcd_query("SELECT id_click, nome FROM mappa_click", 'result');
                            /*Se sono presenti mappe sul database*/
                            if(gdrcd_query($mappe, 'num_rows') > 0) { ?>
                                <!-- Elenco delle mappe -->
                                <select name="mappa">
                                    <!-- Opzione "Nessuna" -->
                                    <option value="-1"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['map_id_default']
                                        ); ?></option>
                                    <!-- Opzione "Nessuno" -->
                                    <?php while($option = gdrcd_query($mappe, 'fetch')) { ?>
                                        <option value="<?php echo gdrcd_filter('out', $option['id_click']); ?>" <?php if($loaded_location['id_mappa'] == $option['id_click']) {echo 'SELECTED';} ?>>
                                            <?php echo gdrcd_filter('out', $option['nome']); ?>
                                        </option>
                                    <?php }
                                    gdrcd_query($mappe, 'free');
                                    ?>
                                </select>
                            <?php
                            } else { /*Altrimenti segnalo l'assenza di mappe*/
                                echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['map_id_err']);
                            } ?>
                        </div>
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['x']); ?>
                        </div>
                        <div class='form_field'>
                            <input name="x_cord" value="<?php echo 0 + gdrcd_filter('num', $loaded_location['x_cord']); ?>" />
                        </div>
                        <div class='form_label'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['y']); ?>
                        </div>
                        <div class='form_field'>
                            <input name="y_cord" value="<?php echo 0 + gdrcd_filter('num', $loaded_location['y_cord']); ?>" />
                        </div>
                        <div class='form_info'>
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['x_info']); ?>
                        </div>
                        <?php if($PARAMETERS['mode']['privaterooms'] == 'OFF') { ?>
                            <input type="hidden" name="proprietario" value="">
                            <input type="hidden" name="day" value="00">
                            <input type="hidden" name="month" value="00">
                            <input type="hidden" name="year" value="0000">
                            <input type="hidden" name="costo" value="0">
                        <?php
                        } else { ?>
                            <div class='form_label'>
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['is_privat']); ?>
                            </div>
                            <div class='form_field'>
                                <input type="checkbox"
                                       name="privata"
                                    <?php if($loaded_location['privata'] == 1) { echo 'checked="checked"'; } ?> value="is_privat" />
                            </div>
                            <div class='form_info'>
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['is_privat_info']); ?>
                            </div>
                            <div class='form_label'>
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['owner']); ?>
                            </div>
                            <div class='form_field'>
                                <?php /* Carico l'elenco delle gilde e dei personaggi */
                                $nomi = gdrcd_query("SELECT nome, cognome FROM personaggio", 'result');

                                $gilde = gdrcd_query("SELECT id_gilda, nome FROM gilda", 'result');
                                /* Controllo che esistano gilde o personaggi */

                                /* Il controllo è in realtà superfluo, di certo esiste almeno il personaggio che visita la pagina*/
                                if(gdrcd_query($nomi, 'num_rows') > 0 || gdrcd_query($gilde, 'num_rows') > 0) { ?>
                                    <!-- Elenco personaggi e gilde -->
                                    <select name="proprietario">
                                        <!-- Opzione "Nessuno" -->
                                        <option value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['owner_default']); ?>"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['owner_default']); ?></option>
                                        <!-- Gilde -->
                                        <optgroup label="<?php echo gdrcd_filter('out', $PARAMETERS['names']['guild_name']['plur']); ?>"></optgroup>
                                        <?php while($option = gdrcd_query($gilde, 'fetch')) { ?>
                                            <option value="<?php echo gdrcd_filter('out', $option['id_gilda']); ?>"
                                                <?php if($option['id_gilda'] == $loaded_location['proprietario']) {echo " selected";} ?>>
                                                <?php echo gdrcd_filter('out', $option['nome']); ?>
                                            </option>
                                        <?php }//while
                                        gdrcd_query($gilde, 'free');
                                        ?>
                                        <!-- PG -->
                                        <optgroup label="<?php echo gdrcd_filter('out', $PARAMETERS['names']['users_name']['plur']); ?>">
                                            <?php while($option = gdrcd_query($nomi, 'fetch')) { ?>
                                                <option value="<?php echo gdrcd_filter('out', $option['nome']); ?>"
                                                    <?php if($option['nome'] == $loaded_location['proprietario']) {echo " selected";} ?>>
                                                    <?php echo gdrcd_filter('out', $option['nome'])." ".gdrcd_filter('out', $option['cognome']); ?>
                                                </option>
                                            <?php }//while
                                            gdrcd_query($nomi, 'free');
                                            ?>
                                    </select>
                                <?php
                                } else { /* Segnalo l'assenza di gilde o personaggi (impossibile)*/
                                    echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['owner_err']);
                                } ?>
                            </div>
                            <div class='form_label'>
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['expiration_date']); ?>
                            </div>
                            <div class='form_field'>
                                <?php /* Processo la data di scadenza della stanza privata */
                                $expiration = explode(" ", $loaded_location['scadenza']);
                                $expiration = explode("-", $expiration[0]);
                                ?>
                                <!-- Giorno -->
                                <select name="day" class="day">
                                    <?php for($i = 1; $i <= 31; $i++) { ?>
                                        <option value="<?php echo $i; ?>" <?php if($expiration[2] == $i) {echo 'selected';} ?>><?php echo $i; ?></option>
                                    <?php }//for ?>
                                </select>
                                <!-- Mese -->
                                <select name="month" class="month">
                                    <?php for($i = 1; $i <= 12; $i++) { ?>
                                        <option value="<?php echo $i; ?>" <?php if($expiration[1] == $i) {echo 'selected';} ?>><?php echo $i; ?></option>
                                    <?php }//for ?>
                                </select>
                                <!-- Anno -->
                                <select name="year" class="year">
                                    <?php for($i = strftime('%Y'); $i <= strftime('%Y') + 20; $i++) { ?>
                                        <option value="<?php echo $i; ?>" <?php if($expiration[0] == $i) {echo 'selected';} ?>><?php echo $i; ?></option>
                                    <?php }//for ?>
                                </select>
                            </div>
                            <div class='form_label'>
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['rent']); ?>
                            </div>
                            <div class='form_field'>
                                <input name="costo" value="<?php echo 0 + gdrcd_filter('num', $loaded_location['costo']); ?>" />
                            </div>
                        <?php
                        }//else
                        ?>
                        <!-- bottoni -->
                        <div class='form_submit'>
                            <?php /* Se l'operazione è una modifica stampo i tasti modifica e annulla */
                            if($operation == "edit") { ?>
                                <input type="hidden" name="id_mappa" value="<?php echo gdrcd_filter('out', $loaded_location['id']); ?>">
                                <input type="hidden" name="op" value="modify">
                                <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['submit']['edit']); ?>" />
                            <?php } /* Altrimenti il tasto inserisci */ else { ?>
                                <input type="hidden" name="op" value="insert">
                                <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['submit']['insert']); ?>" />
                            <?php } ?>
                        </div>
                    </form>
                </div>
                <!-- Link di ritorno alla visualizzazione di base -->
                <div class="link_back">
                    <a href="main.php?page=gestione_luoghi"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['link']['back']); ?></a>
                </div>
            <?php
            }//if
            if((isset($_POST['op']) === false) && (isset($_REQUEST['op']) === false)) { /*Elenco record (Visualizzaione di base della pagina)*/
                //Determinazione pagina (paginazione)
                $pagebegin = (int) gdrcd_filter('get', $_REQUEST['offset']) * $PARAMETERS['settings']['records_per_page'];
                $pageend = $PARAMETERS['settings']['records_per_page'];
                //Conteggio record totali
                $record_globale = gdrcd_query("SELECT COUNT(*) FROM mappa");
                $totaleresults = $record_globale['COUNT(*)'];
                //Lettura record
                $result = gdrcd_query("SELECT mappa.id, mappa.nome, mappa_click.nome AS mappa_click FROM mappa LEFT JOIN mappa_click ON mappa.id_mappa=mappa_click.id_click ORDER BY mappa.nome LIMIT ".$pagebegin.", ".$pageend."", 'result');
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
                                    <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['map_name']
                                        ); ?></div>
                                </td>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco"><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops_col']); ?></div>
                                </td>
                            </tr>
                            <!-- Record -->
                            <?php while($row = gdrcd_query($result, 'fetch')) { ?>
                                <tr>
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco"><?php echo $row['nome']; ?></div>
                                    </td>
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco"><?php echo gdrcd_filter('out', $row['mappa_click']); ?></div>
                                    </td>
                                    <td class="casella_controlli"><!-- Iconcine dei controlli -->
                                        <div class="controlli_elenco">
                                            <!-- Modifica -->
                                            <div class="controllo_elenco">
                                                <form action="main.php?page=gestione_luoghi" method="post">
                                                    <input type="hidden" name="id_record"
                                                           value="<?php echo $row['id'] ?>" />
                                                    <input type="hidden" name="op" value="edit" />
                                                    <input type="image"
                                                           src="imgs/icons/edit.png"
                                                           alt="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['edit']); ?>"
                                                           title="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['edit']); ?>" />
                                                </form>
                                            </div>
                                            <!-- Elimina -->
                                            <div class="controllo_elenco">
                                                <form action="main.php?page=gestione_luoghi" method="post">
                                                    <input type="hidden" name="id_record"
                                                           value="<?php echo $row['id'] ?>" />
                                                    <input type="hidden" name="op" value="erase" />
                                                    <input type="image"
                                                           src="imgs/icons/erase.png"
                                                           alt="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['erase']); ?>"
                                                           title="<?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['ops']['erase']
                                                           ); ?>" />
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
                            if($i != gdrcd_filter('num', $_REQUEST['offset'])) { ?>
                                <a href="main.php?page=gestione_luoghi&offset=<?php echo $i; ?>"><?php echo $i + 1; ?></a>
                            <?php } else {
                                echo ' '.($i + 1).' ';
                            }
                        } //for
                    }//if
                    ?>
                </div>
                <!-- link crea nuovo -->
                <div class="link_back">
                    <a href="main.php?page=gestione_luoghi&op=new">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['locations']['link']['new']); ?>
                    </a>
                </div>
            <?php }//else
            ?>
        </div>
    <?php }//else (controllo permessi utente) ?>
</div> <!-- Pagina -->
