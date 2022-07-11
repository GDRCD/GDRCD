<?php


switch ($_POST['op']) {


    case 'save_new':
        $title = gdrcd_filter('in', $_POST['title']);
        $start = gdrcd_filter('in', $_POST['start']);
        $end = gdrcd_filter('in', $_POST['end']);
        $titolo = gdrcd_filter('in', $_POST['titolo']);
        $descrizione = gdrcd_filter('in', $_POST['descrizione']);
        $colore = gdrcd_filter('in', $_POST['colore']);

        gdrcd_query("INSERT INTO eventi (title, start, end, titolo, descrizione, colore)  VALUES
        ('{$title}', '{$start}','{$end}' ,'{$titolo}','{$descrizione}', '{$colore}') ");
        break;
}
?>
<!-- Link a piè di pagina -->
<div class="link_back">
    <a href="<?php echo (CALENDAR_POPUP)?'popup' : 'main';?>.php?page=calendario">Indietro</a>
</div>
