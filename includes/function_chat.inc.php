<?php
/**
 * Funzioni di core relative alla chat
 */
function inviaParlato($testo, $tag){
    settaTag($tag);
    settaTipo('P');
    gdrcd_query("INSERT INTO chat ( stanza, imgs, mittente, destinatario, ora, tipo, testo) VALUES (".$_SESSION['luogo'].", '".$_SESSION['sesso'].";".$_SESSION['img_razza']."', '".$_SESSION['login']."', '".gdrcd_capital_letter(gdrcd_filter('in', $tag))."', NOW(), 'P', '{$testo}')");
}
function inviaAzione($testo, $tag){
    settaTag($tag);
    settaTipo('A');
    gdrcd_query("INSERT INTO chat ( stanza, imgs, mittente, destinatario, ora, tipo, testo) VALUES (".$_SESSION['luogo'].", '".$_SESSION['sesso'].";".$_SESSION['img_razza']."', '".$_SESSION['login']."', '".gdrcd_capital_letter(gdrcd_filter('in', $tag))."', NOW(), 'A', '{$testo}')");
}

function chat_name($id)
{
    $chat_name = gdrcd_query("SELECT nome FROM mappa WHERE id = {$id} ");

    return $chat_name['nome'];
}

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
function inviaMaster($testo){

    settaTipo('M');
    gdrcd_query("INSERT INTO chat ( stanza, imgs, mittente, destinatario, ora, tipo, testo) VALUES (".$_SESSION['luogo'].", '".$_SESSION['sesso'].";".$_SESSION['img_razza']."', '".$_SESSION['login']."', '', NOW(), 'M', '{$testo}')");
}


function inviaPNG($testo, $tag){

    settaTipo('N');
    gdrcd_query("INSERT INTO chat ( stanza, imgs, mittente, destinatario, ora, tipo, testo) VALUES (".$_SESSION['luogo'].", '".$_SESSION['sesso'].";".$_SESSION['img_razza']."', '".$_SESSION['login']."', '{$tag}', NOW(), 'N', '{$testo}')");
}


    function settaTag($tag)
    {
        $_SESSION['tag'] = $tag;
    }

    function settaTipo($tipo)
    {
        $_SESSION['tipo_azione']=$tipo;
    }


