<?php /*HELP: */ ?>

<div class="pagina_forum">
<!-- Titolo della pagina -->
<div class="page_title">
   <h2><?php echo gdrcd_filter('out',$PARAMETERS['names']['forum']['plur']); ?></h2>
</div>
<!-- Box principale -->
<div class="page_body">

<?php
/* Funzione segna tutto come letto */
if($_POST['action']=='readall')
{
	$result = gdrcd_query("SELECT * FROM messaggioaraldo WHERE id_messaggio_padre = -1", 'result');
	while($row=gdrcd_query($result, 'fetch'))
	{
	$esiste = gdrcd_query("SELECT id FROM araldo_letto WHERE thread_id = ".$row['id_messaggio']." AND nome = '".$_SESSION['login']."'");
		if($esiste['id'] > 0)
		{
		}
		else
		{
		gdrcd_query("INSERT INTO araldo_letto (nome, araldo_id, thread_id) VALUES ('".$_SESSION['login']."', ".$row['id_araldo'].", ".$row['id_messaggio'].")");
		}
	}
}


 /*Inserimento messaggio o topic*/
if($_POST['op']=='insert')
{
    gdrcd_query("INSERT INTO messaggioaraldo (id_messaggio_padre, id_araldo, titolo, messaggio, autore, data_messaggio ) VALUES (".gdrcd_filter('num',$_POST['padre']).", ".gdrcd_filter('num',$_POST['araldo']).", '".gdrcd_filter('in',$_POST['titolo'])."', '".gdrcd_filter('in',$_POST['messaggio'])."', '".gdrcd_filter('in',$_SESSION['login'])."', NOW())");?>
	<div class="warning">
	   <?php echo gdrcd_filter('out',$MESSAGE['warning']['inserted']);?>
	</div>
	<div class="link_back">
       <a href="main.php?page=forum">
	      <?php echo gdrcd_filter('out',$MESSAGE['interface']['forums']['link']['back']); ?>
	   </a>
    </div>
<?php
	gdrcd_query("DELETE FROM araldo_letto WHERE thread_id = ".gdrcd_filter('num',$_POST['padre'])." AND nome != '".$_SESSION['login']."'");
	gdrcd_redirect('main.php?page=forum&op=read&what='.gdrcd_filter('num',$_POST['padre']).'&where='.gdrcd_filter('num',$_POST['araldo']));



} ?>

<?php /*Modifica messaggio o topic*/
if($_POST['op']=='edit')
{
	$row = gdrcd_query("SELECT autore, titolo, messaggio, id_messaggio_padre FROM messaggioaraldo WHERE id_messaggio=".gdrcd_filter('num',$_POST['id_messaggio'])."");


	if ($row['autore'] == $_SESSION['login'] || ($row['autore'] != $_SESSION['login'] && $_SESSION['permessi'] >= MODERATOR))
	{

		$time=strftime('%d').'/'.strftime('%m').'/'.strftime('%Y').' '.strftime('%H:%M');
		
		gdrcd_query("UPDATE messaggioaraldo SET messaggio = '".gdrcd_filter('in',$_POST['messaggio']).'\n\n\n\nEdit ('.$_SESSION['login'].'): '.$time."', titolo = '".gdrcd_filter('in',$_POST['titolo'])."' WHERE id_messaggio = ".gdrcd_filter('num',$_POST['id_messaggio'])." LIMIT 1");
	
	?>
		<div class="warning">
		   <?php echo gdrcd_filter('out',$MESSAGE['warning']['modified']);?>
		</div>
		<div class="link_back">
		   <a href="main.php?page=forum">
			  <?php echo gdrcd_filter('out',$MESSAGE['interface']['forums']['link']['back']); ?>
		   </a>
		</div>
<?php
		if ($row['id_messaggio_padre'] == -1)
		{
		gdrcd_redirect('main.php?page=forum&op=read&what='.gdrcd_filter('num',$_POST['id_messaggio']).'&where='.gdrcd_filter('num',$_POST['araldo']));
		}
		else
		{
		gdrcd_redirect('/main.php?page=forum&op=read&what='.gdrcd_filter('num',$row['id_messaggio_padre']).'&where='.gdrcd_filter('num',$_POST['araldo']));
		}

	}
	else
	{
?>
	<div class="warning">
	   Furbacchione ;-)
	</div>

<?php
	}

} ?>

