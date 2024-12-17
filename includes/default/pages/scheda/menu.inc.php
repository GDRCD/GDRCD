<?php

$me = Filters::out($_SESSION['login']);
$perm = Filters::out($_SESSION['permessi']);
$id_pg = isset($_GET['id_pg']) ? Filters::out($_GET['id_pg']) : Functions::getInstance()->getMyId();

?>

    <!-- ABILITA -->
    <a href="main.php?page=scheda/index&op=abilita&id_pg=<?= $id_pg; ?>">
        Abilità
    </a>

    <a href="main.php?page=scheda/index&op=stats&id_pg=<?= $id_pg; ?>">
       Statistiche
    </a>

<?php if ( Contatti::getInstance()->contactEnables() ) { ?>
    <a href="main.php?page=scheda/index&op=contatti&id_pg=<?= $id_pg; ?>">
       Contatti
    </a>
<?php } ?>

    <a href="main.php?page=scheda/index&op=storia&id_pg=<?= $id_pg; ?>">
        Storia
    </a>

    <!-- TRASFERIMENTI -->
    <a href="main.php?page=scheda/index&op=transazioni&id_pg=<?= $id_pg; ?>">
        Transazioni
    </a>

    <!-- INVENTARIO -->
    <a href="main.php?page=scheda/index&op=oggetti&id_pg=<?= $id_pg; ?>">
        Oggetti
    </a>

    <!-- DIARIO -->
<?php if ( SchedaDiario::getInstance()->diaryActive() ) { ?>
    <a href="main.php?page=scheda/index&op=diario&id_pg=<?= $id_pg; ?>">
        Diario
    </a>
<?php } ?>

    <!-- ROLES -->
<?php

if (RegistrazioneGiocate::getInstance()->activeRegistrazioni() && RegistrazioneGiocate::getInstance()->permissionViewRecords($id_pg) ) { ?>
    <a href="main.php?page=scheda/index&op=registrazioni&id_pg=<?= $id_pg; ?>">
        Giocate registrate
    </a>
<?php } ?>

    <!-- CHAT OPTIONS -->
<?php if ( Personaggio::isMyPg($id_pg) ) { ?>
    <a href="main.php?page=scheda/chat/opzioni/index&id_pg=<?= $id_pg; ?>">
        Opzioni chat
    </a>
<?php } ?>

<?php if ( Log::getInstance()->permissionViewLogs() ) { ?>
    <a href="main.php?page=scheda/index&op=log&id_pg=<?= $id_pg; ?>">
        Log
    </a>
<?php } ?>

    <!-- MODIFICA -->
<?php if ( Scheda::getInstance()->permissionUpdateCharacter($id_pg) ) { ?>
    <a href="main.php?page=scheda/index&op=modifica&id_pg=<?= $id_pg; ?>">
        Modifica
    </a>
<?php } ?>

<?php if ( Scheda::getInstance()->permissionAdministrationCharacter() ) { ?>
    <a href="main.php?page=scheda/index&op=amministra&id_pg=<?= $id_pg; ?>">
        Amministra
    </a>
<?php } ?>