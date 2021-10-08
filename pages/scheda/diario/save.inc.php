<?php
if(isset($_REQUEST['pg']) === false) {
    echo gdrcd_filter('out', $MESSAGE['error']['unknonw_character_sheet']);
    exit();
}
switch ($_POST['op']){
    case 'save_new':
        $query="INSERT INTO diario (titolo,data, data_inserimento, visibile, testo, personaggio )  VALUES
        ('" . gdrcd_filter('in',$_POST['titolo']) . "', '" . gdrcd_filter('in',$_POST['data']) . "',NOW(),
        '" . gdrcd_filter('in', $_POST['visibile']) . "' ,'" . gdrcd_filter('in',  $_POST['testo']) . "',
        '". $_REQUEST['pg'] ."') ";
        gdrcd_query($query);
        break;
    case 'save_edit':
    $query="UPDATE  diario SET titolo = '" . gdrcd_filter('in',$_POST['titolo']) . "',
      data='" . gdrcd_filter('in',$_POST['data']) . "',  visibile='" . gdrcd_filter('in', $_POST['visibile']) . "' ,
      testo='" . gdrcd_filter('in',  $_POST['testo']) . "',  data_modifica=NOW()WHERE id='" . gdrcd_filter('in', $_POST['id']) . "' ";
      gdrcd_query($query);
      break;
    case 'delete':
          $query="DELETE FROM diario WHERE id=" . gdrcd_filter('in', $_POST['id']) . "";
          gdrcd_query($query);
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
