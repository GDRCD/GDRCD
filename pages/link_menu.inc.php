<div class="pagina_link_menu">

<?php

if ($PARAMETERS['mode']['gotomap_list'] == 'ON')
{

	$gotomap_list = array();

	$result = gdrcd_query("	SELECT 	mappa_click.id_click, mappa_click.nome, 
									mappa.id, mappa.nome AS nome_chat, mappa.chat, mappa.pagina, mappa.id_mappa_collegata
							FROM mappa_click 
							LEFT JOIN mappa ON mappa.id_mappa = mappa_click.id_click", 'result');


	if (gdrcd_query($result, 'num_rows') > 0)
	{

		while ($row = gdrcd_query($result, 'fetch'))
				$gotomap_list[$row['nome']. '|@|' . $row['id_click']][$row['id']] = array(	'nome' => $row['nome_chat'], 
																							'chat' => $row['chat'], 
																							'pagina' => $row['pagina'], 
																							'mappa_collegata' => $row['id_mappa_collegata']);
		
		gdrcd_query($result, 'free');
		
?>
<select id="gotomap" onchange="self.location.href=this.value;">

<?php	foreach ($gotomap_list as $infoMap => $infoLocation)
		{
			$splitInfoMap = explode('|@|', $infoMap);

?>			<option value="main.php?page=mappaclick&map_id=<?php echo $splitInfoMap[1]; ?>"<?php echo ($_SESSION['mappa']==$splitInfoMap[1]&&$_SESSION['luogo']==-1)? ' selected="selected"' : ''; ?> class="map"><?php echo $splitInfoMap[0]; ?></option>

<?php		

			if (is_array($infoLocation))
			{

				foreach ($infoLocation as $idLoc => $infoLoc)
				{
				
					if (!empty($infoLoc['nome']))
					{
					
						if ($infoLoc['chat'] != 0)
						{
							$valueLoc = 'dir=' . $idLoc . '&map_id=' . $splitInfoMap[1];
					
						}else
						{				
							if ($infoLoc['mappa_collegata'] != 0)
							{
								$valueLoc = 'page=mappaclick&map_id=' . $infoLoc['mappa_collegata'];
							}else
							{
								$valueLoc = 'page='.$infoLoc['pagina'];
							}
						}
?>
			<option value="main.php?<?php echo $valueLoc; ?>"<?php echo ($_SESSION['luogo']==$idLoc&&$_SESSION['luogo']!=-1)? ' selected="selected"' : ''; ?>>&raquo; <?php echo $infoLoc['nome']; ?></option>

<?php	
						$valueLoc = '';

					}

				}
			
			}
		
	
		}
		
?>

</select>
<?php

		unset($gotomap_list);

	}
	
	
}
	
?>


<div class="page_title">
   <h2><?php echo gdrcd_filter('out',$PARAMETERS['names']['gamemenu']); ?></h2>
</div>

<div class="page_body">

<?php
/* Generazione automatica del menu del gioco */
$raw_counter=0;

foreach($PARAMETERS['menu'] as $link_menu){
   
   if (empty($link_menu['url'])===FALSE){
     if (empty($link_menu['image_file'])===TRUE){
	   if (empty($link_menu['text'])===FALSE){
	      echo '<div class="link_menu"><a href="'.$link_menu['url'].'">'.gdrcd_filter('out',$link_menu['text']).'</a></div>';
	   }
	 } else {
		  if (empty($link_menu['image_file_onclick'])===TRUE){
			  $img_up=$link_menu['image_file'];
			  $img_down=$link_menu['image_file'];
          } else {
			  $img_up=$link_menu['image_file'];
			  $img_down=$link_menu['image_file_onclick'];
		  }
          echo '<SCRIPT LANGUAGE="JavaScript"> if (document.images) { var n'.$raw_counter.'_button1_up = new Image(); n'.$raw_counter.'_button1_up.src = "themes/'. $PARAMETERS['themes']['current_theme'].'/imgs/menu/'.$img_up.'"; var n'.$raw_counter.'_button1_over = new Image(); n'.$raw_counter.'_button1_over.src = "themes/'. $PARAMETERS['themes']['current_theme'].'/imgs/menu/'.$img_down.'";} function n'.$raw_counter.'_over_button() { if (document.images) { document["n'.$raw_counter.'_buttonOne"].src = n'.$raw_counter.'_button1_over.src;}} function n'.$raw_counter.'_up_button() { if (document.images) { document["n'.$raw_counter.'_buttonOne"].src = n'.$raw_counter.'_button1_up.src}}</SCRIPT>';
          echo '<div class="link_menu"><a href="'.$link_menu['url'].'" onMouseOver="n'.$raw_counter.'_over_button()" onMouseOut="n'.$raw_counter.'_up_button()"><img src= "themes/'. $PARAMETERS['themes']['current_theme'].'/imgs/menu/'.$link_menu['image_file'].'" alt="'.gdrcd_filter('out',$link_menu['text']).'" title="'.gdrcd_filter('out',$link_menu['text']).'" name="n'.$raw_counter.'_buttonOne" /></a></div>';
	 }
   }
   $raw_counter++;
}

?>


</div>
<?php /*HELP: Il menu viene generato automaticamente attingendo dalle informazioni contenute in config.inc.php. La versione supporta link testuali ed immagini e può essere modificata direttamente nel file config.ing.php, impostando url di destinazione, testo e selezionado le immagini. Se il link è un'immagine il testo viene interpretato automaticamente come testo alternativo all'immagine. Per realizzare un menu di altro tipo suggeriamo di commentare o cancellare il contenuto di questa pagina e sostituirlo con il codice del nuovo menu. */ ?>

</div>