<?php /*Form modifica*/
if($_REQUEST['op']=='modifica')
{
	$row = gdrcd_query("SELECT titolo, messaggio, id_messaggio_padre FROM messaggioaraldo WHERE id_messaggio=".gdrcd_filter('num',$_REQUEST['what'])."");
?>
    <div class="panels_box">
    <div class="form_gioco">
    <form action="main.php?page=forum"
          method="post">
       <?php if ($row['id_messaggio_padre']==-1){/*Se è il primo di un topic serve un titolo*/ ?>
       <div class="form_label">
          <?php echo $MESSAGE['interface']['forums']['insert']['title']; ?>
       </div>
       <div class="form_field">
          <input name="titolo" 
		         value="<?php echo gdrcd_filter('out',$row['titolo']); ?>"/>
       </div>
       <?php }//if  ?>
       <div class="form_label">
          <?php echo $MESSAGE['interface']['forums']['insert']['message']; ?>
       </div>
       <div class="form_field">
         <textarea name="messaggio" /><?php echo $row['messaggio']; ?></textarea>
       </div>
	   <div class="form_info">
          <?php echo gdrcd_filter('out',$MESSAGE['interface']['help']['bbcode']); ?>
	   </div>
       <div class="form_submit">
       <input type="hidden"
	          name="op" 
		      value="edit" />
   	   <input type="hidden"
	          name="araldo" 
		      value="<?php echo gdrcd_filter('num',$_REQUEST['where']); ?>" />
   	   <input type="hidden"
	          name="messaggio_padre" 
		      value="<?php echo gdrcd_filter('num',$row['id_messaggio_padre']); ?>" />
   	   <input type="hidden"
	          name="id_messaggio" 
		      value="<?php echo gdrcd_filter('num',$_REQUEST['what']); ?>" />
       <input type="submit"
	          name="dummy" 
		      value="<?php echo gdrcd_filter('out',$MESSAGE['interface']['forms']['submit']); ?>" />
       </div>
   </form>
   </div>
   </div>
   <div class="link_back">
       <a href="main.php?page=forum">
	      <?php echo gdrcd_filter('out',$MESSAGE['interface']['forums']['link']['topic']); ?>
	   </a>
    </div>
<?php } ?>

<?php /*Cancellatura messaggio o topic*/
if($_REQUEST['op']=='delete'){
   if((gdrcd_filter('num',$_REQUEST['padre'])==-1)&&($_SESSION['permessi']>=MODERATOR)){/*Cancello un topic da admin*/
   	gdrcd_query("DELETE FROM araldo_letto WHERE thread_id = ".gdrcd_filter('num',$_REQUEST['id_record']));
	  $query="DELETE FROM messaggioaraldo WHERE id_messaggio_padre= ".gdrcd_filter('num',$_REQUEST['id_record'])." OR id_messaggio= ".gdrcd_filter('num',$_REQUEST['id_record'])."";
	  $back='forum';
   } elseif ($_SESSION['permessi']>=MODERATOR) {/*Cancello un post da admin*/
	  $query="DELETE FROM messaggioaraldo WHERE id_messaggio_padre <> -1 AND id_messaggio = ".gdrcd_filter('num',$_REQUEST['id_record'])." LIMIT 1";
	  $back='forum&op=read&what='.gdrcd_filter('num',$_REQUEST['padre']);
   } elseif (gdrcd_filter('num',$_REQUEST['padre'])==-1) {/*Cancello un topic che l'utente ha inserito*/
      	gdrcd_query("DELETE FROM araldo_letto WHERE thread_id = ".gdrcd_filter('num',$_REQUEST['id_record']));

      $query="DELETE FROM messaggioaraldo WHERE id_messaggio_padre= ".gdrcd_filter('num',$_REQUEST['id_record'])." OR (autore = '".$_SESSION['login']."' AND id_messaggio = ".gdrcd_filter('num',$_REQUEST['id_record']).")";
	  $back='forum';
   } else {/*Cancello un post che l'utente ha inserito*/
      $query="DELETE FROM messaggioaraldo WHERE id_messaggio_padre <> -1 AND id_messaggio = ".gdrcd_filter('num',$_REQUEST['id_record'])." AND autore='".$_SESSION['login']."' LIMIT 1";
	  $back='forum&op=read&what='.gdrcd_filter('get',gdrcd_filter('num',$_REQUEST['padre']));
   }
   
   gdrcd_query($query); ?>
 	<div class="warning">
	   <?php echo gdrcd_filter('out',$MESSAGE['warning']['deleted']);?>
	</div>
	<div class="link_back">
       <a href="main.php?page=<?php echo $back; ?>">
	      <?php echo gdrcd_filter('out',$MESSAGE['interface']['forums']['link']['topic']); ?>
	   </a>
    </div>
<?php } ?>


<?php
 
/**	* Procedure messaggi importanti e chiusi
	* @author Blancks <s.rotondo90@gmail.com>
*/

