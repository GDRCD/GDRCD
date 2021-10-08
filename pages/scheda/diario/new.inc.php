<?php /*HELP: */
//Se non e' stato specificato il nome del pg
if (isset($_REQUEST['pg']) === false) {
    echo gdrcd_filter('out', $MESSAGE['error']['unknonw_character_sheet']);
    exit();
}
?>

<div class="form_container">
    <form class="form"
          action="main.php?page=scheda_diario&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>" method="post">

        <!-- TITOLO -->
        <div class="single_input">
            <div class="label"><?= gdrcd_filter('out', $MESSAGE['interface']['sheet']['diary']['title']); ?></div>
            <input type="text" name="titolo" class="form_input" required/>
        </div>

        <!-- DATA -->
        <div class="single_input">
            <div class="label"><?= gdrcd_filter('out', $MESSAGE['interface']['sheet']['diary']['date']); ?></div>
            <input required type="date" name="data" class="form_input" value="<?= date('Y-m-d'); ?>"/>
        </div>

        <!-- VISIBILE -->
        <div class="single_input">
            <div class="label"><?= gdrcd_filter('out', $MESSAGE['interface']['sheet']['diary']['visible']); ?></div>
            <select name="visibile" required>
                <option value="si">si</option>
                <option value="no">no</option>
            </select>
        </div>

        <!-- TESTO -->
        <div class="single_input">
            <div class="label"><?= gdrcd_filter('out', $MESSAGE['interface']['sheet']['diary']['text']); ?></div>
            <textarea name="testo"></textarea>
        </div>

        <!-- SUBMIT -->
        <div class="single_input">
            <input type="submit" name="submit" value="Salva"/>
            <input type="hidden" name="op" value="save_new">
            <input type="hidden" name="pg" value="<?= gdrcd_filter('out', $_REQUEST['pg']); ?>">
        </div>
</div>
<!-- Link a piè di pagina -->
<div class="link_back">
    <a href="main.php?page=scheda_diario&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>"><?php echo gdrcd_filter('out',
            $MESSAGE['interface']['sheet']['diary']['back']); ?></a>
</div>
