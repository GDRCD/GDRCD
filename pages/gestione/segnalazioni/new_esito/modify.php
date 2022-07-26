<?php
/*Inserimento di un nuovo record*/
if ($_POST['op']=='modify') {
    gdrcd_query("UPDATE blocco_esiti SET titolo = '".gdrcd_filter('in',$_POST['titolo'])."', 
	    closed = ".gdrcd_filter('num',$_POST['stato'])."  
	    WHERE id = ".gdrcd_filter('num', $_POST['id'] )."
	    AND (master = '0' || master ='" . gdrcd_filter('in', $_SESSION['login']) . "') ");

    echo '<div class="warning">';
    echo gdrcd_filter('out',$MESSAGE['warning']['inserted']);
    echo '</div>';
    echo '<br><a href="main.php?page=gestione_segnalazioni&segn=esiti_master">Torna indietro</a>';
}
?>
