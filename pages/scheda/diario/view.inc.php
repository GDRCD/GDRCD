<?php /*HELP: */
//Se non e' stato specificato il nome del pg
if (isset($_REQUEST['pg']) === false) {
    echo gdrcd_filter('out', $MESSAGE['error']['unknonw_character_sheet']);
    exit();
}
?>
<div class="panels_box">
    <div class="fake-table view-table">
        <?php
        $id_diario = gdrcd_filter('num', $_POST['id']);
        $row = gdrcd_query("SELECT data, titolo, testo, data_modifica, data_inserimento FROM diario WHERE id='{$id_diario}' LIMIT 1 ");

        ?>
        <div class="tr header">
            <div class="td"><?php echo gdrcd_filter('out', $row['titolo']); ?></div>
            <div class="td"><?php echo gdrcd_format_date($row['data']); ?></div>
        </div>
        <div class="tr">
            <div class="td" colspan="2"><?php echo gdrcd_filter('out', $row['testo']); ?></div>
        </div>
        <div class="tr footer">
            <div class="td">
                Data inserimento: <br> <?php echo gdrcd_format_datetime($row['data_inserimento']); ?>
            </div>
            <div class="td">
                Ultima modifica: <br> <?php if (isset($row['data_modifica'])) {
                    echo gdrcd_format_datetime($row['data_modifica']);
                }
                ?>
            </div>
        </div>

    </div>

    <!-- Link a piè di pagina -->
    <div class="link_back">
        <a href="main.php?page=scheda_diario&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>"><?php echo gdrcd_filter('out',
                $MESSAGE['interface']['sheet']['diary']['back']); ?></a>
    </div>
