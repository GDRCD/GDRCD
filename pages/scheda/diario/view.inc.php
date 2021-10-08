<?php /*HELP: */
//Se non e' stato specificato il nome del pg
if(isset($_REQUEST['pg']) === false) {
    echo gdrcd_filter('out', $MESSAGE['error']['unknonw_character_sheet']);
    exit();
}
?>
<div class="panels_box">
    <table>


    <?php
$query="SELECT data, titolo, testo, data_modifica, data_inserimento FROM diario WHERE id='".gdrcd_filter('url',
        $_POST['id'])."'  ";

$row = gdrcd_query($query, 'query');
?>
        <tr><td class="casella_elemento"><?php echo gdrcd_filter('out', $row['titolo']); ?></td>
            <td class="casella_elemento"><?php echo gdrcd_format_date( $row['data']); ?></td></tr>
    <tr><td class="casella_elemento" colspan="2"><?php echo gdrcd_filter('out', $row['testo']); ?></td></tr>
    <tr><td ><div class="link_back">
Data inserimento: <?php echo gdrcd_format_datetime( $row['data_inserimento']); ?>
        </div></td>
       <td> <div class="link_back">
            Ultima modifica: <?php if(isset($row['data_modifica'])){
                echo gdrcd_format_datetime( $row['data_modifica']); }
               ?>
        </div></td></tr>

    </table>

        <!-- Link a piè di pagina -->
        <div class="link_back">
            <a href="main.php?page=scheda_diario&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>"><?php echo gdrcd_filter('out',
                    $MESSAGE['interface']['sheet']['diary']['back']); ?></a>
        </div>
