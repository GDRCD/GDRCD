<div class="pagina_scheda_oggetti">
    <?php
    //Se non e' stato specificato il nome del pg
    if(isset($_GET['pg']) === false){
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['unknonw_character_sheet']).'</div>';
    } else {
        /*Visualizzo la pagina*/
        /*Verifico l'esistenza del PG*/
        $query = "SELECT nome FROM personaggio WHERE id_personaggio = '".gdrcd_filter('in', $_GET['pg'])."'";   
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
                    $logs_multi = gdrcd_extract_logs(['banca.ricezione_bonifico', 'banca.invio_bonifico'],  $_GET['pg'], $num_logs);
                   
                    usort($logs_multi, function($a, $b) {
                        return $b['data'] - $a['data'];
                    });
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
                                        Transazioni
                                    </div>
                                </td>
                            </tr>

                            <?php foreach ($logs_multi as $record) {
                                
                                $contesto = gdrcd_extract_log_contesto($record);
                                $ammontare = $contesto['ammontare'] . " " . $contesto['valuta'] . " - " . $contesto['causale'];
                                $direzione=$contesto['direzione'];
                                
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
                                            <?php 
                                            if($direzione === 'entrata'){
                                                $id = $contesto['id_autore'];
                                                $nome = $contesto['autore'];
                                                $icona = '<span style="color: #009933; font-size: 16px;font-weight: bold;" title="Entrata">↑</span>';
                                            } else {
                                                 $id = $contesto['id_destinatario'];
                                                $nome = $contesto['nome_destinatario'];
                                                $icona = '<span style="color: #ff0000ff; font-size: 16px;font-weight: bold;" title="Uscita">↓</span>';
                                            }
                                            
                                            echo $icona . ' ' . $record['descrizione'] . ' ' . ' [<a href="main.php?page=scheda&pg=' . $id . '"  >' . gdrcd_filter('out',
                                                      $nome) . '</a>]: ' .gdrcd_filter('out', $ammontare); ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                <?php } 
                ?>
                
                    
                    
                <!-- Link a piè di pagina -->
                <div class="link_back">
                    <a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('url', $_GET['pg']); ?>"><?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['link']['back']
                        ); ?></a>
                </div>
                <?php
                /********* CHIUSURA SCHEDA **********/
            }//else
        }//else
        ?>
    </div>
</div><!-- Pagina -->
