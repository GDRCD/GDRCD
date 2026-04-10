<div class="pagina_gestione_razze">
    <?php
    /*Controllo permessi utente*/
    if ($_SESSION['permessi'] < SUPERUSER) {
        echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['not_allowed']) . '</div>';
    } else {
    ?>

        <div class="page_title">
            <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['events']['page_name']); ?></h2>
        </div>

        <div class="page_body">

            <?php if ((isset($_POST['op']) === false) && (isset($_REQUEST['op']) === false)) { ?>
                <div class="panels_box">
                    <div class="form_gestione">
                        <form action="main.php?page=log_eventi" method="post">
                            <div class='form_label'>
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['events']['log_type']); ?>
                            </div>
                            <div class='form_field'>
                                <select name="which_log">
                                    <?php foreach ($MESSAGE['event'] as $eventKey => $eventLabel) { ?>
                                        <option value="<?php echo (int)$eventKey; ?>">
                                            <?php echo gdrcd_filter('out', $eventLabel); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class='form_submit'>
                                <input type="hidden" value="view" name="op" />
                                <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>" />
                            </div>
                        </form>
                    </div>
                </div>
            <?php } ?>

            <?php
            if (
                isset($_REQUEST['op']) &&
                $_REQUEST['op'] === 'view' &&
                isset($_REQUEST['which_log']) &&
                is_numeric($_REQUEST['which_log'])
            ) {
                $whichLog = (int)$_REQUEST['which_log'];
                $offset = isset($_REQUEST['offset']) ? (int)$_REQUEST['offset'] : 0;
                $pagebegin = $offset * (int)$PARAMETERS['settings']['records_per_page'];
                $pageend = (int)$PARAMETERS['settings']['records_per_page'];

                $eventi = gdrcd_log_group_from_code($whichLog);

                if (empty($eventi)) {
                    echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['unknown_operation']) . '</div>';
                } else {
                    $totaleresults = gdrcd_count_logs($eventi);
                    $logs = gdrcd_extract_logs($eventi, $pageend, $pagebegin);
                    $numresults = count($logs);

                    if ($numresults > 0) { ?>
                        <div class="elenco_record_gestione">
                            <table>
                                <tr>
                                    <td class="casella_titolo">
                                        <div class="titoli_elenco">
                                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['events']['author']); ?>
                                        </div>
                                    </td>
                                    <td class="casella_titolo">
                                        <div class="titoli_elenco">
                                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['events']['dest']); ?>
                                        </div>
                                    </td>
                                    <td class="casella_titolo">
                                        <div class="titoli_elenco">
                                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['events']['date']); ?>
                                        </div>
                                    </td>
                                    <td class="casella_titolo">
                                        <div class="titoli_elenco">
                                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['events']['descr']); ?>
                                        </div>
                                    </td>
                                </tr>

                                <?php foreach ($logs as $row) {
                                    $presentazione = gdrcd_present_log_row($whichLog, $row);
                                ?>
                                    <tr class="risultati_elenco_record_gestione">
                                        <td class="casella_elemento">
                                            <div class="elementi_elenco">
                                                <?php echo gdrcd_filter('out', $presentazione['autore']); ?>
                                            </div>
                                        </td>
                                        <td class="casella_elemento">
                                            <div class="elementi_elenco">
                                                <?php echo gdrcd_filter('out', $presentazione['destinatario']); ?>
                                            </div>
                                        </td>
                                        <td class="casella_elemento">
                                            <div class="elementi_elenco">
                                                <?php echo gdrcd_format_date($row['data']) . ' ' . gdrcd_format_time($row['data']); ?>
                                            </div>
                                        </td>
                                        <td class="casella_elemento">
                                            <div class="elementi_elenco">
                                                <?php echo gdrcd_filter('out', $presentazione['descrizione']); ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>
                        </div>
                    <?php } else { ?>
                        <div class="warning">
                            Nessun log trovato per il filtro selezionato.
                        </div>
                    <?php } ?>

                    <div class="pager">
                        <?php
                        if ($totaleresults > $PARAMETERS['settings']['records_per_page']) {
                            echo gdrcd_filter('out', $MESSAGE['interface']['pager']['pages_name']);

                            for ($i = 0; $i <= floor(($totaleresults - 1) / $PARAMETERS['settings']['records_per_page']); $i++) {
                                if ($i != $offset) { ?>
                                    <a href="main.php?page=log_eventi&op=view&which_log=<?php echo $whichLog; ?>&offset=<?php echo $i; ?>">
                                        <?php echo $i + 1; ?>
                                    </a>
                        <?php } else {
                                    echo ' ' . ($i + 1) . ' ';
                                }
                            }
                        }
                        ?>
                    </div>

                    <div class="link_back">
                        <a href="main.php?page=log_eventi">
                            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['log']['events']['link']['back']); ?>
                        </a>
                    </div>
            <?php }
            } ?>
        </div>
    <?php } ?>
</div>