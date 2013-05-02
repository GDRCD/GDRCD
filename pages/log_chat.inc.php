<div class="pagina_gestione_razze">
<?php /*HELP: */ 


/*Controllo permessi utente*/
if (($_SESSION['permessi']<MODERATOR)||($PARAMETERS['mode']['spymessages']!='ON')){
    echo '<div class="error">'.gdrcd_filter('out',$MESSAGE['error']['not_allowed']).'</div>';
} else { ?>



<!-- Titolo della pagina -->
<div class="page_title">
   <h2><?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['log']['chat']['page_name']); ?></h2>
</div>



<!-- Corpo della pagina -->
<div class="page_body">
 

<?php /*Form di scelta del log (visualizzazione di base)*/
   if ((isset($_POST['op'])===FALSE)&&(isset($_REQUEST['op'])===FALSE)) { ?>
	 
    <!-- Form di inserimento/modifica -->
    <div class="panels_box">
	<div class="form_gestione">
    <form action="main.php?page=log_chat"
	      method="post">
		  <?php
		        $result=gdrcd_query("SELECT nome FROM personaggio ORDER BY nome", 'result'); ?>
		  <div class='form_label'>
             <?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['log']['chat']['log_by_user']); ?>
          </div>
		  <div class='form_field'>
	         <select name="pg">
			   <?php while($row=gdrcd_query($result, 'fetch')){?>
			    <option value="<?php echo gdrcd_filter('out',$row['nome']); ?>">
			       <?php echo  gdrcd_filter('out',$row['nome']); ?>
				</option>
			   <?php }//while 
			   
					gdrcd_query($result, 'free');
			   ?>
			 </select>
		  </div>
		  <!-- bottoni -->
		  <div class='form_submit'>  
              <input type="hidden" 
			         value="view_user" 
					 name="op" />
			  <input type="submit" 
			         value="<?php echo gdrcd_filter('out',$MESSAGE['interface']['forms']['submit']);?>" />
		  </div>

	</form>
    <form action="main.php?page=log_chat"
	      method="post">
		  <?php 
		        $result=gdrcd_query("SELECT nome, id FROM mappa WHERE chat=1 ORDER BY nome", 'result'); ?>
		  <div class='form_label'>
             <?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['log']['chat']['log_by_room']); ?>
          </div>
		  <div class='form_field'>
	         <select name="luogo">
			   <?php while($row=gdrcd_query($result, 'fetch')){?>
			    <option value="<?php echo gdrcd_filter('out',$row['id']); ?>">
			       <?php echo  gdrcd_filter('out',$row['nome']); ?>
				</option>
			   <?php }//while 
			   
					gdrcd_query($result, 'free');
			   ?>
			 </select>
		  </div>
          <div class='form_label'>
              <?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['log']['chat']['begin']); ?>
          </div>
		  <div class='form_field'>
			  <!-- Giorno -->
			  <select name="day_b" class="day">
				 <?php for($i=1; $i<=31; $i++){?>
			     <option value="<?php echo $i;?>"><?php echo $i;?></option>
				 <?php }//for ?> 
			  </select>
			  <!-- Mese -->
		      <select name="month_b" class="month">
			     <?php for($i=1; $i<=12; $i++){?>
			     <option value="<?php echo $i;?>"><?php echo $i;?></option>
			     <?php }//for ?> 
			  </select>
			  <!-- Anno -->
			  <select name="year_b" class="year">
			     <?php for($i=2010; $i<=strftime('%Y')+20; $i++){?>
			     <option value="<?php echo $i;?>"><?php echo $i;?></option>
			     <?php }//for ?> 
			  </select> - 
			  <!-- Ora -->
			  <select name="hour_b" class="month">
			     <?php for($i=0; $i<=23; $i++){?>
			     <option value="<?php echo $i;?>"><?php echo sprintf('%02s', $i); ?></option>
			     <?php }//for ?> 
			  </select>:
			  <!-- Minuto -->
  			  <select name="minut_b" class="month">
			     <?php for($i=0; $i<=60; $i+=5){?>
			     <option value="<?php echo $i;?>"><?php echo sprintf('%02s', $i); ?></option>
			     <?php }//for ?> 
			  </select>
		  </div>
		            <div class='form_label'>
              <?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['log']['chat']['begin']); ?>
          </div>
		  <div class='form_field'>
			  <!-- Giorno -->
			  <select name="day_e" class="day">
				 <?php for($i=1; $i<=31; $i++){?>
			     <option value="<?php echo $i;?>"><?php echo $i;?></option>
				 <?php }//for ?> 
			  </select>
			  <!-- Mese -->
		      <select name="month_e" class="month">
			     <?php for($i=1; $i<=12; $i++){?>
			     <option value="<?php echo $i;?>"><?php echo $i;?></option>
			     <?php }//for ?> 
			  </select>
			  <!-- Anno -->
			  <select name="year_e" class="year">
			     <?php for($i=2010; $i<=strftime('%Y')+20; $i++){?>
			     <option value="<?php echo $i;?>"><?php echo $i;?></option>
			     <?php }//for ?> 
			  </select> - 
			  <!-- Ora -->
			  <select name="hour_e" class="month">
			     <?php for($i=0; $i<=23; $i++){?>
			     <option value="<?php echo $i;?>"><?php echo sprintf('%02s', $i); ?></option>
			     <?php }//for ?> 
			  </select>:
			  <!-- Minuto -->
  			  <select name="minut_e" class="month">
			     <?php for($i=0; $i<=60; $i+=5){?>
			     <option value="<?php echo $i;?>"><?php echo sprintf('%02s', $i); ?></option>
			     <?php }//for ?> 
			  </select>
		  </div>
		  <!-- bottoni -->
		  <div class='form_submit'>  
              <input type="hidden" 
			         value="view_date" 
					 name="op" />
			  <input type="submit" 
			         value="<?php echo gdrcd_filter('out',$MESSAGE['interface']['forms']['submit']);?>" />
		  </div>

	</form>
	</div>
    </div>
