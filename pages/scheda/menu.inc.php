<?php

$pg = gdrcd_filter('out',$_REQUEST['pg']);
$me = gdrcd_filter('out',$_SESSION['login']);
$perm = gdrcd_filter('out',$_SESSION['permessi']);

include_once(__DIR__ . '/../Abilita/abilita_class.php');

# Se la classe esiste, utilizza il controllo dato dalla classe, altrimenti utilizza quello di default
if(class_exists('Abilita')) {
    $abi_class = new Abilita();
    $abi_public = $abi_class->AbiVisibility($pg);
}
else{
    # Se non esiste la costante OR se e' true OR se non e' true: se sono il proprietario del pg OR sono moderatore
    $abi_public = ( !defined('ABI_PUBLIC') || (ABI_PUBLIC) || ($pg == $this->me) || ($this->permessi >= MODERATOR));
}



/*Visualizza il link modifica se l'utente visualizza la propria scheda o se è almeno un capogilda*/
?>
<?php if ($abi_public) { ?>
    <a href="main.php?page=scheda_skill&pg=<?=$pg?>">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['skill']); ?>
    </a>
<?php } ?>
    <a href="main.php?page=scheda_px&pg=<?=$pg;?>">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['experience']); ?>
    </a>
    <a href="main.php?page=scheda_oggetti&pg=<?=$pg;?>">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['inventory']); ?>
    </a>
    <a href="main.php?page=scheda_equip&pg=<?=$pg;?>">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['equipment']); ?>
    </a>
    <a href="main.php?page=scheda_trans&pg=<?=$pg; ?>">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['transictions']); ?>
    </a>
<?php
if ( ($pg == $me) || ($perm >= GUILDMODERATOR) ) { ?>
    <a href="main.php?page=scheda_modifica&pg=<?=$pg; ?>">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['update']); ?>
    </a>
<?php }  /*Visualizza il link modifica se l'utente visualizza la propria scheda o se è almeno un capogilda*/
if ($perm >= MODERATOR) { ?>
    <a href="main.php?page=scheda_gst&pg=<?=$pg;?>">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['gst']); ?>
    </a>
    <a href="main.php?page=scheda_log&pg=<?=$pg; ?>">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['sheet']['menu']['log']); ?>
    </a>
<?php }