<?php
$id_messaggio = gdrcd_filter('num', $_POST['id_messaggio']);
/** * Bugfix: correzione di un bug che permetteva la cancellazione di messaggi non inviati all'utente.
 * Viene quindi aggiunta nella clausola where il controllo sulla proprietà del messaggio.
 * Inoltre viene effettuato un controllo sul numero di righe cancellate. Se non è stato cancellato nulla
 * non verrà mostrato nessun messaggio ma solo il link per tornare alla schermata messaggi.
 * @author Rhllor
 */
//gdrcd_query("DELETE FROM messaggi WHERE id = ".$id_messaggio." LIMIT 1");
gdrcd_query("DELETE FROM messaggi WHERE id = ".$id_messaggio." and destinatario = '".$_SESSION['login']."' LIMIT 1");
if(gdrcd_query("", 'affected') > 0) { ?>
    <div class="warning">
        <?php echo gdrcd_filter('out', $PARAMETERS['names']['private_message']['sing'].$MESSAGE['interface']['messages']['erased']); ?>
    </div>
    <div class="link_back">
        <a href="main.php?page=messages_center&offset=0"><?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['go_back']); ?></a>
    </div>
<?php
} else {
    /** * Enhancement: in caso di nessuna riga cancellata si controlla l'esistenza del messaggio,
     * @author Rhllor
     */
    $result = gdrcd_query("SELECT destinatario FROM messaggi WHERE id = ".gdrcd_filter('num', $_REQUEST['id_messaggio'])." and ( destinatario = '".$_SESSION['login']."') LIMIT 1", 'result');
    if(gdrcd_query($result, 'num_rows') == 0) { ?>
        <div class="warning">
            Il messaggio che stai tentando di cancellare non esiste
        </div>
        <div class="link_back">
            <a href="main.php?page=messages_center&offset=0"><?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['go_back']); ?></a>
        </div>
        <?php
    } else {
        $record = gdrcd_query($result, 'fetch');
        gdrcd_query($result, 'free');
    }
}