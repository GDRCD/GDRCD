<?php /*HELP: */
//Se non e' stato specificato il nome del pg
if (isset($_REQUEST['pg']) === false) {
    echo gdrcd_filter('out', $MESSAGE['error']['unknonw_character_sheet']);
    exit();
}

$id_diario = gdrcd_filter('num', $_POST['id']);
$result = gdrcd_query("SELECT data, titolo, testo,  visibile, id FROM diario WHERE id='{$id_diario}' LIMIT 1");

?>
<div class="form_container">
    <form class="form" action="main.php?page=scheda_diario&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>"
          method="post">


        <!-- TITOLO -->
        <div class="single_input">
            <div class="label"><?= gdrcd_filter('out', $MESSAGE['interface']['sheet']['diary']['title']); ?></div>
            <input type="text" name="titolo" class="form_input" value="<?= gdrcd_filter('out', $result['titolo']); ?>"
                   required/>
        </div>

        <!-- DATA -->
        <div class="single_input">
            <div class="label"><?= gdrcd_filter('out', $MESSAGE['interface']['sheet']['diary']['date']); ?></div>
            <input required type="date" name="data" class="form_input"
                   value="<?= gdrcd_filter('out', $result['data']); ?>"/>
        </div>

        <!-- VISIBILE -->
        <div class="single_input">
            <div class="label"><?= gdrcd_filter('out', $MESSAGE['interface']['sheet']['diary']['visible']); ?></div>
            <select name="visibile" required>
                <?php
                $selection = array('si', 'no');
                foreach ($selection as $sel_voice) {
                    $selected = (gdrcd_filter('out', $result['visibile']) == $sel_voice) ? "selected" : "";
                    echo "<option {$sel_voice} value='{$sel_voice}'>{$sel_voice}</option>";
                }
                ?>
            </select>
        </div>

        <!-- TESTO -->
        <div class="single_input">
            <div class="label"><?= gdrcd_filter('out', $MESSAGE['interface']['sheet']['diary']['text']); ?></div>
            <textarea name="testo"><?= gdrcd_filter('out', $result['testo']); ?></textarea>
        </div>

        <!-- SUBMIT -->
        <div class="single_input">
            <input type="submit" name="submit" value="Salva"/>
            <input hidden name="op" value="save_edit">
            <input hidden name="id" value="<?= $id_diario; ?>">
        </div>
    </form>
</div>

<!-- Link a piè di pagina -->
<div class="link_back">
    <a href="main.php?page=scheda_diario&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>"><?php echo gdrcd_filter('out',
            $MESSAGE['interface']['sheet']['diary']['back']); ?></a>
</div>
