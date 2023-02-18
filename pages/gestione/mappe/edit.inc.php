<?php
    /*HELP: */
    /*Controllo permessi utente*/
    if(!gdrcd_controllo_permessi($PARAMETERS['administration']['maps']['access_level'])) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
        die();
    }

    /*Carico il record da modificare*/
    $results = gdrcd_query("SELECT * FROM mappa_click WHERE id_click=".gdrcd_filter('num', $_POST['id_click'])." LIMIT 1 ", 'result');

    if(gdrcd_query($results, 'num_rows') == 0) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['unknown']).'</div>';
    } else {
        // Carico i dati del record
        $record = gdrcd_query($results, 'fetch');
        gdrcd_query($results, 'free');
        ?>
        <!-- Form di modifica -->
        <div id="GestioneMappeEdit" class="form_container">
            <form action="main.php?page=gestione/mappe" method="post" class="form">
                <div class='single_input'>
                    <div class="label"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['name']); ?></div>
                    <input type="text" name="nome" value="<?=gdrcd_filter('out', $record['nome']); ?>" class="form_input" required />
                </div>

                <div class='single_input'>
                    <div class="label"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['is_mobile']); ?></div>
                    <input type="checkbox" name="mobile" <?php if($record['mobile'] == 1) { ?>checked="checked"<?php } ?> value="is_mobile" />
                    <div class="subtitle"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['is_mobile_info']); ?></div>
                </div>

                <div class='single_input'>
                    <div class="label"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['is_main']); ?></div>
                    <input type="checkbox" name="principale" <?php if($record['principale'] == 1) { ?>checked="checked"<?php } ?> value="is_main" />
                    <div class="subtitle"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['is_main_info']); ?></div>
                </div>


                <div class='single_input'>
                    <div class="label"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['position']); ?></div>
                    <input type="number" name="posizione" value="<?=(0 + gdrcd_filter('num', $record['posizione'])); ?>" />
                    <div class="subtitle"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['position_info']); ?></div>
                </div>

                <div class='single_input'>
                    <div class="label"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['image']); ?></div>
                    <input name="immagine" value="<?=gdrcd_filter('out', $record['immagine']); ?>" />
                </div>

                <div class='single_input'>
                    <div class="label"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['width']); ?></div>
                    <input name="larghezza" value="<?=gdrcd_filter('out', $record['larghezza']); ?>" />
                    <div class="subtitle"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['width_info']); ?></div>
                </div>

                <div class='single_input'>
                    <div class="label"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['height']); ?></div>
                    <input name="altezza" value="<?=gdrcd_filter('out', $record['altezza']); ?>" />
                    <div class="subtitle"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['height_info']); ?></div>
                </div>

                <!-- bottoni -->
                <div class='single_input'>
                    <input type="hidden" name="id_click" value="<?=gdrcd_filter('out', $record['id_click']); ?>">
                    <input type="submit" value="<?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['submit']['edit']); ?>" />
                    <input type="hidden" name="op" value="save">
                </div>
            </form>
        </div>
    <?php } ?>

    <!-- Link di ritorno alla visualizzazione di base -->
    <div class="link_back">
        <a href="main.php?page=gestione/mappe">
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['link']['back']); ?>
        </a>
    </div>
