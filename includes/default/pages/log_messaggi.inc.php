<div class="pagina_gestione_razze">
    <?php /*HELP: */
    /*Controllo permessi utente*/
    if(($_SESSION['permessi'] < MODERATOR) || ($PARAMETERS['mode']['spymessages'] != 'ON')) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
    } else { ?>
        <!-- Titolo della pagina -->
        <div class="page_title">
            <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['messages']['page_name']); ?></h2>
        </div>
        <!-- Corpo della pagina -->
        <div class="page_body">
            <?php /*Form di scelta del log (visualizzazione di base)*/
            if((isset($_POST['op']) === false) && (isset($_REQUEST['op']) === false)) { ?>
                <!-- Form di inserimento/modifica -->
                <div class="panels_box">
                    <div class="form_gestione">
                        <form action="main.php?page=log_messaggi" method="post">
                            <?php
                            $result = gdrcd_query("SELECT nome FROM personaggio WHERE permessi > ".DELETED." ORDER BY nome", 'result'); ?>
                            <div class='form_label'>
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['messages']['log_type']); ?>
                            </div>
                            <div class='form_field'>
                                <select name="pg">
                                    <?php while($row = gdrcd_query($result, 'fetch')) { ?>
                                        <option value="<?php echo gdrcd_filter('out', $row['nome']); ?>">
                                            <?php echo gdrcd_filter('out', $row['nome']); ?>
                                        </option>
                                    <?php }//while
                                    gdrcd_query($result, 'free');
                                    ?>
                                </select>
                            </div>
                            <!-- bottoni -->
                            <div class='form_submit'>
                                <input type="hidden" value="view" name="op" />
                                <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>" />
                            </div>
                        </form>
                    </div>
                </div>
            <?php
            }//if ?
            //*Elenco log*/
            if(isset($_REQUEST['op']) == 'view') {
                //Determinazione pagina (paginazione)
                $pagebegin = (int) $_REQUEST['offset'] * $PARAMETERS['settings']['records_per_page'];
                $pageend = $PARAMETERS['settings']['records_per_page'];
                //Conteggio record totali
                $record_globale = gdrcd_query("SELECT COUNT(*) FROM backmessaggi WHERE mittente = '".gdrcd_filter('in', $_REQUEST['pg'])."'");
                $totaleresults = $record_globale['COUNT(*)'];
                //Lettura record
                $result = gdrcd_query("SELECT destinatario, spedito, testo FROM backmessaggi WHERE mittente = '".gdrcd_filter('in', $_REQUEST['pg'])."' ORDER BY spedito DESC LIMIT ".$pagebegin.", ".$pageend."", 'result');
                $numresults = gdrcd_query($result, 'num_rows');

                /* Se esistono record */
                if($numresults > 0) { ?>
                    <!-- Elenco dei record paginato -->
                    <div class="elenco_record_gestione">
                        <table>
                            <!-- Intestazione tabella -->
                            <tr>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco">
                                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['messages']['dest']); ?>
                                    </div>
                                </td>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco">
                                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['messages']['date']); ?>
                                    </div>
                                </td>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco">
                                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['messages']['text']); ?>
                                    </div>
                                </td>
                            </tr>
                            <!-- Record -->
                            <?php while($row = gdrcd_query($result, 'fetch')) { ?>
                                <tr class="risultati_elenco_record_gestione">
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco">
                                            <?php echo gdrcd_filter('out', $row['destinatario']); ?>
                                        </div>
                                    </td>
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco">
                                            <?php echo gdrcd_format_date($row['spedito']).' '.gdrcd_format_time($row['spedito']); ?>
                                        </div>
                                    </td>
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco">
                                            <?php echo gdrcd_filter('out', $row['testo']); ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php } //while
                            gdrcd_query($result, 'free');
                            ?>
                        </table>
                    </div>
                <?php }//if  ?>
                <!-- Paginatore elenco -->
                <div class="pager">
                    <?php if($totaleresults > $PARAMETERS['settings']['records_per_page']) {
                        echo gdrcd_filter('out', $MESSAGE['interface']['pager']['pages_name']);
                        for($i = 0; $i <= floor($totaleresults / $PARAMETERS['settings']['records_per_page']); $i++) {
                            if($i != $_REQUEST['offset']) {
                                ?>
                                <a href="main.php?page=log_messaggi&op=view&pg=<?php echo $_REQUEST['pg']; ?>&offset=<?php echo $i; ?>"><?php echo $i + 1; ?></a>
                            <?php } else {
                                echo ' '.($i + 1).' ';
                            }
                        } //for
                    }//if
                    ?>
                </div>
                <!-- link crea nuovo -->
                <div class="link_back">
                    <a href="main.php?page=log_messaggi">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['messages']['link']['back']); ?>
                    </a>
                </div>
            <?php }//else ?>
        </div>
    <?php }//else (controllo permessi utente) ?>
</div><!--Pagina-->