<?php
$row = gdrcd_query("SELECT titolo, messaggio, id_messaggio_padre FROM messaggioaraldo WHERE id_messaggio=".gdrcd_filter('num', $_REQUEST['what'])."");
?>
<div class="panels_box">
    <div class="form_gioco">
        <form action="main.php?page=forum" method="post">
            <?php
            if($row['id_messaggio_padre'] == -1) {
                /*Se è il primo di un topic serve un titolo*/
                ?>
                <div class="form_label">
                    <?php echo $MESSAGE['interface']['forums']['insert']['title']; ?>
                </div>
                <div class="form_field">
                    <input name="titolo" value="<?php echo gdrcd_filter('out', $row['titolo']); ?>" />
                </div>
                <?php
            }//if
            ?>
            <div class="form_label">
                <?php echo $MESSAGE['interface']['forums']['insert']['message']; ?>
            </div>
            <div class="form_field">
                <textarea name="messaggio" /><?php echo $row['messaggio']; ?></textarea>
            </div>
            <div class="form_info">
                <?php echo gdrcd_filter('out', $MESSAGE['interface']['help']['bbcode']); ?>
            </div>
            <div class="form_submit">
                <input type="hidden" name="op" value="edit" />
                <input type="hidden" name="araldo" value="<?php echo gdrcd_filter('num', $_REQUEST['where']); ?>" />
                <input type="hidden" name="messaggio_padre" value="<?php echo gdrcd_filter('num', $row['id_messaggio_padre']); ?>" />
                <input type="hidden" name="id_messaggio" value="<?php echo gdrcd_filter('num', $_REQUEST['what']); ?>" />
                <input type="submit" name="dummy" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>" />
            </div>
        </form>
    </div>
</div>
<div class="link_back">
    <a href="main.php?page=forum">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['link']['topic']); ?>
    </a>
</div>
