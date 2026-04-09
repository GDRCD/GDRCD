<div class="pagina_scheda_log">
    <?php 

    if ($_SESSION['permessi'] < MODERATOR){
        echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['not_allowed']) . '</div>';
    } else {
        //Se non e' stato specificato il nome del pg
        if (isset($_REQUEST['pg']) === false) {
            echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['unknonw_character_sheet']) . '</div>';
        }else {
            /*Visualizzo la pagina*/
            /*Verifico l'esistenza del PG*/
            $query = "SELECT id_personaggio FROM personaggio WHERE id_personaggio = '" . gdrcd_filter('get', $_REQUEST['pg']) . "'";
            $result = gdrcd_query($query, 'result');
            //Se non esiste il pg
            if (gdrcd_query($result, 'num_rows') == 0){
                    echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['unknown_character_sheet']) . '</div>';
                }else{
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
            <?php 
            /*Seleziono gli ultimi login*/
            
           $logs_login = gdrcd_extract_logs('auth.login.successo', $num_logs, (int)$_REQUEST['pg']);

            ?>
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

                    <?php foreach ($logs_login as $record) {
                        $contesto = $record['contesto_decodificato'];
                        $ip = $contesto['ip'] ?? '';
                    ?>
                        <tr>
                            <td class="casella_elemento" style="width: 30%;">
                                <div class="elementi_elenco">
                                    <?php echo gdrcd_filter('out',
                                        gdrcd_format_date($record['data']) . ' ' . gdrcd_format_time($record['data'])
                                    ); ?>
                                </div>
                            </td>
                            <td class="casella_elemento">
                                <div class="elementi_elenco">
                                    <?php echo gdrcd_filter('out', $ip); ?>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
            <div class="page_title">
                <h2>Multiaccount Login</h2>        
            </div>

            <?php
                $logs_multi_cookie = gdrcd_extract_logs('auth.multiaccount.cookie', $num_logs, (int)$_REQUEST['pg']);
                $logs_multi_ip = gdrcd_extract_logs('auth.multiaccount.ip', $num_logs, (int)$_REQUEST['pg']);

                $logs_multi = array_merge($logs_multi_cookie, $logs_multi_ip);

                usort($logs_multi, function ($a, $b) {
                    return strtotime($b['data']) <=> strtotime($a['data']);
                });

                $logs_multi = array_slice($logs_multi, 0, $num_logs);

                if (!empty($logs_multi)) {
                ?>
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

                            <?php foreach ($logs_multi as $record) {
                                $contesto = $record['contesto_decodificato'];
                                $altroAccount = $contesto['altro_account'] ?? '';
                            ?>
                                <tr>
                                    <td class="casella_elemento" style="width: 30%;">
                                        <div class="elementi_elenco">
                                            <?php echo gdrcd_filter('out',
                                                gdrcd_format_date($record['data']) . ' ' . gdrcd_format_time($record['data'])
                                            ); ?>
                                        </div>
                                    </td>
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco">
                                            <?php echo gdrcd_filter('out', $altroAccount); ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                <?php } 
                
                /*Seleziono gli ultimi messaggi*/
            if ($PARAMETERS['mode']['spymessages'] == 'ON')
            {
                $query = "SELECT 
                            id_personaggio_destinatario, 
                            personaggio.nome AS destinatario,
                            spedito, 
                            testo  
                            FROM backmessaggi 
                            LEFT JOIN personaggio ON backmessaggi.id_personaggio_destinatario = personaggio.id_personaggio
                            WHERE id_personaggio_mittente = '" . gdrcd_filter('in', $_REQUEST['pg']) . "' 
                            ORDER BY spedito DESC LIMIT " . $num_logs . "";
                            $result = gdrcd_query($query, 'result');


                if (gdrcd_query($result, 'num_rows') > 0)
                {
                    ?>
                    <div class="page_title">
                        <h2>Messaggi</h2>        
                    </div>
                    <!-- Intestazione tabella elenco -->
                    <div class="elenco_record_gioco">
                        <table>
                            <tr>
                                <td class="casella_titolo" style="width: 30%;">
                                    <div class="titoli_elenco" >
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
                                    <td class="casella_elemento" style="width: 30%;">
                                        <div class="elementi_elenco"><?php echo gdrcd_filter('out',
                                                gdrcd_format_date($record['spedito']) . ' ' . gdrcd_format_time($record['spedito'])); ?></div>
                                    </td>
                                    <td class="casella_elemento">
                                        <div
                                            class="elementi_elenco"><?php echo '[<a href="main.php?page=scheda&pg=' . gdrcd_filter('out',
                                                    $record['id_personaggio_destinatario']) . '"  >' . gdrcd_filter('out',
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
                 }//if spymessages on
           
           $logs_cambio_nome = gdrcd_extract_logs('personaggio.cambio_nome', $num_logs, (int)$_REQUEST['pg']);

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
                                $contesto = $record['contesto_decodificato'];
                                $altroAccount = "Nome precedente: " . $contesto['nome_precedente'] . " -> Nome nuovo: " . $contesto['nome_nuovo'];
                            ?>
                                <tr>
                                    <td class="casella_elemento" style="width: 30%;">
                                        <div class="elementi_elenco">
                                            <?php echo gdrcd_filter('out',
                                                gdrcd_format_date($record['data']) . ' ' . gdrcd_format_time($record['data'])
                                            ); ?>
                                        </div>
                                    </td>
                                    <td class="casella_elemento">
                                        <div class="elementi_elenco">
                                            <?php echo gdrcd_filter('out', $altroAccount); ?>
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
        }//else

    }//else
        ?>


        <?php 
    } //else </div>?>
    </div>
    <!-- Pagina -->