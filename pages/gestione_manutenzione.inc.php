<div class="pagina_gestione_manutenzione">

<?php
/*HELP: */ 
/*Controllo permessi utente*/
if ($_SESSION['permessi']<MODERATOR)
{
    echo '<div class="error">'.gdrcd_filter('out',$MESSAGE['error']['not_allowed']).'</div>';
} 
else 
{ 
?>
<!-- Titolo della pagina -->
<div class="page_title">
   <h2><?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['maintenance']['page_name']); ?></h2>
</div>

<!-- Corpo della pagina -->
<div class="page_body">
  
<?php /*Elimina vecchi log*/
	if ($_POST['op']=='old_log')
	{ 
		if ((is_numeric($_POST['mesi'])===TRUE) &&
		   ($_POST['mesi']>=0)&&
		   ($_POST['mesi']<=12))
		{
	   		/*Eseguo l'aggiornamento*/ 
	   		gdrcd_query("DELETE FROM log WHERE DATE_SUB(NOW(), INTERVAL ".gdrcd_filter('num',$_POST['mesi'])." MONTH) > data_evento");
	   
	   		/**  * Ottimizziamo la tabella dopo averne svuotato i dati, è come se equivalesse ad una deframmentazione.
			* @author Blancks
			*/
	   		gdrcd_query("OPTIMIZE TABLE log");
?>
       			<!-- Conferma -->
	   		<div class="warning">
		  		<?php echo gdrcd_filter('out',$MESSAGE['warning']['modified']);?>
	   		</div>
<?php 
			
		} 
		else 
		{ 
?>
       			<div class="error">
		  		<?php echo gdrcd_filter('out',$MESSAGE['warning']['cant_do']);?>
			</div>
<?php 
		} 
?>
	   	<!-- Link di ritorno alla visualizzazione di base -->
	   	<div class="link_back">
           		<a href="main.php?page=gestione_manutenzione">
		      		<?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['maintenance']['link']['back']); ?>
		   	</a>
       		</div>
<?php 
	} 

	/*Elimina vecchi log*/
	if (gdrcd_filter('get',$_POST['op'])=='old_chat')
	{ 
		if ((is_numeric($_POST['mesi'])===TRUE) &&
		   ($_POST['mesi']>=0)&&
		   ($_POST['mesi']<=12))
		 {
	   		/*Eseguo l'aggiornamento*/ 
			gdrcd_query("DELETE FROM chat WHERE DATE_SUB(NOW(), INTERVAL ".gdrcd_filter('num',$_POST['mesi'])." MONTH) > ora");
			gdrcd_query("OPTIMIZE TABLE chat");
?>
       			<!-- Conferma -->
	   		<div class="warning">
		  		<?php echo gdrcd_filter('out',$MESSAGE['warning']['modified']);?>
	   		</div>
<?php
		} 
		else 
		{ 
?>
       			<div class="error">
		  		<?php echo gdrcd_filter('out',$MESSAGE['warning']['cant_do']);?>
	   		</div>
<?php 
		} 
?>
	   	<!-- Link di ritorno alla visualizzazione di base -->
	   	<div class="link_back">
           		<a href="main.php?page=gestione_manutenzione">
		      		<?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['maintenance']['link']['back']); ?>
		   	</a>
       		</div>
<?php 
	} 

	/*Elimina blacklist*/
	if (gdrcd_filter('get',$_POST['op'])=='blacklisted')
	{ 
		/*Eseguo l'aggiornamento*/ 
		gdrcd_query("DELETE FROM blacklist WHERE 1");
	   	gdrcd_query("OPTIMIZE TABLE blacklist");
?>
       		<!-- Conferma -->
	   	<div class="warning">
			<?php echo gdrcd_filter('out',$MESSAGE['warning']['modified']);?>
	   	</div>
       		<!-- Link di ritorno alla visualizzazione di base -->
	   	<div class="link_back">
           	<a href="main.php?page=gestione_manutenzione">
			<?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['maintenance']['link']['back']); ?>
		</a>
       	</div>
