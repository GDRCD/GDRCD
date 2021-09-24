<?php
$padre = gdrcd_filter('num', $_REQUEST['what']);
$araldo = gdrcd_filter('num', $_REQUEST['where']);

$quote = gdrcd_filter('num', $_REQUEST['quote']);

$join = '';
$cond = '';
if($padre != -1) {
    //Se sto inserendo in un thread, verifico che esista
    $join = ' INNER JOIN messaggioaraldo AS MA ON araldo.id_araldo=MA.id_araldo ';
    $cond = ' AND id_messaggio='.$padre." AND id_messaggio_padre=-1";
}

$araldoData = gdrcd_query("SELECT count(*) AS N FROM araldo".$join." WHERE araldo.id_araldo = ".$araldo.$cond);

if($araldoData['N'] > 0) {
    ?>
    <div class="panels_box">
        <div class="form_gioco">
            <form action="main.php?page=forum" method="post">
                <?php
                if($padre == -1) {
                    /*Se e' il primo post di un topic serve il titolo*/
                    ?>
                    <div class="form_label">
                        <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['insert']['title']); ?>
                    </div>
                    <div class="form_field">
                        <input name="titolo" />
                    </div>
                    <?php
                }
                ?>
                <div class="form_label">
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['insert']['message']); ?>
                </div>
                <div class="form_field">
    <textarea name="messaggio">
<?php
if($quote) {
    $query = "SELECT messaggio, autore FROM messaggioaraldo WHERE id_messaggio=".$quote;
    $result = gdrcd_query($query);
    echo gdrcd_filter('out', "[quote=".$result['autore']."]".$result['messaggio']."[/quote]");
}
?>
</textarea>
                </div>
                <div class="form_info">
                    <?php echo gdrcd_filter('out', $MESSAGE['interface']['help']['bbcode']); ?>
                </div>
                <div class="form_submit">
                    <input type="hidden" name="op" value="insert" />
                    <input type="hidden" name="araldo" value="<?php echo $araldo; ?>" />
                    <input type="hidden" name="padre" value="<?php echo $padre; ?>" />
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
    <?php
} else {
    echo '<div class="warning">', $MESSAGE['interface']['administration']['forums']['not_exists'], '</div>';
}