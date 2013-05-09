<div class="pagina_messages_center">
    <div class="page_title">
        <h2><?php echo gdrcd_filter('out',$PARAMETERS['names']['private_message']['plur']); ?></h2>
	</div>

	<div class="page_body">
<?php /*HELP: */ 
/*Inserimento nuovo messaggio nel db*/
if (gdrcd_filter('get',$_POST['op'])=="send_message")
{
    if (gdrcd_filter('get',$_POST['multipli'])=='singolo')
	{
		$check_dest=explode(',',gdrcd_filter('get',$_POST['destinatario']));
		$destinat=trim($check_dest[0]);

		$result=gdrcd_query("SELECT nome FROM personaggio WHERE nome = '".$destinat."'", 'result');
		if ((gdrcd_query($result, 'num_rows')>0)&&(empty($destinat)===FALSE))
		{
	    	gdrcd_query("INSERT INTO messaggi (mittente, destinatario, spedito, testo) VALUES ('".$_SESSION['login']."', '".gdrcd_capital_letter(gdrcd_filter('in',$destinat))."', NOW(), '".gdrcd_filter('in',$_POST['testo'])."')");

			gdrcd_query("INSERT INTO backmessaggi (mittente, destinatario, spedito, testo) VALUES ('".$_SESSION['login']."', '".gdrcd_capital_letter(gdrcd_filter('in',$destinat))."', NOW(), '".gdrcd_filter('in',$_POST['testo'])."')");

		}//if
	}
	else if ($_POST['multipli']=='presenti')
	{
		$query = "SELECT personaggio.nome, personaggio.cognome, personaggio.permessi, personaggio.sesso, personaggio.id_razza, razza.sing_m, razza.sing_f, razza.icon, personaggio.disponibile, personaggio.online_status, personaggio.is_invisible, personaggio.ultima_mappa, personaggio.ultimo_luogo, personaggio.posizione, personaggio.ora_entrata, personaggio.ora_uscita, personaggio.ultimo_refresh, mappa.stanza_apparente, mappa.nome as luogo, mappa_click.nome as mappa FROM personaggio LEFT JOIN mappa ON personaggio.ultimo_luogo = mappa.id LEFT JOIN mappa_click ON personaggio.ultima_mappa = mappa_click.id_click LEFT JOIN razza ON personaggio.id_razza = razza.id_razza WHERE personaggio.ora_entrata > personaggio.ora_uscita AND DATE_ADD(personaggio.ultimo_refresh, INTERVAL 4 MINUTE) > NOW() ORDER BY personaggio.is_invisible, personaggio.ultima_mappa, personaggio.ultimo_luogo, personaggio.nome";
		$result = gdrcd_query($query, 'result');

		while ($record = gdrcd_query($result, 'fetch'))
		{
	    	gdrcd_query("INSERT INTO messaggi (mittente, destinatario, spedito, testo) VALUES ('".$_SESSION['login']."', '".$record['nome']."', NOW(), '".gdrcd_filter('in',$_POST['testo'])."')");
		}
	}
	else if ($_POST['multipli']=='multiplo')
	{
		$check_dest=explode(',',$_POST['destinatario']);
		$query="INSERT INTO messaggi (mittente, destinatario, spedito, testo) VALUES";
		foreach ($check_dest as $destinat)
		{
			$destinat=trim($destinat);

			$result=gdrcd_query("SELECT nome FROM personaggio WHERE nome = '".gdrcd_filter('in',$destinat)."'", 'result');
			if (gdrcd_query($result, 'num_rows')>0)
			{
	        	$query.=" ('".$_SESSION['login']."', '".gdrcd_capital_letter(gdrcd_filter('in',$destinat))."', NOW(), '".gdrcd_filter('in',$_POST['testo'])."'),";
			}
		}
		$query=substr($query,0,-1);
		gdrcd_query($query);
		/** * Bugfix: commentato la stampa della variabile $query. In caso di messaggio multiplo stampava 
	    	* l'ultima query eseguita. 
			* @author Rhllor
		*/
	} 
	else if ($_POST['multipli']=="broadcast") 
	{
		$query = gdrcd_query("SELECT nome FROM personaggio" , 'result');
	
		while($row = gdrcd_query($query , 'fetch'))
		{
			gdrcd_query("INSERT INTO messaggi (mittente, destinatario, spedito, testo) VALUES ('".$_SESSION['login']."', '" . $row['nome'] . "' , NOW(), '".gdrcd_filter('in',$_POST['testo'])."')");
		}
	
		gdrcd_query($query , 'free');
	}
	else if (is_numeric($_POST['multipli'])===TRUE) 
	{
      gdrcd_query("INSERT INTO messaggi (mittente, destinatario, spedito, testo) VALUES ('".$_SESSION['login']."', '".$_POST['multipli']."', NOW(), '".gdrcd_filter('in',$_POST['testo'])."')");

	} 
	else if (empty($_POST['destinatario'])===FALSE)
	{
		gdrcd_query("INSERT INTO messaggi (mittente, destinatario, spedito, testo) VALUES ('".$_SESSION['login']."', '".gdrcd_capital_letter(gdrcd_filter('in',$_POST['destinatario']))."', NOW(), '".gdrcd_filter('in',$_POST['testo'])."')");

		gdrcd_query("INSERT INTO backmessaggi (mittente, destinatario, spedito, testo) VALUES ('".$_SESSION['login']."', '".gdrcd_capital_letter(gdrcd_filter('in',$_POST['destinatario']))."', NOW(), '".gdrcd_filter('in',$_POST['testo'])."')");

 	}//else ?>
			<div class="warning">
				<?php echo $PARAMETERS['names']['private_message']['sing'].$MESSAGE['interface']['messages']['sent']; ?>
			</div>
			<div class="link_back">
				<a href="main.php?page=messages_center&offset=0"><?php echo $MESSAGE['interface']['messages']['go_back']; ?></a>
			</div>
<?php 
}//if ?>