<?php 
	} 
	
	/*Elimina vecchi messaggi*/
	if (gdrcd_filter('get',$_POST['op'])=='old_messages')
	{ 
		if ((is_numeric($_POST['mesi'])===TRUE) &&
		   ($_POST['mesi']>=0)&&
		   ($_POST['mesi']<=12))
		{
			/*Eseguo l'aggiornamento*/ 
	   		gdrcd_query("DELETE FROM messaggi WHERE DATE_SUB(NOW(), INTERVAL ".gdrcd_filter('num',$_POST['mesi'])." MONTH) > spedito");
	   		gdrcd_query("OPTIMIZE TABLE messaggi");
	   		gdrcd_query("DELETE FROM backmessaggi WHERE DATE_SUB(NOW(), INTERVAL ".gdrcd_filter('num',$_POST['mesi'])." MONTH) > spedito");
	   		gdrcd_query("OPTIMIZE TABLE backmessaggi");
?>
       		<!-- Conferma -->
	   		<div class="warning">
		  		<?php echo gdrcd_filter('out',$MESSAGE['warning']['modified']);?>
	   		</div>
<?php 
		} 
		else 
		{ 
?>
       		<div class="error">
		  		<?php echo gdrcd_filter('out',$MESSAGE['warning']['cant_do']);?>
	   		</div>
<?php 
		} 
?>
	   	<!-- Link di ritorno alla visualizzazione di base -->
	   	<div class="link_back">
        	<a href="main.php?page=gestione_manutenzione">
		    	<?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['maintenance']['link']['back']); ?>
		   	</a>
       	</div>
<?php 
	} 

	/*Elimina personaggi che non si loggano più*/
	if (gdrcd_filter('get',$_POST['op'])=='deleted')
	{ 
		/*Eseguo l'aggiornamento*/ 
	   	gdrcd_query("DELETE FROM clgpersonaggiooggetto WHERE nome IN (SELECT nome FROM personaggio WHERE permessi = -1)");
	   	gdrcd_query("OPTIMIZE TABLE clgpersonaggiooggetto");
	   
	   	gdrcd_query("DELETE FROM clgpersonaggioabilita WHERE nome IN (SELECT nome FROM personaggio WHERE permessi = -1)");
	   	gdrcd_query("OPTIMIZE TABLE clgpersonaggioabilita");
	   
	   	gdrcd_query("DELETE FROM clgpersonaggiomostrine WHERE nome IN (SELECT nome FROM personaggio WHERE permessi = -1)");
	   	gdrcd_query("OPTIMIZE TABLE clgpersonaggiomostrine");
	   
	   	gdrcd_query("DELETE FROM clgpersonaggioruolo WHERE personaggio IN (SELECT nome AS personaggio FROM personaggio WHERE permessi = -1)");
	   	gdrcd_query("OPTIMIZE TABLE clgpersonaggioruolo");
	   	
	   	gdrcd_query("DELETE FROM messaggi WHERE mittente IN (SELECT nome AS personaggio FROM personaggio WHERE permessi = -1)");
	   	gdrcd_query("DELETE FROM messaggi WHERE destinatario IN (SELECT nome AS personaggio FROM personaggio WHERE permessi = -1)");
	   	gdrcd_query("DELETE FROM backmessaggi WHERE mittente IN (SELECT nome AS personaggio FROM personaggio WHERE permessi = -1)");
	   	gdrcd_query("DELETE FROM backmessaggi WHERE destinatario IN (SELECT nome AS personaggio FROM personaggio WHERE permessi = -1)");
	   	gdrcd_query("OPTIMIZE TABLE messaggi");

	   	gdrcd_query("DELETE FROM araldo_letto WHERE nome IN (SELECT nome AS personaggio FROM personaggio WHERE permessi = -1)");
	   	gdrcd_query("OPTIMIZE TABLE araldo_letto");
	   	
	   	gdrcd_query("UPDATE chat SET mittente = 'Cancellato' WHERE nome IN (SELECT nome AS personaggio FROM personaggio WHERE permessi = -1)");
	   	gdrcd_query("UPDATE chat SET destinatario = 'Cancellato' WHERE nome IN (SELECT nome AS personaggio FROM personaggio WHERE permessi = -1)");
	   	gdrcd_query("OPTIMIZE TABLE chat");
	   	
	   	gdrcd_query("UPDATE log SET nome_interessato = 'Cancellato' WHERE nome IN (SELECT nome AS personaggio FROM personaggio WHERE permessi = -1)");
	   	gdrcd_query("OPTIMIZE TABLE log");

	   	gdrcd_query("UPDATE messaggiaraldo SET autore = 'Cancellato' WHERE nome IN (SELECT nome AS personaggio FROM personaggio WHERE permessi = -1)");
	   	gdrcd_query("OPTIMIZE TABLE messaggiaraldo");

	   	gdrcd_query("DELETE FROM personaggio WHERE permessi = -1");
	   	gdrcd_query("OPTIMIZE TABLE personaggio");
?>
       	<!-- Conferma -->
	   	<div class="warning">
		  	<?php echo gdrcd_filter('out',$MESSAGE['warning']['modified']);?>
	   	</div>
	   	<!-- Link di ritorno alla visualizzazione di base -->
	   	<div class="link_back">
        	<a href="main.php?page=gestione_manutenzione">
		    	<?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['maintenance']['link']['back']); ?>
		   	</a>
       	</div>
