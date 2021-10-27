<?php
require 'header.inc.php';
gdrcd_controllo_sessione();

echo '<div class="popup">';

if ( ! empty($_GET['page'])) {
    gdrcd_load_modules(
        gdrcd_filter(
            'include',
            $_GET['page']
        )
    );
} else {
    echo $MESSAGE['interface']['layout_not_found'];
}

echo '</div>';

require 'footer.inc.php';
?>
