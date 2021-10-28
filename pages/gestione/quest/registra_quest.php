<?php /*Form di inserimento/modifica*/
	if ((gdrcd_filter('get',$_POST['op']=='edit_quest')) ||
		(gdrcd_filter('get',$_REQUEST['op'])=='new_quest')){
	  /*Preseleziono l'operazione di inserimento*/
	  $operation='insert';
	  /*Se è stata richiesta una modifica*/
	  if ($_POST['op']=='edit_quest'){
		 /*Carico il record da modificare*/
		 $loaded_record=gdrcd_query("SELECT * FROM quest WHERE id=".gdrcd_filter('num',$_POST['id_record'])." LIMIT 1 ");
		 $parts = explode(', ', $loaded_record['partecipanti']); //array partecipanti
		 /*Cambio l'operazione in modifica*/
		 $operation='edit';
		 
	  }	?>
	 
    <!-- Form di inserimento/modifica -->
    <div class="panels_box">
    <form action="main.php?page=gestione_quest"
	      method="post"
		  class="form_gestione">
 
	<div class="page_title">
        <?php if ($_POST['op']=='edit_quest') { ?>
		<h2>Modifica quest
            <?php } else { ?>
		<h2>Inserisci nuova quest
            <?php } ?></h2>

	</div>
          
		  <div class='form_label'>
             Titolo
          </div>
          <div class='form_field'>
	         <input name="titolo" 
			        value="<?php echo gdrcd_filter('out',$loaded_record['titolo']); ?>" />
		  </div>		  
		  <div class='form_label'>
             Descrizione
          </div>
          <div class='form_field'>
	         <textarea name="descrizione" ><?php echo gdrcd_filter('out',$loaded_record['descrizione']); ?></textarea>
		  </div>


    <?php if ((($_SESSION['permessi']>=Functions::get_constant('TRAME_PERM') && $loaded_record['autore']==$_SESSION['login'])|| $_SESSION['permessi']>=Functions::get_constant('QUEST_SUPER_PERMISSION'))
        && Functions::get_constant('TRAME_ENABLED')) { ?>
		  <div class='form_label'>
            Trama di riferimento
          </div>
        <?php $query1="SELECT * FROM trama ";
			$result1=gdrcd_query($query1, 'result');
		?>		  
			<div class='form_field' >
			   <select name="trama"  />
			   <option value="0">Nessuno</option>
                <?php while ($rec=gdrcd_query($result1, 'fetch')){
				   echo '<option value="'.$rec['id'].'"';
				   if ($rec['id']==$loaded_record['trama']) { echo 'selected';}
				   echo '>'.gdrcd_filter('out',$rec['titolo']).'</option>';
			   }?>
			   </select>
			</div>
	<?php   }

		$i=0; 
		for ($i=0; $i<10; $i++) {
		$a = $i+1; 
		$pgs="SELECT * FROM clgpgquest WHERE id_quest=".gdrcd_filter('num',$_POST['id_record'])." AND nome_pg= '".$parts[$i]."' ";
		$res_pg=gdrcd_query($pgs, 'result');
			$rec_pg=gdrcd_query($res_pg, 'fetch');
		?>
		
		<div class="container_quest">
				 Partecipante n°<?php echo $a;?>
			  <div class='form_field'>
				 <input name="part<?php echo $a;?>" value="<?php echo $parts[$i];?>"/>
			  </div>
				 PX: <input name="px<?php echo $a;?>" style="width: 20%;" value="<?php echo gdrcd_filter('num',$rec_pg['px_assegnati']);?>" /><br>

				 Commento
			  <div class='form_field'>
				 <textarea name="comm<?php echo $a;?>"><?php echo gdrcd_filter('out',$rec_pg['commento']);?></textarea>
			  </div>
		</div>
        <?php } ?>
	
</div>		  
		  <!-- bottoni -->
		  <div class='form_submit'>  
			  <?php /* Se l'operazione è una modifica stampo i tasti modifica*/
			        if ($operation == "edit"){?>
			  <input type="hidden" name="id_record" value="<?php echo gdrcd_filter('num',$loaded_record['id']);?>">
			  <input type="submit" 
			         value="<?php echo gdrcd_filter('out',$MESSAGE['interface']['forms']['modify']);?>" />
			  <input type="hidden"
			         name="op"
					 value="doedit_quest">

			  <?php	} /* Altrimenti il tasto inserisci */
					  else { ?>
			  <input type="hidden"
			         name="op"
					 value="insert_quest">
			  <input type="submit" 
			         value="<?php echo gdrcd_filter('out',$MESSAGE['interface']['forms']['submit']);?>" />
			  <?php	} ?>
				  
		  </div>

	</form>
    </div>
	   <!-- Link di ritorno alla visualizzazione di base -->
	   <div class="link_back">
          <a href="main.php?page=gestione_quest">
		     Torna a gestione quest
		  </a>
       </div>
<?php }//if ?>
