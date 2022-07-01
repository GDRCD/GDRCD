<div class="pagina_scheda_log">
    <?php /*HELP: */ ?>

    <?php

    if ($_SESSION['permessi'] < MODERATOR)
    {
        echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['not_allowed']) . '</div>';
    } else
    {
    //Se non e' stato specificato il nome del pg
    if (isset($_REQUEST['pg']) === false)
    {
        echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['unknonw_character_sheet']) . '</div>';
    } else
    {
    /*Visualizzo la pagina*/
    /*Verifico l'esistenza del PG*/
    $query = "SELECT nome FROM personaggio WHERE personaggio.nome = '" . gdrcd_filter('get', $_REQUEST['pg']) . "'";
    $result = gdrcd_query($query, 'result');
    //Se non esiste il pg
    if (gdrcd_query($result, 'num_rows') == 0)
    {
        echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['unknown_character_sheet']) . '</div>';
    }
    else
    {
    $num_logs = $PARAMETERS['settings']['view_logs'];
    ?>

    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['log']['page_name']); ?></h2>
    </div>

    <div class="page_body">


        <div class="panels_box">


            <?php /*Seleziono gli ultimi login*/
            $query = "SELECT  descrizione_evento, data_evento  FROM log WHERE nome_interessato = '" . gdrcd_filter('in',
                    $_REQUEST['pg']) . "'  AND codice_evento = " . LOGGEDIN . " ORDER BY data_evento DESC LIMIT " . $num_logs . "";
            $result = gdrcd_query($query, 'result');
            ?>
            <!-- Intestazione tabella elenco -->
            <div class="elenco_record_gioco">
                <table>
                    <tr>
                        <td class="casella_titolo">
                            <div class="titoli_elenco">
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['log']['date']); ?>
                            </div>
                        </td>
                        <td class="casella_titolo">
                            <div class="titoli_elenco">
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['log']['ip']); ?>
                            </div>
                        </td>
                    </tr>
                    <?php while ($record = gdrcd_query($result, 'fetch'))
                    { ?>
                        <tr>
                            <td class="casella_elemento">
                                <div class="elementi_elenco"><?php echo gdrcd_filter('out',
                                        gdrcd_format_date($record['data_evento']) . ' ' . gdrcd_format_time($record['data_evento'])); ?></div>
                            </td>
                            <td class="casella_elemento">
                                <div class="elementi_elenco"><?php echo gdrcd_filter('out',
                                        $record['descrizione_evento']); ?></div>
                            </td>
                        </tr>
                    <?php }//while

                    gdrcd_query($result, 'free');
                    ?>
                </table>
            </div>


            <?php /*Seleziono gli eventuali doppi*/
            $query = "SELECT  descrizione_evento, data_evento  FROM log WHERE nome_interessato = '" . gdrcd_filter('in',
                    $_REQUEST['pg']) . "'  AND codice_evento = " . ACCOUNTMULTIPLO . " ORDER BY data_evento DESC LIMIT " . $num_logs . "";
            $result = gdrcd_query($query, 'result');

            if (gdrcd_query($result, 'num_rows') > 0)
            {
                ?>
                <!-- Intestazione tabella elenco -->
                <div class="elenco_record_gioco">
                    <table>
                        <tr>
                            <td class="casella_titolo">
                                <div class="titoli_elenco">
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['log']['date']); ?>
                                </div>
                            </td>
                            <td class="casella_titolo">
                                <div class="titoli_elenco">
                                    <?php echo gdrcd_filter('out',
                                        $MESSAGE['interface']['sheet']['log']['other_account']); ?>
                                </div>
                            </td>
                        </tr>
                        <?php while ($record = gdrcd_query($result, 'fetch'))
                        { ?>
                            <tr>
                                <td class="casella_elemento">
                                    <div class="elementi_elenco"><?php echo gdrcd_filter('out',
                                            gdrcd_format_date($record['data_evento']) . ' ' . gdrcd_format_time($record['data_evento'])); ?></div>
                                </td>
                                <td class="casella_elemento">
                                    <div class="elementi_elenco"><?php echo gdrcd_filter('out',
                                            $record['descrizione_evento']); ?></div>
                                </td>
                            </tr>
                        <?php }//while
                        gdrcd_query($result, 'free');

                        ?>
                    </table>
                </div>
            <?php } ?>


            <?php /*Seleziono gli ultimi messaggi*/
            if ($PARAMETERS['mode']['spymessages'] == 'ON')
            {
                $query = "SELECT  destinatario, spedito, testo  FROM backmessaggi WHERE mittente = '" . gdrcd_filter('in',
                        $_REQUEST['pg']) . "' ORDER BY spedito DESC LIMIT " . $num_logs . "";
                $result = gdrcd_query($query, 'result');


                if (gdrcd_query($result, 'num_rows') > 0)
                {
                    ?>
                    <!-- Intestazione tabella elenco -->
                    <div class="elenco_record_gioco">
                        <table>
                            <tr>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco">
                                        <?php echo gdrcd_filter('out',
                                            $MESSAGE['interface']['sheet']['log']['date']); ?>
                                    </div>
                                </td>
                                <td class="casella_titolo">
                                    <div class="titoli_elenco">
                                        <?php echo gdrcd_filter('out',
                                            $MESSAGE['interface']['sheet']['log']['message']); ?>
                                    </div>
                                </td>
                            </tr>
                            <?php while ($record = gdrcd_query($result, 'fetch'))
                            { ?>
                                <tr>
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco"><?php echo gdrcd_filter('out',
                                                gdrcd_format_date($record['spedito']) . ' ' . gdrcd_format_time($record['spedito'])); ?></div>
                                    </td>
                                    <td class="casella_elemento">
                                        <div
                                            class="elementi_elenco"><?php echo '[<a href="main.php?page=scheda&pg=' . gdrcd_filter('out',
                                                    $record['destinatario']) . '"  >' . gdrcd_filter('out',
                                                    $record['destinatario']) . '</a>]: ' . gdrcd_filter('out',
                                                    $record['testo']); ?></div>
                                    </td>
                                </tr>
                            <?php }//while

                            gdrcd_query($result, 'free');
                            ?>
                        </table>
                    </div>
                <?php }//if
                ?>
            <?php }//if spymessages on
            ?>



            <?php /*Seleziono gli ultimi login*/
            $query = "SELECT  descrizione_evento, data_evento, autore  FROM log WHERE nome_interessato = '" . gdrcd_filter('in',
                    $_REQUEST['pg']) . "'  AND codice_evento = " . CHANGEDNAME . " ORDER BY data_evento DESC LIMIT " . $num_logs . "";
            $result = gdrcd_query($query, 'result');
            if (gdrcd_query($result, 'num_rows') > 0)
            {
            ?>
            <!-- Intestazione tabella elenco -->
            <div class="elenco_record_gioco">
                <table>
                    <tr>
                        <td class="casella_titolo">
                            <div class="titoli_elenco">
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['log']['date']); ?>
                            </div>
                        </td>
                        <td class="casella_titolo">
                            <div class="titoli_elenco">
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['log']['author']); ?>
                            </div>
                        </td>
                        <td class="casella_titolo">
                            <div class="titoli_elenco">
                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['log']['name_change']); ?>
                            </div>
                        </td>
                    </tr>
                    <?php while ($record = gdrcd_query($result, 'fetch'))
                    { ?>
                        <tr>
                            <td class="casella_elemento">
                                <div class="elementi_elenco"><?php echo gdrcd_filter('out',
                                        gdrcd_format_date($record['data_evento']) . ' ' . gdrcd_format_time($record['data_evento'])); ?></div>
                            </td>
                            <td class="casella_elemento">
                                <div class="elementi_elenco"><?php echo gdrcd_filter('out', $record['autore']); ?></div>
                            </td>
                            <td class="casella_elemento">
                                <div class="elementi_elenco"><?php echo gdrcd_filter('out',
                                        $record['descrizione_evento']); ?></div>
                            </td>
                        </tr>
                    <?php }//while

                    gdrcd_query($result, 'free');
                    ?>
                </table>
                <?php }//if
                ?>
            </div>

        </div>
        <!-- panels_box -->


        <!-- Link a piè di pagina -->
        <div class="link_back">
            <a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('url',
                $_REQUEST['pg']); ?>"><?php echo gdrcd_filter('out',
                    $MESSAGE['interface']['sheet']['link']['back']); ?></a>
        </div>


        <?php
        /********* CHIUSURA SCHEDA **********/
        }//else
        gdrcd_query($result, 'free');
        }//else
        ?>


        <?php } //else </div>?>
    </div>
    <!-- Pagina -->