if ($_SESSION['permessi'] >= MODERATOR)
{
	switch ($_POST['ops'])
	{
		case 'important':
			
			$id_record	= (int)$_POST['id_record'];
			$status_imp = (int)$_POST['status_imp'];
		
			gdrcd_query("UPDATE messaggioaraldo SET importante = $status_imp WHERE id_messaggio = $id_record")or die(mysql_error());
		
		break;
		
		case 'close':
		
			$id_record	= (int)$_POST['id_record'];
			$status_cls = (int)$_POST['status_cls'];
		
			gdrcd_query("UPDATE messaggioaraldo SET chiuso = $status_cls WHERE id_messaggio = $id_record")or die(mysql_error());
		
		break;
	}
}

/**	* Fine Procedura per topic importanti/chiusi
*/

?>


<?php /*Creazione nuovi messaggi e topic*/
if(gdrcd_filter('get',$_REQUEST['op'])=='composer'){ 
$padre=gdrcd_filter('num',$_REQUEST['what']);	
$araldo=gdrcd_filter('num',$_REQUEST['where']);

$quote=gdrcd_filter('num',$_REQUEST['quote']);
?>
<div class="panels_box">
<div class="form_gioco">
<form action="main.php?page=forum"
      method="post">
<?php if ($padre==-1){ /*Se e' il primo post di un topic serve il titolo*/?>
  <div class="form_label">
    <?php echo gdrcd_filter('out',$MESSAGE['interface']['forums']['insert']['title']); ?>
  </div>
  <div class="form_field">
    <input name="titolo" />
  </div>
<?php }  ?>
  <div class="form_label">
    <?php echo gdrcd_filter('out',$MESSAGE['interface']['forums']['insert']['message']); ?>
  </div>
  <div class="form_field">
    <textarea name="messaggio" />
<?php
if($quote){
	$query="SELECT messaggio, autore FROM messaggioaraldo WHERE id_messaggio=".$quote;
	$result=gdrcd_query($query);
	echo gdrcd_filter('out',"[quote=".$result['autore']."]".$result['messaggio']."[/quote]");
}
?>
	 </textarea>
  </div>
  <div class="form_info">
     <?php echo gdrcd_filter('out',$MESSAGE['interface']['help']['bbcode']); ?>
  </div>
  <div class="form_submit">
    <input type="hidden"
	       name="op" 
		   value="insert" />
   	<input type="hidden"
	       name="araldo" 
		   value="<?php echo $araldo; ?>" />
    <input type="hidden"
	       name="padre" 
		   value="<?php echo $padre; ?>" />
    <input type="submit"
	       name="dummy" 
		   value="<?php echo gdrcd_filter('out',$MESSAGE['interface']['forms']['submit']); ?>" />
  </div>
</form>
</div>
</div>
   <div class="link_back">
       <a href="main.php?page=forum">
	      <?php echo gdrcd_filter('out',$MESSAGE['interface']['forums']['link']['topic']); ?>
	   </a>
    </div>
<?php } ?>

