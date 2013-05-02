<?php


			/** * Lista dei campi da controllare e in caso aggiornare sul database 
				* Se sei uno sviluppatore di patch e vuoi rilasciare una modifica che necessita di aggiunte alle tabelle già esistenti puoi implementare questo modulo e rilasciarlo con la patch
				* @author Blancks
			*/
			$tables['mappa']['link_immagine'] 				= "varchar(256) NOT NULL";
			$tables['mappa']['link_immagine_hover']			= "varchar(256) NOT NULL";
			$tables['mappa']['id_mappa_collegata']			= "int(11) NOT NULL DEFAULT '0'";
			$tables['mappa']['ora_prenotazione']			= "datetime DEFAULT NULL";

			$tables['mappa_click']['larghezza']				= "smallint(4) NOT NULL DEFAULT '500'";
			$tables['mappa_click']['altezza']				= "smallint(4) NOT NULL DEFAULT '330'";

			$tables['messaggioaraldo']['importante']		= "binary(1) NOT NULL DEFAULT '0'";
			$tables['messaggioaraldo']['chiuso']			= "binary(1) NOT NULL DEFAULT '0'";

			$tables['personaggio']['url_img_chat']			= "varchar(255) NOT NULL DEFAULT ' '";
			$tables['personaggio']['blocca_media']			= "binary(1) NOT NULL DEFAULT '0'";
			$tables['personaggio']['online_status']			= "varchar(100) DEFAULT NULL";
			$tables['personaggio']['ultimo_messaggio']		= "bigint(20) NOT NULL DEFAULT '0'";
			$tables['personaggio']['ultimo_cambiopass']		= "DATETIME NULL DEFAULT NULL";


?>