<?php

$me = Filters::out($_SESSION['login']);
$perm = Filters::out($_SESSION['permessi']);
$id_pg = isset($_GET['id_pg']) ? Filters::out($_GET['id_pg']) : Functions::getInstance()->getMyId();

?>

    <!-- ABILITA -->
    <a href="main.php?page=scheda/index&op=abilita&id_pg=<?= $id_pg; ?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['skill']); ?>
    </a>

    <a href="main.php?page=scheda/index&op=stats&id_pg=<?= $id_pg; ?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['stats']); ?>
    </a>

<?php if ( Contatti::getInstance()->contactEnables() ) { ?>
    <a href="main.php?page=scheda/index&op=contatti&id_pg=<?= $id_pg; ?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['contatti']); ?>
    </a>
<?php } ?>

    <a href="main.php?page=scheda/index&op=storia&id_pg=<?= $id_pg; ?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['background']); ?>
    </a>

    <!-- TRASFERIMENTI -->
    <a href="main.php?page=scheda/index&op=transazioni&id_pg=<?= $id_pg; ?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['transictions']); ?>
    </a>

    <!-- INVENTARIO -->
    <a href="main.php?page=scheda/index&op=oggetti&id_pg=<?= $id_pg; ?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['equipment']); ?>
    </a>

    <!-- DIARIO -->
<?php if ( SchedaDiario::getInstance()->diaryActive() ) { ?>
    <a href="main.php?page=scheda/index&op=diario&id_pg=<?= $id_pg; ?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['diary']); ?>
    </a>
<?php } ?>

    <!-- ROLES -->
<?php if ( ($_SESSION['permessi'] >= ROLE_PERM || $_REQUEST['pg'] == $_SESSION['login']) && REG_ROLE ) { ?>
    <a href="main.php?page=scheda_roles&id_pg=<?= $id_pg; ?>">
        Giocate registrate
    </a>
<?php } ?>

    <!-- CHAT OPTIONS -->
<?php if ( Personaggio::isMyPg($id_pg) ) { ?>
    <a href="main.php?page=scheda/chat/opzioni/index&id_pg=<?= $id_pg; ?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['chat_options']); ?>
    </a>
<?php } ?>

<?php if ( Log::getInstance()->permissionViewLogs() ) { ?>
    <a href="main.php?page=scheda/index&op=log&id_pg=<?= $id_pg; ?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['log']); ?>
    </a>
<?php } ?>

    <!-- MODIFICA -->
<?php if ( Scheda::getInstance()->permissionUpdateCharacter($id_pg) ) { ?>
    <a href="main.php?page=scheda/index&op=modifica&id_pg=<?= $id_pg; ?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['update']); ?>
    </a>
<?php } ?>

<?php if ( Scheda::getInstance()->permissionAdministrationCharacter() ) { ?>
    <a href="main.php?page=scheda/index&op=amministra&id_pg=<?= $id_pg; ?>">
        <?php echo Filters::out($MESSAGE['interface']['sheet']['menu']['gst']); ?>

    </a>
<?php } ?>