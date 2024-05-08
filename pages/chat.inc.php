<?php 
	//require '../ref_header.inc.php';
	//require '../footer.inc.php';
include_once('../header.inc.php');

switch (gdrcd_filter_get($_REQUEST['op'])) {

    case 'send_action': // Salvataggio modifiche
    case 'check_chat':
    case 'take_action':


        include('chat/save.inc.php');
        break;


}


?>