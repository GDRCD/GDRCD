<?php
if(isset($_REQUEST['pg']) === false) {
    echo gdrcd_filter('out', $MESSAGE['error']['unknonw_character_sheet']);
    exit();
}
switch ($_POST['op']){
    case 'save_new':
        echo "insert <br>";
        $query="INSERT INTO diario (titolo,data, data_inserimento, visibile, testo, personaggio )  VALUES 
        ('" . gdrcd_filter('in',$_POST['titolo']) . "', '" . gdrcd_filter('in',$_POST['data']) . "',NOW(), 
        '" . gdrcd_filter('in', $_POST['visibile']) . "' ,'" . gdrcd_filter('in',  $_POST['testo']) . "', 
        '". $_REQUEST['pg'] ."') ";
        gdrcd_query($query);
        echo $query;
        break;
    case 'save_edit':
        echo "update";
        break;
    case 'delete':
        echo 'delete';
        break;
    default:

        break;
}
    echo '<div class="warning">' . gdrcd_filter('out', $MESSAGE['warning']['done']) . '</div>';

?>
<!-- Link a piè di pagina -->
<div class="link_back">
    <a href="main.php?page=scheda_diario&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>"><?php echo gdrcd_filter('out',
            $MESSAGE['interface']['sheet']['diary']['back']); ?></a>
</div>