<?php /*Form di composizione di un messaggio*/
if ((gdrcd_filter('get',$_POST['op'])=='send')||
    (gdrcd_filter('get',$_POST['op'])=='attach')||
    (gdrcd_filter('get',$_POST['op'])=='reply')||
    (gdrcd_filter('get',$_REQUEST['newmessage'])=='yes'))
{ 
?>
    <div class="panels_box">
        <form class="form_messaggi" 
            action="main.php?page=messages_center" 
		    method="post">

   <!-- Destinatario -->
        <div class='form_label'>
            <?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['destination']); ?>
        </div>
        <div class='form_field'>
            <input type="text" 
                list="personaggi" 
	            name="destinatario" 
                placeholder="Nome del personaggio"
			    value="<?php echo gdrcd_filter('get',$_REQUEST['reply_dest']); ?>" />
        </div>
<?php 
    if($_SESSION['permessi']>=GUILDMODERATOR)
    { 
?>
        <div class="form_field">
            <select name="multipli">
		        <option value="singolo" SELECTED> 
		            <?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['multiple']['single']); ?> 
		        </option>
		        <option value="multiplo"> 
		            <?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['multiple']['multiple']); ?> 
		        </option>
		        <option value="presenti"> 
		            <?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['multiple']['online']); ?> 
		        </option>
<?php 
        if (empty($_SESSION['gilda'])===FALSE)
        {
            $gilde=explode(',', $_SESSION['gilda']);
			foreach ($gilde as $getname)
            {
                if ((is_numeric($getname)===TRUE) && ($getname>-1))
                {
                    $row_getname=gdrcd_query("SELECT nome FROM gilda WHERE id_gilda=".$getname."");
?>
                <option value="<?php echo $getname; ?>">
		            <?php echo gdrcd_filter('out',$row_getname['nome']); ?> 
		        </option>
<?php           }
			}
        }
