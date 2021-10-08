<?php
if (isset($_REQUEST['pg']) === false) {
    echo gdrcd_filter('out', $MESSAGE['error']['unknonw_character_sheet']);
    exit();
}
switch ($_POST['op']) {

    # Creazione Diario
    case 'save_new':
        $titolo = gdrcd_filter('in', $_POST['titolo']);
        $data = gdrcd_filter('in', $_POST['data']);
        $inv = gdrcd_filter('in', $_POST['visibile']);
        $testo = gdrcd_filter('in', $_POST['testo']);
        $pg = gdrcd_filter('in', $_POST['pg']);

        gdrcd_query("INSERT INTO diario (titolo,data, data_inserimento, visibile, testo, personaggio )  VALUES
        ('{$titolo}', '{$data}',NOW(),'{$inv}' ,'{$testo}','{$pg}') ");
        break;

    # Modifica Diario
    case 'save_edit':
        $id = gdrcd_filter('in', $_POST['id']);
        $titolo = gdrcd_filter('in', $_POST['titolo']);
        $data = gdrcd_filter('in', $_POST['data']);
        $inv = gdrcd_filter('in', $_POST['visibile']);
        $testo = gdrcd_filter('in', $_POST['testo']);
        $pg = gdrcd_filter('in', $_POST['pg']);

        gdrcd_query("UPDATE  diario 
                SET titolo = '{$titolo}',data='{$data}',  visibile='{$inv}' ,testo='{$testo}',  data_modifica=NOW()
                WHERE id='{$id}' LIMIT 1 ");
        break;

    # Delete diario
    case 'delete':
        $id = gdrcd_filter('in', $_POST['id']);
        gdrcd_query("DELETE FROM diario WHERE id='{$id}'");
        break;

    default:
        die('Operazione non riconosciuta.');
}

echo '<div class="warning">' . gdrcd_filter('out', $MESSAGE['warning']['done']) . '</div>';

?>
<!-- Link a piè di pagina -->
<div class="link_back">
    <a href="main.php?page=scheda_diario&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>"><?php echo gdrcd_filter('out',
            $MESSAGE['interface']['sheet']['diary']['back']); ?></a>
</div>
