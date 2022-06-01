<?php
/**    * Fix Require al posto di include
 * Require blocca l'esecuzione dello script se il file è assente
 * dal momento che il file in questione è fondamentale è buona norma applicarlo
 * @author Blancks
 */
require('header.inc.php'); /*Header comune*/

gdrcd_controllo_sessione(); /*Se si è tentato di accedere senza un'autenticazione blocca l'esecuzione*/

$strInnerPage = "";

/** * Bug fix del mapwise: la gestione dello spostamento della mappa va gestita da main e non da mappaclick
 * @author Blancks
 */
if( ! empty($_GET['map_id'])) {
    $_SESSION['mappa'] = (int) $_GET['map_id'];
    gdrcd_query("UPDATE personaggio SET ultima_mappa=".gdrcd_filter('num', $_SESSION['mappa']).", ultimo_luogo=-1 WHERE nome = '".gdrcd_filter('in', $_SESSION['login'])."'");
}

/** * Ottengo la pagina da visualizzare nel blocco principale
 * @author Kasa
 */
if(isset($_REQUEST['page'])) {
    $strInnerPage = $_REQUEST['page'];
}
//se e' impostato dir allora cambio stanza.
elseif(isset($_REQUEST['dir']) && is_numeric($_REQUEST['dir'])) {
    if($_REQUEST['dir'] >= 0) {
        $strInnerPage = 'frame_chat';
    } else {
        $strInnerPage = 'mappaclick';
        $_REQUEST['id_map'] = $_SESSION['mappa'];
    }

    gdrcd_query("UPDATE personaggio SET ultimo_luogo=".gdrcd_filter('num', $_REQUEST['dir'])." WHERE nome='".gdrcd_filter('in', $_SESSION['login'])."'");
}
/** * Caso di fix
 * se non ci sono variabili via url, si ripristinano dei valori di default
 * @author Blancks
 */
else {
    $strInnerPage = 'mappaclick';
    $_REQUEST['id_map'] = $_SESSION['mappa'];
}
/**    * Fine caso di Fix */

if(gdrcd_controllo_esilio($_SESSION['login']) === true) {
    session_destroy();
} else {
    require('layouts/'.$PARAMETERS['themes']['kind_of_layout'].'_frames.php');
}

require('footer.inc.php');  /*Footer comune*/
