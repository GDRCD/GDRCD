<?php
if($_SESSION['permessi'] < MODERATOR) {
    echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
    return; 
} 

switch ($_REQUEST['op']) {
    case 'save_new':
        $is_visible = ((isset($_POST['visible']) == true) && ($_POST['visible'] == 'is_visible')) ? 1 : 0;
        $url_sito = ((isset($_POST['url_sito']) == true) && ($_POST['url_sito'] == 'http://')) ? '' :  $_POST['url_sito'];
        $immagine = ($_POST['immagine'] == '') ? "standard_gilda.png" : gdrcd_filter('in', $_POST['immagine']);
        $statuto = gdrcd_filter('in', $_POST['statuto']);

        
        gdrcd_stmt("INSERT INTO gilda (nome, tipo, immagine,  visibile, url_sito, statuto) 
        VALUES (?, ?, ?, ?, ?, ?)",[
        gdrcd_filter('in', $_POST['nome']),
        gdrcd_filter('num', $_POST['tipo']),
        $immagine,
        $is_visible,
        $url_sito,
        $statuto]);  
        echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['warning']['inserted']).'</div>';
        break;
    case 'save_edit':
        $is_visible = ((isset($_POST['visible']) == true) && ($_POST['visible'] == 'is_visible')) ? 1 : 0;
        $url_sito = ((isset($_POST['url_sito']) == true) && ($_POST['url_sito'] == 'http://')) ? '' :  $_POST['url_sito'];
        $immagine = ($_POST['immagine'] == '') ? "standard_gilda.png" : gdrcd_filter('in', $_POST['immagine']);
        $statuto = gdrcd_filter('in', $_POST['statuto']);
        gdrcd_stmt("UPDATE gilda SET nome = ?, visibile = ?, immagine = ?, tipo = ?, url_sito = ?, statuto = ?
            WHERE id_gilda = ?",[gdrcd_filter('in', $_POST['nome']),
            $is_visible,
            $immagine,
            gdrcd_filter('num', $_POST['tipo']),
            $url_sito,
            $statuto,
            gdrcd_filter('num', $_POST['id'])]);
        echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['warning']['modified']).'</div>';
        break;
    case 'save_delete':
        
        $result = gdrcd_stmt_all("SELECT id_ruolo FROM ruolo WHERE gilda = ?",[gdrcd_filter('num', $_POST['id'])]);
        foreach($result as $row) {
            gdrcd_stmt("DELETE FROM clgpersonaggioruolo WHERE id_ruolo=?",[$row['id_ruolo']]);
        }
        
        gdrcd_stmt("DELETE FROM ruolo WHERE gilda = ?",[gdrcd_filter('num', $_POST['id'])]);
        gdrcd_stmt("DELETE FROM gilda WHERE id_gilda=?",[gdrcd_filter('num', $_POST['id'])]);

        echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['warning']['deleted']).'</div>';
        break;
    default:
        die('Operazione non riconosciuta.');

        
}
echo '<div class="link_back">
    <a href="main.php?page=gestione_gilde">Torna indietro</a>
</div>';