<?php }//if ?>



<?php //*Elenco log*/
        
	if (isset($_REQUEST['op'])=='view_user'){
	//Determinazione pagina (paginazione)
    $pagebegin=(int)$_REQUEST['offset']*$PARAMETERS['settings']['records_per_page'];
	$pageend=$PARAMETERS['settings']['records_per_page'];
	//Conteggio record totali 
	$record_globale=gdrcd_query("SELECT COUNT(*) FROM chat WHERE mittente = '".gdrcd_filter('get',$_REQUEST['pg'])."'");
	$totaleresults=$record_globale['COUNT(*)'];
	//Lettura record
	$result=gdrcd_query("SELECT chat.destinatario, chat.tipo, chat.ora, chat.testo, mappa.nome FROM chat JOIN mappa on chat.stanza=mappa.id WHERE chat.mittente = '".$_REQUEST['pg']."' ORDER BY ora DESC LIMIT ".$pagebegin.", ".$pageend."", 'result'); 
    $numresults=gdrcd_query($result, 'num_rows');
        
	/* Se esistono record */   
	if ($numresults>0){ ?>
       <!-- Elenco dei record paginato -->
       <div class="elenco_record_gestione">
       <table>
	      <!-- Intestazione tabella -->
          <tr>
		     <td class="casella_titolo"><div class="titoli_elenco">
			 <?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['log']['chat']['room']);?>
			 </div></td>
			 <td class="casella_titolo"><div class="titoli_elenco">
			 <?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['log']['chat']['date']); ?>
			 </div></td>
		     <td class="casella_titolo"><div class="titoli_elenco">
			 <?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['log']['chat']['text']); ?>
			 </div></td>
		  </tr>
		  <!-- Record -->
          <?php while ($row=gdrcd_query($result, 'fetch')){ ?>
	      <tr class="risultati_elenco_record_gestione">
             <td class="casella_elemento"><div class="elementi_elenco">
			 <?php echo gdrcd_filter('out',$row['nome']); ?>
			 </div></td>
		     <td class="casella_elemento"><div class="elementi_elenco">
			 <?php echo gdrcd_format_date($row['ora']).' '.gdrcd_format_time($row['ora']);?>
			 </div></td>
			 <td class="casella_elemento"><div class="elementi_elenco">
			 <?php if (empty($row['destinatario'])===FALSE){echo '(-> '.gdrcd_filter('out',$row['destinatario']).') ';}
		           echo gdrcd_filter('out',$row['testo']); ?>
			 </div></td>
          </tr>
		  <?php } //while 
		  
				gdrcd_query($result, 'free');
		  ?>
       </table>
       </div>
     <?php }//if ?>		
		
	 <!-- Paginatore elenco -->
	 <div class="pager">
       <?php if($totaleresults>$PARAMETERS['settings']['records_per_page']){
	            echo gdrcd_filter('out',$MESSAGE['interface']['pager']['pages_name']);
		        for($i=0;$i<=floor($totaleresults/$PARAMETERS['settings']['records_per_page']);$i++){ 
			       if ($i!=$_REQUEST['offset']){?>
                   <a href="main.php?page=log_chat&op=view&pg=<?php echo $_REQUEST['pg']; ?>&offset=<?php echo $i; ?>"><?php echo $i+1; ?></a>
				   <?php } else { echo ' '.($i+1).' '; }
                } //for
             }//if ?>
     </div>
	  
     <!-- link crea nuovo -->
     <div class="link_back">
        <a href="main.php?page=log_chat">
		   <?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['log']['messages']['link']['back']); ?>
		</a>
     </div>

