<?php

/*HELP: */
/*Controllo permessi utente*/
if(!gdrcd_controllo_permessi($PARAMETERS['administration']['maintenance']['access_level'])) {
    echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
    die();
}

/*Eseguo l'aggiornamento*/
gdrcd_query("DELETE FROM clgpersonaggiooggetto WHERE nome IN (SELECT nome FROM personaggio WHERE permessi = -1)");
gdrcd_query("OPTIMIZE TABLE clgpersonaggiooggetto");

gdrcd_query("DELETE FROM clgpersonaggioabilita WHERE nome IN (SELECT nome FROM personaggio WHERE permessi = -1)");
gdrcd_query("OPTIMIZE TABLE clgpersonaggioabilita");

gdrcd_query("DELETE FROM clgpersonaggiomostrine WHERE nome IN (SELECT nome FROM personaggio WHERE permessi = -1)");
gdrcd_query("OPTIMIZE TABLE clgpersonaggiomostrine");

gdrcd_query("DELETE FROM clgpersonaggioruolo WHERE personaggio IN (SELECT nome AS personaggio FROM personaggio WHERE permessi = -1)");
gdrcd_query("OPTIMIZE TABLE clgpersonaggioruolo");

gdrcd_query("DELETE FROM messaggi WHERE mittente IN (SELECT nome AS personaggio FROM personaggio WHERE permessi = -1)");
gdrcd_query("DELETE FROM messaggi WHERE destinatario IN (SELECT nome AS personaggio FROM personaggio WHERE permessi = -1)");
gdrcd_query("DELETE FROM backmessaggi WHERE mittente IN (SELECT nome AS personaggio FROM personaggio WHERE permessi = -1)");
gdrcd_query("DELETE FROM backmessaggi WHERE destinatario IN (SELECT nome AS personaggio FROM personaggio WHERE permessi = -1)");
gdrcd_query("OPTIMIZE TABLE messaggi");

gdrcd_query("DELETE FROM araldo_letto WHERE nome IN (SELECT nome AS personaggio FROM personaggio WHERE permessi = -1)");
gdrcd_query("OPTIMIZE TABLE araldo_letto");

gdrcd_query("UPDATE chat SET mittente = 'Cancellato' WHERE mittente IN (SELECT nome AS personaggio FROM personaggio WHERE permessi = -1)");
gdrcd_query("UPDATE chat SET destinatario = 'Cancellato' WHERE destinatario IN (SELECT nome AS personaggio FROM personaggio WHERE permessi = -1)");
gdrcd_query("OPTIMIZE TABLE chat");

gdrcd_query("UPDATE log SET nome_interessato = 'Cancellato' WHERE nome_interessato IN (SELECT nome AS personaggio FROM personaggio WHERE permessi = -1)");
gdrcd_query("OPTIMIZE TABLE log");

gdrcd_query("UPDATE messaggioaraldo SET autore = 'Cancellato' WHERE autore IN (SELECT nome AS personaggio FROM personaggio WHERE permessi = -1)");
gdrcd_query("OPTIMIZE TABLE messaggiaraldo");

gdrcd_query("DELETE FROM personaggio WHERE permessi = -1");
gdrcd_query("OPTIMIZE TABLE personaggio");
?>
<!-- Conferma -->
<div class="success">
    <?php echo gdrcd_filter('out', $MESSAGE['warning']['modified']); ?>
</div>