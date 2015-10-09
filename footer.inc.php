    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
    <script type="text/javascript" src="/includes/corefunctions.js"></script>
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
    <script type="text/javascript" src="/includes/tooltip.js"></script>
    <?php
    }
    /** * Caricamento script per il titolo "lampeggiante" per i nuovi pm
        * @author Blancks
    */
    if ($PARAMETERS['mode']['alert_pm_via_pagetitle']=='ON')
    {
    ?>
    <script type="text/javascript" src="/includes/changetitle.js"></script>
    <?php
    }
    /** * Caricamento script per la scelta popup nel login
        * @author Blancks
    */
    if ($PARAMETERS['mode']['popup_choise']=='ON')
    {
    ?>
    <script type="text/javascript" src="/includes/popupchoise.js"></script>
    <?php
    }
    ?>
    <script type="text/javascript">
    function modalWindow(name, title, url, width, height) {
        // per width ed height imposto dei valori di default così non occorre specificarli in ogni occasione
        width = typeof width === 'undefined'? 800 : width;
        height = typeof height === 'undefined'? 600 : height;

        // verifichiamo se nel body non esiste il sorgente per la dialog
        if ($('#dialog-'+name).length == 0) {

            // in questo caso lo creiamo:
            $('body').append('<div id="dialog-'+name+'" title="'+title+'" style="padding:0;"><iframe src="'+ url +'" frameborder="no" style="position:absolute;width:100%;height:100%;" scrolling="yes"></div>');

        } else {

            // se il sorgente invece esiste già assegnamo la nuova url all´iframe:
            $('#dialog-'+name).attr('title', title);
            $('#dialog-'+name+' iframe').attr('src', url);
        }

        // Ok, adesso siamo pronti per lanciare la modale!
        $('#dialog-'+name).dialog({width: width, height: height});
    }
    </script>
    <script type="text/javascript">setTimeout("self.location.href.reload();", <?php echo (int)$_GET['ref'] * 1000; ?>);</script>
</body>
</html>
<?php
/*Chiudo la connessione al database*/
gdrcd_close_connection($handleDBConnection);

/**	* Per ottimizzare le risorse impiegate le liberiamo dopo che non ne abbiamo pi� bisogno
	* @author Blancks
*/
unset($MESSAGE);
unset($PARAMETERS);
?>