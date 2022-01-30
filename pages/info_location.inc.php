<div class="pagina_info_location">
    <?php /* HELP: Il box delle informazioni carica l'immagine del luogo corrente, lo stato e la descrizione. Genera, inoltre, il meteo */
    $class =Meteo::getInstance();
    $perm = Permissions::getInstance();

    $id= Functions::getPgId($_SESSION['login']);
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
        <?php echo Template::getInstance()->startTemplate()->render('meteo/meteo',Meteo::getInstance()->createMeteoData());



          if($perm->permission('MANAGE_WEATHER') && ($_REQUEST['dir'] )){?>
              <p><a href="javascript:modalWindow('meteo', 'Modifica Meteo Chat', 'popup.php?page=meteo_chat&dir=<?=$_REQUEST['dir']?>')">Modifica Meteo</a></p>
         <?php
          }


        } else {
            echo '<div class="error">' . gdrcd_filter('out', $MESSAGE['error']['location_doesnt_exist']) . '</div>';
        } ?>
    </div>
    <!-- page_body -->
</div><!-- Pagina -->
