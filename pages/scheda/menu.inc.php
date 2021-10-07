<?php
/*Visualizza il link modifica se l'utente visualizza la propria scheda o se è almeno un capogilda*/
if($_REQUEST['pg'] == $_SESSION['login'] || $_SESSION['permessi'] >= GUILDMODERATOR) { ?>
    <a href="main.php?page=scheda_modifica&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['update']); ?>
    </a>
<?php } ?>
    <a href="main.php?page=scheda_trans&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['transictions']); ?>
    </a>
    <a href="main.php?page=scheda_px&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['experience']); ?>
    </a>
    <a href="main.php?page=scheda_oggetti&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['inventory']); ?>
    </a>
    <a href="main.php?page=scheda_equip&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['equipment']); ?>
    </a>
    <?php
        if($PARAMETERS['settings']['diary']==="ON"){?>
            <a href="main.php?page=scheda_diario&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>">
                <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['diary']); ?>
            </a>
    <?php    }
    ?>
<?php /*Visualizza il link se l'utente visualizza la propria scheda o se è almeno un master*/
if (($_SESSION['permessi'] >= ROLE_PERM || $_REQUEST['pg'] == $_SESSION['login']) && REG_ROLE) { ?>
    <a href="main.php?page=scheda_roles&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>">
        Giocate registrate
    </a>
<?php
}
/*Visualizza il link modifica se l'utente visualizza la propria scheda o se è almeno un capogilda*/
if($_SESSION['permessi'] >= MODERATOR) { ?>
    <a href="main.php?page=scheda_log&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['log']); ?>
    </a>
    <a href="main.php?page=scheda_gst&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['gst']); ?>
    </a>
<?php }