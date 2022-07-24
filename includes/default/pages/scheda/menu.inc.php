<?php

$pg = Filters::out($_REQUEST['pg']);
$me = Filters::out($_SESSION['login']);
$perm = Filters::out($_SESSION['permessi']);
$id_pg = Filters::out($_REQUEST['id_pg']);

/*Visualizza il link modifica se l'utente visualizza la propria scheda o se è almeno un capogilda*/
?>

    <!-- ABILITA -->
    <a href="main.php?page=scheda/index&op=abilita&id_pg=<?= $id_pg; ?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['skill']); ?>
    </a>

    <a href="main.php?page=scheda/index&op=stats&id_pg=<?= $id_pg; ?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['stats']); ?>
    </a>

<?php if (Contatti::getInstance()->contactEnables()) { ?>
    <a href="main.php?page=scheda/index&op=contatti&id_pg=<?= $id_pg; ?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['contatti']); ?>
    </a>
<?php } ?>

    <a href="main.php?page=scheda/index&op=storia&id_pg=<?= $id_pg; ?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['background']); ?>
    </a>

    <!-- TRASFERIMENTI -->
    <a href="main.php?page=scheda/index&op=transazioni&pg=<?= $pg ?>&id_pg=<?= $id_pg; ?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['transictions']); ?>
    </a>

    <!-- INVENTARIO -->
    <a href="main.php?page=scheda/index&op=oggetti&id_pg=<?= $id_pg; ?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['equipment']); ?>
    </a>

    <!-- DIARIO -->
<?php if (SchedaDiario::getInstance()->diaryActive()) { ?>
    <a href="main.php?page=scheda/index&op=diario&id_pg=<?= $id_pg; ?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['diary']); ?>
    </a>
<?php } ?>

    <!-- ROLES -->
<?php if (($_SESSION['permessi'] >= ROLE_PERM || $_REQUEST['pg'] == $_SESSION['login']) && REG_ROLE) { ?>
    <a href="main.php?page=scheda_roles&pg=<?= $pg; ?>&id_pg=<?= $id_pg; ?>">
        Giocate registrate
    </a>
<?php } ?>

    <!-- Se maggiore di moderatore -->
<?php if ($_SESSION['permessi'] >= MODERATOR) { ?>

    <!-- LOG -->
    <a href="main.php?page=scheda_log&pg=<?= $pg; ?>&id_pg=<?= $id_pg; ?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['log']); ?>
    </a>

    <!-- AMMINISTRA -->
    <a href="main.php?page=scheda_gst&pg=<?= $pg; ?>&id_pg=<?= $id_pg; ?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['gst']); ?>
    </a>
    <a href="main.php?page=scheda_log&pg=<?= $pg; ?>&id_pg=<?= $id_pg; ?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['log']); ?>
    </a>
<?php } ?>

    <!-- CHAT OPTIONS -->
<?php if ($_REQUEST['pg'] == $_SESSION['login']) { ?>
    <a href="main.php?page=scheda/chat/opzioni/index&pg=<?= $pg; ?>&id_pg=<?= $id_pg; ?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['chat_options']); ?>
    </a>
<?php } ?>

    <!-- MODIFICA -->
<?php if (($_REQUEST['pg'] == $_SESSION['login']) || ($_SESSION['permessi'] >= GUILDMODERATOR)) { ?>
    <a href="main.php?page=scheda_modifica&pg=<?= $pg; ?>&id_pg=<?= $id_pg; ?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['update']); ?>
    </a>
<?php } ?>