<?php /*Visualizzazione topic*/
if($_REQUEST['op']=='read')
{
    $result = gdrcd_query("SELECT messaggioaraldo.id_messaggio, messaggioaraldo.id_messaggio_padre, messaggioaraldo.titolo, messaggioaraldo.messaggio, messaggioaraldo.autore, messaggioaraldo.data_messaggio, messaggioaraldo.chiuso, araldo.tipo, araldo.nome, araldo.proprietari, personaggio.url_img FROM messaggioaraldo LEFT JOIN araldo ON messaggioaraldo.id_araldo = araldo.id_araldo LEFT JOIN personaggio ON messaggioaraldo.autore = personaggio.nome WHERE (messaggioaraldo.id_messaggio_padre = ".gdrcd_filter('num',$_REQUEST['what'])." AND messaggioaraldo.id_messaggio_padre != -1) OR messaggioaraldo.id_messaggio = ".gdrcd_filter('num',$_REQUEST['what'])." ORDER BY id_messaggio_padre, data_messaggio", 'result');
    $row = gdrcd_query($result, 'fetch'); 
    
    $chiuso = $row['chiuso'];

	//Inserimento il record al pg come thread letto
	$check_letto = gdrcd_query("SELECT * FROM araldo_letto WHERE nome = '".$_SESSION['login']."' AND thread_id = ".gdrcd_filter('num',$_REQUEST['what']));
	if ($check_letto['id'] > 0)
	{
		
	}
	else
	{
		gdrcd_query("INSERT INTO araldo_letto (nome, araldo_id, thread_id) VALUES ('".$_SESSION['login']."', ".gdrcd_filter('num',$_REQUEST['where']).", ".gdrcd_filter('num',$_REQUEST['what']).")");
	}

    
/*Restrizione di accesso i forum admin e master*/
	if ((($row['tipo']==SOLORAZZA)&&($_SESSION['id_razza']!=$row['proprietari'])&&($_SESSION['permessi']<MODERATOR))||
    	(($row['tipo']==SOLOGILDA)&&(strpos($_SESSION['gilda'],'*'.$row['proprietari'].'*')===FALSE)&&($_SESSION['permessi']<MODERATOR))||
		(($row['tipo']>=SOLOMASTERS)&&($_SESSION['permessi']<GAMEMASTER))||
		(($row['tipo']>=SOLOMODERATORS)&&($_SESSION['permessi']<MODERATOR)))
	{
   		echo '<div class="error">'.gdrcd_filter('out',$MESSAGE['error']['not_allowed']).'</div>';
	}
	else 
	{ 
?>
<div class="panels_box">
    <table>
	<tr><!-- Intestazione tabella -->
    <td colspan="2"><div class="capitolo_elenco">
      <?php echo gdrcd_filter('out',$row['nome']); ?>
    </div></td>
    </tr> 
	<tr>
	<td colspan="2" class="forum_main_title">
      <div class="forum_post_title">
	      <?php echo gdrcd_filter('out',$row['titolo']); ?>
	  </div>
    </td>
	</tr>
	<tr>
	<td class="forum_main_post_author">
	     <div class="forum_post_author">
		      <a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('out',$row['autore']); ?>">
		         <?php echo gdrcd_filter('out',$row['autore']); ?>
			  </a>
              <div class="forum_avatar">
			     <img src="<?php echo gdrcd_filter('out',$row['url_img']); ?>" class="img_forum_avatar">
			  </div>
			  <div class="forum_date_small">
			     <?php  echo gdrcd_format_date($row['data_messaggio']).' '.gdrcd_format_time($row['data_messaggio']); ?>
			  </div>
		  </div>
	</td>
	<td class="forum_main_post_message">
		  <div class="forum_post_message">
<?php 
		      
				/** * Se è disponibile il plugin bbd per il trattamento del bbcode usiamo quella
					* @author Blancks
				*/
		if ($PARAMETERS['settings']['forum_bbcode']['type'] == 'bbd')
		{
			echo bbdecoder(gdrcd_filter('out',$row['messaggio']), true);
		}
		else
		{
			echo gdrcd_bbcoder(gdrcd_filter('out',$row['messaggio'])); 
		}    
?>
		  </div>
		  <div class="forum_post_modify">
<?php
		if ($chiuso == 0 || $_SESSION['permessi']>=MODERATOR)	
		{ 
?>

		  <a href="main.php?page=forum&op=composer&what=<?php echo gdrcd_filter('num',$_REQUEST['what']); ?>&where=<?php echo gdrcd_filter('num',$_REQUEST['where']); ?>"e=<?php echo $row['id_messaggio'];?>">[<?php echo gdrcd_filter('out',$MESSAGE['interface']['forums']['link']['quote']); ?>]</a>
<?php 
		} 
		
		if(($_SESSION['login']==$row['autore'] && $chiuso == 0) || ($_SESSION['permessi']>=MODERATOR))
		{ 
?>
		     <a href="main.php?page=forum&op=modifica&what=<?php echo $row['id_messaggio'];?>">[<?php echo $MESSAGE['interface']['forums']['link']['edit']; ?>]</a>
			 <a href="main.php?page=forum&op=delete&id_record=<?php echo $row['id_messaggio'];?>&padre=<?php echo $row['id_messaggio_padre'];?>">[<?php echo gdrcd_filter('out',$MESSAGE['interface']['forums']['link']['delete']); ?>]</a>
<?php 
		} 
?>
		  </div>
	</td>
	</tr>
<?php 
		while($row = gdrcd_query($result, 'fetch'))
		{ 
?>
	<tr>
	<td class="forum_other_post_author">
	      <div class="forum_post_author">
		      <a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('out',$row['autore']); ?>">
		         <?php echo gdrcd_filter('out',$row['autore']); ?>
			  </a>
			  <div class="forum_avatar">
			     <img src="<?php echo gdrcd_filter('out',$row['url_img']); ?>" class="img_forum_avatar">
			  </div>
			  <div class="forum_date_small">
			     <?php  echo gdrcd_format_date($row['data_messaggio']).' '.gdrcd_format_time($row['data_messaggio']); ?>
			  </div>
		  </div>
    </td>
	<td class="forum_other_post_message">
		  <div class="forum_post_message">
			<?php 
			
				/** * Se è disponibile il plugin bbd per il trattamento del bbcode usiamo quella
					* @author Blancks
				*/
			if ($PARAMETERS['settings']['forum_bbcode']['type'] == 'bbd')
			{
				echo bbdecoder(gdrcd_filter('out',$row['messaggio']), true);
			
			}
			else
			{
				echo gdrcd_bbcoder(gdrcd_filter('out',$row['messaggio'])); 
			} 
				
			?>
		  </div>
		  <div class="forum_post_modify">
<?php 
			if ($chiuso == 0 || $_SESSION['permessi']>=MODERATOR)
			{
?>
      <a href="main.php?page=forum&op=composer&what=<?php echo gdrcd_filter('num',$_REQUEST['what']); ?>&where=<?php echo gdrcd_filter('num',$_REQUEST['where']); ?>"e=<?php echo $row['id_messaggio'];?>">[<?php echo gdrcd_filter('out',$MESSAGE['interface']['forums']['link']['quote']); ?>]</a>
<?php
			}
	  		if(($_SESSION['login']==$row['autore'] && $row['chiuso'] == 0) || ($_SESSION['permessi']>=MODERATOR))
			{ 
?>
		     <a href="main.php?page=forum&op=modifica&what=<?php echo $row['id_messaggio'];?>">[<?php echo gdrcd_filter('out',$MESSAGE['interface']['forums']['link']['edit']); ?>]</a>
			 <a href="main.php?page=forum&op=delete&id_record=<?php echo $row['id_messaggio'];?>&padre=<?php echo $row['id_messaggio_padre'];?>">[<?php echo gdrcd_filter('out',$MESSAGE['interface']['forums']['link']['delete']); ?>]</a>
<?php 
			} 
?>
		  </div>
    </td>
	</tr>
	<?php 
		}//while 
		gdrcd_query($result, 'free');
	?>
	</table>
</div>
<?php
		if ($chiuso == 0 || $_SESSION['permessi']>=MODERATOR)
		{ 
			$padre=gdrcd_filter('num',$_REQUEST['what']);	
			$araldo=gdrcd_filter('num',$_REQUEST['where']);
?>
<div class="panels_box">
<div class="form_gioco">
<form action="main.php?page=forum"
      method="post">
  <font color="cccccc"><div class="form_label">
    Risposta rapida
  </div>
  <div class="form_field">
    <textarea name="messaggio" /></textarea>
  </div>
  <div class="form_info">
     <?php echo gdrcd_filter('out',$MESSAGE['interface']['help']['bbcode']); ?>
  </div></font>
  <div class="form_submit">
    <input type="hidden"
	       name="op" 
		   value="insert" />
   	<input type="hidden"
	       name="araldo" 
		   value="<?php echo $araldo; ?>" />
    <input type="hidden"
	       name="padre" 
		   value="<?php echo $padre; ?>" />
    <input type="submit"
	       name="dummy" 
		   value="<?php echo gdrcd_filter('out',$MESSAGE['interface']['forms']['submit']); ?>" />
  </div>
</form>
</div>
</div>

<?php 
		} //Fine risposta rapida
	}//else 
?> 
    <!-- link a fondo pagina -->
	<div class="link_back">

<?php

	if ($chiuso == 0 || $_SESSION['permessi']>=MODERATOR)
	{ 

?>
       <a href="main.php?page=forum&op=composer&what=<?php echo gdrcd_filter('num',$_REQUEST['what']); ?>&where=<?php echo gdrcd_filter('num',$_REQUEST['where']); ?>">
		   <?php echo gdrcd_filter('out',$MESSAGE['interface']['forums']['link']['new_post']); ?>
		</a><br />
<?php
	
	}
	
?>
	    <a href="main.php?page=forum&op=visit&what=<?php echo gdrcd_filter('num',$_REQUEST['where']); ?>">
		   <?php echo gdrcd_filter('out',$MESSAGE['interface']['forums']['link']['forum']); ?>
		</a><br />
	</div>

<?php 
}
?>

