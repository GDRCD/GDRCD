<?php
if( ! empty($_POST['ids'])) {
    foreach($_POST['ids'] as $k => $v) {
        if(is_numeric($v)) {
            $POST['ids'][$k] = (int) $v;
        } else {
            unset($_POST['ids'][$k]);
        }
    }
    $msgs = implode(',', $_POST['ids']);
    $query = "DELETE FROM messaggi WHERE destinatario='".gdrcd_filter('in', $_SESSION['login'])."' AND id IN (".$msgs.")";
    gdrcd_query($query);
    if(gdrcd_query("", 'affected') > 0) { ?>
        <div class="warning">
            <?php echo gdrcd_filter('out', $PARAMETERS['names']['private_message']['plur'].$MESSAGE['interface']['messages']['all_erased']); ?>
        </div>
        <div class="link_back">
            <a href="main.php?page=messages_center&offset=0"><?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['go_back']); ?></a>
        </div>
        <?php
    }
} else { ?>
    <div class="warning">
        <?php echo gdrcd_filter('out', $PARAMETERS['names']['private_message']['plur'].$MESSAGE['interface']['messages']['erased']); ?>
    </div>
    <div class="link_back">
        <a href="main.php?page=messages_center&offset=0"><?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['go_back']); ?></a>
    </div>
    <?php
}