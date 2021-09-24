<?php

if( ! empty($_POST['ids'])) {
    foreach($_POST['ids'] as $k => $v) {
        if(is_numeric($v)) {
            $_POST['ids'][$k] = (int) $v;
        } else {
            unset($_POST['ids'][$k]);
        }
    }
    $msgs = implode(',', $_POST['ids']);

    if(gdrcd_filter_in($_POST['type']) === 'destinatario_del') {
        $query = "UPDATE messaggi SET destinatario_del = 1 WHERE destinatario='".gdrcd_filter('in', $_SESSION['login'])."' AND id IN (".$msgs.")";
    } elseif(gdrcd_filter_in($_POST['type']) === 'mittente_del') {
        $query = "UPDATE messaggi SET mittente_del = 1 WHERE mittente='".gdrcd_filter('in', $_SESSION['login'])."' AND id IN (".$msgs.")";
    }
    gdrcd_query($query);
    if(gdrcd_query("", 'affected') > 0) { ?>
        <div class="warning">
            <?php echo gdrcd_filter('out', $PARAMETERS['names']['private_message']['plur'].$MESSAGE['interface']['messages']['all_erased']); ?>
        </div>
        <?php
    }
} else { ?>
    <div class="warning">
        <?php echo gdrcd_filter('out', $PARAMETERS['names']['private_message']['plur'].$MESSAGE['interface']['messages']['erased']); ?>
    </div>
    <?php
}