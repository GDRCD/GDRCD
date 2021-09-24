<?php
/*Eseguo l'aggiornamento*/
gdrcd_query("DELETE FROM blacklist WHERE 1");
gdrcd_query("OPTIMIZE TABLE blacklist");
?>
<!-- Conferma -->
<div class="warning">
    <?php echo gdrcd_filter('out', $MESSAGE['warning']['modified']); ?>
</div>
<!-- Link di ritorno alla visualizzazione di base -->
<div class="link_back">
    <a href="main.php?page=gestione_manutenzione">
        <?php echo gdrcd_filter('out', $MESSAGE['interface']['administration']['maintenance']['link']['back']); ?>
    </a>
</div>