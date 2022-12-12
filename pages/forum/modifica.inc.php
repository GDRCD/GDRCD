<?php
// Ottengo i dati del messaggio e i campi di controllo del permesso
$row = gdrcd_query("SELECT messaggioaraldo.id_messaggio, messaggioaraldo.titolo, messaggioaraldo.messaggio, messaggioaraldo.id_messaggio_padre, araldo.tipo, araldo.proprietari FROM messaggioaraldo LEFT JOIN araldo ON messaggioaraldo.id_araldo = araldo.id_araldo WHERE messaggioaraldo.id_messaggio=".gdrcd_filter('num', $_REQUEST['what']));

// Controllo esistenza del messaggio
if(!empty($row)) {

    /*Restrizione di accesso i forum admin e master*/
    if(!gdrcd_controllo_permessi_forum($row['tipo'],$row['proprietari'])){
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
    } else { ?>
        <div class="panels_box">
            <div class="form_gioco">
                <form action="main.php?page=forum&op=modifica&what=<?php echo $row['id_messaggio']; ?>" method="post">
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
        <?php
    }
} //!empty
else {
    echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['interface']['forums']['warning']['topic_not_exists']).'</div>';
}
?>

<div class="link_back">
    <a href="main.php?page=forum">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['forums']['link']['topic']); ?>
    </a>
</div>
