<div class="pagina_scheda_oggetti">
    <?php
    //Se non e' stato specificato il nome del pg
    if(isset($_REQUEST['pg']) === false){
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['unknonw_character_sheet']).'</div>';
    } else {
        /*Visualizzo la pagina*/
        /*Verifico l'esistenza del PG*/
        $query = "SELECT nome FROM personaggio WHERE id_personaggio = '".gdrcd_filter('in', $_REQUEST['pg'])."'";
        $result = gdrcd_query($query, 'result');
        //Se non esiste il pg
        if(gdrcd_query($result, 'num_rows') == 0) {
            echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['unknown_character_sheet']).'</div>';
        } else {
            gdrcd_query($result, 'free');

            $num_logs = $PARAMETERS['settings']['view_logs'];
            ?>
            <!-- Riepilogo PX -->
            <div class="page_title">
                <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['trans']['page_name']); ?></h2>
            </div>
            <div class="page_body">
                <div class="panels_box">
                    <?php /*Seleziono le ultime 20 assegnamzioni px*/
                    $ricezione_bonifico = estraiLog('banca.ricezione_bonifico', $num_logs, (int)$_REQUEST['pg']);
                    
                    if (!empty($ricezione_bonifico)) {
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
                                        Transazione in ingresso
                                    </div>
                                </td>
                            </tr>

                            <?php foreach ($ricezione_bonifico as $record) {
                                $contesto = $record['contesto_decodificato'];
                                $ammontare =   $contesto['ammontare'] . " " . $contesto['valuta'] . " - " . $contesto['causale'];
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
                                            <?php echo '[<a href="main.php?page=scheda&pg=' . gdrcd_filter('out',
                                                    $contesto['controparte_id']) . '"  >' . gdrcd_filter('out',
                                                    $contesto['nome_interessato']) . '</a>]: ' .gdrcd_filter('out', $ammontare); ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                <?php } 
                $invio_bonifico = estraiLog('banca.invio_bonifico', $num_logs, (int)$_REQUEST['pg']);
                    
                    if (!empty($invio_bonifico)) {
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
                                        Transazione in uscita
                                    </div>
                                </td>
                            </tr>

                            <?php foreach ($invio_bonifico as $record) {
                                $contesto = $record['contesto_decodificato'];
                                $ammontare =   $contesto['ammontare'] . " " . $contesto['valuta'] . " - " . $contesto['causale'];
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
                                            <?php echo '[<a href="main.php?page=scheda&pg=' . gdrcd_filter('out',
                                                    $contesto['controparte_id']) . '"  >' . gdrcd_filter('out',
                                                    $contesto['controparte_nome']) . '</a>]: ' .gdrcd_filter('out', $ammontare); ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                <?php } ?>
                <!-- Link a piè di pagina -->
                <div class="link_back">
                    <a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>"><?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['link']['back']
                        ); ?></a>
                </div>
                <?php
                /********* CHIUSURA SCHEDA **********/
            }//else
        }//else
        ?>
    </div>
</div><!-- Pagina -->