?>
<?php   if($_SESSION['permessi']>=MODERATOR)
        { 
?>
                <option value="broadcast">
		            <?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['multiple']['all']); ?> 
		        </option>   
<?php   } ?>
            </select>
        </div>   
<?php
    } //if 
?>
    <div class="form_info">
        <?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['multiple']['info']); ?> 
    </div>
   
    <!-- Testo -->
    <div class='form_label'>
        <?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['body']); ?>
    </div>
    <div class='form_field'>
	    <textarea type="textbox" name="testo">
<?php          
/**	* Fix per evitare le parentesi quadre vuote quando si compone un nuovo messaggio
    * @author Blancks
    */
	if (isset($_POST['testo']))
		echo "\n\n\n[".gdrcd_filter('out', trim($_POST['testo']))."]"; 
	             
 ?>
        </textarea>
    </div>

   <!-- Submit -->
   <input type="hidden" 
          name="op" 
		  value="send_message" />
   <input type="hidden" 
          name="reply_attach" 
		  value="<?php echo gdrcd_filter('get',$_POST['reply_arrach']); ?>" />

   <div class='form_submit'>
	  <input type="submit" 
	         value="<?php echo gdrcd_filter('out',$MESSAGE['interface']['forms']['submit']); ?>" />
   </div>

  </form>
</div>
<div class="link_back">
   <a href="main.php?page=messages_center&offset=0"><?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['go_back']); ?></a>
</div>
<?php } //if ?>