<?php }//else ?>

<?php //*Elenco log*/
        
	if (isset($_REQUEST['op'])=='view_date'){
	//Determinazione pagina (paginazione)
    $pagebegin=(int)$_REQUEST['offset']*$PARAMETERS['settings']['records_per_page'];
	$pageend=$PARAMETERS['settings']['records_per_page'];
	//Conteggio record totali 
	$record_globale=gdrcd_query("SELECT COUNT(*) FROM chat WHERE stanza = '".gdrcd_filter('get',$_REQUEST['luogo'])."'");
	$totaleresults=$record_globale['COUNT(*)'];
	
	//Lettura record
    $date_b=gdrcd_filter('num',$_REQUEST['year_b']).'-'.sprintf('%02s',gdrcd_filter('num',$_REQUEST['month_b'])).'-'.sprintf('%02s',gdrcd_filter('num',$_REQUEST['day_b'])).' '.sprintf('%02s',gdrcd_filter('num',$_REQUEST['hour_b'])).':'.sprintf('%02s',gdrcd_filter('num',$_REQUEST['minut_b'])).':00';
    $date_e=gdrcd_filter('num',$_REQUEST['year_e']).'-'.sprintf('%02s',gdrcd_filter('num',$_REQUEST['month_e'])).'-'.sprintf('%02s',gdrcd_filter('num',$_REQUEST['day_e'])).' '.sprintf('%02s',gdrcd_filter('num',$_REQUEST['hour_e'])).':'.sprintf('%02s',gdrcd_filter('num',$_REQUEST['minut_e'])).':00';

	$result=gdrcd_query("SELECT chat.mittente, chat.destinatario, chat.tipo, chat.ora, chat.testo FROM chat WHERE chat.stanza = '".gdrcd_filter('get',$_REQUEST['luogo'])."' AND ora >= '".$date_b."' AND ora <= '".$date_e."' ORDER BY ora DESC LIMIT ".$pagebegin.", ".$pageend."", 'result'); 
    $numresults=gdrcd_query($result, 'num_rows');
	/* Se esistono record */   
	if ($numresults>0){ ?>
       <!-- Elenco dei record paginato -->
       <div class="elenco_record_gestione">
       <table>
	      <!-- Intestazione tabella -->
          <tr>
		     <td class="casella_titolo"><div class="titoli_elenco">
			 <?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['log']['chat']['sender']);?>
			 </div></td>
			 <td class="casella_titolo"><div class="titoli_elenco">
			 <?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['log']['chat']['date']); ?>
			 </div></td>
		     <td class="casella_titolo"><div class="titoli_elenco">
			 <?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['log']['chat']['text']); ?>
			 </div></td>
		  </tr>
		  <!-- Record -->
          <?php while ($row=gdrcd_query($result, 'fetch')){ ?>
	      <tr class="risultati_elenco_record_gestione">
             <td class="casella_elemento"><div class="elementi_elenco">
			 <?php echo gdrcd_filter('out',$row['mittente']); ?>
			 </div></td>
		     <td class="casella_elemento"><div class="elementi_elenco">
			 <?php echo gdrcd_format_date($row['ora']).' '.gdrcd_format_time($row['ora']);?>
			 </div></td>
			 <td class="casella_elemento"><div class="elementi_elenco">
			 <?php if (empty($row['destinatario'])===FALSE){echo '(-> '.gdrcd_filter('out',$row['destinatario']).') ';}
		           echo gdrcd_filter('out',$row['testo']); ?>
			 </div></td>
          </tr>
		  <?php } //while 
		  
				gdrcd_query($result, 'free');
		  ?>
       </table>
       </div>
     <?php }//if ?>		
		
	 <!-- Paginatore elenco -->
	 <div class="pager">
       <?php if($totaleresults>$PARAMETERS['settings']['records_per_page']){
	            echo gdrcd_filter('out',$MESSAGE['interface']['pager']['pages_name']);
		        for($i=0;$i<=floor($totaleresults/$PARAMETERS['settings']['records_per_page']);$i++){ 
			       if ($i!=$_REQUEST['offset']){?>
                   <a href="main.php?page=log_chat&op=view&luogo=<?php echo gdrcd_filter('get',$_REQUEST['luogo']); ?>&year_b=<?php echo gdrcd_filter('num',$_REQUEST['year_b']);?>&day_b=<?php echo sprintf('%02s', gdrcd_filter('num',$_REQUEST['day_b']));?>&month_b=<?php echo sprintf('%02s', gdrcd_filter('num',$_REQUEST['month_b']));?>&hour_b=<?php echo sprintf('%02s',gdrcd_filter('num',$_REQUEST['hour_b']));?>&minut_b=<?php echo sprintf('%02s', gdrcd_filter('num',$_REQUEST['minut_b']));?>&year_e=<?php echo gdrcd_filter('num',$_REQUEST['year_e']);?>&day_e=<?php echo sprintf('%02s', gdrcd_filter('num',$_REQUEST['day_e']));?>&month_e=<?php echo sprintf('%02s', gdrcd_filter('num',$_REQUEST['month_e']));?>&hour_e=<?php echo sprintf('%02s', gdrcd_filter('num',$_REQUEST['hour_e']));?>&minut_e=<?php echo sprintf('%02s', gdrcd_filter('num',$_REQUEST['minut_e']));?>&offset=<?php echo $i; ?>"><?php echo $i+1; ?></a>
				   <?php } else { echo ' '.($i+1).' '; }
                } //for
             }//if ?>
     </div>
	  

     <!-- link crea nuovo -->
     <!--div class="link_back">
        <a href="main.php?page=log_chat">
		   <?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['log']['messages']['link']['back']); ?>
		</a>
     </div-->

<?php }//else ?>


</div>

<?php }//else (controllo permessi utente) ?>
</div><!--Pagina-->