<?php session_start();

	header('Content-Type:text/html; charset=UTF-8');
	$last_message = $_SESSION['last_message'];


	//Includio i parametri, la configurazione, la lingua e le funzioni
	require 'includes/constant_values.inc.php';
	require 'config.inc.php';
	require 'vocabulary/'.$PARAMETERS['languages']['set'].'.vocabulary.php';
	require 'includes/functions.inc.php';

	//Eseguo la connessione al database
	$handleDBConnection = gdrcd_connect();
	//Ricevo il tempo di reload
	$i_ref_time = gdrcd_filter_get($_GET['ref']);


/**********************************************************************************/
if((gdrcd_filter_get($_REQUEST['chat'])=='yes')&&(empty($_SESSION['login'])===FALSE))
{
	/*Aggiornamento chat*/
	/*Se ho inviato un azione*/
	if ((gdrcd_filter('get',$_POST['op'])=='take_action')&&(($PARAMETERS['mode']['skillsystem']=='ON')||($PARAMETERS['mode']['dices']=='ON')))
	{
		$actual_healt = gdrcd_query("SELECT salute FROM personaggio WHERE nome = '".$_SESSION['login']."'");

		if (gdrcd_filter('get',$_POST['id_ab'])!='no_skill')
		{
			if ($actual_healt['salute']>0)
			{
				$skill = gdrcd_query("SELECT nome, car FROM abilita WHERE id_abilita = ".gdrcd_filter('num',$_POST['id_ab'])." LIMIT 1");

				$car = gdrcd_query("SELECT car".gdrcd_filter('num',$skill['car'])." AS car_now FROM personaggio WHERE nome = '".$_SESSION['login']."' LIMIT 1");

				$bonus = gdrcd_query("SELECT SUM(oggetto.bonus_car".gdrcd_filter('num',$skill['car']).") as bonus FROM oggetto JOIN clgpersonaggiooggetto ON clgpersonaggiooggetto.id_oggetto=oggetto.id_oggetto WHERE clgpersonaggiooggetto.nome='".$_SESSION['login']."' AND clgpersonaggiooggetto.posizione > 1");

				$racial_bonus = gdrcd_query("SELECT bonus_car".gdrcd_filter('num',$skill['car'])." AS racial_bonus FROM razza WHERE id_razza IN (SELECT id_razza FROM personaggio WHERE nome='".$_SESSION['login']."')");

				$rank = gdrcd_query("SELECT grado FROM clgpersonaggioabilita WHERE id_abilita=".gdrcd_filter('num',$_POST['id_ab'])." AND nome='".$_SESSION['login']."' LIMIT 1");

				if ($PARAMETERS['mode']['dices']=='ON')
				{
					mt_srand((double)microtime()*1000000);
					$die = mt_rand(1,(int)$_POST['dice']);

					$chat_dice_msg =  gdrcd_filter('in', $MESSAGE['chat']['commands']['use_skills']['die']).' '.gdrcd_filter('num',$die).',';

				}
				else
				{
					$chat_dice_msg = '';
					$die = 0;
				}

				gdrcd_query("INSERT INTO chat ( stanza, imgs, mittente, destinatario, ora, tipo, testo ) VALUES (".$_SESSION['luogo'].", '".$_SESSION['sesso'].";".$_SESSION['img_razza']."', '".$_SESSION['login']."', '', NOW(), 'C', '".$_SESSION['login'].' '.gdrcd_filter('in',$MESSAGE['chat']['commands']['use_skills']['uses']).' '.gdrcd_filter('in',$skill['nome']).': '.gdrcd_filter('in',$PARAMETERS['names']['stats']['car'.$skill['car'].'']).' '.gdrcd_filter('num',$car['car_now']+$racial_bonus['racial_bonus']).', '.$chat_dice_msg.' '.gdrcd_filter('in',$MESSAGE['chat']['commands']['use_skills']['ramk']).' '.gdrcd_filter('num',$rank['grado']).', '.gdrcd_filter('in',$MESSAGE['chat']['commands']['use_skills']['items']).' '.gdrcd_filter('num',$bonus['bonus']).', '.gdrcd_filter('in',$MESSAGE['chat']['commands']['use_skills']['sum']).' '.(gdrcd_filter('num',$car['car_now']+$racial_bonus['racial_bonus'])+gdrcd_filter('num',$die)+gdrcd_filter('num',$rank['grado'])+gdrcd_filter('in',$bonus['bonus']))."')");

	   		}
	   		else
			{
	      			gdrcd_query("INSERT INTO chat ( stanza, imgs, mittente, destinatario, ora, tipo, testo ) VALUES (".$_SESSION['luogo'].", '".$_SESSION['sesso'].";".$_SESSION['img_razza']."', '".$_SESSION['login']."', '".gdrcd_capital_letter(gdrcd_filter('in', $_SESSION['login']))."', NOW(), 'S', '".
		gdrcd_filter('in',$MESSAGE['status_pg']['exausted'])."')");

			}
	/** * Tiro su caratteristica
		* @author Blancks
	*/
		}
		else if (gdrcd_filter('get', $_POST['id_stats']) != 'no_stats' && gdrcd_filter('get',$_POST['dice']) != 'no_dice')
		{
			mt_srand((double)microtime()*1000000);
			$die=mt_rand(1,gdrcd_filter('num', (int)$_POST['dice']));

			$id_stats = explode('_', $_POST['id_stats']);

			$car = gdrcd_query("SELECT car".gdrcd_filter('num',$id_stats[1])." AS car_now FROM personaggio WHERE nome = '".$_SESSION['login']."' LIMIT 1");

			$racial_bonus = gdrcd_query("SELECT bonus_car".gdrcd_filter('num',$id_stats[1])." AS racial_bonus FROM razza WHERE id_razza IN (SELECT id_razza FROM personaggio WHERE nome='".$_SESSION['login']."')");

			gdrcd_query("INSERT INTO chat ( stanza, imgs, mittente, destinatario, ora, tipo, testo ) VALUES (".$_SESSION['luogo'].", '".$_SESSION['sesso'].";".$_SESSION['img_razza']."', '".$_SESSION['login']."', '', NOW(), 'C', '".$_SESSION['login'].' '.gdrcd_filter('in',$MESSAGE['chat']['commands']['use_skills']['uses']).' '.gdrcd_filter('in',$PARAMETERS['names']['stats']['car'.$id_stats[1]]).': '.gdrcd_filter('in',$PARAMETERS['names']['stats']['car'.$id_stats[1].'']).' '.gdrcd_filter('num',$car['car_now']+$racial_bonus['racial_bonus']).', '.gdrcd_filter('in', $MESSAGE['chat']['commands']['use_skills']['die']).' '.gdrcd_filter('num',$die).', '.gdrcd_filter('in',$MESSAGE['chat']['commands']['use_skills']['sum']).' '.(gdrcd_filter('num',$car['car_now']+$racial_bonus['racial_bonus'])+gdrcd_filter('num',$die)+gdrcd_filter('num',$rank['grado'])+gdrcd_filter('in',$bonus['bonus']))."')");

		}
		else if (gdrcd_filter('get',$_POST['dice'])!='no_dice')
		{
		       	mt_srand((double)microtime()*1000000);
	   		$die=mt_rand(1,gdrcd_filter('num',$_POST['dice']));

	   		gdrcd_query("INSERT INTO chat ( stanza, imgs, mittente, destinatario, ora, tipo, testo ) VALUES (".$_SESSION['luogo'].", '".$_SESSION['sesso'].";".$_SESSION['img_razza']."', '".$_SESSION['login']."', '', NOW(), 'D', '".$_SESSION['login'].' '.gdrcd_filter('in',$MESSAGE['chat']['commands']['die']['cast']).gdrcd_filter('num',$_POST['dice']).': '.gdrcd_filter('in',$MESSAGE['chat']['commands']['die']['sum']).' '.gdrcd_filter('num',$die)."')");

    		}
    		else if (gdrcd_filter('get',$_POST['id_item'])!='no_item')
    		{
			$item=explode('-', gdrcd_filter('in',$_POST['id_item']));
			if ($item[1]==1)
			{
				$query="DELETE FROM clgpersonaggiooggetto WHERE nome ='".$_SESSION['login']."' AND id_oggetto='".gdrcd_filter('num',$item[0])."' LIMIT 1";
	    		}
	    		elseif ($item[1]>1)
	    		{
            			$query="UPDATE clgpersonaggiooggetto SET cariche = cariche -1 WHERE nome ='".$_SESSION['login']."' AND id_oggetto='".gdrcd_filter('num',$item[0])."' LIMIT 1";
			}
		 	gdrcd_query($query);

			gdrcd_query("INSERT INTO chat ( stanza, imgs, mittente, destinatario, ora, tipo, testo ) VALUES (".$_SESSION['luogo'].", '".$_SESSION['sesso'].";".$_SESSION['img_razza']."', '".$_SESSION['login']."', '', NOW(), 'O', '".$_SESSION['login'].' '.gdrcd_filter('in',$MESSAGE['chat']['commands']['die']['item']).': '.gdrcd_filter('in',$item[2])."')");
		}

	}

/*Se ho inviato un messaggio*/
	if (gdrcd_filter('get',$_POST['op'])=='new_chat_message')
	{

		$actual_healt = gdrcd_query("SELECT salute FROM personaggio WHERE nome = '".$_SESSION['login']."'");

		$chat_message=gdrcd_filter('in', gdrcd_angs($_POST['message']));
		$tag_n_beyond=gdrcd_filter('in',$_POST['tag']);
		$type=gdrcd_filter('in',$_POST['type']);
		$first_char=substr($chat_message,0,1);


    if($PARAMETERS['mode']['exp_by_chat']=='ON')
    {
      $msg_length = strlen($chat_message);
      $char_needed = gdrcd_filter('num', $PARAMETERS['settings']['exp_by_chat']['number']);
      $exp_bonus = $msg_length/$char_needed;
    }

 		if($type < "5")
 		{
 		    if(!empty($_POST['message'])){
   		    //E' un messaggio.
  		    /*Verifico il tipo di messaggio*/
     			if (($type=="4")||($first_char=="@"))
     			{ /*Sussurro*/
  				$m_type='S';
  				if($type!='4')
  				{
   	  				$dest_end = strpos(substr($chat_message, 1), "@");
        					if ($dest_end === FALSE)
        					{
  	     				/*Se il destinatario e' mal formattato lo prendo come parlato*/
           					$m_type='P';
  	  				}
  	  				else
  	  				{
           					$tag_n_beyond=gdrcd_capital_letter(substr($chat_message, 1, $dest_end));
  	    					 $chat_message=substr($chat_message, $dest_end+2);
  	  				}
  				}//if
  				if ($m_type=='S')
  				{/*Se il sussurro e' inviato correttamente*/

  	     				$r_check_dest = gdrcd_query("SELECT nome FROM personaggio WHERE DATE_ADD(ultimo_refresh, INTERVAL 2 MINUTE) > NOW() AND ultimo_luogo = ".$_SESSION['luogo']." AND nome = '".$tag_n_beyond."' LIMIT 1", 'result');

  	     				if (gdrcd_query($r_check_dest, 'num_rows') < 1)
  	     				{
              					$chat_message=$tag_n_beyond.' '.gdrcd_filter('in',$MESSAGE['chat']['whisper']['no']);
  		    				$tag_n_beyond=$_SESSION['login'];
  	    				}
  				}
  				else
  				{
  					$tag_n_beyond=$_SESSION['tag'];
  				}
     			}
    			else if($first_char == "#")
  			{ //Dado
  	   			$m_type ='C';

  				if (preg_match("/^#d+([1-9][0-9]*)$/si", $chat_message,$matches))
  				{
  		   			$nstring = $matches[1];
  		   			$die = mt_rand(1,(int)$nstring);
  		   			$chat_message = "A ".$_SESSION['login']." esce ".$die." su ".$nstring;
  				}
  				else if (preg_match("/^#([1-9][0-9]*)d+([1-9][0-9]*)$/si", $chat_message,$matches))
  				{
  					$numero = (int)$matches[1];
  					$dado = (int)$matches[2];
  					$x = 0;
  					$chat_message = "A ".$_SESSION['login']." esce ";
  					for($x = 0; $x < $numero; $x++)
  					{
  			   			$die = mt_rand(1,$dado);
  			   			$chat_message .= $die." su ".$dado.", ";
  					}
  					$chat_message = substr($chat_message, 0, -2);
  				}
  			}
     			elseif (($type=="1")||($first_char=="+"))
     			{ /*Azione*/
      				if ($actual_healt['salute']>0)
      				{
  	   				if ($first_char=="+")
  	   				{
  	   					$chat_message=substr($chat_message, 1);
  	   				}
  	  				$m_type='A';
  	   				$_SESSION['tag']=$tag_n_beyond;
  				}
  				else
  				{
  	       				$m_type='S';
  					$tag_n_beyond=$_SESSION['login'];
  					$chat_message=gdrcd_filter('in',$MESSAGE['status_pg']['exausted']);
  				}
     			}
     			elseif ((($type=="2")||($first_char=="§")||($first_char=="-")||($first_char=="*"))&&($_SESSION['permessi']>=GAMEMASTER))
     			{ /*Master*/
  				$m_type='M';
  				if(($first_char=="§")||($first_char=="-"))
  				{
  					$chat_message=substr($chat_message, 1);
  				}
  				if($first_char=="*")
  				{
  					$chat_message=substr($chat_message, 1);
  					$m_type='I';
  				}
     			}
     			elseif (($type=="3")&&($_SESSION['permessi']>=GAMEMASTER))
     			{ /*PNG*/
  				$m_type='N';
  				$_SESSION['tag']=$tag_n_beyond;
     			}
     			else if (($type=="0") || (empty($type)===TRUE))
  	   		{ /*Parlato*/
  				if ($actual_healt['salute']>0)
  				{
  		  			$m_type='P';
  		   			$_SESSION['tag']=$tag_n_beyond;
  				}
  				else
  				{
  	       				$m_type='S';
  		   			$tag_n_beyond=$_SESSION['login'];
  		  			$chat_message=gdrcd_filter('in',$MESSAGE['status_pg']['exausted']);
  				}
     			} //elseif
     			/*Inserisco il messaggio*/
  			gdrcd_query("INSERT INTO chat ( stanza, imgs, mittente, destinatario, ora, tipo, testo ) VALUES (".$_SESSION['luogo'].", '".$_SESSION['sesso'].";".$_SESSION['img_razza']."', '".$_SESSION['login']."', '".gdrcd_capital_letter(gdrcd_filter('in', $tag_n_beyond))."', NOW(), '".$m_type."', '".$chat_message."')");

        if($PARAMETERS['mode']['exp_by_chat']=='ON')
        {
          if ($m_type == 'A' || $m_type == 'P' || $m_type == 'M')
          {
            gdrcd_query("UPDATE personaggio SET esperienza = esperienza + ".$exp_bonus." WHERE nome = '".$_SESSION['login']."' LIMIT 1");
          }
        }
      }//Not empty message
		}
		else
		{ //Altrimenti e' un comando di stanza privata.
			$info = gdrcd_query("SELECT invitati, nome, proprietario FROM mappa WHERE id=".$_SESSION['luogo']."");

   			$ok_command=FALSE;
   			if($info['proprietario']==$_SESSION['login'])
   			{
   				$ok_command=TRUE;
   			}
   			if(strpos($_SESSION['gilda'],$info['proprietario'])!=FALSE)
   			{
   				$ok_command=TRUE;
   			}
	   		if (($type=="5")&&($ok_command===TRUE))
	   		{ //invita
				gdrcd_query("UPDATE mappa SET invitati = '".$info['invitati'].','.gdrcd_capital_letter(strtolower(gdrcd_filter('in', $tag_n_beyond)))."' WHERE id=".$_SESSION['luogo']." LIMIT 1");

				gdrcd_query("INSERT INTO chat ( stanza, mittente, destinatario, ora, tipo, testo ) VALUES (".$_SESSION['luogo'].", 'System message', '".$_SESSION['login']."', NOW(), 'S', '".gdrcd_capital_letter(gdrcd_filter('in', $tag_n_beyond)).' '.$MESSAGE['chat']['warning']['invited']."')");

				if(empty($_POST['tag'])===FALSE)
			   	{
					gdrcd_query("INSERT INTO messaggi ( mittente, destinatario, spedito, letto, testo ) VALUES ('System message', '".gdrcd_capital_letter(gdrcd_filter('in',$_POST['tag']))."', NOW(), 0,  '".$_SESSION['login'].' '.$MESSAGE['chat']['warning']['invited_message'].' '.$info['nome']."')");
			   	}
   			}
   			else if (($type=="6")&&($ok_command===TRUE))
   			{ //caccia
       				$scaccia=str_replace(','.gdrcd_capital_letter(gdrcd_filter('in', $tag_n_beyond)), '',$info['invitati']);
	   			gdrcd_query("UPDATE mappa SET invitati = '".$scaccia."' WHERE id=".$_SESSION['luogo']." LIMIT 1");

	   			gdrcd_query("INSERT INTO chat ( stanza, mittente, destinatario, ora, tipo, testo ) VALUES (".$_SESSION['luogo'].", 'System message', '".$_SESSION['login']."', NOW(), 'S', '".gdrcd_capital_letter(gdrcd_filter('in', $tag_n_beyond)).' '.$MESSAGE['chat']['warning']['expelled']."')");

   			}
   			else if ($ok_command===TRUE)
   			{ //elenco
			       	$ospiti=str_replace(',', '', $info['invitati']);
       				gdrcd_query("INSERT INTO chat ( stanza, mittente, destinatario, ora, tipo, testo ) VALUES (".$_SESSION['luogo'].", 'System message', '".$_SESSION['login']."', NOW(), 'S', '".$MESSAGE['chat']['warning']['list'].': '.$ospiti."')");
   			}//else
 		}//else
	}
	else//if(op)
	{
		$_SESSION['tag'] = gdrcd_filter('in',$_POST['tag']);
	}

	/*Carico i nuovi messaggi*/
	if(empty($last_message)) $last_message = 0;

/** * Scorrimento dei messaggi in chat, verifico se non è stato invertito il flusso, in caso modifico l'ordinamento della query
	* @author Blancks
*/
	$typeOrder = 'ASC';

	if ($PARAMETERS['mode']['chat_from_bottom']=='ON')
	{
		$typeOrder = 'DESC';
	}

/** * Controllo per impedire il print in chat delle azioni dei precedenti proprietari di una stanza privata
	* Per stanze non private ora_prenotazione equivarrà ad un tempo sempre inferiore all'orario dell'azione inviata
	* facendo risultare quindi sempre veritiero il controllo in questo caso.

	* @author Blancks
*/
	$query= gdrcd_query("	SELECT chat.id, chat.imgs, chat.mittente, chat.destinatario, chat.tipo, chat.ora, chat.testo, personaggio.url_img_chat, mappa.ora_prenotazione
						FROM chat
						INNER JOIN mappa ON mappa.id = chat.stanza
						LEFT JOIN personaggio ON personaggio.nome = chat.mittente
						WHERE chat.id > ".$last_message." AND stanza = ".$_SESSION['luogo']." AND chat.ora > IFNULL(mappa.ora_prenotazione, '0000-00-00 00:00:00') AND DATE_SUB(NOW(), INTERVAL 30 MINUTE) < ora ORDER BY id ". $typeOrder, 'result');

	while ($row = gdrcd_query($query, 'fetch'))
	{
	  //Impedisci XSS nelle immagini
	  $row['url_img_chat']=gdrcd_filter('fullurl', $row['url_img_chat']);

		if ($PARAMETERS['mode']['chaticons']=='ON')
		{
			$icone_chat=explode(";",gdrcd_filter('out', $row['imgs']));
			$add_icon = '<span class="chat_icons"> <img class="presenti_ico" src="themes/'.$PARAMETERS['themes']['current_theme'].'/imgs/races/'.$icone_chat[1].'"><img class="presenti_ico" src="imgs/icons/testamini'.$icone_chat[0].'.png"> </span>';
		}

		switch ($row['tipo'])
		{
			case 'P':

				/**	* Fix problema visualizzazione spazi vuoti con i sussurri
					* @author eLDiabolo
				*/
				$add_chat.= '<div class="chat_row_'.$row['tipo'].'">';

				/** * Avatar di chat
					*@author Blancks
				*/
				if ($PARAMETERS['mode']['chat_avatar']=='ON' && !empty($row['url_img_chat']))
				{
					$add_chat .='<img src="'.$row['url_img_chat'].'" class="chat_avatar" style="width:'.$PARAMETERS['settings']['chat_avatar']['width'].'px; height:'.$PARAMETERS['settings']['chat_avatar']['height'].'px;" />';
				}


				$add_chat.= '<span class="chat_time">'.gdrcd_format_time($row['ora']).'</span>';

				if ($PARAMETERS['mode']['chaticons']=='ON')
				{
					$add_chat.= $add_icon;
				}

				$add_chat.= '<span class="chat_name"><a href="#" onclick="Javascript: document.getElementById(\'tag\').value=\''.$row['mittente'].'\'; document.getElementById(\'type\')[2].selected = \'1\'; document.getElementById(\'message\').focus();">'.$row['mittente'].'</a>';

				if(empty ($row['destinatario']) === FALSE )
				{
					$add_chat.= '<span class="chat_tag"> ['.gdrcd_filter('out',$row['destinatario']).']</span>';
				}

				$add_chat.=': </span> ';
				$add_chat.= '<span class="chat_msg">'.gdrcd_chatme($_SESSION['login'], gdrcd_chatcolor(gdrcd_filter('out',$row['testo']))).'</span>';

					/**	* Fix problema visualizzazione spazi vuoti con i sussurri
						* @author eLDiabolo
					*/
					if ($PARAMETERS['mode']['chat_avatar']=='ON')
						$add_chat .= '<br style="clear:both;" />';

					$add_chat.= '</div>';

			break;


			case 'A':
				/**	* Fix problema visualizzazione spazi vuoti con i sussurri
					* @author eLDiabolo
				*/
				$add_chat.= '<div class="chat_row_'.$row['tipo'].'">';

				/** * Avatar di chat
					*@author Blancks
				*/
				if ($PARAMETERS['mode']['chat_avatar']=='ON' && !empty($row['url_img_chat']))
				{
					$add_chat .='<img src="'.$row['url_img_chat'].'" class="chat_avatar" style="width:'.$PARAMETERS['settings']['chat_avatar']['width'].'px; height:'.$PARAMETERS['settings']['chat_avatar']['height'].'px;" />';
				}


				$add_chat.= '<span class="chat_time">'.gdrcd_format_time($row['ora']).'</span>';

				if ($PARAMETERS['mode']['chaticons']=='ON')
				{
					$add_chat.= $add_icon;
				}

				$add_chat.= '<span class="chat_name"><a href="#" onclick="Javascript: document.getElementById(\'tag\').value=\''.$row['mittente'].'\';  document.getElementById(\'type\')[2].selected = \'1\'; document.getElementById(\'message\').focus();">'.$row['mittente'].'</a>';

				if(empty ($row['destinatario']) === FALSE )
				{
					$add_chat.= '<span class="chat_tag"> ['.gdrcd_filter('out',$row['destinatario']).']</span>';
				}
				$add_chat.='</span> ';
				$add_chat.= '<span class="chat_msg">'.gdrcd_chatme($_SESSION['login'], gdrcd_chatcolor(gdrcd_filter('out',$row['testo']))).'</span>';

					/**	* Fix problema visualizzazione spazi vuoti con i sussurri
						* @author eLDiabolo
					*/
					if ($PARAMETERS['mode']['chat_avatar']=='ON')
						$add_chat .= '<br style="clear:both;" />';

					$add_chat.= '</div>';

			break;


			case 'S':
				if ($_SESSION['login']==$row['destinatario'])
				{
					/**	* Fix problema visualizzazione spazi vuoti con i sussurri
						* @author eLDiabolo
					*/
					$add_chat.= '<div class="chat_row_'.$row['tipo'].'">';

					$add_chat.= '<span class="chat_name">'.$row['mittente'].' '.$MESSAGE['chat']['whisper']['by'].': </span> ';
					$add_chat.= '<span class="chat_msg">'.gdrcd_filter('out',$row['testo']).'</span>';

				/**	* Fix problema visualizzazione spazi vuoti con i sussurri
					* @author eLDiabolo
				*/
					$add_chat.= '</div>';

				} else if ($_SESSION['login']==$row['mittente'])
				{
					/**	* Fix problema visualizzazione spazi vuoti con i sussurri
						* @author eLDiabolo
					*/
					$add_chat.= '<div class="chat_row_'.$row['tipo'].'">';

					$add_chat.= '<span class="chat_msg">'.$MESSAGE['chat']['whisper']['to'].' '.gdrcd_filter('out',$row['destinatario']).': </span>';
					$add_chat.= '<span class="chat_msg">'.gdrcd_filter('out',$row['testo']).'</span>';

					/**	* Fix problema visualizzazione spazi vuoti con i sussurri
						* @author eLDiabolo
					*/
					$add_chat.= '</div>';

				} else if (($_SESSION['permessi']>=MODERATOR)&&($PARAMETERS['mode']['spyprivaterooms']=='ON'))
				{
					/**	* Fix problema visualizzazione spazi vuoti con i sussurri
						* @author eLDiabolo
					*/
					$add_chat.= '<div class="chat_row_'.$row['tipo'].'">';

					$add_chat.= '<span class="chat_msg">'.$row['mittente'].' '.$MESSAGE['chat']['whisper']['from_to'].' '.gdrcd_filter('out',$row['destinatario']).' </span>';
					$add_chat.= '<span class="chat_msg">'.gdrcd_filter('out',$row['testo']).'</span>';

					/**	* Fix problema visualizzazione spazi vuoti con i sussurri
						* @author eLDiabolo
					*/
					$add_chat.= '</div>';

				}
			break;


			case 'N':
				/**	* Fix problema visualizzazione spazi vuoti con i sussurri
					* @author eLDiabolo
				*/
				$add_chat.= '<div class="chat_row_'.$row['tipo'].'">';

				$add_chat.= '<span class="chat_time">'.gdrcd_format_time($row['ora']).'</span>';
				$add_chat.= '<span class="chat_name">'.$row['destinatario'].'</span> ';
				$add_chat.= '<span class="chat_msg">'.gdrcd_chatcolor(gdrcd_filter('out',$row['testo'])).'</span>';

				/**	* Fix problema visualizzazione spazi vuoti con i sussurri
					* @author eLDiabolo
				*/
				$add_chat.= '</div>';
			break;


			case 'M':
				/**	* Fix problema visualizzazione spazi vuoti con i sussurri
					* @author eLDiabolo
				*/
				$add_chat.= '<div class="chat_row_'.$row['tipo'].'">';

				$add_chat.= '<span class="chat_master">'.gdrcd_chatme_master($_SESSION['login'], gdrcd_filter('out',$row['testo'])).'</span>';

				/**	* Fix problema visualizzazione spazi vuoti con i sussurri
					* @author eLDiabolo
				*/
				$add_chat.= '</div>';
			break;


			case 'I':
				/**	* Fix problema visualizzazione spazi vuoti con i sussurri
					* @author eLDiabolo
				*/
				$add_chat.= '<div class="chat_row_'.$row['tipo'].'">';

				$add_chat.= '<img class="chat_img" src="'.gdrcd_filter('fullurl',$row['testo']).'" />';

				/**	* Fix problema visualizzazione spazi vuoti con i sussurri
					* @author eLDiabolo
				*/
				$add_chat.= '</div>';
			break;


			case 'C':
				/**	* Fix problema visualizzazione spazi vuoti con i sussurri
					* @author eLDiabolo
				*/
				$add_chat.= '<div class="chat_row_'.$row['tipo'].'">';

				$add_chat.= '<span class="chat_time">'.gdrcd_format_time($row['ora']).'</span>';
				$add_chat.= '<span class="chat_msg">'.gdrcd_filter('out',$row['testo']).'</span>';

				/**	* Fix problema visualizzazione spazi vuoti con i sussurri
					* @author eLDiabolo
				*/
				$add_chat.= '</div>';
			break;


			case 'D':
				/**	* Fix problema visualizzazione spazi vuoti con i sussurri
					* @author eLDiabolo
				*/
				$add_chat.= '<div class="chat_row_'.$row['tipo'].'">';

				$add_chat.= '<span class="chat_time">'.gdrcd_format_time($row['ora']).'</span>';
				$add_chat.= '<span class="chat_msg">'.gdrcd_filter('out',$row['testo']).'</span>';

				/**	* Fix problema visualizzazione spazi vuoti con i sussurri
					* @author eLDiabolo
				*/
				$add_chat.= '</div>';
			break;


			case 'O':
				/**	* Fix problema visualizzazione spazi vuoti con i sussurri
					* @author eLDiabolo
				*/
				$add_chat.= '<div class="chat_row_'.$row['tipo'].'">';

				$add_chat.= '<span class="chat_time">'.gdrcd_format_time($row['ora']).'</span>';
				$add_chat.= '<span class="chat_msg">'.gdrcd_filter('out',$row['testo']).'</span>';

				/**	* Fix problema visualizzazione spazi vuoti con i sussurri
					* @author eLDiabolo
				*/
				$add_chat.= '</div>';
			break;
		}

		if ($row['id'] > (int)$last_message)
				$last_message=$row['id'];

	}
	gdrcd_query($query, 'free');
	$_SESSION['last_message']=$last_message;
}//if
/******************************************************************************************/
?>
<html>
<head>

  <?php
if(gdrcd_filter('get',$_REQUEST['chat'])=='yes')
{
	echo '<script type="text/javascript"> function echoChat(){';

	/** * Gestione dell'ordinamento
		* @author Blancks
	*/
	if ($PARAMETERS['mode']['chat_from_bottom']=='OFF')
	{
		echo 'parent.document.getElementById(\'pagina_chat\').innerHTML+= '.json_encode((string)$add_chat).';';
		echo 'scrolling = parent.document.getElementById(\'pagina_chat\').scrollHeight;';

	}
	elseif ($PARAMETERS['mode']['chat_from_bottom']=='ON')
	{
		echo 'parent.document.getElementById(\'pagina_chat\').innerHTML= '.json_encode((string)$add_chat).'+parent.document.getElementById(\'pagina_chat\').innerHTML;';
		echo 'scrolling = 0;';
	}


	/** * Gestione intelligente della scrollbar
		* Forza lo scroll solo quando ci sono nuovi messaggi
		* @author Blancks
	*/
	if (!empty($add_chat))
			echo 'parent.document.getElementById(\'pagina_chat\').scrollTop = scrolling;';


	if ((gdrcd_filter('get',$_POST['op'])=='take_action')||(gdrcd_filter('get',$_POST['op'])=='new_chat_message'))
	{
      		if($PARAMETERS['mode']['skillsystem']=='ON')
      		{
         		echo 'parent.document.getElementById(\'chat_form_actions\').reset();';
     		}
     		echo 'parent.document.getElementById(\'chat_form_messages\').reset();
	         parent.document.getElementById(\'chat_form_messages\').elements["tag"].value=\''.$_SESSION["tag"].'\';';
	}//if
	echo '}</script>';
}
?>
   <!--meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"-->
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
   <meta http-equiv="refresh" content="<?php echo $i_ref_time; ?>">

   <link rel="stylesheet" href="../themes/<?php echo $PARAMETERS['themes']['current_theme'];?>/presenti.css" TYPE="text/css">
   <link rel="stylesheet" href="../themes/<?php echo $PARAMETERS['themes']['current_theme'];?>/main.css" TYPE="text/css">
   <link rel="stylesheet" href="../themes/<?php echo $PARAMETERS['themes']['current_theme'];?>/chat.css" TYPE="text/css">
</head>
<body class="transparent_body" <?php if(gdrcd_filter('get',$_REQUEST['chat'])=='yes'){ echo 'onLoad="echoChat();"';} ?> >
<?php
    //controlla sessione
	//controlla esilio
?>