<?php 
	} 

	/*Elimina personaggi che non si loggano più*/
	if (gdrcd_filter('get',$_POST['op'])=='missing')
	{ 
		if ((is_numeric($_POST['mesi'])===TRUE) &&
		   ($_POST['mesi']>=1)&&
		   ($_POST['mesi']<=12))
		{
	   		/*Eseguo l'aggiornamento*/ 
	   		gdrcd_query("DELETE FROM clgpersonaggiooggetto WHERE nome IN (SELECT nome FROM personaggio WHERE DATE_SUB(NOW(), INTERVAL ".gdrcd_filter('num',$_POST['mesi'])." MONTH) > ora_entrata)");
	   		gdrcd_query("OPTIMIZE TABLE clgpersonaggiooggetto");
	   
	   		gdrcd_query("DELETE FROM clgpersonaggioabilita WHERE nome IN (SELECT nome FROM personaggio WHERE DATE_SUB(NOW(), INTERVAL ".gdrcd_filter('num',$_POST['mesi'])." MONTH) > ora_entrata)");
	   		gdrcd_query("OPTIMIZE TABLE clgpersonaggioabilita");
	   
	   		gdrcd_query("DELETE FROM clgpersonaggiomostrine WHERE nome IN (SELECT nome FROM personaggio WHERE DATE_SUB(NOW(), INTERVAL ".gdrcd_filter('num',$_POST['mesi'])." MONTH) > ora_entrata)");
	   		gdrcd_query("OPTIMIZE TABLE clgpersonaggiomostrine");
	   
	   		gdrcd_query("DELETE FROM clgpersonaggioruolo WHERE personaggio IN (SELECT nome AS personaggio FROM personaggio WHERE DATE_SUB(NOW(), INTERVAL ".gdrcd_filter('num',$_POST['mesi'])." MONTH) > ora_entrata)");
	   		gdrcd_query("OPTIMIZE TABLE clgpersonaggioruolo");
	   
	   		gdrcd_query("DELETE FROM personaggio WHERE DATE_SUB(NOW(), INTERVAL ".gdrcd_filter('num',$_POST['mesi'])." MONTH) > ora_entrata");
	   		gdrcd_query("OPTIMIZE TABLE personaggio");
?>
       		<!-- Conferma -->
	   		<div class="warning">
		  		<?php echo gdrcd_filter('out',$MESSAGE['warning']['modified']);?>
	   		</div>
<?php 
			
		} 
		else 
		{ 
?>
       		<div class="error">
		  		<?php echo gdrcd_filter('out',$MESSAGE['warning']['cant_do']);?>
	   		</div>
<?php 
		} 
?>
	   	<!-- Link di ritorno alla visualizzazione di base -->
	   	<div class="link_back">
        	<a href="main.php?page=gestione_manutenzione">
		    	<?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['maintenance']['link']['back']); ?>
		   	</a>
       	</div>
