<?php 
$dont_check = TRUE;
$check_for_update = TRUE;
require 'header.inc.php'; /*Header comune*/ ?>
<div class="pagina_ambientazione">
<?php	




if (!isset($updating_queryes[0]) || empty($updating_queryes[0]))
{ 
	echo '<div="error">'.$MESSAGE['homepage']['updater']['no_fields'].'</div>';

}else
{

	/** * Aggiornamento delle tabelle
		* @author Blancks
	*/
	foreach ($updating_queryes as $query)
			gdrcd_query($query);
			
	
	/** * Aggiornamento delle password
		* @author Blancks
	*/
	if ($PARAMETERS['mode']['encriptpassword']=='ON')
	{
		if ($updating_password)
		{
			switch (strtolower($PARAMETERS['mode']['encriptalgorithm']))
			{
				case 'md5':		$query = "UPDATE personaggio SET pass = MD5(pass)"; break;
				case 'sha-1': 	$query = "UPDATE personaggio SET pass = SHA1(pass)"; break;
			}
			
			gdrcd_query($query);
		}
	}
	
	
	echo '<div class="warning">'.gdrcd_filter('out',$MESSAGE['homepage']['updater']['done']).'</div>';


} ?>
       <!-- Link di ritorno alla homepage -->
	   <div class="link_back">
           <a href="index.php">
		      <?php echo gdrcd_filter('out',$PARAMETERS['info']['homepage_name']); ?>
		   </a>
       </div>
</div>
<?php require 'footer.inc.php';  /*Footer comune*/?>