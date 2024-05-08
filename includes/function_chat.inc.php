<?php
/**
 * Funzioni di core relative alla chat
 */


/**
 * Recupera il nome della chat
 */
function chat_name($id)
{
    $chat_name = gdrcd_query("SELECT nome FROM mappa WHERE id = {$id} ");
    return $chat_name['nome'];
}
/**
 * Controllo se si tratta di una chat pubblica o privata
 */
function controlloChat($id)
{
    $query="SELECT * FROM mappa WHERE id = '{$id}'";
    $do=gdrcd_query($query);
    if($do['privata']==1){
        //la chat è privata
        $invitati=explode(",", $do['invitati']);
        if($do['proprietario']==$_SESSION['login']){
            return true;
        } elseif (in_array($_SESSION['login'],$invitati)){
            return true;
        } else{
            return false;
        }
    }else{
        //la chat è pubblica
        return true;
    }
}
/**
 * Settaggio dei SESSION per tag e tipo
 */
function settaTag($tag)
{
    $_SESSION['tag'] = $tag;
}

function settaTipo($tipo)
{
    $_SESSION['tipo_azione']=$tipo;
}

/**
 * invio e lettura  Azione e parlato
  */
function inviaAzione($testo, $tag){
    settaTag($tag);
    settaTipo('A');
    gdrcd_query("INSERT INTO chat ( stanza, imgs, mittente, destinatario, ora, tipo, testo) VALUES (".$_SESSION['luogo'].", '".$_SESSION['sesso'].";".$_SESSION['img_razza']."', '".$_SESSION['login']."', '".gdrcd_capital_letter(gdrcd_filter('in', $tag))."', NOW(), 'A', '{$testo}')");
}
function inviaParlato($testo, $tag){
    settaTag($tag);
    settaTipo('P');
    gdrcd_query("INSERT INTO chat ( stanza, imgs, mittente, destinatario, ora, tipo, testo) VALUES (".$_SESSION['luogo'].", '".$_SESSION['sesso'].";".$_SESSION['img_razza']."', '".$_SESSION['login']."', '".gdrcd_capital_letter(gdrcd_filter('in', $tag))."', NOW(), 'P', '{$testo}')");
}


function Azione($azione)
{
    $add_chat="";
    $PARAMETERS = $GLOBALS['PARAMETERS'];

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
    $add_chat .= '<br style="clear:both;" />';
    return $add_chat;
}

/**
 * Sussurri
 */
function inviaSussurro($testo, $tag)
{
    $tag=ucfirst($tag);
    switch ($tag){
        case 'Tutti':
            gdrcd_query("INSERT INTO chat ( stanza, imgs, mittente, destinatario, ora, tipo, testo) VALUES (".$_SESSION['luogo'].", '".$_SESSION['sesso'].";".$_SESSION['img_razza']."', '".$_SESSION['login']."', '{$tag}', NOW(), 'S', '{$testo}')");
            break;
        default:
            $r_check_dest = gdrcd_query("SELECT nome FROM personaggio WHERE DATE_ADD(ultimo_refresh, INTERVAL 30 MINUTE) > NOW() AND ultimo_luogo = ".$_SESSION['luogo']." AND nome = '".$tag."' LIMIT 1", 'result');
            if (gdrcd_query($r_check_dest, 'num_rows') < 1)
            {//se non c'è nessuno da notificare
                $testo=$tag.' non è presente ' ;
                $tag=$_SESSION['login'];
            }
            gdrcd_query("INSERT INTO chat ( stanza, imgs, mittente, destinatario, ora, tipo, testo) VALUES (".$_SESSION['luogo'].", '".$_SESSION['sesso'].";".$_SESSION['img_razza']."', '".$_SESSION['login']."', '{$tag}', NOW(), 'S', '{$testo}')");
            break;
    }
    settaTag('');
    settaTipo('');
}

function Sussurri($azione)
{
    $add_chat="";
    //sussurri
    $MESSAGE = $GLOBALS['MESSAGE'];
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
    $add_chat .= '<br style="clear:both;" />';
    return $add_chat;

}

/**
 * invio / lettura Master
 */
function inviaMaster($testo){

    settaTipo('M');
    gdrcd_query("INSERT INTO chat ( stanza, imgs, mittente, destinatario, ora, tipo, testo) VALUES (".$_SESSION['luogo'].", '".$_SESSION['sesso'].";".$_SESSION['img_razza']."', '".$_SESSION['login']."', '', NOW(), 'M', '{$testo}')");
}

function Master($azione)
{
//Master
    $add_chat= '<div class="chat_row_'.$azione['tipo'].'">';
    $add_chat.= '<div class="ora_ms">'.gdrcd_format_time($azione['ora']).' Master Screen</div>';
    $exp = explode('<br>',$azione['testo']);
    $add_chat.= '<span class="chat_master">'.gdrcd_chatme($_SESSION['login'],  (gdrcd_bbcoder(gdrcd_filter('out',$exp[0])))).'</span><br><br>'.$exp[1];
    $add_chat.= '</div>';
    $add_chat .= '<br style="clear:both;" />';
    return $add_chat;
}
/**
 * invio / lettura PNG
 */
