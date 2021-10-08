<?php

# Modifica
if (($_REQUEST['pg'] == $_SESSION['login']) || ($_SESSION['permessi'] >= GUILDMODERATOR)) { ?>
    <a href="main.php?page=scheda_modifica&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['update']); ?>
    </a>
<?php } ?>

    <!-- TRASFERIMENTI -->
    <a href="main.php?page=scheda_trans&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['transictions']); ?>
    </a>

    <!-- ESPERIENZA -->
    <a href="main.php?page=scheda_px&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['experience']); ?>
    </a>

    <!-- OGGETTI -->
    <a href="main.php?page=scheda_oggetti&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['inventory']); ?>
    </a>

    <!-- INVENTARIO -->
    <a href="main.php?page=scheda_equip&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['equipment']); ?>
    </a>

    <!-- DIARIO -->
<?php if (defined('PG_DIARY_ENABLED') and PG_DIARY_ENABLED) { ?>
    <a href="main.php?page=scheda_diario&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['diary']); ?>
    </a>
<?php } ?>

    <!-- ROLES -->
<?php if (($_SESSION['permessi'] >= ROLE_PERM || $_REQUEST['pg'] == $_SESSION['login']) && REG_ROLE) { ?>
    <a href="main.php?page=scheda_roles&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>">
        Giocate registrate
    </a>
<?php } ?>

    <!-- Se maggiore di moderatore -->
<?php if ($_SESSION['permessi'] >= MODERATOR) { ?>

    <!-- LOG -->
    <a href="main.php?page=scheda_log&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['log']); ?>
    </a>

    <!-- AMMINISTRA -->
    <a href="main.php?page=scheda_gst&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['gst']); ?>
    </a>
<?php }