<?php /*Visualizzazione completa di un messaggio*/
if (gdrcd_filter('get',$_REQUEST['op'])=='read'){
	
	/** * Bugfix: correzione di un bug che permetteva la visualizzazione di messaggi non inviati all'utente 
		* semplicemente modificando l'id. Viene quindi aggiunta nella clausola where il controllo sulla proprietà
		* del messaggio. Nel caso in cui non venga trovato alcun messaggio verrà mostrato un errore. 
		* @author Rhllor
	*/
	//$result=gdrcd_query("SELECT * FROM messaggi WHERE id = ".gdrcd_filter('num',$_REQUEST['id_messaggio'])." LIMIT 1", 'result');
	$result=gdrcd_query("SELECT * FROM messaggi WHERE id = ".gdrcd_filter('num',$_REQUEST['id_messaggio'])." AND destinatario = '". $_SESSION['login'] ."' LIMIT 1", 'result');
	if (gdrcd_query($result, 'num_rows') == 0){
		?>
		<div class="warning">
			Impossibile visualizzare il messaggio richiesto, il messaggio potrebbe non esistere oppure non disponi delle autorizzazioni necessarie per poterlo visionare
		</div>
		<?php 		
	} else {
        $record=gdrcd_query($result, 'fetch');
        gdrcd_query($result, 'free');
		//Leggi id messaggio
		//Formatta messaggio
		//Bottoni Rispondi, Rispondi e allega, cancella ?>
	   <div class="read_message_box">
	      <div class="infos">
	        <span class="title"><?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['date']).": "; ?></span>
	        <span class="body">
			   <?php $quando=explode(' ',$record['spedito']); 
		             echo gdrcd_format_date($quando[0]) ?>
			</span>
			<span class="title">
			       <?php echo ' '.gdrcd_filter('out',$MESSAGE['interface']['messages']['time']).' '; ?>
			</span>
			<span class="body">
				   <?php echo gdrcd_format_time($quando[1]); ?>
		    </span>
		  </div>
		  <div class="infos">
		    <span class="title"><?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['sender']).": "; ?>
			</span>
			<span class="body"><?php echo gdrcd_filter('out',$record['mittente']);?></span>
	      </div>
		  <?php if (($record['destinatario']==$_SESSION['login'])&&($record['letto']==0)){
				    gdrcd_query("UPDATE messaggi SET letto = 1 WHERE id = ".gdrcd_filter('num',$_REQUEST['id_messaggio'])." LIMIT 1");
				}?>
	      <div class="read_message_box_text">
	        <?php echo nl2br(gdrcd_filter('out', $record['testo']));?>
	      </div>
	   
	      <div class="read_message_box_forms">
	      
	      <div class="read_message_box_form">
	      <?php
	      /** * Bugfix: correzione di un bug che causava la visualizzazione del pulsante di cancellazione a tutti
	          * gli utenti, anche a quelli che non dispongono dell'autorizzazione, come nel caso del messaggio inviato 
	          * ad "all". 
		      * @author Rhllor
			*/
		  /*if((($row['mittente']=='all')&&($_SESSION['permessi']>=MODERATOR))||
			 ((is_numeric($record['mittente'])===TRUE)&&($_SESSION['affiliato_gilda']==$record['mittente'])&&($_SESSION['permessi']>=GUILDMODERATOR))||
			 ((is_numeric($record['mittente'])===FALSE)&&($record['mittente']!='all'))){*/
		  if((($record['destinatario']=='all')&&($_SESSION['permessi']>=MODERATOR))||
		     ((is_numeric($record['destinatario'])===TRUE)&&($_SESSION['permessi']>=GUILDMODERATOR))||
		     ((is_numeric($record['destinatario'])===FALSE)&&($record['destinatario']!='all'))){
		  ?>
		          <!-- erase -->
				  <form action="main.php?page=messages_center" 
	                    method="post">
	              <input type="hidden" 
	                     name="op" 
	                     value="erase" />
				  <input type="hidden" 
	                     name="id_messaggio" 
	                     value="<?php echo gdrcd_filter('num',$_REQUEST['id_messaggio']);?>" />
				  <input type="image" 
	                     src="imgs/icons/erase.png" 
	                     value="submit" 
	                     alt="<?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['erase']); ?>" 
	                     title="<?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['erase']); ?>" />
				  </form> 
	      <?php } else { echo '&nbsp;'; } ?>
	      </div>
	
	      <div class="read_message_box_form">
		          <!-- attach -->
				  <form action="main.php?page=messages_center" 
	                    method="post">
				  <input type="hidden" 
	                     name="reply_dest" 
	                     value="<?php echo $record['mittente'];?>" />
				  <input type="hidden" 
	                     name="testo" 
	                     value="<?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['attachment'].$record['testo']);?>" />
				  <input type="hidden" 
	                     name="op" 
	                     value="attach" />
				  <input type="image" 
	                     src="imgs/icons/attach.png" 
	                     value="submit" 
	                     alt="<?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['attach']); ?>" 
	                     title="<?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['attach']); ?>"  />
				  </form>
	      </div>
	
	      <div class="read_message_box_form" >
	              <!-- reply -->
				  <form action="main.php?page=messages_center" method="post">
				  <input type="hidden" 
	                     name="reply_dest" 
	                     value="<?php echo $record['mittente'];?>" />
				  <input type="hidden" 
	                     name="op" 
	                     value="reply" />
				  <input type="image" 
	                     src="imgs/icons/reply.png" value="submit" 
	                     alt="<?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['reply']); ?>" 
	                     title="<?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['reply']); ?>" />
				  </form>
	      </div>
		        
	      </div><!-- read_message_box_form -->
	   
	   </div>
	
	   <div class="link_back">
	      <a href="main.php?page=messages_center&offset=0"><?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['go_back']); ?></a>
	   </div>
<?php 
	} // Chiudo controllo paternità messaggio
} // Chiudo controllo lettura messaggio 
?>



