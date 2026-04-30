<?php
/*Inserimento di un nuovo record*/
if ($_POST['op']=='modify') {
    gdrcd_query("UPDATE blocco_esiti SET titolo = '".gdrcd_filter('in',$_POST['titolo'])."', 
	    closed = ".gdrcd_filter('num',$_POST['stato'])."  
	    WHERE id = ".gdrcd_filter('num', $_POST['id'] )."
	    AND (id_personaggio_master IS NULL || id_personaggio_master ='" . $_SESSION['id_personaggio'] . "') ");

    echo '<div class="warning">';
    echo gdrcd_filter('out',$MESSAGE['warning']['inserted']);
    echo '</div>';
    echo '<br><a href="main.php?page=gestione_segnalazioni&segn=esiti_master">Torna indietro</a>';
}
?>
