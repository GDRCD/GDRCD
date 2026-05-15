<?php

 if($_SESSION['permessi'] < MODERATOR) {
    echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
    return;
} 
$id = gdrcd_filter('num', $_POST['id']);
$loaded_record = gdrcd_stmt_one("SELECT * FROM ambientazione WHERE capitolo=?  LIMIT 1", [$id]);
?>
<div class="panels_box">
    <form action="main.php?page=gestione_ambientazione" method="post" class="form_gestione">
        <div class='form_label'>
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['plot']['art']); ?>
        </div>
        <div class='form_field'>
            <input name="articolo" value="<?php echo 0 + $loaded_record['capitolo']; ?>" />
        </div>
        <div class='form_label'>
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['plot']['title']); ?>
        </div>
        <div class='form_field'>
            <input name="titolo" value="<?php echo gdrcd_filter('out', $loaded_record['titolo']); ?>" />
        </div>
        <div class='form_label'>
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['plot']['infos']); ?>
        </div>
        <div class='form_field'>
            <textarea name="testo"><?php echo gdrcd_filter('out', $loaded_record['testo']); ?></textarea>
        </div>
        <div class="form_info">
            <?php echo gdrcd_filter('out', $MESSAGE['interface']['help']['bbcode']); ?>
        </div>
        <!-- bottoni -->
        <div class='form_submit'>
                <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['modify']); ?>" />
                <input type="hidden" name="art" value="<?php echo 0 + $loaded_record['capitolo']; ?>">
                <input type="hidden" name="op" value="doedit">
        </div>
    </form>
</div>
<!-- Link di ritorno alla visualizzazione di base -->
<div class="link_back">
    <a href="main.php?page=gestione_ambientazione">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['plot']['link']['back']); ?>
    </a>
</div>