<?php /*Visualizzazione di base (Elenco forum)*/
if(isset($_REQUEST['op'])===FALSE){ 

/*Carico l'elenco dei forum*/
$result = gdrcd_query("SELECT id_araldo, nome, tipo, proprietari FROM araldo ORDER BY tipo, nome", 'result');

$ultimotipo=-1;?>
<!-- Elenco forum -->
<div class="elenco_esteso">
<div class="elenco_record_gioco">
<table>
<?php while($row = gdrcd_query($result, 'fetch'))
{ 
  if($row['tipo']!=$ultimotipo){/*Sono ordinati per tipo, se cambia stampo il nuovo tipo come capoverso*/ 
	$ultimotipo=$row['tipo']; ?>
  <tr><!-- Intestazione tabella -->
    <td colspan="2"><div class="capitolo_elenco">
      <?php echo gdrcd_filter('out',$PARAMETERS['names']['forum']['plur'].' '.strtolower($MESSAGE['interface']['forums']['type'][$ultimotipo])); ?>
    </div></td>
  </tr> 
  <?php } //if 
  
	$new_msg = gdrcd_query("SELECT COUNT(id) AS num FROM araldo_letto WHERE araldo_id = ".$row['id_araldo']." AND nome = '".$_SESSION['login']."';");
  
//  print_r($result2);
  $new_msg2 = gdrcd_query("SELECT COUNT(id_messaggio) AS num FROM messaggioaraldo WHERE id_araldo = ".$row['id_araldo']." AND id_messaggio_padre = -1");
  ?>
  <tr><!-- Forum della categoria -->
    <td class="forum_main_post_author">
	   <div class="forum_date_big">
	      <?php 
					if($new_msg['num'] == $new_msg2['num'])
					{
					}
					else
					{
						if ($new_msg2['num']-$new_msg['num'] == 1)
						{
								echo '1 ' . $MESSAGE['interface']['forums']['topic']['new_posts']['sing'];
						}
						else
						{
							$numero = $new_msg2['num']-$new_msg['num'];
								echo $numero. ' ' . $MESSAGE['interface']['forums']['topic']['new_posts']['plur'];
						}
					}
			?>
	   </div>
	</td>

	<td  class="casella_elemento"><div class="elementi_elenco">

	 <?php if (($row['tipo']<=PERTUTTI)||
	 (($row['tipo']==SOLORAZZA)&&($_SESSION['id_razza']==$row['proprietari']))||
     (($row['tipo']==SOLOGILDA)&&(strpos($_SESSION['gilda'],'*'.$row['proprietari'].'*')!=FALSE))||
	 (($row['tipo']==SOLOMASTERS)&&($_SESSION['permessi']>=GAMEMASTER))||
	 ($_SESSION['permessi']>=MODERATOR)){ /*Restrizione di visualizzazione solo master e admin*/ ?>
	  
	 <a href="main.php?page=forum&op=visit&what=<?php echo gdrcd_filter('out',$row['id_araldo']); ?>&name=<?php echo gdrcd_filter('out',$row['nome']); ?>">
     
	 <?php } ?>

	 <?php echo gdrcd_filter('out',$row['nome']); ?>

     <?php if (($row['tipo']<=PERTUTTI)||
	 (($row['tipo']==SOLORAZZA)&&($_SESSION['id_razza']==$row['proprietari']))||
     (($row['tipo']==SOLOGILDA)&&(strpos($_SESSION['gilda'],'*'.$row['proprietari'].'*')!=FALSE))||
	 (($row['tipo']==SOLOMASTERS)&&($_SESSION['permessi']>=GAMEMASTER))||
	 ($_SESSION['permessi']>=MODERATOR)){ /*Restrizione di visualizzazione solo master e admin*/ ?>
	 </a>
	 <?php }//if ?>
    
	
	</div></td>
  </tr>
  
 

<?php }//while 

		gdrcd_query($result, 'free');

?>
</table>
<?php //Pulsante segna tutto come letto ?>
<div class="panels_box">
<div class="form_gioco">
<form action="main.php?page=forum"
      method="post">
  <div class="form_submit">
    <input type="hidden"
	       name="action" 
		   value="readall" />
    <input type="submit"
	       name="dummy" 
		   value="Segna tutto come letto" />
  </div>
</form>
</div>
</div>

</div>
</div>
<?php } ?>

