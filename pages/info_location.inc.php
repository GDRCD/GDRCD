<div class="pagina_info_location">
    <?php /* HELP: Il box delle informazioni carica l'immagine del luogo corrente, lo stato e la descrizione. Genera, inoltre, il meteo */

    # Funzione per il calcolo della fase lunare
    function gdrcd_lunar_phase()
    {
        # Inizializzo dati necessari
        $year = date('Y');
        $month = date('n');
        $days = date('j');

        # Se e' prima di aprile sottraggo un anno
        if ($month < 4) {
            $year = $year - 1;
            $month = $month + 12;
        }

        # Eseguo calcoli astronomici
        $days_y = 365.25 * $year;
        $days_m = 30.42 * $month;
        $plenilunio = $days_y + $days_m + $days - 694039.09;
        $plenilunio = $plenilunio / 29.53;
        $phase = intval($plenilunio);
        $plenilunio = $plenilunio - $phase;
        $phase = round($plenilunio * 8 + 0.5);

        # ...
        if ($phase == 8) {
            $phase = 0;
        }

        # Creo gli array delle fasi
        $phase_array = array('nuova', 'crescente', 'primo-quarto', 'gibbosa-crescente', 'piena', 'gibbosa-calante', 'ultimo-quarto', 'calante');
        $phase_title = array('Nuova', 'Crescente', 'Primo Quarto', 'Gibbosa crescente', 'Piena', 'Gibbosa calante', 'Ultimo quarto', 'Calante');

        # Estraggo e ritorno la fase calcolata
        return array('phase' => $phase_array[$phase], 'title' => $phase_title[$phase]);
    }

    $result = gdrcd_query("SELECT mappa.nome, mappa.descrizione, mappa.stato, mappa.immagine, mappa.stanza_apparente, mappa.scadenza, mappa_click.meteo FROM  mappa_click LEFT JOIN mappa ON mappa_click.id_click = mappa.id_mappa WHERE id = " . $_SESSION['luogo'], 'result');
    $record_exists = gdrcd_query($result, 'num_rows');
    $record = gdrcd_query($result, 'fetch');

    /** * Fix: quando non si � in una mappa visualizza il nome della chat
     * Quando si � in una mappa si visualizza il nome della mappa
     * @author Blancks
     */
    if (empty($record['nome'])) {
        $nome_mappa = gdrcd_query("SELECT nome FROM mappa_click WHERE id_click = " . (int)$_SESSION['mappa']);
        $nome_luogo = $nome_mappa['nome'];
    } else {
        $nome_luogo = $record['nome'];
    }
    ?>
    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $nome_luogo); ?></h2>
    </div>
    <div class="page_body">
        <?php
        if ($record_exists > 0 || $_SESSION['luogo'] == -1) {
            gdrcd_query($result, 'free');

            if (empty($record['nome']) === false) {
                $nome_luogo = $record['nome'];
            } elseif ($_SESSION['mappa'] >= 0) {
                $nome_luogo = $PARAMETERS['names']['maps_location'];
            } else {
                $nome_luogo = $PARAMETERS['names']['base_location'];
            }
            ?>
            <!--Immagine/descrizione -->
            <div class="info_image">
                <?php
                if (empty($record['immagine']) === false) {
                    $immagine_luogo = $record['immagine'];
                } else {
                    $immagine_luogo = 'standard_luogo.png';
                }
                ?>
                <img src="themes/<?php echo gdrcd_filter('out', $PARAMETERS['themes']['current_theme']); ?>/imgs/locations/<?php echo $immagine_luogo ?>"
                     class="immagine_luogo" alt="<?php echo gdrcd_filter('out', $record['descrizione']); ?>"
                     title="<?php echo gdrcd_filter('out', $record['descrizione']); ?>">
            </div>
            <?php if ((isset($record['stato']) === true) || (isset($record['descrizione']) === true)) {
                echo '<div class="box_stato_luogo"><marquee onmouseover="this.stop()" onmouseout="this.start()" direction="left" scrollamount="3" class="stato_luogo">&nbsp;' . $MESSAGE['interface']['maps']['Status'] . ': ' . gdrcd_filter('out', $record['stato']) . ' -  ' . gdrcd_filter('out', $record['descrizione']) . '</marquee></div>';
            } else {
                echo '<div class="box_stato_luogo">&nbsp;</div>';
            }
            if ($PARAMETERS['mode']['auto_meteo'] == 'ON') {
                /* Meteo */
                $ore = strftime("%H");
                $minuti = strftime("%M");
                $mese = strftime("%m");
                $giorno = strftime("%j");
                $caso = ((floor($giorno / 3)) % 2) + 1;

                /**    * Bug FIX: corretta l'assegnazione della $minima
                 * @author Blancks
                 */
                switch ($mese) {
                    case 1:
                        $minima = $PARAMETERS['date']['base_temperature'] + 0;
                        break;
                    case 2:
                        $minima = $PARAMETERS['date']['base_temperature'] + 4;
                        break;
                    case 3:
                        $minima = $PARAMETERS['date']['base_temperature'] + 8;
                        break;
                    case 4:
                        $minima = $PARAMETERS['date']['base_temperature'] + 14;
                        break;
                    case 5:
                        $minima = $PARAMETERS['date']['base_temperature'] + 20;
                        break;
                    case 6:
                        $minima = $PARAMETERS['date']['base_temperature'] + 28;
                        break;
                    case 7:
                        $minima = $PARAMETERS['date']['base_temperature'] + 30;
                        break;
                    case 8:
                        $minima = $PARAMETERS['date']['base_temperature'] + 27;
                        break;
                    case 9:
                        $minima = $PARAMETERS['date']['base_temperature'] + 21;
                        break;
                    case 10:
                        $minima = $PARAMETERS['date']['base_temperature'] + 15;
                        break;
                    case 11:
                        $minima = $PARAMETERS['date']['base_temperature'] + 5;
                        break;
                    case 12:
                        $minima = $PARAMETERS['date']['base_temperature'] + 1;
                        break;
                }
                /**
                 * Fine fix
                 */
                if ($ore < 14) {
                    $gradi = $minima + (floor($ore / 3) * $caso);
                } else {
                    $gradi = $minima + (4 * $caso) - ((floor($ore / 3) * $caso)) + (3 * $caso);
                }

                $caso = ($giorno + ($ore / 4)) % 12;
                switch ($caso) {
                    case 0:
                    case 6:
                    case 10:
                    case 11:
                    case 1:
                        $meteo_cond = $MESSAGE['interface']['meteo']['status'][0];
                        break;
                    case 7:
                    case 5:
                    case 2:
                        $meteo_cond = $MESSAGE['interface']['meteo']['status'][1];
                        break;
                    case 9:
                    case 3:
                        $meteo_cond = $MESSAGE['interface']['meteo']['status'][2];
                        break;
                    case 8:
                    case 4:
                        if ($minima < 4) {
                            $meteo_cond = $MESSAGE['interface']['meteo']['status'][4];
                        } else {
                            $meteo_cond = $MESSAGE['interface']['meteo']['status'][3];
                        }
                        break;
                }
                $meteo = $meteo_cond . " - " . $gradi . "&deg;C "; //.Tempo();
            } else {
                $meteo = gdrcd_filter('out', $record['meteo']);
            }
            if (empty($meteo) === false) { ?>
                <div class="page_title">
                    <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['meteo']['title']); ?></h2>
                </div>
                <div class="meteo_date">
                    <?php echo strftime('%d') . '/' . strftime('%m') . '/' . (strftime('%Y') + $PARAMETERS['date']['offset']); ?>
                </div>
                <div class="meteo_luna">
                    <?php if (defined('MOON') and MOON) {
                        $moon = gdrcd_lunar_phase();
                        echo '<img title="' . $moon['title'] . '"  src="themes/' . gdrcd_filter('out', $PARAMETERS['themes']['current_theme']) . '/imgs/luna/' . $moon['phase'] . '.png">';
                    } ?>
                </div>

                <div class="meteo">
                    <?php
                    echo $meteo; ?>
                </div>
            <?php } ?>
            <?php
        } else {
            echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['location_doesnt_exist']) . '</div>';
        } ?>
    </div>
    <!-- page_body -->
</div><!-- Pagina -->
