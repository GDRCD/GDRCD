<?php
if ( (is_numeric($_POST['mesi']) === true) && ($_POST['mesi'] >= 0) && ($_POST['mesi'] <= 12) ) {
    /*Eseguo l'aggiornamento*/
    gdrcd_query("DELETE FROM messaggi WHERE DATE_SUB(NOW(), INTERVAL " . gdrcd_filter('num', $_POST['mesi']) . " MONTH) > spedito");
    gdrcd_query("OPTIMIZE TABLE messaggi");
    ?>
    <!-- Conferma -->
    <div class="warning">
        <?php echo gdrcd_filter('out', $MESSAGE['warning']['modified']); ?>
    </div>
    <?php
} else {
    ?>
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