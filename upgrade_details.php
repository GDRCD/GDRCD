<?php
/** * Questo files contiene i dettagli per l'upgrade del database della vecchia versione alla nuova.
	* Il modulo è riutilizzabile anche per il futuro, i campi vengono aggiunti solo se non ci sono nel db
	* @author Blancks
*/
	
	
		$tablelist 	= gdrcd_query("SHOW TABLES", 'result');
		$table 		= gdrcd_query($tablelist, 'num_rows');
		gdrcd_query($tablelist, 'free');
		
		
		if ($table > 0)
		{
			$updating_queryes = array();
			$updating_password = false;
	
			/** * Elenco dei campi da aggiornare
				* @author Blancks
			*/
			$tables = array();
			
			require 'upgrade_list.php';
			
			foreach ($tables as $tablename => $newfields)
			{
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
			
			
			/** * Controllo se da configurazione è abilitato l'encript delle password e se le password non sono criptate nel db
				* @author Blancks
			*/
			if ($PARAMETERS['mode']['encriptpassword']=='ON')
			{
				$check_record = gdrcd_query("SELECT pass FROM personaggio LIMIT 1");
				$password_len = strlen($check_record['pass']);
				
				if ($password_len < 32)
						$updating_password = true;
			}
			
		}
?>