<?php /*Eliminazione di un messaggio*/
if ($_POST['op']=='erase')
{ 
    $id_messaggio=gdrcd_filter('num',$_POST['id_messaggio']);
   /** * Bugfix: correzione di un bug che permetteva la cancellazione di messaggi non inviati all'utente.
       * Viene quindi aggiunta nella clausola where il controllo sulla proprietà del messaggio.
       * Inoltre viene effettuato un controllo sul numero di righe cancellate. Se non è stato cancellato nulla
       * non verrà mostrato nessun messaggio ma solo il link per tornare alla schermata messaggi. 
	   * @author Rhllor
	*/
   //gdrcd_query("DELETE FROM messaggi WHERE id = ".$id_messaggio." LIMIT 1");
   gdrcd_query("DELETE FROM messaggi WHERE id = ".$id_messaggio." and destinatario = '". $_SESSION['login'] ."'   LIMIT 1");
   if (mysqli_affected_rows() > 0) {
   	?>	
		<div class="warning">
		   <?php echo gdrcd_filter('out',$PARAMETERS['names']['private_message']['sing'].$MESSAGE['interface']['messages']['erased']); ?>
		</div>
		<div class="link_back">
		   <a href="main.php?page=messages_center&offset=0"><?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['go_back']); ?></a>
		</div>
  	<?php } else { 
	  		/** * Enhancement: in caso di nessuna riga cancellata si controlla l'esistenza del messaggio,
	  		    * se esiste ed il destinatario è "all" allora può essere cancellato dagli utenti che ne hanno 
	  		    * il permesso 
			    * @author Rhllor
			*/
  			$result=gdrcd_query("SELECT destinatario FROM messaggi WHERE id = ".gdrcd_filter('num',$_REQUEST['id_messaggio'])." and ( destinatario = '". $_SESSION['login'] ."' or destinatario = 'all') LIMIT 1", 'result');
			if (gdrcd_query($result, 'num_rows') == 0){
				?>
					<div class="warning">
						Il messaggio che stai tentando di cancellare non esiste
					</div>
					<div class="link_back">
		   				<a href="main.php?page=messages_center&offset=0"><?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['go_back']); ?></a>
					</div>
				<?php 		
			} else {
        		$record=gdrcd_query($result, 'fetch');
        		gdrcd_query($result, 'free');
	  			if((($record['destinatario']=='all')&&($_SESSION['permessi']>=MODERATOR))||
				   ((is_numeric($record['destinatario'])===TRUE)&&($_SESSION['permessi']>=GUILDMODERATOR))||
				   ((is_numeric($record['destinatario'])===FALSE)&&($record['destinatario']!='all'))){
				   gdrcd_query("DELETE FROM messaggi WHERE id = ".$id_messaggio." LIMIT 1");
				   if (mysqli_affected_rows() > 0) {
				   		?>	
						<div class="warning">
						   <?php echo gdrcd_filter('out',$PARAMETERS['names']['private_message']['sing'].$MESSAGE['interface']['messages']['erased']); ?>
						</div>
						<div class="link_back">
						   <a href="main.php?page=messages_center&offset=0"><?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['go_back']); ?></a>
						</div>
				  		<?php 
				   }
		   		}
		   		else {
		   			?>
		   			<div class="warning">
						Non hai l'autorizzazione per cancellare il messaggio richiesto.
					</div>
					<div class="link_back">
		   				<a href="main.php?page=messages_center&offset=0"><?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['go_back']); ?></a>
					</div>
					<?php 
		   		}
  			}
	} 
}

/*Eliminazione di tutti i messaggi*/
if ($_REQUEST['op']=='eraseall'){ 
   gdrcd_query("DELETE FROM messaggi WHERE destinatario = '".$_SESSION['login']."' AND letto = 1");
?>	
<div class="warning">
   <?php echo gdrcd_filter('out',$PARAMETERS['names']['private_message']['sing'].$MESSAGE['interface']['messages']['erased']); ?>
</div>
<div class="link_back">
   <a href="main.php?page=messages_center&offset=0"><?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['go_back']); ?></a>
</div>
<?php } ?>