function inviaPNG($testo, $tag){

    settaTipo('N');
    gdrcd_query("INSERT INTO chat ( stanza, imgs, mittente, destinatario, ora, tipo, testo) VALUES (".$_SESSION['luogo'].", '".$_SESSION['sesso'].";".$_SESSION['img_razza']."', '".$_SESSION['login']."', '{$tag}', NOW(), 'N', '{$testo}')");
}
function PNG($azione)
{
    $add_chat= '<div class="chat_row_'.$azione['tipo'].'">';
    $add_chat.= '<span class="chat_time">'.gdrcd_format_time($azione['ora']).'</span>';
    $add_chat.= '<span class="chat_name">'.$azione['destinatario'].'</span> ';
    $add_chat.= '<span class="chat_msg">'.gdrcd_chatcolor(gdrcd_filter('out',$azione['testo'])).'</span>';
    $add_chat.= '</div>';
    return $add_chat;
}

/**
 * invio Abilità
 */
function inviaAbilita($abilita)
{
    $PARAMETERS = $GLOBALS['PARAMETERS'];
    $MESSAGE = $GLOBALS['MESSAGE'];
    $actual_healt = gdrcd_query("SELECT salute FROM personaggio WHERE nome = '".$_SESSION['login']."'");


    if($actual_healt['salute'] > 0) {


        $skill = gdrcd_query("SELECT nome, car FROM abilita WHERE id_abilita = " . gdrcd_filter('num', $abilita) . " LIMIT 1");
        $car = gdrcd_query("SELECT car" . gdrcd_filter('num', $skill['car']) . " AS car_now FROM personaggio WHERE nome = '" . $_SESSION['login'] . "' LIMIT 1");
        $bonus = gdrcd_query("SELECT SUM(oggetto.bonus_car" . gdrcd_filter('num', $skill['car']) . ") as bonus FROM oggetto JOIN clgpersonaggiooggetto ON clgpersonaggiooggetto.id_oggetto=oggetto.id_oggetto WHERE clgpersonaggiooggetto.nome='" . $_SESSION['login'] . "' AND clgpersonaggiooggetto.posizione > 1");
        $racial_bonus = gdrcd_query("SELECT bonus_car" . gdrcd_filter('num', $skill['car']) . " AS racial_bonus FROM razza WHERE id_razza IN (SELECT id_razza FROM personaggio WHERE nome='" . $_SESSION['login'] . "')");
        $rank = gdrcd_query("SELECT grado FROM clgpersonaggioabilita WHERE id_abilita=" . gdrcd_filter('num', $abilita) . " AND nome='" . $_SESSION['login'] . "' LIMIT 1");
        if ($PARAMETERS['mode']['dices'] == 'ON') {
            mt_srand((double)microtime() * 1000000);
            $dice = ($_POST['dice'] != 'no_dice') ? $_POST['dice'] : '1';

            $die = mt_rand(1, (int)$dice);

            $chat_dice_msg = gdrcd_filter('in', $MESSAGE['chat']['commands']['use_skills']['die']) . ' ' . gdrcd_filter('num', $die) . ',';
        } else {
            $chat_dice_msg = '';
            $die = 0;
        }
        $car_value = gdrcd_filter('num', $car['car_now']) + gdrcd_filter('num', $racial_bonus['racial_bonus']);
        $carr = gdrcd_filter('num', $car['car_now']) + gdrcd_filter('num', $racial_bonus['racial_bonus']) + gdrcd_filter('num', $die) + gdrcd_filter('num', $rank['grado']) + gdrcd_filter('num', $bonus['bonus']);

        $testo = "{$_SESSION['login']} " . gdrcd_filter('in', $MESSAGE['chat']['commands']['use_skills']['uses']) . " " . gdrcd_filter('in', $skill['nome']) . ": " . gdrcd_filter('in', $PARAMETERS['names']['stats']['car' . $skill['car'] . '']) . " {$car_value}, {$chat_dice_msg} " . gdrcd_filter('in', $MESSAGE['chat']['commands']['use_skills']['ramk']) . " " . gdrcd_filter('num', $rank['grado']) . ", " . gdrcd_filter('in', $MESSAGE['chat']['commands']['use_skills']['items']) . " " . gdrcd_filter('num', $bonus['bonus']) . ", " . gdrcd_filter('in', $MESSAGE['chat']['commands']['use_skills']['sum']) . " {$carr}";
        gdrcd_query("INSERT INTO chat ( stanza, imgs, mittente, destinatario, ora, tipo, testo ) VALUES (" . $_SESSION['luogo'] . ", '" . $_SESSION['sesso'] . ";" . $_SESSION['img_razza'] . "', '" . $_SESSION['login'] . "', '', NOW(), 'C', '{$testo}')");


        }
    else {
       gdrcd_query("INSERT INTO chat ( stanza, imgs, mittente, destinatario, ora, tipo, testo ) VALUES (".$_SESSION['luogo'].", '".$_SESSION['sesso'].";".$_SESSION['img_razza']."', '".$_SESSION['login']."', '".gdrcd_capital_letter(gdrcd_filter('in', $_SESSION['login'])."', NOW(), 'S', '".gdrcd_filter('in', $MESSAGE['status_pg']['exausted'])."')"));
    }

}

function Statistiche($azione)
{

    $add_chat = '<span class="chat_time">'.gdrcd_format_time($azione['ora']).'</span>';
    $add_chat .= '<span class="chat_msg">'.gdrcd_filter('out', $azione['testo']).'</span>';
    $add_chat .= '<br style="clear:both;" />';
    return $add_chat;
}