<?php /*Visualizzazione dei topic */
if(gdrcd_filter('get',$_REQUEST['op'])=='visit')
{ 
//Permessi
$row=gdrcd_query("SELECT tipo, proprietari FROM araldo WHERE id_araldo = ".gdrcd_filter('num',$_REQUEST['what'])."");

if ((($row['tipo']==SOLORAZZA)&&($_SESSION['id_razza']!=$row['proprietari'])&&($_SESSION['permessi']<MODERATOR))||
    (($row['tipo']==SOLOGILDA)&&(strpos($_SESSION['gilda'],'*'.$row['proprietari'].'*')===FALSE)&&($_SESSION['permessi']<MODERATOR))||
	(($row['tipo']>=SOLOMASTERS)&&($_SESSION['permessi']<GAMEMASTER))||
	(($row['tipo']>=SOLOMODERATORS)&&($_SESSION['permessi']<MODERATOR))){ /*Restrizione di visualizzazione solo master e admin*/ 
    echo '<div class="error">'.gdrcd_filter('out',$MESSAGE['error']['not_allowed']).'</div>'; ?>
<div class="link_back">
        <a href="main.php?page=forum">
		   <?php echo gdrcd_filter('out',$MESSAGE['interface']['forums']['link']['back']); ?>
		</a>
</div>
<?php } else { 
//Determinazione pagina (paginazione)
$pagebegin=(int)$_REQUEST['offset']*$PARAMETERS['settings']['posts_per_page'];
$pageend=$pagebegin+$PARAMETERS['settings']['posts_per_page'];

//Conteggio record totali
$record_globale = gdrcd_query("SELECT COUNT(*) FROM messaggioaraldo WHERE id_messaggio_padre = -1 AND id_araldo = ".gdrcd_filter('num',$_REQUEST['what'])."");
$totaleresults = $record_globale['COUNT(*)'];


/*Carico l'elenco dei forum*/
$result = gdrcd_query("SELECT id_messaggio, titolo, autore, data_messaggio, importante, chiuso FROM messaggioaraldo WHERE id_messaggio_padre = -1 AND id_araldo = ".gdrcd_filter('num',$_REQUEST['what'])." ORDER BY importante DESC, data_messaggio DESC LIMIT ".$pagebegin.", ".$pageend."", 'result'); 

if (gdrcd_query($result, 'num_rows') == 0){?>
<div class="warning"><?php echo gdrcd_filter('out',$MESSAGE['interface']['forums']['warning']['no_topic']); ?></div>
<?php } else { ?>
<!-- Elenco forum -->
<div class="elenco_esteso">
<div class="elenco_record_gioco">
<table>
  <tr><!-- Intestazione tabella -->
    <?php if ($_SESSION['permessi']>=MODERATOR){ ?><td colspan="4"><?php } else {?>
	<td colspan="3"><?php } ?>
	<div class="capitolo_elenco">
      <?php echo gdrcd_filter('get',$_REQUEST['nome']); ?>
    </div></td>
  </tr>
  <tr><!-- Intestazione tabella -->
    <td class="casella_titolo"><div class="capitolo_elenco">
      <?php echo gdrcd_filter('out',$MESSAGE['interface']['forums']['topic']['title']); ?>
    </div></td>
    <td class="casella_titolo"><div class="capitolo_elenco">
      <?php echo gdrcd_filter('out',$MESSAGE['interface']['forums']['topic']['author']); ?>
    </div></td>
	<td class="casella_titolo"><div class="capitolo_elenco">
      <?php echo gdrcd_filter('out',$MESSAGE['interface']['forums']['topic']['posts']); ?>
    </div></td>
	<?php if ($_SESSION['permessi']>=MODERATOR){ ?>
	<td class="casella_titolo"><div class="capitolo_elenco">
      <?php echo '&nbsp;'; ?>
    </div></td>
	<?php } ?>
  </tr>
  <?php while($row=gdrcd_query($result, 'fetch'))
  { 
		$readinfo=gdrcd_query("SELECT MAX(data_messaggio) AS latest, COUNT(*) AS replies FROM messaggioaraldo WHERE id_messaggio_padre = ".gdrcd_filter('get',$row['id_messaggio'])."");
		$lastupdate=$readinfo['latest'];
        $postsnumber=$readinfo['replies'];
        
		
		$new_msg = gdrcd_query("SELECT COUNT(id) AS num FROM araldo_letto WHERE thread_id = ".$row['id_messaggio']." AND nome = '".$_SESSION['login']."'");
		  
?>
  <tr><!-- Topic -->
    <td  class="casella_elemento"><div class="elementi_elenco"><!-- Titolo -->
      <a href="main.php?page=forum&op=read&what=<?php echo gdrcd_filter('out',$row['id_messaggio']); ?>&where=<?php echo gdrcd_filter('num',$_REQUEST['what']); ?>">
	   <div class="forum_column">
<?php 

/**	* Topic importante
	* @author Blancks <s.rotondo90@gmail.com>
*/   
	   echo ($row['importante'])? $MESSAGE['interface']['administration']['ops']['important'].': ': ''; 
	   
/**	* Fine
*/	   
?>
	   <?php echo gdrcd_filter('out',$row['titolo']); ?> 
	   
	   <?php
	   
			if($new_msg['num'] > 0)
			{
			}
	   		else
			{
				echo '(';
				echo $MESSAGE['interface']['forums']['topic']['new_posts']['plur'];
				echo ')';
			}
	   
	   ?>
	   
	   </div></a>

<?php 

/**	* Topic Chiuso
	* @author Blancks <s.rotondo90@gmail.com>
*/   
	   echo ($row['chiuso'])? '<div class="forum_column">'.$MESSAGE['interface']['forums']['topic']['title'].' '.$MESSAGE['interface']['administration']['ops']['close'].'</div>': ''; 
	   
/**	* Fine
*/	   
?>
	   <div class="forum_date_big"><?php echo gdrcd_format_date($row['data_messaggio']).' '.gdrcd_format_time($row['data_messaggio']); ?></div>
	  
    </div></td>
	<td  class="casella_elemento"><div class="elementi_elenco"><!-- Autore -->
      <a href="main.php?page=scheda&pg=<?php echo gdrcd_filter('out',$row['autore']); ?>">
	   <?php echo gdrcd_filter('out',$row['autore']); ?>
	  </a>
    </div></td>
	<td  class="casella_elemento">
	   <div class="elementi_elenco"><!-- Data -->
       <?php echo $postsnumber.' '.gdrcd_filter('out',$MESSAGE['interface']['forums']['topic']['posts']); ?>
	   <div class="forum_date_big"><?php if($postsnumber > 0) { echo gdrcd_filter('out',$MESSAGE['interface']['forums']['topic']['last_post']).':   '.gdrcd_format_date($lastupdate).' '.gdrcd_format_time($lastupdate); }?></div>
	   </div>
	</td>
	<?php if ($_SESSION['permessi']>=MODERATOR){ ?>


<?php

/**	* Topic importanti/chiusi
	* @author Blancks <s.rotondo90@gmail.com>
*/

$set_imp = ($row['importante'])? '0' : '1';
$set_cls = ($row['chiuso'])? '0' : '1';

$img_imp = ($row['importante'])? 'importante.png' : 'non_importante.png';
$img_cls = ($row['chiuso'])? 'topic_chiuso.png' : 'topic_aperto.png' ;

$label_imp = ($row['importante'])? 'important' : 'not_important';
$label_cls = ($row['chiuso'])? 'close' : 'open';

/**	* Fine
*/

?>
    <td  class="casella_titolo">
	<div class="controlli_elenco"><!-- controlli -->
	
<!-- 
/**	* Topic importanti/chiusi
	* @author Blancks <s.rotondo90@gmail.com>
*/
-->

				<!-- Importante -->
				<div class="controllo_elenco" >
				   <form action="main.php?<?php echo $_SERVER['QUERY_STRING']; ?>" method="post">
			          <input type="hidden" name="id_record" value="<?php echo $row['id_messaggio']?>" />
			          <input type="hidden" name="status_imp" value="<?php echo $set_imp; ?>" />
			          <input type="hidden" name="ops" value="important" />
						     
                      <input type="image" 
				             src="imgs/icons/<?php echo $img_imp; ?>"  
				             alt="<?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['ops'][$label_imp]); ?>" 
						     title="<?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['ops'][$label_imp]); ?>"/>
			       </form>
			    </div>
			    
			    
			    <!-- Topic Chiuso -->
			    <div class="controllo_elenco" >
				   <form action="main.php?<?php echo $_SERVER['QUERY_STRING']; ?>" method="post">
			          <input type="hidden" name="id_record" value="<?php echo $row['id_messaggio']?>" />
			          <input type="hidden" name="status_cls" value="<?php echo $set_cls; ?>" />
			          <input type="hidden" name="ops" value="close" />
						     
                      <input type="image" 
				             src="imgs/icons/<?php echo $img_cls; ?>"  
				             alt="<?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['ops'][$label_cls]); ?>" 
						     title="<?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['ops'][$label_cls]); ?>"/>
			       </form>
			    </div>
			    
<!--


/**	* Fine
*/

-->

				<!-- Elimina -->
			    <div class="controllo_elenco" >
				   <form action="main.php?page=forum" method="post">
			          <input type="hidden" name="id_record" value="<?php echo $row['id_messaggio']?>" />
                      <input type="hidden" name="padre" value="-1" />
                      <input type="hidden" name="op" value="delete" />
                      <input type="image" 
				             src="imgs/icons/erase.png" 
						     alt="<?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['ops']['erase']); ?>" 
						     title="<?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['ops']['erase']); ?>"/>
			       </form>
			    </div>
    </div>
	</td>
	<?php } ?>
  </tr> 
  <?php }//while
  
			gdrcd_query($result, 'free');

  
   ?>
</table>
</div>
</div>
<?php }//else ?>
<!-- Paginatore elenco -->
     <div class="pager">
       <?php if($totaleresults>$PARAMETERS['settings']['posts_per_page']){
	            echo gdrcd_filter('out',$MESSAGE['interface']['pager']['pages_name']);
		        for($i=0;$i<=floor($totaleresults/$PARAMETERS['settings']['posts_per_page']);$i++){ 
				   if ($i!=$_REQUEST['offset']){?>
                   <a href="main.php?page=forum&op=visit&what=<?php echo gdrcd_filter('num',$_REQUEST['what']) ?>&offset=<?php echo $i; ?>"><?php echo $i+1; ?></a>
				   <?php } else { echo ' '.($i+1).' '; }
                } //for
             }//if ?>
     </div>	 

     <!-- link crea nuovo -->
     <div class="link_back">
        <a href="main.php?page=forum&op=composer&what=-1&where=<?php echo gdrcd_filter('num',$_REQUEST['what']); ?>">
		   <?php echo gdrcd_filter('out',$MESSAGE['interface']['forums']['link']['new_topic']); ?>
		</a><br />
        <a href="main.php?page=forum">
		   <?php echo gdrcd_filter('out',$MESSAGE['interface']['forums']['link']['back']); ?>
		</a>
     </div>
<?php } //else ?>
<?php } ?>






</div><!-- Box principale -->

</div><!-- Pagina -->
