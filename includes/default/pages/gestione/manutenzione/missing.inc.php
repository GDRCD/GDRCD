<?php
if ( (is_numeric($_POST['mesi']) === true) && ($_POST['mesi'] >= 1) && ($_POST['mesi'] <= 12) ) {
    /*Eseguo l'aggiornamento*/

    gdrcd_query("DELETE FROM clgpersonaggioabilita WHERE nome IN (SELECT nome FROM personaggio WHERE DATE_SUB(NOW(), INTERVAL " . gdrcd_filter('num', $_POST['mesi']) . " MONTH) > ora_entrata)");
    gdrcd_query("OPTIMIZE TABLE clgpersonaggioabilita");

    gdrcd_query("DELETE FROM clgpersonaggioruolo WHERE personaggio IN (SELECT nome AS personaggio FROM personaggio WHERE DATE_SUB(NOW(), INTERVAL " . gdrcd_filter('num', $_POST['mesi']) . " MONTH) > ora_entrata)");
    gdrcd_query("OPTIMIZE TABLE clgpersonaggioruolo");

    gdrcd_query("DELETE FROM personaggio WHERE DATE_SUB(NOW(), INTERVAL " . gdrcd_filter('num', $_POST['mesi']) . " MONTH) > ora_entrata");
    gdrcd_query("OPTIMIZE TABLE personaggio");
    ?>
    <!-- Conferma -->
    <div class="warning">
        <?php echo gdrcd_filter('out', $MESSAGE['warning']['modified']); ?>
    </div>
    <?php
} else { ?>
    <div class="error">
        <?php echo gdrcd_filter('out', $MESSAGE['warning']['cant_do']); ?>
    </div>
    <?php
}
?>
<!-- Link di ritorno alla visualizzazione di base -->
<div class="link_back">
    <a href="main.php?page=gestione_manutenzione">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['maintenance']['link']['back']); ?>
    </a>
</div>