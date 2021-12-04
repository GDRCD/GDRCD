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
if($PARAMETERS['mode']['check_messages'] === 'ON') {

    /**
     * Controllo se rispetto all'ultimo messaggio visualizzato dall'utente ne sono stati inviati altri
     */
    $messaggi_non_letti = gdrcd_query("SELECT id FROM messaggi WHERE destinatario = '".gdrcd_filter('in', $_SESSION['login'])."' AND destinatario_del = 0 AND letto = 0", 'result');
    $cntNewMessage = gdrcd_query($messaggi_non_letti, 'num_rows');
    $hasNewMessage = ($cntNewMessage > 0);
    gdrcd_query($messaggi_non_letti, 'free');

    // NO NUOVI MESSAGGI
    if(!$hasNewMessage) {

        // Nel caso sia prevista una immagine, la preparo
        if(empty ($PARAMETERS['names']['private_message']['image_file']) === false) {
            // L'immagine cambia sul click del mouse
            if(($PARAMETERS['names']['private_message']['image_file_onclick']) === true) {
                $img_up = $PARAMETERS['names']['private_message']['image_file'];
                $img_down = $PARAMETERS['names']['private_message']['image_file'];
            } else {
                $img_up = $PARAMETERS['names']['private_message']['image_file'];
                $img_down = $PARAMETERS['names']['private_message']['image_file_onclick'];
            }

            // Inserisco lo script per il click dell'immagine
            $textMessages = '
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
            $textMessages .= '<a onMouseOver="msg_over_button()" onMouseOut="msg_up_button()" href="../../../main.php?page=messages_center&offset=0"  target="_top">
                                    <img src="../../../themes/'.$PARAMETERS['themes']['current_theme'].'/imgs/menu/'.$PARAMETERS['names']['private_message']['image_file'].'" alt="'.gdrcd_filter('out', $PARAMETERS['names']['private_message']['plur']).'" title="'.gdrcd_filter('out', $PARAMETERS['names']['private_message']['plur']).'" name="msg_buttonOne" />
                              </a>';
        }
        // Testo normale
        else {
            $textMessages = gdrcd_filter('out', $PARAMETERS['names']['private_message']['plur']);
        }

        // Preparo il modulo
        $linkMessages = '<a href="../../../main.php?page=messages_center&offset=0" target="_top">'.$textMessages.'</a>';
        $cntMessagesFrame = '<div class="messaggio_forum">'.$linkMessages.'</div>';

        // Forzo lo stop della notifica sul title se previsto
        if($PARAMETERS['mode']['alert_pm_via_pagetitle'] == 'ON') {
            $cntMessagesFrame .= '<script type="text/javascript">parent.stop_blinking_title();</script>';
        }
    }
    // NUOVI MESSAGGI
    else {
        // Determino se costruire una immagine
        if(empty($PARAMETERS['names']['private_message']['image_file_new']) === false) {
            $textMessages = '<img src="../../../themes/'.$PARAMETERS['themes']['current_theme'].'/imgs/menu/'.$PARAMETERS['names']['private_message']['image_file_new'].'" alt="'.gdrcd_filter('out', $PARAMETERS['names']['private_message']['plur']).'" title="'.gdrcd_filter('out', $PARAMETERS['names']['private_message']['plur']).'" />';
        }
        // Altrimenti preparo il testo
        else {
            $textMessages = gdrcd_filter('out', $PARAMETERS['names']['private_message']['plur']) . ' ['.$cntNewMessage.']';
        }

        // Preparo il modulo
        $linkMessages = '<a href="../../../main.php?page=messages_center&offset=0" target="_top">'.$textMessages.'</a>';
        $cntMessagesFrame = '<div class="messaggio_forum_nuovo">'.$linkMessages.'</div>';

        // Avvio notifica sul title
        if($PARAMETERS['mode']['alert_pm_via_pagetitle'] == 'ON') {
            $cntMessagesFrame .= '<script type="text/javascript">parent.blink_title("('.$MESSAGE['interface']['forums']['topic']['new_posts']['sing'].')" '.$PARAMETERS['info']['site_name'].');</script>';
        }

        // Avvio notifica sonora
        $cntMessagesFrame .= AudioController::play('messages');
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
    <title>Messaggi</title>
</head>
<body class="transparent_body">
    <?=AudioController::build('messages');?>
    <div class="box_messages"><?=isset($cntMessagesFrame) ? $cntMessagesFrame : '';?></div>

<?php include('../../../footer.inc.php'); /*Footer comune*/
