<?php

$pg = Filters::out($_REQUEST['pg']);
$me = Filters::out($_SESSION['login']);
$perm = Filters::out($_SESSION['permessi']);
$id_pg=Personaggio::IdFromName($pg);

/*Visualizza il link modifica se l'utente visualizza la propria scheda o se è almeno un capogilda*/
?>

    <!-- ABILITA -->
    <a href="main.php?page=scheda_skill&pg=<?= $pg ?>&id_pg=<?=$id_pg;?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['skill']); ?>
    </a>

    <a href="main.php?page=scheda_stats&pg=<?= $pg ?>&id_pg=<?=$id_pg;?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['stats']); ?>
    </a>

    <!-- Descrizione e Storia separate dalla pagina principale della scheda -->
    <a href="main.php?page=scheda_descrizione&pg=<?= $pg ?>&id_pg=<?=$id_pg;?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['detail']); ?>
    </a>

    <a href="main.php?page=scheda_contatti&pg=<?= $pg ?>&id_pg=<?=$id_pg;?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['contatti']); ?>
    </a>
    <a href="main.php?page=scheda_storia&pg=<?= $pg ?>&id_pg=<?=$id_pg;?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['background']); ?>
    </a>
    <!-- TRASFERIMENTI -->
    <a href="main.php?page=scheda_trans&pg=<?= $pg ?>&id_pg=<?=$id_pg;?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['transictions']); ?>
    </a>

    <!-- ESPERIENZA -->
    <a href="main.php?page=scheda_px&pg=<?= $pg ?>&id_pg=<?=$id_pg;?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['experience']); ?>
    </a>

    <!-- INVENTARIO -->
    <a href="main.php?page=scheda_oggetti&pg=<?= $pg ?>&id_pg=<?=$id_pg;?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['equipment']); ?>
    </a>

    <!-- DIARIO -->
<?php if (defined('PG_DIARY_ENABLED') and PG_DIARY_ENABLED) { ?>
    <a href="main.php?page=scheda_diario&pg=<?= $pg ?>&id_pg=<?=$id_pg;?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['diary']); ?>
    </a>
<?php } ?>

    <!-- ROLES -->
<?php if (($_SESSION['permessi'] >= ROLE_PERM || $_REQUEST['pg'] == $_SESSION['login']) && REG_ROLE) { ?>
    <a href="main.php?page=scheda_roles&pg=<?php echo Filters::url( $_REQUEST['pg']); ?>&id_pg=<?=$id_pg;?>">
        Giocate registrate
    </a>
<?php } ?>

    <!-- Se maggiore di moderatore -->
<?php if ($_SESSION['permessi'] >= MODERATOR) { ?>

    <!-- LOG -->
    <a href="main.php?page=scheda_log&pg=<?php echo Filters::url( $_REQUEST['pg']); ?>&id_pg=<?=$id_pg;?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['log']); ?>
    </a>

    <!-- AMMINISTRA -->
    <a href="main.php?page=scheda_gst&pg=<?php echo Filters::url( $_REQUEST['pg']); ?>&id_pg=<?=$id_pg;?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['gst']); ?>
    </a>
    <a href="main.php?page=scheda_log&pg=<?= $pg; ?>&id_pg=<?=$id_pg;?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['log']); ?>
    </a>
<?php } ?>

    <!-- MODIFICA -->
<?php if (($_REQUEST['pg'] == $_SESSION['login']) || ($_SESSION['permessi'] >= GUILDMODERATOR)) { ?>
    <a href="main.php?page=scheda_modifica&pg=<?php echo Filters::url( $_REQUEST['pg']); ?>&id_pg=<?=$id_pg;?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['update']); ?>
    </a>
<?php } ?>