<script type="text/javascript" src="includes/corefunctions.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.3.4/sweetalert2.min.js" integrity="sha512-GDiDlK2vvO6nYcNorLUit0DSRvcfd7Vc0VRg7e3PuZcsTwQrJQKp5hf8bCaad+BNoBq7YMH6QwWLPQO3Xln0og==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<?php
/** * Abilitazione tooltip
 * @author Blancks
 */
if($PARAMETERS['mode']['map_tooltip'] == 'ON' || $PARAMETERS['mode']['user_online_state'] == 'ON') { ?>
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
if($PARAMETERS['mode']['alert_pm_via_pagetitle'] == 'ON') {
    echo '<script type="text/javascript" src="includes/changetitle.js"></script>';

}
/** * Caricamento script per la scelta popup nel login
 * @author Blancks
 */
if($PARAMETERS['mode']['popup_choise'] == 'ON') {
    echo '<script type="text/javascript" src="includes/popupchoise.js"></script>';
}
?>

<!--<script type="text/javascript">
    setTimeout("self.location.href.reload();",<?php //echo (int) $_GET['ref'] * 1000; ?>);
</script-->
</body>
</html>
<?php
/*Chiudo la connessione al database*/

/**    * Per ottimizzare le risorse impiegate le liberiamo dopo che non ne abbiamo pi� bisogno
 * @author Blancks
 */
unset($MESSAGE);
unset($PARAMETERS);
?>
