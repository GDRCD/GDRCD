<?php
/* HELP: Generazione automatica della classificazione PEGI, per scegliere le icone PEGI da visualizzare modificare il gile config.inc.php */
if (isset($PARAMETERS['pegi']) === true) {
    foreach ($PARAMETERS['pegi'] as $pegi_icon) {
        echo '<img src= "imgs/pegi/' . $pegi_icon['image_file'] . '" alt="' . gdrcd_filter('out',
                $pegi_icon['text']) . '" title="' . gdrcd_filter('out', $pegi_icon['text']) . '" class="pegi_img" />';
    }
}
?>

