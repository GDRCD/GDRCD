<?php
require 'header.inc.php';
gdrcd_controllo_sessione();

?>
<div class="popup">


    <?php if (!empty($_GET['page'])) {
        Router::loadFramePart($_GET['page']);
    } else {
        echo $MESSAGE['interface']['layout_not_found'];
    } ?>

</div>

