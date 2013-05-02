<script type="text/javascript" src="includes/corefunctions.js"></script>
<?php

/** * Abilitazione tooltip
	* @author Blancks
*/
if ($PARAMETERS['mode']['map_tooltip'] == 'ON' || $PARAMETERS['mode']['user_online_state'] == 'ON')
{
?>

<script type="text/javascript">
var tooltip_offsetX = <?php echo $PARAMETERS['settings']['map_tooltip']['offset_x']; ?>;
var tooltip_offsetY = <?php echo $PARAMETERS['settings']['map_tooltip']['offset_y']; ?>;
</script>
<script type="text/javascript" src="includes/tooltip.js"></script>

<?php
}

/** * Caricamento script per il titolo "lampeggiante" per i nuovi pm
	* @author Blancks
*/
if ($PARAMETERS['mode']['alert_pm_via_pagetitle']=='ON')
{
?>

<script type="text/javascript" src="includes/changetitle.js"></script>

<?php
}


/** * Caricamento script per la scelta popup nel login
	* @author Blancks
*/
if ($PARAMETERS['mode']['popup_choise']=='ON')
{
?>

<script type="text/javascript" src="includes/popupchoise.js"></script>

<?php
}
?>

</body>
</html><?php 

/*Chiudo la connessione al database*/
gdrcd_close_connection($handleDBConnection);


/**	* Per ottimizzare le risorse impiegate le liberiamo dopo che non ne abbiamo più bisogno 
	* @author Blancks
*/
unset($MESSAGE);
unset($PARAMETERS);

?>