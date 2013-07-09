<?php
/** * Questo files contiene i dettagli per l'upgrade del database della vecchia versione alla nuova.
	* Il modulo è riutilizzabile anche per il futuro, i campi vengono aggiunti solo se non ci sono nel db
	* @author Blancks
*/
	
	
		$tablelist 	= gdrcd_query("SHOW TABLES", 'result');
		$table 		= gdrcd_query($tablelist, 'num_rows');
		
		if ($table > 0)
		{
		  $tables_list=array();
		  while($row=gdrcd_query($tablelist,'fetch')){
		    $tables_list[]=$row[0];
      }
      gdrcd_query($tablelist,'free');
      
			$updating_queryes = array();
			$updating_password = false;
	
			/** * Elenco dei campi da aggiornare
				* @author Blancks
			*/
			$tables = array();
			
			require 'upgrade_list.php';
			
			foreach ($tables as $tablename => $newfields)
			{
			  if(in_array($tablename, $tables_list)){//Facciamo il controllo solo se la tabella esiste per davvero
  				$fields = gdrcd_check_tables($tablename);
  		
  				foreach ($newfields as $newfield_name => $newfield_info)
  				{
  					$match = false;
  										
  					foreach ($fields as $field)
  					{
  						if ($field->Field == $newfield_name)
  								$match = true;
  					}
  					
  					if (!$match)
  							$updating_queryes[] = "ALTER TABLE `$tablename` ADD `$newfield_name` $newfield_info";
  				}
        }
        else{
          //TODO rilevazione di un'installazione parziale o un db con tabelle non di gdrcd...dovrei lanciare qualche tipo di errore?
        }
			}
			
			
			/** * Controllo se da configurazione è abilitato l'encript delle password e se le password non sono criptate nel db
				* @author Blancks
			*/
			if ($PARAMETERS['mode']['encriptpassword']=='ON')
			{
			  if(in_array('personaggio', $tables_list)){
  				$check_record = gdrcd_query("SELECT pass FROM personaggio LIMIT 1");
  				$password_len = strlen($check_record['pass']);
  				
  				if ($password_len < 32)
  						$updating_password = true;
        }
			}
			
		}
?>