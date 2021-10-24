<div class="pagina_gestione_razze">
    <?php /*HELP: */


    /*Controllo permessi utente*/
    if ($_SESSION['permessi'] < SUPERUSER) {
        echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['not_allowed']) . '</div>';
    } else { ?>


        <!-- Titolo della pagina -->
        <div class="page_title">
            <h2><?php echo gdrcd_filter('out',
                    $MESSAGE['interface']['administration']['log']['events']['page_name']); ?></h2>
        </div>


        <!-- Corpo della pagina -->
        <div class="page_body">


            <?php /*Form di scelta del log (visualizzazione di base)*/
            if ((isset($_POST['op']) === false) && (isset($_REQUEST['op']) === false)) { ?>

                <!-- Form di inserimento/modifica -->
                <div class="panels_box">
                    <div class="form_gestione">
                        <form action="main.php?page=log_eventi"
                              method="post">
                            <div class='form_label'>
                                <?php echo gdrcd_filter('out',
                                    $MESSAGE['interface']['administration']['log']['events']['log_type']); ?>
                            </div>
                            <div class='form_field'>
                                <select name="which_log">
                                    <?php $count = 1;
                                    foreach ($MESSAGE['event'] as $event) { ?>
                                        <option value="<?php echo $count; ?>"><?php echo $event; ?></option>
                                        <?php $count++;
                                    } ?>
                                </select>
                            </div>
                            <!-- bottoni -->
                            <div class='form_submit'>
                                <input type="hidden"
                                       value="view"
                                       name="op"/>
                                <input type="submit"
                                       value="<?php echo gdrcd_filter('out',
                                           $MESSAGE['interface']['forms']['submit']); ?>"/>
                            </div>

                        </form>
                    </div>
                </div>
            <?php }//if
            ?>



            <?php //*Elenco log*/

            if ((isset($_REQUEST['op']) == 'view') && (is_numeric($_REQUEST['which_log']) === true)) {
                //Determinazione pagina (paginazione)
                $pagebegin = (int)$_REQUEST['offset'] * $PARAMETERS['settings']['records_per_page'];
                $pageend = $PARAMETERS['settings']['records_per_page'];
                //Conteggio record totali
                $record_globale = gdrcd_query("SELECT COUNT(*) FROM log WHERE codice_evento =" . gdrcd_filter('num',
                        $_REQUEST['which_log']) . "");
                $totaleresults = $record_globale['COUNT(*)'];
                //Lettura record
                $result = gdrcd_query("SELECT autore, nome_interessato, data_evento, descrizione_evento FROM log WHERE codice_evento =" . $_REQUEST['which_log'] . " ORDER BY data_evento DESC LIMIT " . $pagebegin . ", " . $pageend . "",
                    'result');
                $numresults = gdrcd_query($result, 'num_rows');


                /* Se esistono record */
                if ($numresults > 0) { ?>
                    <!-- Elenco dei record paginato -->
                    <div class="elenco_record_gestione">
                        <table>
                            <!-- Intestazione tabella -->
                            <tr>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco">
                                        <?php echo gdrcd_filter('out',
                                            $MESSAGE['interface']['administration']['log']['events']['author']); ?>
                                    </div>
                                </td>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco">
                                        <?php echo gdrcd_filter('out',
                                            $MESSAGE['interface']['administration']['log']['events']['dest']); ?>
                                    </div>
                                </td>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco">
                                        <?php echo gdrcd_filter('out',
                                            $MESSAGE['interface']['administration']['log']['events']['date']); ?>
                                    </div>
                                </td>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco">
                                        <?php echo gdrcd_filter('out',
                                            $MESSAGE['interface']['administration']['log']['events']['descr']); ?>
                                    </div>
                                </td>
                            </tr>
                            <!-- Record -->
                            <?php while ($row = gdrcd_query($result, 'fetch')) {

                                switch ($_REQUEST['which_log']) {
                                    case BLOCKED:
                                    case LOGGEDIN:
                                    case ERRORELOGIN:
                                        $list = explode('.', $row['descrizione_evento']);
                                        $list[3] = 'X';
                                        $list[2] = 'X';
                                        $descr = implode('.', $list);
                                        break;
                                    default;
                                        $descr = $row['descrizione_evento'];
                                        break;
                                }

                                switch ($_REQUEST['which_log']) {
                                    case BLOCKED:
                                    case LOGGEDIN:
                                    case ERRORELOGIN:
                                        $list2 = explode('.', $row['autore']);
                                        $list2[3] = 'X';
                                        $list2[2] = 'X';
                                        $autore = implode('.', $list2);
                                        break;
                                    default;
                                        $autore = $row['autore'];
                                        break;
                                }

                                ?>
                                <tr class="risultati_elenco_record_gestione">
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco">
                                            <?php echo gdrcd_filter('out', $autore); ?>
                                        </div>
                                    </td>
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco">
                                            <a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('out',
                                                $row['nome_interessato']); ?>">
                                                <?php echo gdrcd_filter('out', $row['nome_interessato']); ?>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco">
                                            <?php echo gdrcd_format_date($row['data_evento']) . ' ' . gdrcd_format_time($row['data_evento']); ?>
                                        </div>
                                    </td>
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco">
                                            <?php echo gdrcd_filter('out', $descr); ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php } //while


                            gdrcd_query($result, 'free');
                            ?>
                        </table>
                    </div>
                <?php }//if
                ?>

                <!-- Paginatore elenco -->
                <div class="pager">
                    <?php if ($totaleresults > $PARAMETERS['settings']['records_per_page']) {
                        echo gdrcd_filter('out', $MESSAGE['interface']['pager']['pages_name']);
                        for ($i = 0; $i <= floor($totaleresults / $PARAMETERS['settings']['records_per_page']); $i++) {
                            if ($i != $_REQUEST['offset']) {
                                ?>
                                <a href="main.php?page=log_eventi&op=view&which_log=<?php echo $_REQUEST['which_log']; ?>&offset=<?php echo $i; ?>"><?php echo $i + 1; ?></a>
                            <?php } else {
                                echo ' ' . ($i + 1) . ' ';
                            }
                        } //for
                    }//if
                    ?>
                </div>

                <!-- link crea nuovo -->
                <div class="link_back">
                    <a href="main.php?page=log_eventi">
                        <?php echo gdrcd_filter('out',
                            $MESSAGE['interface']['administration']['log']['events']['link']['back']); ?>
                    </a>
                </div>

            <?php }//else
            ?>


        </div>

    <?php }//else (controllo permessi utente) ?>
</div><!--Pagina-->