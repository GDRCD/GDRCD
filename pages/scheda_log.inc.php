<div class="pagina_scheda_log">
    <?php

    if ($_SESSION['permessi'] < MODERATOR) {
        echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['not_allowed']) . '</div>';
    } else {
        //Se non e' stato specificato il nome del pg
        if (isset($_REQUEST['pg']) === false) {
            echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['unknonw_character_sheet']) . '</div>';
        } else {
            /*Visualizzo la pagina*/
            /*Verifico l'esistenza del PG*/
            $query = "SELECT id_personaggio
             FROM personaggio 
            WHERE id_personaggio = ?";
            $result = gdrcd_stmt_one($query, [$_REQUEST['pg']]);
            //Se non esiste il pg
            if ($result === false) {
                echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['unknown_character_sheet']) . '</div>';
            } else {
                $num_logs = $PARAMETERS['settings']['view_logs'];
    ?>

                <div class="page_title">
                    <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['log']['page_name']); ?></h2>
                </div>

                <div class="page_body">
                    <div class="page_title">
                        <h2>Ultimi Login</h2>
                    </div>
                    <div class="panels_box">
                        <!-- Intestazione tabella elenco -->
                        <div class="elenco_record_gioco">
                            <table>
                                <tr>
                                    <td class="casella_titolo" style="width: 30%;">
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

                                <?php
                                /*Seleziono gli ultimi login*/
                                $logs_login = gdrcd_extract_logs('auth.login.successo',$_REQUEST['pg'], $num_logs);

                                foreach ($logs_login as $record) {
                                    $contesto = gdrcd_extract_log_contesto($record);
                                   
                                ?>
                                    <tr>
                                        <td class="casella_elemento" style="width: 30%;">
                                            <div class="elementi_elenco">
                                                <?php echo gdrcd_filter(
                                                    'out',
                                                    gdrcd_format_date($record['data']) . ' ' . gdrcd_format_time($record['data'])
                                                ); ?>
                                            </div>
                                        </td>
                                        <td class="casella_elemento">
                                            <div class="elementi_elenco">
                                                <?php echo $contesto['ip']; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>
                        </div>
                        

                        <?php
                         
                        $logs_multi = gdrcd_extract_logs(['auth.multiaccount.ip','auth.multiaccount.cookie'], $_GET['pg'], $num_logs);

                        usort($logs_multi, function ($a, $b) {
                            return strtotime($b['data']) <=> strtotime($a['data']);
                        });

                        $logs_multi = array_slice($logs_multi, 0, $num_logs);
                        
                        if (!empty($logs_multi)) {
                        ?>
                        <div class="page_title">
                            <h2>Multiaccount Login</h2>
                        </div>
                            <div class="elenco_record_gioco">
                                <table>
                                    <tr>
                                        <td class="casella_titolo" style="width: 30%;">
                                            <div class="titoli_elenco">
                                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['log']['date']); ?>
                                            </div>
                                        </td>
                                        <td class="casella_titolo">
                                            <div class="titoli_elenco">
                                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['log']['other_account']); ?>
                                            </div>
                                        </td>
                                    </tr>

                                    <?php 
                                    foreach ($logs_multi as $record) {
                                        $contesto = gdrcd_extract_log_contesto($record);
                                        //controllo che utente corrente e altro account non siano lo stesso                                            
                                    ?>
                                        <tr>
                                            <td class="casella_elemento" style="width: 30%;">
                                                <div class="elementi_elenco">
                                                    <?php echo gdrcd_filter(
                                                        'out',
                                                        gdrcd_format_date($record['data']) . ' ' . gdrcd_format_time($record['data'])
                                                    ); ?>
                                                </div>
                                            </td>
                                            <td class="casella_elemento">
                                                <div class="elementi_elenco">
                                                    <?php echo $contesto['soggetto'] . " - ".$record['descrizione']." IP:" . $contesto['ip']; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php
                                     } //fine ciclo

                                     ?>
                                </table>
                            </div>
                            <?php }

                        /*Seleziono gli ultimi messaggi*/
                        if ($PARAMETERS['mode']['spymessages'] == 'ON') {

                            $query = "SELECT 
                            id_personaggio_destinatario, 
                            personaggio.nome AS destinatario,
                            spedito, 
                            testo  
                            FROM messaggi 
                            LEFT JOIN personaggio ON messaggi.id_personaggio_destinatario = personaggio.id_personaggio
                            WHERE id_personaggio_mittente = ? 
                            ORDER BY spedito DESC LIMIT " . $num_logs . "";
                           
                           
                            $result = gdrcd_stmt_all($query, [$_GET['pg']]);


                            if ($result) {
                            ?>
                                <div class="page_title">
                                    <h2>Messaggi</h2>
                                </div>
                                <!-- Intestazione tabella elenco -->
                                <div class="elenco_record_gioco">
                                    <table>
                                        <tr>
                                            <td class="casella_titolo" style="width: 30%;">
                                                <div class="titoli_elenco">
                                                    <?php echo gdrcd_filter(
                                                        'out',
                                                        $MESSAGE['interface']['sheet']['log']['date']
                                                    ); ?>
                                                </div>
                                            </td>
                                            <td class="casella_titolo">
                                                <div class="titoli_elenco">
                                                    <?php echo gdrcd_filter(
                                                        'out',
                                                        $MESSAGE['interface']['sheet']['log']['message']
                                                    ); ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php foreach ($result as $record) { ?>
                                            <tr>
                                                <td class="casella_elemento" style="width: 30%;">
                                                    <div class="elementi_elenco"><?php echo gdrcd_filter(
                                                                                        'out',
                                                                                        gdrcd_format_date($record['spedito']) . ' ' . gdrcd_format_time($record['spedito'])
                                                                                    ); ?></div>
                                                </td>
                                                <td class="casella_elemento">
                                                    <div
                                                        class="elementi_elenco"><?php echo '[<a href="main.php?page=scheda&pg=' . gdrcd_filter(
                                                                                    'out',
                                                                                    $record['id_personaggio_destinatario']
                                                                                ) . '"  >' . gdrcd_filter(
                                                                                    'out',
                                                                                    $record['destinatario']
                                                                                ) . '</a>]: ' . gdrcd_filter(
                                                                                    'out',
                                                                                    $record['testo']
                                                                                ); ?></div>
                                                </td>
                                            </tr>
                                        <?php } //while
                                        ?>
                                    </table>
                                </div>
                            <?php } //if
                        } //if spymessages on

                        $logs_cambio_nome = gdrcd_extract_logs('personaggio.cambio_nome', $_REQUEST['pg'], $num_logs,);

                        if (!empty($logs_cambio_nome)) {
                            ?>
                            <div class="page_title">
                                <h2>Cambio nome</h2>
                            </div>
                            <div class="elenco_record_gioco">
                                <table>
                                    <tr>
                                        <td class="casella_titolo" style="width: 30%;">
                                            <div class="titoli_elenco">
                                                <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['log']['date']); ?>
                                            </div>
                                        </td>
                                        <td class="casella_titolo">
                                            <div class="titoli_elenco">
                                                Cambio nome
                                            </div>
                                        </td>
                                    </tr>

                                    <?php foreach ($logs_cambio_nome as $record) {
                                        $presentazione = gdrcd_present_log_row(CHANGEDNAME, $record);

                                    ?>
                                        <tr>
                                            <td class="casella_elemento" style="width: 30%;">
                                                <div class="elementi_elenco">
                                                    <?php echo gdrcd_filter(
                                                        'out',
                                                        gdrcd_format_date($record['data']) . ' ' . gdrcd_format_time($record['data'])
                                                    ); ?>
                                                </div>
                                            </td>
                                            <td class="casella_elemento">
                                                <div class="elementi_elenco">
                                                    <?php echo $presentazione['descrizione']; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </table>
                            </div>
                        <?php } ?>
                        <!-- panels_box -->


                        <!-- Link a piè di pagina -->
                        <div class="link_back">
                            <a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>"><?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['link']['back']); ?></a>
                        </div>


                <?php
                /********* CHIUSURA SCHEDA **********/
            } //else

        } //else
                ?>


            <?php
        } //else </div>
            ?>
</div>
               