<?php /*Elenco messaggi (visualizzazione di base della pagina)*/
if ((isset($_REQUEST['op'])===FALSE)&&(isset($_REQUEST['newmessage'])===FALSE)){ 

	//Determinazione pagina
	if (isset($_REQUEST['offset'])===FALSE){$pagebegin=0;}
	else {$pagebegin=(int)$_REQUEST['offset']*$PARAMETERS['settings']['messages_per_page'];}
	$pageend=$PARAMETERS['settings']['messages_per_page'];
	//Gilde
	$extracond='';
	if (empty($_SESSION['gilda'])===FALSE){
       $gilde=explode(',', $_SESSION['gilda']);
	   foreach ($gilde as $guild){
         if ((is_numeric($guild)===TRUE) && ($guild>-1)){
                 $extracond.=" OR destinatario = '".$guild."'";
	     }//if
	   }//foreach
	}//if
	//Conteggio messaggi totali
	$record=gdrcd_query("SELECT COUNT(*) FROM messaggi WHERE destinatario = '".$_SESSION['login']."' OR destinatario = 'all'".$extracond."");
	$totaleresults=$record['COUNT(*)'];

	//Elenco messaggi paginato
	$result=gdrcd_query("SELECT * FROM messaggi WHERE destinatario = '".$_SESSION['login']."' OR destinatario = 'all'".$extracond." ORDER BY spedito DESC LIMIT ".$pagebegin.", ".$pageend."", 'result'); 
	$numresults=gdrcd_query($result, 'num_rows');
	?>

<div class="elenco_record_gioco">

<?php if ($numresults>0){ ?>
<table>

  <tr>
    <td>
	    <!-- Icona -->
	</td>
	<td>
	   <div class="titoli_elenco">
	       <?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['sender']); ?>
	   </div>
	</td>
	<td>
	   <div class="titoli_elenco">
	        <?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['date']); ?>
	   </div>
	</td>
    <td>
   	   <div class="titoli_elenco">
	        <?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['preview']); ?>
	   </div>
	</td>
	<td>
   	   <!-- Controlli -->
	</td>
  </tr>

  <?php while ($row=gdrcd_query($result, 'fetch')){?>
  <tr>
    <td>
	   <div class="elementi_elenco">
	   <?php if (($row['mittente']=='all') || (is_numeric($row['mittente'])===TRUE)){?>
                <img src="imgs/icons/mail_broad.png" class="colonna_elengo_messaggi_icon">	  
             <?php } elseif (is_numeric($row['mittente'])==TRUE) { ?>
                <img src="imgs/icons/mail_guild.png" class="colonna_elengo_messaggi_icon">	
			 <?php } else { 
	                   if($row['letto']==0){?>
                            <img src="imgs/icons/mail_new.png" class="colonna_elengo_messaggi_icon">	
			 <?php } else {?>
                            <img src="imgs/icons/mail_read.png" class="colonna_elengo_messaggi_icon">	
                       <?php }
				   }?>
	    </div>
	</td>
	<td>
	   <div class="elementi_elenco"> 
	   <?php if ($row['mittente']=='all'){
	            echo gdrcd_filter('out',$MESSAGE['interface']['messages']['broadcast']);
             } elseif (is_numeric($row['mittente'])==TRUE) {
			    echo gdrcd_filter('out',$MESSAGE['interface']['messages']['to_guild']);
			 } else {
				echo '<a href="main.php?page=scheda&pg='.$row['mittente'].'">'.$row['mittente'].'</a>'; 
			 }
	   ?>
	   </div>
	</td>
	<td>
	   <div class="elementi_elenco">
	     <?php $quando=explode (" ",$row['spedito']);
		   echo gdrcd_format_date($quando[0]).'<br/>'.gdrcd_filter('out',$MESSAGE['interface']['messages']['time']).' '. gdrcd_format_time($quando[1]); ?>
		</div>
	</td>
	<td>
	   <div class="elementi_elenco">
	     <a href="main.php?page=messages_center&op=read&id_messaggio=<?php echo $row['id']?>"><?php echo gdrcd_filter('out',substr($row['testo'],0,40)); ?>...</a>
	   </div>
	</td>
	<td>
      <div class="controlli_elenco" >
	  <div class="controllo_elenco" >
              <!-- reply -->
			  <form action="main.php?page=messages_center" method="post">
			  <input type="hidden" name="reply_dest" value="<?php echo $row['mittente'];?>" />
			  <input type="hidden" name="op" value="reply" />
			  <input type="image" src="imgs/icons/reply.png" value="submit" alt="<?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['reply']); ?>" title="<?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['reply']); ?>" />
			  </form>
	  </div>
	  <div class="controllo_elenco" >
	          <!-- attach -->
			  <form action="main.php?page=messages_center" method="post">
			  <input type="hidden" name="reply_dest" value="<?php echo $row['mittente'];?>" />
			  <input type="hidden" name="testo" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['messages']['attachment'].$row['testo']);?>" />
			  <input type="hidden" name="op" value="attach" />
			  <input type="image" src="imgs/icons/attach.png" value="submit" alt="<?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['attach']); ?>" title="<?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['attach']); ?>"  />
			  </form>
	  </div>
	  <?php if((($row['destinatario']=='all')&&($_SESSION['permessi']>=MODERATOR))||
		       ((is_numeric($row['destinatario'])===TRUE)&&($_SESSION['permessi']>=GUILDMODERATOR))||
		       ((is_numeric($row['destinatario'])===FALSE)&&($row['destinatario']!='all'))){ ?>
	  <div class="controllo_elenco" >
              <!-- erase -->
			  <form action="main.php?page=messages_center" method="post">
              <input type="hidden" name="op" value="erase" />
			  <input type="hidden" name="id_messaggio" value="<?php echo $row['id'];?>" />
			  <input type="image" src="imgs/icons/erase.png" value="submit" alt="<?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['erase']); ?>" title="<?php echo gdrcd_filter('out',$MESSAGE['interface']['messages']['erase']); ?>" />
			  </form>
	  </div>
	 
	  <?php } ?>
      </div>
  
	</td>
  </tr>
	  
  <?php $_SESSION['last_istant_message']=$row['id'];
  
  
	}//while
	
		gdrcd_query($result, 'free');
	 
        gdrcd_query("UPDATE personaggio SET ultimo_messaggio = ".$_SESSION['last_istant_message']." WHERE nome='".$_SESSION['login']."'");

