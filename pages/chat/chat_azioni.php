<div class="chat_azioni">

    <?php

    $elenco_azioni=gdrcd_query("SELECT chat.id, chat.imgs, chat.mittente, chat.destinatario, chat.tipo, chat.ora, chat.testo,   mappa.ora_prenotazione
						FROM chat
						INNER JOIN mappa ON mappa.id = chat.stanza
						LEFT JOIN personaggio ON personaggio.nome = chat.mittente
						WHERE  stanza = {$_REQUEST['dir']} AND chat.ora > IFNULL(mappa.ora_prenotazione, '0000-00-00 00:00:00') AND DATE_SUB(NOW(), INTERVAL 4 HOUR) < ora ORDER BY id ASC ", "result");


    foreach ($elenco_azioni as $azione) {
        $tipo=$azione['tipo'];
        $add_chat="";
        switch ($tipo) {
            case 'P':
            case 'A':
                //azione

                if($PARAMETERS['mode']['chaticons'] == 'ON') {
                    $icone_chat = explode(";", gdrcd_filter('out', $azione['imgs']));
                    $add_icon = '<span class="chat_icons"> <img class="presenti_ico" src="themes/'.$PARAMETERS['themes']['current_theme'].'/imgs/races/'.$icone_chat[1].'"><img class="presenti_ico" src="imgs/icons/testamini'.$icone_chat[0].'.png"> </span>';
                }
                $pg=gdrcd_query("SELECT url_img_chat FROM personaggio WHERE nome = '".gdrcd_filter("in", "{$azione['mittente']}")."' ");
                if($PARAMETERS['mode']['chat_avatar'] == 'ON' && ! empty($pg['url_img_chat'])) {
                    $chat_avatar = '<img src="'.$pg['url_img_chat'].'" class="chat_avatar" style="width:'.$PARAMETERS['settings']['chat_avatar']['width'].'px; height:'.$PARAMETERS['settings']['chat_avatar']['height'].'px;" />';

                    // Se è stato impostato il link sull'avatar di chat, avvio la costruzione
                    if(isset($PARAMETERS['settings']['chat_avatar']['link']['mode']) and ($PARAMETERS['settings']['chat_avatar']['link']['mode']  == 'ON')) {
                        $chat_avatar_url = ( isset($PARAMETERS['settings']['chat_avatar']['link']['popup']) and ($PARAMETERS['settings']['chat_avatar']['link']['popup'] == 'ON') )
                            ? "javascript:modalWindow('scheda', 'Scheda di ". $azione['mittente'] ."', 'popup.php?page=scheda&pg=". $azione['mittente'] ."');"
                            : "main.php?page=scheda&pg=".$azione['mittente'];

                        // Inserisco l'avatar di chat cliccabile
                        $add_chat .= '<a href="'.$chat_avatar_url.'">'.$chat_avatar.'</a>';
                    }
                    // Altrimenti mostro solo l'avatar di chat
                    else {
                        $add_chat .= $chat_avatar;
                    }
                }

                $add_chat .= '<span class="chat_time">'.gdrcd_format_time($azione['ora']).'</span>';
                if($PARAMETERS['mode']['chaticons'] == 'ON') {
                    $add_chat .= $add_icon;
                }
                $add_chat .= '<span class="chat_name"><a href="#" onclick="Javascript: document.getElementById(\'tag\').value=\''.$azione['mittente'].'\'; document.getElementById(\'tipo\')[2].selected = \'1\'; document.getElementById(\'message\').focus();">'.$azione['mittente'].'</a>';
                if(empty ($row['destinatario']) === false) {
                    $add_chat .= '<span class="chat_tag"> ['.gdrcd_filter('out', $azione['destinatario']).']</span>';
                }
                $add_chat .=  ($azione['tipo'] === 'P') ? ': </span> ' : '</span> ';
                $add_chat .= '<span class="chat_msg">'.gdrcd_chatme($_SESSION['login'], gdrcd_chatcolor(gdrcd_filter('out', $azione['testo']))).'</span>';
                /**    * Fix problema visualizzazione spazi vuoti con i sussurri
                 * @author eLDiabolo
                 */

                break;

            case 'S':
                //sussurri
                if($_SESSION['login'] == $azione['destinatario']) {
                    $add_chat .= '<span class="chat_name">'.$azione['mittente'].' '.$MESSAGE['chat']['whisper']['by'].': </span> ';
                    $add_chat .= '<span class="chat_msg">'.gdrcd_filter('out', $azione['testo']).'</span>';
                } elseif($_SESSION['login'] == $azione['mittente']) {
                    $add_chat .= '<span class="chat_msg">'.$MESSAGE['chat']['whisper']['to'].' '.gdrcd_filter('out', $azione['destinatario']).': </span>';
                    $add_chat .= '<span class="chat_msg">'.gdrcd_filter('out', $azione['testo']).'</span>';
                } elseif(($_SESSION['permessi'] >= MODERATOR) && ($PARAMETERS['mode']['spyprivaterooms'] == 'ON')) {
                    $add_chat .= '<span class="chat_msg">'.$azione['mittente'].' '.$MESSAGE['chat']['whisper']['from_to'].' '.gdrcd_filter('out', $azione['destinatario']).' </span>';
                    $add_chat .= '<span class="chat_msg">'.gdrcd_filter('out', $azione['testo']).'</span>';
                }

                break;
            case 'C':
                //visualizzazione lancio stat


                break;

            case 'M':
                //Master
                $add_chat= '<div class="chat_row_'.$azione['tipo'].'">';
                $add_chat.= '<div class="ora_ms">'.gdrcd_format_time($azione['ora']).' Master Screen</div>';
                $exp = explode('<br>',$azione['testo']);
                $add_chat.= '<span class="chat_master">'.gdrcd_chatme($_SESSION['login'],  (gdrcd_bbcoder(gdrcd_filter('out',$exp[0])))).'</span><br><br>'.$exp[1];
                $add_chat.= '</div>';
                break;
            case 'N':
                //PNG
                $add_chat= '<div class="chat_row_'.$azione['tipo'].'">';
                $add_chat.= '<span class="chat_time">'.gdrcd_format_time($azione['ora']).'</span>';
                $add_chat.= '<span class="chat_name">'.$azione['destinatario'].'</span> ';
                $add_chat.= '<span class="chat_msg">'.gdrcd_chatcolor(gdrcd_filter('out',$azione['testo'])).'</span>';
                $add_chat.= '</div>';
                break;

        }
        $add_chat .= '<br style="clear:both;" />';
        echo $add_chat;

    }

    $chat_id = gdrcd_filter("num",$_GET['dir']);
    //$conta_azioni=gdrcd_query("SELECT count(*) as conta from chat where stanza = '{$chat_id}' ");
    $last_message = $_SESSION['last_message'];
    if(empty($last_message)) $last_message = 0;
    $conta_azioni=gdrcd_query("SELECT count(*) as conta 
                        FROM chat
						INNER JOIN mappa ON mappa.id = chat.stanza
						LEFT JOIN personaggio ON personaggio.nome = chat.mittente
where stanza = '{$chat_id}'	   AND chat.ora > IFNULL(mappa.ora_prenotazione, '0000-00-00 00:00:00') AND DATE_SUB(NOW(), INTERVAL 24 HOUR) < ora");

    ?>


</div>
<input type="hidden" id="countmessages" value="<?=$conta_azioni['conta']?>">