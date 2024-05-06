<?php

$arrayReturn = array();

 switch ($_REQUEST['op']) {
    case 'azione':



        break;

    case 'check_chat':
        $last_message = $_SESSION['last_message'];
        if(empty($last_message)) $last_message = 0;

        $conta_azioni=gdrcd_query("SELECT count(*) as conta FROM chat
						INNER JOIN mappa ON mappa.id = chat.stanza
						LEFT JOIN personaggio ON personaggio.nome = chat.mittente where stanza = '{$_SESSION['luogo']}' 
	 AND chat.ora > IFNULL(mappa.ora_prenotazione, '0000-00-00 00:00:00') AND DATE_SUB(NOW(), INTERVAL 4 HOUR) < ora
	 
	 ");

        $arrayReturn['esito'] = $conta_azioni['conta'];

        break;

    case 'send_action':

        $tipo=gdrcd_filter("in",$_REQUEST['tipo']);
        $testo=gdrcd_filter("in",$_REQUEST['testo']);
        $tag=gdrcd_filter("in",$_REQUEST['tag']);
        $primoCarattere = substr($testo, 0, 1);


        if(($primoCarattere=="*")&&($_SESSION['permessi'] )){
            $tipo='H';
        }

        switch ($tipo){
            case 'P':
                // invia parlato
                 inviaParlato($testo, $tag);
                break;
            case 'A':
                // invia azione
                inviaAzione($testo, $tag);
                break;
            case 'S':
                // sussurro
                 inviaSussurro($testo, $tag);
                break;
            case 'M':
                // azione master
                inviaMaster($testo);
                break;
            case 'N':
                // azione png
                inviaPNG($testo, $tag);
                break;
/*

            case '5':
                $chat->invita($tag);
                break;
*/
        }
/*
        break;
     case 'invio_stat':
         $forma=gdrcd_filter("in",$_REQUEST['forma']);
         $id_stats=gdrcd_filter("in",$_REQUEST['id_stats']);
         $dice=gdrcd_filter("in",$_REQUEST['dice']);
         $id_item=gdrcd_filter("in",$_REQUEST['id_item']);
         $number_item=gdrcd_filter("in",$_REQUEST['number_item']);
         $locationValue = gdrcd_filter("in",$_REQUEST['location']);
         if($id_stats!="no_stats"){
             $chat->inviaStatistiche($forma, $id_stats, $dice);
         }elseif($dice!="no_dice" && $id_stats=="no_stats"){
             $chat->inviaDado($dice);
         }else if($id_item!="no_item"){
             $chat->inviaOggetto($id_item, $number_item);
         }
         break;
     case 'get_max_number':

         $id=gdrcd_filter("in",$_REQUEST['id_item']);


         $arrayReturn['esito'] = $chat->contaOggetti($id);;


         break;


*/






}




echo json_encode($arrayReturn);
?>