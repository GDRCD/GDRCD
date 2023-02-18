<?php
    /*HELP: */
    /*Controllo permessi utente*/
    if(!gdrcd_controllo_permessi($PARAMETERS['administration']['maps']['access_level'])) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
        die();
    }

    ?>
    <!-- Form di modifica -->
    <div id="GestioneMappeCreate" class="form_container">
        <form action="main.php?page=gestione/mappe" method="post" class="form">
            <div class='single_input'>
                <div class="label"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['name']); ?></div>
                <input type="text" name="nome" value="<?=gdrcd_filter('out', $_POST['nome']); ?>" class="form_input" required />
            </div>

            <div class='single_input'>
                <div class="label"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['is_mobile']); ?></div>
                <input type="checkbox" name="mobile" <?php if($_POST['mobile'] == 1) { ?>checked="checked"<?php } ?> value="is_mobile" />
                <div class="subtitle"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['is_mobile_info']); ?></div>
            </div>

            <div class='single_input'>
                <div class="label"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['is_main']); ?></div>
                <input type="checkbox" name="principale" <?php if($_POST['principale'] == 1) { ?>checked="checked"<?php } ?> value="is_main" />
                <div class="subtitle"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['is_main_info']); ?></div>
            </div>

            <div class='single_input'>
                <div class="label"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['position']); ?></div>
                <input type="number" name="posizione" value="<?=(0 + gdrcd_filter('num', $_POST['posizione'])); ?>" />
                <div class="subtitle"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['position_info']); ?></div>
            </div>

            <div class='single_input'>
                <div class="label"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['image']); ?></div>
                <input name="immagine" value="<?=gdrcd_filter('out', $_POST['immagine']); ?>" />
            </div>

            <div class='single_input'>
                <div class="label"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['width']); ?></div>
                <input name="larghezza" value="<?=gdrcd_filter('out', $_POST['larghezza']); ?>" />
                <div class="subtitle"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['width_info']); ?></div>
            </div>

            <div class='single_input'>
                <div class="label"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['height']); ?></div>
                <input name="altezza" value="<?=gdrcd_filter('out', $_POST['altezza']); ?>" />
                <div class="subtitle"><?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['height_info']); ?></div>
            </div>

            <!-- bottoni -->
            <div class='single_input'>
                <input type="submit" value="<?=gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['submit']['create']); ?>" />
                <input type="hidden" name="op" value="save">
            </div>
        </form>
    </div>

    <!-- Link di ritorno alla visualizzazione di base -->
    <div class="link_back">
        <a href="main.php?page=gestione/mappe">
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['maps']['link']['back']); ?>
        </a>
    </div>
