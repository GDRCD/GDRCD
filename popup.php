<?php
require 'header.inc.php'; 
gdrcd_controllo_sessione();

echo '<div class="popup">';

if (!empty($_GET['page']))
{
    gdrcd_load_modules(
        gdrcd_filter(
            'include', 
            __DIR__ 
            . DIRECTORY_SEPARATOR 
            . 'pages' 
            . DIRECTORY_SEPARATOR
            . $_GET['page'] 
            . '.inc.php'
        )
    );
} 
else
{
    echo $MESSAGE['interface']['layout_not_found'];
}

echo '</div>';

require 'footer.inc.php';
?>
