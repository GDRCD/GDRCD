<?php

$arrayReturn = array();

 switch ($_REQUEST['op']) {
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


            case '5':
                invita($tag);
                break;
            case '6':
                Leave($tag);
                break;
            case '7':
                Elenco();
                break;

        }

        break;
     case 'take_action':
        if(($PARAMETERS['mode']['skillsystem'] == 'ON') || ($PARAMETERS['mode']['dices'] == 'ON')) {
             $abilita=gdrcd_filter("in", $_POST['id_ab']);
             $stat=gdrcd_filter("in", $_POST['id_stats']);
             $dado=gdrcd_filter("in", $_POST['dice']);
             $oggetto=gdrcd_filter("in", $_POST['id_item']);

             if ((gdrcd_filter('get', $_POST['id_ab']) != 'no_skill') && !empty($_POST['id_ab'])) {
                inviaAbilita($abilita);
             }elseif( (gdrcd_filter('get', $_POST['id_stats']) != 'no_stats') && (gdrcd_filter('get', $_POST['dice']) != 'no_dice') && !empty($_POST['id_stats']) ) {
                inviaStatistica($stat,$dado);
            }elseif( (gdrcd_filter('get', $_POST['dice']) != 'no_dice') && !empty($_POST['dice']) ){
                 inviaDado($dado);
             }
             elseif( ($oggetto != 'no_item') && !empty($oggetto) ) {
                 inviaOggetto($oggetto);
             }
         }
        break;
}




echo json_encode($arrayReturn);
?>