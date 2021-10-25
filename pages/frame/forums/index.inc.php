<?php

//Includio i parametri, la configurazione, la lingua e le funzioni
require ('../../../includes/required.php');

// Determino il tema selezionato
if(!empty($_SESSION['theme']) and array_key_exists($_SESSION['theme'], $PARAMETERS['themes']['available'])){
    $PARAMETERS['themes']['current_theme'] = $_SESSION['theme'];
}

//Ricevo il tempo di reload
$i_ref_time = gdrcd_filter_get($_GET['ref']);

// Nel caso in cui sia presente il controllo sui nuovi messaggi ottenuti, prevedo le operazioni
if($PARAMETERS['mode']['check_forum'] === 'ON') {

    /**
     * Controllo se rispetto all'ultimo topic visualizzato dall'utente ne sono stati inviati creati altri
     */
    $hasNewForumMessage = false;
    $result = gdrcd_query("SELECT id_araldo, nome, tipo, proprietari FROM araldo ORDER BY tipo, nome", 'result');
    while($row = gdrcd_query($result, 'fetch')) {
        if(($row['tipo'] <= PERTUTTI) || (($row['tipo'] == SOLORAZZA) && ($_SESSION['id_razza'] == $row['proprietari'])) || (($row['tipo'] == SOLOGILDA) && (strpos($_SESSION['gilda'], '*'.$row['proprietari'].'*') != false)) || (($row['tipo'] == SOLOMASTERS) && ($_SESSION['permessi'] >= GAMEMASTER)) || ($_SESSION['permessi'] >= MODERATOR)) {
            $new_msg = gdrcd_query("SELECT COUNT(id) AS num FROM araldo_letto WHERE araldo_id = ".$row['id_araldo']." AND nome = '".$_SESSION['login']."';");

            $new_msg2 = gdrcd_query("SELECT COUNT(id_messaggio) AS num FROM messaggioaraldo WHERE id_araldo = ".$row['id_araldo']." AND id_messaggio_padre = -1");

            if($new_msg2['num'] > $new_msg['num']) {
                $hasNewForumMessage = true;
            }
        }
    }

    // NO NUOVI TOPIC
    if(!$hasNewForumMessage) {

        // Nel caso sia prevista una immagine, la preparo
        if(empty ($PARAMETERS['names']['forum']['image_file']) === false) {
            // L'immagine cambia sul click del mouse
            if(($PARAMETERS['names']['forum']['image_file_onclick']) === true) {
                $img_up = $PARAMETERS['names']['forum']['image_file'];
                $img_down = $PARAMETERS['names']['forum']['image_file'];
            } else {
                $img_up = $PARAMETERS['names']['forum']['image_file'];
                $img_down = $PARAMETERS['names']['forum']['image_file_onclick'];
            }

            // Inserisco lo script per il click dell'immagine
            $textForum = '
                <script type="text/javascript"> 
                    if (document.images) { 
                        var msg_button1_up = new Image(); 
                        msg_button1_up.src = "../../../themes/'.$PARAMETERS['themes']['current_theme'].'/imgs/menu/'.$img_up.'"; 
                        
                        var msg_button1_over = new Image(); 
                        msg_button1_over.src = "../../../themes/'.$PARAMETERS['themes']['current_theme'].'/imgs/menu/'.$img_down.'";
                    } 
                    
                    function msg_over_button() { 
                        if (document.images) { 
                            document["msg_buttonOne"].src = msg_button1_over.src;
                        }
                    } 
                    
                    function msg_up_button() { 
                        if (document.images) { 
                            document["msg_buttonOne"].src = msg_button1_up.src
                        }
                    }
                </script>';
            // Inserisco l'immagine
            $textForum .= '<a onMouseOver="msg_over_button()" onMouseOut="msg_up_button()" href="../../../main.php?page=forum"  target="_top">
                                    <img src="../../../themes/'.$PARAMETERS['themes']['current_theme'].'/imgs/menu/'.$PARAMETERS['names']['forum']['image_file'].'" alt="'.gdrcd_filter('out', $PARAMETERS['names']['forum']['plur']).'" title="'.gdrcd_filter('out', $PARAMETERS['names']['forum']['plur']).'" name="msg_buttonOne" />
                              </a>';
        }
        // Testo normale
        else {
            $textForum = gdrcd_filter('out', $PARAMETERS['names']['forum']['plur']);
        }

        // Preparo il modulo
        $linkForum = '<a href="../../../main.php?page=forum" target="_top">'.$textForum.'</a>';
        $cntForumFrame = '<div class="messaggio_forum">'.$linkForum.'</div>';
    }
    // NUOVI TOPIC
    else {

        // Determino se costruire una immagine
        if(empty($PARAMETERS['names']['forum']['image_file_new']) === false) {
            $textForum = '<img src="../../../themes/'.$PARAMETERS['themes']['current_theme'].'/imgs/menu/'.$PARAMETERS['names']['forum']['image_file_new'].'" alt="'.gdrcd_filter('out', $PARAMETERS['names']['forum']['plur']).'" title="'.gdrcd_filter('out', $PARAMETERS['names']['forum']['plur']).'" />';
        }
        // Altrimenti preparo il testo
        else {
            $textForum = gdrcd_filter('out', $PARAMETERS['names']['forum']['plur']);
        }

        // Preparo il modulo
        $linkForum = '<a href="../../../main.php?page=forum" target="_top">'.$textForum.'</a>';
        $cntForumFrame = '<div class="messaggio_forum_nuovo">'.$linkForum.'</div>';
    }
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <!--meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"-->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="refresh" content="<?php echo $i_ref_time; ?>">
    <link rel="stylesheet" href="../../../themes/<?php echo $PARAMETERS['themes']['current_theme']; ?>/presenti.css" TYPE="text/css">
    <link rel="stylesheet" href="../../../themes/<?php echo $PARAMETERS['themes']['current_theme']; ?>/main.css" TYPE="text/css">
    <link rel="stylesheet" href="../../../themes/<?php echo $PARAMETERS['themes']['current_theme']; ?>/chat.css" TYPE="text/css">
    <title>Forum</title>
</head>
<body class="transparent_body">
    <div class="box_forums"><?=isset($cntForumFrame) ? $cntForumFrame : '';?></div>

<?php include('../../../footer.inc.php'); /*Footer comune*/


