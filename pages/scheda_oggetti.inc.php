<div class="pagina_scheda_oggetti">
    <?php /*HELP: */ ?>

    <?php
    //Se non e' stato specificato il nome del pg
    if (isset($_REQUEST['pg']) === false)
    {
        echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['unknonw_character_sheet']) . '</div>';
    } else
    {

    /*Visualizzo la pagina*/
    /*Verifico l'esistenza del PG*/
    $query = "SELECT id_personaggio FROM personaggio WHERE personaggio.id_personaggio = '" . gdrcd_filter('get', $_REQUEST['pg']) . "'";
    $result = gdrcd_query($query, 'result');
    //Se non esiste il pg
    if (gdrcd_query($result, 'num_rows') == 0)
    {
        echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['unknown_character_sheet']) . '</div>';
    }
    else
    {

    gdrcd_query($result, 'free');

    /* Spostamento di un oggetto dallo zaino nell'inventario*/
    if (($_POST['op'] == "togli") && ($_SESSION['id_personaggio'] == $_REQUEST['pg']))
    {
        gdrcd_query("UPDATE clgpersonaggiooggetto SET posizione = 0 WHERE id_oggetto = " . gdrcd_filter('num',
                $_POST['id_oggetto']) . " AND id_personaggio = '" . gdrcd_filter('in', $_REQUEST['pg']) . "' LIMIT 1 ");

        echo '<div class="warning">' . gdrcd_filter('out', $MESSAGE['warning']['done']) . '</div>';
    }


    /* Aggiungi/modifica un commento*/
    if ((gdrcd_filter('get', $_POST['op']) == "commenta") && ($_SESSION['id_personaggio'] == $_REQUEST['pg']))
    {
        gdrcd_query("UPDATE clgpersonaggiooggetto SET commento = '" . gdrcd_filter('in',
                $_POST['commento']) . "' WHERE id_oggetto = " . gdrcd_filter('num',
                $_POST['id_oggetto']) . " AND id_personaggio = '" . $_SESSION['id_personaggio'] . "' LIMIT 1 ");

        echo '<div class="warning">' . gdrcd_filter('out', $MESSAGE['warning']['done']) . '</div>';
    }


    /*Rimuovo un oggetto dall'inventario o dallo zaino*/
    if ((gdrcd_filter('get', $_POST['op']) == "abbandona") && ($_SESSION['id_personaggio'] == $_REQUEST['pg'])) {
        /*Rimuovo un oggetto*/
        /*Se ne possiedo più di uno ne rimuovo uno solo*/
        if ($_POST['numero'] <= 1) {
            $query = "DELETE FROM clgpersonaggiooggetto WHERE id_oggetto = " . gdrcd_filter('num',
                    $_POST['id_oggetto']) . " AND id_personaggio = '" . gdrcd_filter('in', $_REQUEST['pg']) . "' LIMIT 1 ";
        } else {
            $query = "UPDATE clgpersonaggiooggetto SET numero = numero - 1 WHERE id_oggetto = " . gdrcd_filter('num',
                    $_POST['id_oggetto']) . " AND id_personaggio = '" . gdrcd_filter('get', $_REQUEST['pg']) . "' LIMIT 1 ";
        }
        gdrcd_query($query);
         /*Registro l'evento*/
                $personaggio = gdrcd_query("SELECT nome FROM personaggio WHERE id_personaggio = '" . gdrcd_filter('in', $_REQUEST['pg']) . "'");
                gdrcd_log_notice(
                    'Oggetto abbandonato dal personaggio',
                    json_encode([
                        'evento' => 'inventory.item.dropped',
                        'codice_evento' => BONIFICO,
                        'nome_interessato' => $personaggio['nome'],
                        'autore' => $_SESSION['login'],
                        'id_oggetto' => gdrcd_filter('num', $_POST['id_oggetto']),
                        'oggetto' => $_POST['checosa'],
                        'quantita_rimossa' => 1,
                        'origine' => 'inventario'
                    ]),
                    gdrcd_filter('num', $_REQUEST['pg'])
                );

        echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['items']['warning']['done']);
    }

    /*Cedo un oggetto non trasportabile ad un altro personaggio*/
    if ((gdrcd_filter('get', $_POST['op']) == "cedi") && ($_SESSION['id_personaggio'] == $_REQUEST['pg'])) {
        $result_min = gdrcd_query("SELECT id_oggetto FROM clgpersonaggiooggetto WHERE id_oggetto = ".gdrcd_filter('num', $_POST['id_oggetto'])."", 'result');
        // Se si tratta dell'ultimo oggetto a disposizione, allora rimuovo la riga in database
        if($_POST['numero'] <= 1) {
            $query = "DELETE FROM clgpersonaggiooggetto WHERE id_oggetto = ".gdrcd_filter('num', $_POST['id_oggetto'])." AND id_personaggio = '".gdrcd_filter('in', $_REQUEST['pg'])."' LIMIT 1 ";
        }
        // Altrimenti diminuisco di uno gli oggetti posseduti
        else {
            $query = "UPDATE clgpersonaggiooggetto SET numero = numero - 1 WHERE id_oggetto = ".gdrcd_filter('num', $_POST['id_oggetto'])." AND id_personaggio = '".gdrcd_filter('in', $_REQUEST['pg'])."' LIMIT 1 ";
        }
        // Eseguo la query
        gdrcd_query($query);

        if(gdrcd_query($result_min, 'num_rows') > 0) {
            gdrcd_query($result_min, 'free');
            // Controllo se il personaggio possiede già l oggettp
            $result_loc = gdrcd_query("SELECT id_oggetto FROM clgpersonaggiooggetto WHERE id_oggetto = ".gdrcd_filter('num', $_POST['id_oggetto'])." AND id_personaggio = '".gdrcd_filter('in', $_POST['give_item'])."'", 'result');
            // Se possiede l'oggetto, lo aggiungo a quelli presenti
            if(gdrcd_query($result_loc, 'num_rows') > 0) {
                $query = "UPDATE clgpersonaggiooggetto SET numero = numero + 1 WHERE id_oggetto = ".gdrcd_filter('num', $_POST['id_oggetto'])." AND id_personaggio = '".gdrcd_filter('in', $_POST['give_item'])."'";
            }
            else {
                $query = "INSERT INTO clgpersonaggiooggetto (id_personaggio, id_oggetto, cariche, numero) VALUES ('".gdrcd_filter('in', $_POST['give_item'])."',".gdrcd_filter('num', $_POST['id_oggetto']).", ".gdrcd_filter('num', $_POST['cariche']).", 1)";
            }
            gdrcd_query($result_loc, 'free');
            // Eseguo la query
            gdrcd_query($query);
            $nome = gdrcd_query("SELECT nome FROM personaggio WHERE id_personaggio = '" . gdrcd_filter('in', $_POST['give_item']) . "'");
            $mittente = gdrcd_query("SELECT nome FROM personaggio WHERE id_personaggio = '" . gdrcd_filter('in', $_REQUEST['pg']) . "'");

            /* Registro l'evento lato destinatario */
            gdrcd_log_notice(
                'Oggetto ricevuto da un altro personaggio',
                json_encode([
                    'evento' => 'inventory.item.received',
                    'codice_evento' => BONIFICO,
                    'direzione' => 'entrata',
                    'id_oggetto' => gdrcd_filter('num', $_POST['id_oggetto']),
                    'oggetto' => $_POST['checosa'],
                    'quantita' => 1,
                    'cariche' => gdrcd_filter('num', $_POST['cariche']),
                    'controparte_id' => gdrcd_filter('num', $_REQUEST['pg']),
                    'controparte_nome' => $mittente['nome'],
                    'autore' => $_SESSION['login'],
                    'origine' => 'inventario'
                ]),
                gdrcd_filter('num', $_POST['give_item'])
            );

            /* Registro l'evento lato mittente */
            gdrcd_log_notice(
                'Oggetto ceduto a un altro personaggio',
                json_encode([
                    'evento' => 'inventory.item.given',
                    'codice_evento' => BONIFICO,
                    'direzione' => 'uscita',
                    'id_oggetto' => gdrcd_filter('num', $_POST['id_oggetto']),
                    'oggetto' => $_POST['checosa'],
                    'quantita' => 1,
                    'cariche' => gdrcd_filter('num', $_POST['cariche']),
                    'controparte_id' => gdrcd_filter('num', $_POST['give_item']),
                    'controparte_nome' => $nome['nome'],
                    'autore' => $_SESSION['login'],
                    'origine' => 'inventario'
                ]),
                gdrcd_filter('num', $_REQUEST['pg'])
            );

             echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['warning']['done']).'</div>';
        } else {
            echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['warning']['cant_do']).'</div>';
        }
    }

    ?>
    <!-- Elenco oggetti nello zaino -->
    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['inventory']); ?></h2>
    </div>

    <div class="page_body">
        <div class="panels_box">
            <?php /*Oggetti nello zaino*/
            $query = "SELECT oggetto.id_oggetto, oggetto.nome AS nome_oggetto, oggetto.descrizione, oggetto.urlimg, oggetto.ubicabile, oggetto.difesa, oggetto.attacco, oggetto.bonus_car0, oggetto.bonus_car1, oggetto.bonus_car2, oggetto.bonus_car3, oggetto.bonus_car4, oggetto.bonus_car5, clgpersonaggiooggetto.* FROM clgpersonaggiooggetto LEFT JOIN oggetto ON clgpersonaggiooggetto.id_oggetto=oggetto.id_oggetto WHERE clgpersonaggiooggetto.id_personaggio = '" . gdrcd_filter('in',
                    $_REQUEST['pg']) . "' AND clgpersonaggiooggetto.posizione = 0 ORDER BY oggetto.nome DESC";
            $result = gdrcd_query($query, 'result'); ?>
            <!-- Intestazione tabella elenco -->
            <div class="elenco_record_gioco">
                <table>
                    <tr>
                        <td class="casella_titolo">
                            <div class="titoli_elenco">
                                <?php echo gdrcd_filter('out',
                                    $MESSAGE['interface']['sheet']['items']['list']['item']); ?>

                            </div>
                        </td>
                        <td class="casella_titolo">
                            <div class="titoli_elenco">
                                <?php echo gdrcd_filter('out',
                                    $MESSAGE['interface']['sheet']['items']['list']['stats']['bonus']); ?>
                            </div>
                        </td>
                        <td class="casella_titolo">
                            <div class="titoli_elenco">
                                <?php echo gdrcd_filter('out',
                                    $MESSAGE['interface']['sheet']['items']['list']['decription']); ?>
                            </div>
                        </td>
                        <td class="casella_titolo">
                            <div class="titoli_elenco">
                                <?php echo "&nbsp;"; ?>
                            </div>
                        </td>
                    </tr>


                    <?php while ($record = gdrcd_query($result, 'fetch'))
                    { ?>

                        <tr>
                            <!-- Oggetto, immagine, quantità -->
                            <td class="casella_elemento">
                                <div class="inventario_nome"><?php echo gdrcd_filter('out',
                                        $record['nome_oggetto']); ?></div>
                                <div class="inventario_img">
                                    <img
                                        src="themes/<?php echo $PARAMETERS['themes']['current_theme'] ?>/imgs/items/<?php echo gdrcd_filter('out',
                                            $record['urlimg']); ?>"/>
                                </div>
                                <div class="inventario_quantita">
                                    <?php if ($record['cariche'] > 0)
                                    {
                                        echo gdrcd_filter('out',
                                            $MESSAGE['interface']['sheet']['items']['list']['charges'] . ": " . $record['cariche']);
                                    } else
                                    {
                                        echo '&nbsp;';
                                    } ?>
                                </div>
                                <div class="inventario_quantita"><?php echo gdrcd_filter('out',
                                        $MESSAGE['interface']['sheet']['items']['list']['pts'] . ": " . $record['numero']); ?></div>
                            </td>
                            <!-- Bonus -->
                            <td class="casella_elemento">
                                <div class="inventario_riga_proprieta">
                                    <div style="float: left; clear: left;">
                                        <?php echo gdrcd_filter('out',
                                            $MESSAGE['interface']['sheet']['items']['list']['stats']['attack'] . ": "); ?>
                                    </div>
                                    <div style="float: right; clear: right;">
                                        <?php echo $record['attacco']; ?>
                                    </div>
                                </div>
                                <div class="inventario_riga_proprieta">
                                    <div style="float: left; clear: left;">
                                        <?php echo gdrcd_filter('out',
                                            $MESSAGE['interface']['sheet']['items']['list']['stats']['defence'] . ": "); ?>
                                    </div>
                                    <div style="float: right; clear: right;">
                                        <?php echo $record['difesa']; ?>
                                    </div>
                                </div>
                                <div class="inventario_riga_proprieta">
                                    <div style="float: left; clear: left;">
                                        <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car0'] . ": "); ?>
                                    </div>
                                    <div style="float: right; clear: right;">
                                        <?php echo $record['bonus_car0']; ?>
                                    </div>
                                </div>
                                <div class="inventario_riga_proprieta">
                                    <div style="float: left; clear: left;">
                                        <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car1'] . ": "); ?>
                                    </div>
                                    <div style="float: right; clear: right;">
                                        <?php echo $record['bonus_car1']; ?>
                                    </div>
                                </div>
                                <div class="inventario_riga_proprieta">
                                    <div style="float: left; clear: left;">
                                        <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car2'] . ": "); ?>
                                    </div>
                                    <div style="float: right; clear: right;">
                                        <?php echo $record['bonus_car2']; ?>
                                    </div>
                                </div>
                                <div class="inventario_riga_proprieta">
                                    <div style="float: left; clear: left;">
                                        <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car3'] . ": "); ?>
                                    </div>
                                    <div style="float: right; clear: right;">
                                        <?php echo $record['bonus_car3']; ?>
                                    </div>
                                </div>
                                <div class="inventario_riga_proprieta">
                                    <div style="float: left; clear: left;">
                                        <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car4'] . ": "); ?>
                                    </div>
                                    <div style="float: right; clear: right;">
                                        <?php echo $record['bonus_car4']; ?>
                                    </div>
                                </div>
                                <div class="inventario_riga_proprieta">
                                    <div style="float: left; clear: left;">
                                        <?php echo gdrcd_filter('out', $PARAMETERS['names']['stats']['car5'] . ": "); ?>
                                    </div>
                                    <div style="float: right; clear: right;">
                                        <?php echo $record['bonus_car5']; ?>
                                    </div>
                                </div>
                                <!-- Descrizione e note-->
                            </td>
                            <td class="casella_elemento">
                                <div class="inventario_riga_descrizione">
                                    <?php echo gdrcd_filter('out', $record['descrizione']); ?>
                                </div><?php if (($record['commento'] != '') && ($_SESSION['id_personaggio'] != gdrcd_filter('get',
                                            $_REQUEST['pg']))
                                )
                                { ?>
                                    <div class="inventario_riga_commento">
                                    <?php echo gdrcd_filter('out',
                                    $MESSAGE['interface']['sheet']['items']['list']['notes'] . ": " . $record['commento']); ?>
                                    </div><?php } else
                                {
                                    if ($_SESSION['id_personaggio'] == gdrcd_filter('get', $_REQUEST['pg']))
                                    {//if
                                        ?>
                                        <div>
                                        <!-- Commento -->
                                        <form action="main.php?page=scheda_oggetti"
                                              method="post">
                                            <input type="hidden"
                                                   value="commenta"
                                                   name="op"/>
                                            <input type="hidden"
                                                   value="<?php echo $record['id_oggetto']; ?>"
                                                   name="id_oggetto"/>
                                            <textarea type="textbox" name="commento"
                                                      class="form_textarea"><?php echo $record['commento']; ?></textarea>
                                            <input type="submit"
                                                   value="<?php echo gdrcd_filter('out',
                                                       $MESSAGE['interface']['sheet']['items']['list']['add_note']); ?>"/>
                                            <input type="hidden"
                                                   value="<?php echo $_REQUEST['pg']; ?>"
                                                   name="pg"/>
                                        </form>
                                        </div><?php }
                                } //else if
                                ?>
                            </td>
                            <!-- Comandi elenco -->
                            <td class="casella_controlli">
                                <?php if ($_SESSION['id_personaggio'] == $_REQUEST['pg'])
                                { ?>
                                    <div class="form_gioco">
                                        <!-- Abbandona -->
                                        <form action="main.php?page=scheda_oggetti"
                                              method="post">
                                            <input type="hidden"
                                                   value="abbandona"
                                                   name="op"/>
                                            <input type="hidden"
                                                   value="<?php echo $record['numero']; ?>"
                                                   name="numero"/>
                                            <input type="hidden"
                                                   value="<?php echo $record['id_oggetto']; ?>"
                                                   name="id_oggetto"/>
                                            <input type="submit"
                                                   value="<?php echo gdrcd_filter('out',
                                                       $MESSAGE['interface']['sheet']['items']['list']['drop']); ?>"/>
                                            <input type="hidden"
                                                   value="<?php echo $_REQUEST['pg']; ?>"
                                                   name="pg"/>
                                        </form>
                                        <?php if ($record['ubicabile'] > 0)
                                        { ?>
                                            <!-- Zaino -->
                                            <form action="main.php?page=scheda_equip"
                                                  method="post">
                                                <input type="hidden"
                                                       value="in_zaino"
                                                       name="op"/>
                                                <input type="hidden"
                                                       value="<?php echo $record['id_oggetto']; ?>"
                                                       name="id_oggetto"/>
                                                <input type="submit"
                                                       value="<?php echo gdrcd_filter('out',
                                                           $MESSAGE['interface']['sheet']['items']['list']['put_in']); ?>"/>
                                                <input type="hidden"
                                                       value="<?php echo $_REQUEST['pg']; ?>"
                                                       name="pg"/>
                                            </form>
                                        <?php } ?>
                                        <?php
                                            /*Personaggi nella stessa location*/
                                            if($PARAMETERS['mode']['give_only_if_online'] == 'ON') {
                                            $query = "SELECT id_personaggio, nome FROM personaggio WHERE ultimo_luogo = ".$_SESSION['luogo']." AND ultimo_luogo  <> -1 AND id_personaggio <> '".$_SESSION['id_personaggio']."' AND DATE_ADD(ultimo_refresh, INTERVAL 2 MINUTE) > NOW() ORDER BY nome";
                                            } else {
                                                $query = "SELECT id_personaggio, nome FROM personaggio WHERE id_personaggio <> '".$_SESSION['id_personaggio']."'  ORDER BY nome";
                                            }
                                            $characters = gdrcd_query($query, 'result');

                                            if(gdrcd_query($characters, 'num_rows') > 0) { ?>
                                                <!-- Cedi oggetto non trasportabile -->
                                                <form action="main.php?page=scheda_oggetti" method="post">
                                                    <input type="hidden" value="<?php echo gdrcd_filter('out', $_REQUEST['pg']); ?>" name="pg" />
                                                    <input type="hidden" value="<?php echo $record['id_oggetto']; ?>" name="id_oggetto" />
                                                    <input type="hidden" value="<?php echo $record['cariche']; ?>" name="cariche" />
                                                    <input type="hidden" value="<?php echo $record['numero']; ?>" name="numero" />
                                                    <input type="hidden" value="<?php echo gdrcd_filter('out', $record['nome_oggetto']); ?>" name="checosa" />
                                                    <input type="hidden"value="cedi" name="op" />
                                                    <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['items']['list']['give']); ?>" />
                                                    <select name="give_item">
                                                        <?php while($option = gdrcd_query($characters, 'fetch')) { ?>
                                                            <option value="<?php echo $option['id_personaggio']; ?>">
                                                                <?php echo gdrcd_filter('out', $option['nome']); ?>
                                                            </option>
                                                        <?php }
                                                        gdrcd_query($characters, 'free');
                                                        ?>
                                                    </select>
                                                </form>
                                            <?php }//if ?>
                                    </div>
                                <?php } else
                                {
                                    echo "&nbsp;";
                                } ?>
                            </td>
                        </tr>


                    <?php }//while

                    gdrcd_query($result, 'free');

                    ?>
                </table>
            </div>

        </div>
        <!-- Link a piè di pagina -->
        <div class="link_back">
            <a href="main.php?page=scheda_equip&pg=<?php echo gdrcd_filter('url',
                $_REQUEST['pg']); ?>"><?php echo gdrcd_filter('out',
                        $MESSAGE['interface']['sheet']['items']['list']['put_in']) . '.'; ?></a><br/>
            <a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('url',
                $_REQUEST['pg']); ?>"><?php echo gdrcd_filter('out',
                    $MESSAGE['interface']['sheet']['link']['back']); ?></a>
        </div>


        <?php
        /********* CHIUSURA SCHEDA **********/
        }//else

        }//else
        ?>
    </div>
</div><!-- Pagina -->