?> 
  

</table>

<?php } else {
	       if($totaleresults>$PARAMETERS['settings']['messages_limit']){
			     echo '<div class="warning">'.gdrcd_filter('out',$MESSAGE['interface']['messages']['please_erase']).'</div>';
		   }
		   echo '<div class="warning">'.gdrcd_filter('out',$MESSAGE['interface']['messages']['no_message']).'</div>';
	  } ?>

<div class="pager">

<?php if($totaleresults>$PARAMETERS['settings']['messages_per_page']){
	    echo gdrcd_filter('out',$MESSAGE['interface']['pager']['pages_name']);
		for($i=0;$i<=floor($totaleresults/$PARAMETERS['settings']['messages_per_page']);$i++){ 
        if ($i!=$_REQUEST['offset']){ ?>
           <a href="main.php?page=messages_center&offset=<?php echo $i; ?>"><?php echo $i+1; ?></a>     
		   <?php } else { echo ' '.($i+1).' '; }
        }
      }	?>
</div>

</div>


<!-- link scrivi messaggio -->
<div class="link_back">
   <a href="main.php?page=messages_center&newmessage=yes">
      <?php echo $MESSAGE['interface']['messages']['new']; ?>
   </a>
</div>

<!-- link scrivi messaggio -->
<div class="link_back">
   <a href="main.php?page=messages_center&op=eraseall">
      <?php echo $MESSAGE['interface']['messages']['erase_all']; ?>
   </a>
</div>

<?php } ?>

</div><!-- page_body -->

</div><!-- Pagina -->
