<div class="pagina_info_location">
    <?php /* HELP: Il box delle informazioni carica l'immagine del luogo corrente, lo stato e la descrizione. Genera, inoltre, il meteo */
    $class =Meteo::getInstance();
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
            }?>
            <div class="page_title">
                <h2><?php echo gdrcd_filter('out', $MESSAGE['interface']['meteo']['title']); ?></h2>
            </div>
            <div class="meteo_date">
                <?php echo strftime('%d') . '/' . strftime('%m') . '/' . (strftime('%Y') + $PARAMETERS['date']['offset']); ?>
            </div>
        <?php


           $moon= (Functions::get_constant('WEATHER_MOON')==1)  ? $class->lunar_phase() : 0;
            if (Functions::get_constant('WEATHER_MOON')) {
                echo '<img title="' . $moon['title'] . '"  src="themes/' . gdrcd_filter('out', $PARAMETERS['themes']['current_theme']) . '/imgs/luna/' . $moon['phase'] . '.png">';
            }


            switch (Functions::get_constant('WEATHER_TYPE')){
                case 1: //stagioni
                    $data1=date("Y-m-d H:i");
                    $data2=Functions::get_constant('WEATHER_LAST_DATE');
                    if(($class->dateDifference($data2,$data1 ,  '%h') >4)|| (Functions::get_constant('WEATHER_LAST_DATE')=="")) {
                        $data = date("Y-m-d");
                        $stagione = DB::query("SELECT * FROM meteo_stagioni WHERE data_inizio <'{$data}' AND DATA_fine > '{$data}'", 'query');
                        $condizioni = $class->getAllState($stagione['id']);
                        $rand = rand(0, 100);
                        while ($row = DB::query($condizioni, 'fetch')) {
                            if (($rand >= $row['percentuale'])) {
                                $condizione = $row['condizione'];
                            }
                        }
                        $condizione = $class->getOneCondition($condizione);
                        $img = "<img src='" . $condizione['img'] . "' title='" . $condizione['nome'] . " ' >";
                        $vento = explode(",", $condizione['vento']);
                        shuffle($vento);
                        $wind = (Functions::get_constant('WEATHER_WIND') == 1) ? " - " . $vento[0] : '';
                        $temp = rand($stagione['minima'], $stagione['massima']);
                        $temp = Filters::int($temp) . "&deg;C";
                        $meteo= Filters::in($img . " " .$temp . " " .$wind);
                        $class->saveWeather($meteo);
                    }

                        break;
                    default: //webapi
                        $api = $class->getWebApiWeather();
                        $wind = (Functions::get_constant('WEATHER_WIND') == 1) ? " - " . $class->wind($api['wind']['speed']) : '';
                        $url = (Functions::get_constant('WEATHER_WEBAPI_ICON') == 0) ? "http://openweathermap.org/img/wn/" : "imgs/meteo/";
                        $estensione = (Functions::get_constant('WEATHER_WEBAPI_ICON') == 0) ? "png" : Functions::get_constant('WEATHER_WEBAPI_FORMAT');
                        $img = "<img src='" . $url . "" . $api['weather'][0]['icon'] . "." . $estensione . "' title='" . $api['weather'][0]['description'] . " ' >";
                        $temp = Filters::int($api['main']['temp']) . "&deg;C";
                        echo $img . " " .$temp . " " .$wind;



                    break;
            }


            echo Functions::get_constant('WEATHER_LAST');

 } else {
            echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['location_doesnt_exist']) . '</div>';
        } ?>
    </div>
    <!-- page_body -->
</div><!-- Pagina -->
