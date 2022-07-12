<?php


switch ($_POST['op']) {


    case 'save_new':
        $title = gdrcd_filter('int', $_POST['title']);
        $start = gdrcd_format_datetime_standard($_POST['start']);
        $end = ($_POST['end'])? "'".gdrcd_format_datetime_standard($_POST['end'])."'" : 'NULL';
        $titolo = gdrcd_filter('in', $_POST['titolo']);
        $descrizione = gdrcd_filter('in', $_POST['descrizione']);
        $colore = gdrcd_filter('int', $_POST['colore']);
        if(($start<$end)||(empty($end))){
            gdrcd_query("INSERT INTO eventi (title, start, end, titolo, descrizione, colore)  VALUES
            ('{$title}', '{$start}',{$end} ,'{$titolo}','{$descrizione}', '{$colore}') ");
        }else {
            echo gdrcd_filter('out', $MESSAGE['error']['error_date']);
        }

        break;
    case 'save_edit':
        $id = gdrcd_filter('int', $_POST['id']);
        $title = gdrcd_filter('int', $_POST['title']);
        $start = gdrcd_format_datetime_standard($_POST['start']);
        $end = ($_POST['end'])? "'".gdrcd_format_datetime_standard($_POST['end'])."'" : 'NULL';
        $titolo = gdrcd_filter('in', $_POST['titolo']);
        $descrizione = gdrcd_filter('in', $_POST['descrizione']);
        $colore = gdrcd_filter('int', $_POST['colore']);
        if(($start<$end)||(empty($end))){
            gdrcd_query("UPDATE  eventi 
                SET title = '{$title}',start='{$start}',  end='{$end}' ,titolo='{$titolo}',descrizione='{$descrizione}', colore='{$colore}'
                WHERE id='{$id}' LIMIT 1 ");
        }else {
            echo gdrcd_filter('out', $MESSAGE['error']['error_date']);
        }
        break;
    case 'delete':
        $id = gdrcd_filter('int', $_POST['id']);
        gdrcd_query("DELETE FROM eventi WHERE id='{$id}'");
        break;

}
?>
<!-- Link a piè di pagina -->
<div class="link_back">
    <a href="<?php echo (CALENDAR_POPUP)?'popup' : 'main';?>.php?page=calendario">Indietro</a>
</div>
