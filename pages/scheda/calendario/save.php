<?php


switch ($_POST['op']) {


    case 'save_new':
        $title = gdrcd_filter('int', $_POST['title']);
        $start = gdrcd_filter('in', $_POST['start']);
        $end = gdrcd_filter('in', $_POST['end']);
        $titolo = gdrcd_filter('in', $_POST['titolo']);
        $descrizione = gdrcd_filter('in', $_POST['descrizione']);
        $colore = gdrcd_filter('int', $_POST['colore']);
        $personaggio=gdrcd_filter('in', $_POST['personaggio']);

        gdrcd_query("INSERT INTO eventi_personaggio (title, start, end, titolo, descrizione, colore, personaggio)  VALUES
        ('{$title}', '{$start}','{$end}' ,'{$titolo}','{$descrizione}', '{$colore}', '{$personaggio}') ");
        break;
    case 'save_edit':
        $id = gdrcd_filter('int', $_POST['id']);
        $title = gdrcd_filter('int', $_POST['title']);
        $start = gdrcd_filter('in', $_POST['start']);
        $end = gdrcd_filter('in', $_POST['end']);
        $titolo = gdrcd_filter('in', $_POST['titolo']);
        $descrizione = gdrcd_filter('in', $_POST['descrizione']);
        $colore = gdrcd_filter('int', $_POST['colore']);
        $personaggio=gdrcd_filter('in', $_POST['pg']);

        gdrcd_query("UPDATE  eventi_personaggio 
                SET title = '{$title}',start='{$start}',  end='{$end}' ,titolo='{$titolo}',descrizione='{$descrizione}', colore='{$colore}' 
                WHERE id='{$id}' LIMIT 1 ");
        break;
    case 'delete':
        $id = gdrcd_filter('int', $_POST['id']);
        gdrcd_query("DELETE FROM eventi_personaggio WHERE id='{$id}'");
        break;

}
?>
<!-- Link a piè di pagina -->
<div class="link_back">
    <a href="main.php?page=scheda_calendario&pg=<?=$personaggio?>">Indietro</a>
</div>
