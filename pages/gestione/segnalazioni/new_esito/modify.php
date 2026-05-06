<?php
/*Inserimento di un nuovo record*/
if ($_POST['op']=='modify') {
    gdrcd_stmt("UPDATE blocco_esiti SET titolo = ? ,
	    closed = ? 
	    WHERE id = ? 
	    AND (id_personaggio_master IS NULL || id_personaggio_master ='" . $_SESSION['id_personaggio'] . "') ", [$_POST['titolo'], $_POST['stato'], $_POST['id']]);

    echo '<div class="warning">';
    echo gdrcd_filter('out',$MESSAGE['warning']['inserted']);
    echo '</div>';
    echo '<br><a href="main.php?page=gestione_segnalazioni&segn=esiti_master">Torna indietro</a>';
}
?>
