<div class="user_stats">
    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['stats']['page_name']); ?></h2>
    </div>
    <div class="page_body">
        <?php /*Visualizzazione di base*/
        if (isset($_REQUEST['op']) === false) {
        ?>
            <div class="panels_box">
                <div class="elenco_record_gioco">
                    <table>
                        <?php $row = gdrcd_query("SELECT MIN(data_iscrizione) AS stat FROM personaggio"); ?>
                        <tr>
                            <td class="casella_elemento">
                                <div class="elementi_elenco">
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['stats']['creation_date']) . ': '; ?>
                                </div>
                            </td>
                            <td class="casella_elemento">
                                <div class="elementi_elenco">
                                    <?php echo gdrcd_format_date($row['stat']); ?>
                                </div>
                            </td>
                        </tr>
                        <?php $row = gdrcd_query("SELECT COUNT(*) AS stat FROM personaggio"); ?>
                        <tr>
                            <td class="casella_elemento">
                                <div class="elementi_elenco">
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['stats']['characters']) . ': '; ?>
                                </div>
                            </td>
                            <td class="casella_elemento">
                                <div class="elementi_elenco">
                                    <?php echo $row['stat']; ?>
                                </div>
                            </td>
                        </tr>
                        <?php $row = gdrcd_query("SELECT COUNT(*) AS stat FROM personaggio WHERE esilio > NOW()"); ?>
                        <tr>
                            <td class="casella_elemento">
                                <div class="elementi_elenco">
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['stats']['exiled']) . ': '; ?>
                                </div>
                            </td>
                            <td class="casella_elemento">
                                <div class="elementi_elenco">
                                    <?php
                                    if (gdrcd_filter_get($_REQUEST['links']) == 'yes') { ?>
                                        <a href='main.php?page=user_stats&op=esiliati'>
                                            <?php echo $row['stat']; ?>
                                        </a>
                                    <?php
                                    } else {
                                        echo $row['stat'];
                                    } ?>
                                </div>
                            </td>
                        </tr>
                        <?php $row = gdrcd_query("SELECT COUNT(*) AS stat FROM personaggio WHERE permessi = " . GAMEMASTER . ""); ?>
                        <tr>
                            <td class="casella_elemento">
                                <div class="elementi_elenco">
                                    <?php echo gdrcd_filter('out', $PARAMETERS['names']['master']['plur']) . ': '; ?>
                                </div>
                            </td>
                            <td class="casella_elemento">
                                <div class="elementi_elenco">
                                    <?php echo $row['stat']; ?>
                                </div>
                            </td>
                        </tr>
                        <?php $row = gdrcd_query("SELECT COUNT(*) AS stat FROM personaggio WHERE permessi >= " . MODERATOR . ""); ?>
                        <tr>
                            <td class="casella_elemento">
                                <div class="elementi_elenco">
                                    <?php echo gdrcd_filter('out', $PARAMETERS['names']['moderators']['plur']) . ': '; ?>
                                </div>
                            </td>
                            <td class="casella_elemento">
                                <div class="elementi_elenco">
                                    <?php echo $row['stat']; ?>
                                </div>
                            </td>
                        </tr>
                        <?php $row = gdrcd_query("SELECT COUNT(*) AS stat FROM messaggioaraldo WHERE data_messaggio > DATE_SUB(NOW(), INTERVAL 7 DAY)"); ?>
                        <tr>
                            <td class="casella_elemento">
                                <div class="elementi_elenco">
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['stats']['topics']) . ': '; ?>
                                </div>
                            </td>
                            <td class="casella_elemento">
                                <div class="elementi_elenco">
                                    <?php echo $row['stat']; ?>
                                </div>
                            </td>
                        </tr>
                        <?php $row = gdrcd_query("SELECT COUNT(*) AS stat FROM chat WHERE ora > DATE_SUB(NOW(), INTERVAL 7 DAY)"); ?>
                        <tr>
                            <td class="casella_elemento">
                                <div class="elementi_elenco">
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['stats']['last_chat']) . ': '; ?>
                                </div>
                            </td>
                            <td class="casella_elemento">
                                <div class="elementi_elenco">
                                    <?php echo $row['stat']; ?>
                                </div>
                            </td>
                        </tr>
                        <?php $row = gdrcd_query("SELECT COUNT(*) AS stat FROM personaggio WHERE data_iscrizione > DATE_SUB(NOW(), INTERVAL 7 DAY)"); ?>
                        <tr>
                            <td class="casella_elemento">
                                <div class="elementi_elenco">
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['stats']['last_characters']) . ': '; ?>
                                </div>
                            </td>
                            <td class="casella_elemento">
                                <div class="elementi_elenco">
                                    <?php
                                    if (gdrcd_filter_get($_REQUEST['links']) == 'yes') { ?>
                                        <a href='main.php?page=user_stats&op=nuovi'>
                                            <?php echo $row['stat']; ?>
                                        </a>
                                    <?php
                                    } else {
                                        echo $row['stat'];
                                    } ?>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
                <!--elenco_record_gioco-->
            </div><!--panels_box-->
        <?php
        }//if
        if (gdrcd_filter_get($_REQUEST['op']) == 'nuovi') {/*Nuovi iscritti*/
            $query = "SELECT nome, cognome, data_iscrizione FROM personaggio WHERE data_iscrizione > DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY data_iscrizione DESC";
            $result = gdrcd_query($query, 'result'); ?>
            <div class="panels_box">
                <div class="elenco_record_gioco">
                    <table>
                        <tr>
                            <td class="casella_titolo">
                                <div class="titoli_elenco">
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['stats']['character']); ?>
                                </div>
                            </td>
                            <td class="casella_titolo">
                                <div class="titoli_elenco">
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['stats']['date']); ?>
                                </div>
                            </td>
                        </tr>
                        <?php while ($row = gdrcd_query($result, 'fetch')) { ?>
                            <tr>
                                <td class="casella_elemento">
                                    <div class="elementi_elenco">
                                        <a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('out', $row['nome']); ?>">
                                            <?php echo gdrcd_filter('out', $row['nome']) . ' ' . gdrcd_filter('out', $row['cognome']); ?>
                                        </a>
                                    </div>
                                </td>
                                <td class="casella_elemento">
                                    <div class="elementi_elenco">
                                        <?php echo gdrcd_format_date($row['data_iscrizione']) . ' ' . gdrcd_format_time($row['data_iscrizione']) ?>
                                    </div>
                                </td>
                            </tr>
                        <?php }//while
                        gdrcd_query($result, 'free');
                        ?>
                    </table>
                </div>
                <!--elenco_record_gioco-->
            </div><!--panels_box-->
                  <!-- Link di ritorno alla visualizzazione di base -->
            <div class="link_back">
                <a href="main.php?page=user_stats&links=yes">
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['stats']['link']['back']); ?>
                </a>
            </div>
        <?php
        }
        if (gdrcd_filter_get($_REQUEST['op']) == 'esiliati') {/*Esiliati*/
            $query = "SELECT nome, cognome, esilio, data_esilio, autore_esilio, motivo_esilio FROM personaggio WHERE esilio > NOW() ORDER BY data_esilio DESC";
            $result = gdrcd_query($query, 'result'); ?>
            <div class="panels_box">
                <div class="elenco_record_gioco">
                    <table>
                        <tr>
                            <td class="casella_titolo">
                                <div class="titoli_elenco">
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['stats']['character']); ?>
                                </div>
                            </td>
                            <td class="casella_titolo">
                                <div class="titoli_elenco">
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['stats']['date_end']); ?>
                                </div>
                            </td>
                            <td class="casella_titolo">
                                <div class="titoli_elenco">
                                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['stats']['why']); ?>
                                </div>
                            </td>
                        </tr>
                        <?php while ($row = gdrcd_query($result, 'fetch')) { ?>
                            <tr>
                                <td class="casella_elemento">
                                    <div class="elementi_elenco">
                                        <a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('out', $row['nome']); ?>">
                                            <?php echo gdrcd_filter('out', $row['nome']) . ' ' . gdrcd_filter('out', $row['cognome']); ?>
                                        </a>
                                    </div>
                                </td>
                                <td class="casella_elemento">
                                    <div class="elementi_elenco">
                                        <?php echo gdrcd_format_date($row['esilio']); ?>
                                    </div>
                                </td>
                                <td class="casella_elemento">
                                    <div class="elementi_elenco">
                                        <?php echo gdrcd_filter('out', $row['motivo_esilio']) . ' (' . gdrcd_filter('out', $row['autore_esilio'] . ', ' . gdrcd_format_date($row['data_esilio']) . ') '); ?>
                                    </div>
                                </td>
                            </tr>
                        <?php
                        }//while
                        gdrcd_query($result, 'free');
                        ?>
                    </table>
                </div>
                <!--elenco_record_gioco-->
            </div><!--panels_box-->
                  <!-- Link di ritorno alla visualizzazione di base -->
            <div class="link_back">
                <a href="main.php?page=user_stats&links=yes">
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['user']['stats']['link']['back']); ?>
                </a>
            </div>
        <?php } ?>
    </div>
</div><!-- pagina -->
