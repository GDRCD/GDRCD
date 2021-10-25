<?php

$pg = gdrcd_filter('out', $_REQUEST['pg']);
$me = gdrcd_filter('out', $_SESSION['login']);
$perm = gdrcd_filter('out', $_SESSION['permessi']);

include_once(__DIR__ . '/../Abilita/abilita_class.php');

# Se la classe esiste, utilizza il controllo dato dalla classe, altrimenti utilizza quello di default
if (class_exists('Abilita')) {
    $abi_class = new Abilita();
    $abi_public = $abi_class->AbiVisibility($pg);
} else {
    # Se non esiste la costante OR se e' true OR se non e' true: se sono il proprietario del pg OR sono moderatore
    $abi_public = (!defined('ABI_PUBLIC') || (ABI_PUBLIC) || ($pg == $this->me) || ($this->permessi >= MODERATOR));
}


/*Visualizza il link modifica se l'utente visualizza la propria scheda o se è almeno un capogilda*/
?>

    <!-- ABILITA -->
<?php if ($abi_public) { ?>
    <a href="main.php?page=scheda_skill&pg=<?= $pg ?>">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['skill']); ?>
    </a>
<?php } ?>


    <!-- Descrizione e Storia separate dalla pagina principale della scheda -->
    <a href="main.php?page=scheda_descrizione&pg=<?php echo $_SESSION['login']; ?>">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['detail']); ?>
    </a>
    <a href="main.php?page=scheda_storia&pg=<?php echo $_SESSION['login']; ?>">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['background']); ?>
    </a>
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
    <a href="main.php?page=scheda_log&pg=<?= $pg; ?>">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['log']); ?>
    </a>
<?php } ?>

    <!-- MODIFICA -->
<?php if (($_REQUEST['pg'] == $_SESSION['login']) || ($_SESSION['permessi'] >= GUILDMODERATOR)) { ?>
    <a href="main.php?page=scheda_modifica&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['update']); ?>
    </a>
<?php } ?>