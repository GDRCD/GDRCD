<?php
/**
 *  @file		template_basic.inc.php
 *  
 *  @brief 		File contenente la funzione per l'inclusione dei template per gdrcd 5.X
 *  
 *  @author		Davide 'Dyrr' Grandi
 */	
	
	/**
	 *  @brief 			Funzione per l'inclusione di template
	 *  
	 *  @author			Davide 'Dyrr' Grandi
	 *  
	 *  @version		1.5.1
	 *  @date			15/03/2016
	 *  
	 *  @details 		La funzione include il file contenente il template html per la pagina cercandolo:
	 *  					- prima nella cartella del tema utilizzato;
	 *  					- nella cartella contenente i template di default;
	 *  
	 *  				Questo permette di poter creare i template solo per le pagine che si desidera 
	 *  				e non per tutto il tema.<br />
	 *  				
	 *  				I template vanno salvati nelle directory:
	 *  					- themes/_common/template (per i template comuni a tutti i temi)
	 *  					- themes/tema_utilizzato/template (per i template specifici del tema usato)<br />
	 *  					es: themes/advanced/template nel caso si usi il tema advanced.
	 *  
	 *  				I template vanno salvati nel formato nome_file.template.php es: index.template.php 
	 *  				e richiamati omettendo le estensioni del file
	 *  				Se si vuole avere accesso diretto agli array $MESSAGE e $PARAMETERS nei template basta 
	 *  				decommentare le due righe.<br />
	 *  				L'accesso diretto ai due array è disabilitato di default perchè l'idea di base di questa 
	 *  				meccanica di template è chi lavori sul template lavori solo su variabili locali per non 
	 *  				influenzare in alcun modo la parte della pagina relativa al codice puro.
	 *  
	 * 	 				[Changelog](@ref changelog_template_functions)
	 *  
	 *  @param [in] 	$path <b>(string)</b> il path del file interno alla cartella template.
	 *  @param [in] 	$TAGS <b>(array)</b> l'array contenente i dati da utilizzare nel template.
	 *  
	 *  @returns		none
	 *  
	 *  @todo			inserire un controllo sui caratteri usati per $path più per paranoia mia che per altro
	 */	
	function GdrcdLoadTemplate($path, $TAGS=null)
	{

		//$MESSAGE = $GLOBALS['MESSAGE'];
		//$PARAMETERS = $GLOBALS['PARAMETERS'];		
	
		//parte del path del templat ein comune tra template di default e specifico del tema
		$base_path = __DIR__ .'/../themes/';
		
		//path del template per il tema specifico in uso
		$theme_path = $base_path.$GLOBALS['PARAMETERS']['themes']['current_theme'].'/template/'.$path.'.template.php';
		//path di default del template
		$common_path = $base_path.'_common/template/'.$path.'.template.php';

		//se esiste il template specifico per il tema usato
		if (file_exists($theme_path)) {
			
			//imposta il template specifico come template per la pagina
			$template_path = $theme_path;		
		
		//se non esiste il template specifico ma quello di default
		} elseif (file_exists($common_path)) {
			
			//imposta il template di default come template della pagina
			$template_path = $common_path;
		
		//in caso non esistano ne il template specifico ne quello di default
		} else {
			
			//inserisce nella lista degli errori il percorso dei template non trovati
			$TAG['error_list']['item'][] = 'Il template '.$full_path.' non esiste.';
			$TAG['error_list']['item'][] = 'Il template '.$common_path.' non esiste.';
			
			//imposta la pagina di errore come template
			$template_path = $base_path.'_common/template/template_not_found.template.php';
		
		}

		//include il template richiesto
		require($template_path);

		//libera le variabili
		unset($TAG);
		unset($MESSAGE);
		unset($PARAMETERS);
	
	}
?>