<?php 
	} 

	/*Form di manutenzione*/
	if (isset($_POST['op'])===FALSE) 
	{ 
?>
		<!-- Log -->
		<div class="panels_box">
        <form action="main.php?page=gestione_manutenzione"
	          method="post"
		      class="form_gestione">
			<div class='form_label'>
             	<?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['maintenance']['old_log']);?>
		  	</div>
          	<div class='form_field'>
	         	<select name="mesi" ?>
			    	<?php for($i=0; $i<=12; $i++){ ?><option value="<?php echo $i; ?>"><?php echo $i.' '.gdrcd_filter('out',$MESSAGE['interface']['administration']['maintenance']['months']); ?></option><?php } ?>
			 	</select>
		  	</div>
		  	<!-- bottoni -->
		  	<div class='form_submit'>  
			  	<input type="hidden" name="op" value="old_log">
			  	<input type="submit" 
			         value="<?php echo gdrcd_filter('out',$MESSAGE['interface']['forms']['submit']);?>" />
		  	</div>
		</form>
		</div>

		<!-- Chat -->
		<div class="panels_box">
        <form action="main.php?page=gestione_manutenzione"
	          method="post"
		      class="form_gestione">
			<div class='form_label'>
             	<?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['maintenance']['old_chat']);?>
		  	</div>
          	<div class='form_field'>
	         	<select name="mesi" ?>
			    	<?php for($i=0; $i<=12; $i++){ ?><option value="<?php echo $i; ?>"><?php echo $i.' '.gdrcd_filter('out',$MESSAGE['interface']['administration']['maintenance']['months']); ?></option><?php } ?>
			 	</select>
		  	</div>
		  	<!-- bottoni -->
		  	<div class='form_submit'>  
			  	<input type="hidden" name="op" value="old_chat">
			  	<input type="submit" 
			         value="<?php echo gdrcd_filter('out',$MESSAGE['interface']['forms']['submit']);?>" />
		  	</div>
	    </form>
		</div>

        <!-- Messaggi -->
		<div class="panels_box">
        <form action="main.php?page=gestione_manutenzione"
	          method="post"
		      class="form_gestione">
		  	<div class='form_label'>
             	<?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['maintenance']['old_messages']);?>
		  	</div>
          	<div class='form_field'>
	         	<select name="mesi" ?>
			    	<?php for($i=0; $i<=12; $i++){ ?><option value="<?php echo $i; ?>"><?php echo $i.' '.gdrcd_filter('out',$MESSAGE['interface']['administration']['maintenance']['months']); ?></option><?php } ?>
			 	</select>
		  	</div>
		  	<div class='form_info'>
             	<?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['maintenance']['old_messages_info']);?>
		  	</div>
		  	<!-- bottoni -->
		  	<div class='form_submit'>  
			  	<input type="hidden" name="op" value="old_messages">
			  	<input type="submit" 
			         value="<?php echo gdrcd_filter('out',$MESSAGE['interface']['forms']['submit']);?>" />
		  	</div>
	    </form>
		</div>

        <!-- Cancellati -->
		<div class="panels_box">
        <form action="main.php?page=gestione_manutenzione"
	          method="post"
		      class="form_gestione">
		  	<div class='form_label'>
             	<?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['maintenance']['deleted']);?>
		  	</div>
		  	<div class='form_info'>
             	<?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['maintenance']['deleted_info']);?>
		  	</div>
		  	<!-- bottoni -->
		  	<div class='form_submit'>  
			  	<input type="hidden" name="op" value="deleted">
			  	<input type="submit" 
			         value="<?php echo gdrcd_filter('out',$MESSAGE['interface']['forms']['submit']);?>" />
		  	</div>
	    </form>
		</div>

        <!-- Assenti -->
		<div class="panels_box">
        <form action="main.php?page=gestione_manutenzione"
	          method="post"
		      class="form_gestione">
		  	<div class='form_label'>
             	<?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['maintenance']['missing']);?>
		  	</div>
          	<div class='form_field'>
	         	<select name="mesi" ?>
<?php 
					for($i=1; $i<=12; $i++)
					{ 
?>
						<option value="<?php echo $i; ?>"><?php echo $i.' '.gdrcd_filter('out',$MESSAGE['interface']['administration']['maintenance']['months']); ?></option>
<?php 
					} 
?>
			 	</select>
		  	</div>
		  	<div class='form_info'>
             	<?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['maintenance']['missing_info']);?>
		  	</div>
		  	<!-- bottoni -->
		  	<div class='form_submit'>  
			  	<input type="hidden" name="op" value="missing">
			  	<input type="submit" 
			         value="<?php echo gdrcd_filter('out',$MESSAGE['interface']['forms']['submit']);?>" />
		  	</div>
	    </form>
		</div>
		
		<!-- Blacklisted -->
		<div class="panels_box">
        <form action="main.php?page=gestione_manutenzione"
	          method="post"
		      class="form_gestione">
		  	<div class='form_label'>
             	<?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['maintenance']['blacklisted']);?>
		  	</div>
		  	<div class='form_info'>
             	<?php echo gdrcd_filter('out',$MESSAGE['interface']['administration']['maintenance']['blacklisted_info']);?>
		  	</div>
		  	<!-- bottoni -->
		  	<div class='form_submit'>  
			  	<input type="hidden" name="op" value="blacklisted">
			  	<input type="submit" 
			         value="<?php echo gdrcd_filter('out',$MESSAGE['interface']['forms']['submit']);?>" />
		  	</div>
	    </form>
		</div>
<?php 
	} //if 
?>

</div><!-- page_body -->
<?php 
} //else 
?>
</div><!-- pagina -->
