<?php
if (isset($_REQUEST['pg']) === false) {
    echo gdrcd_filter('out', $MESSAGE['error']['unknonw_character_sheet']);
    exit();
}else{
    $pg=gdrcd_filter('out', $_REQUEST['pg']);
    $me = gdrcd_filter('out',$_SESSION['login']);
    $permessi  = gdrcd_filter('out',$_SESSION['permessi']);
}
if ((CALENDAR and CALENDAR_PERSONAL and CALENDAR_PERSONAL_PUBLIC)
|| (CALENDAR and CALENDAR_PERSONAL and $permessi >= ROLE_PERM)
|| (CALENDAR and CALENDAR_PERSONAL and $pg == $me)) {

?>
<form action="main.php?page=scheda_calendario&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>" method="post" class="form_gestione">
        <div class='form_label'>Tipo di evento</div>
        <div class='form_field'>
            <?=(gdrcd_list('eventi_tipo'))?>
        </div>
        <div class='form_label'>
            <?php echo gdrcd_filter('out','Colore - indicazioni in guida'); ?>
        </div>
        <div class='form_field'>
            <?=(gdrcd_list('eventi_colori'))?>
        </div>
        <div class='form_label'>Titolo</div>
        <div class='form_field'>
            <input name="titolo" required />
        </div>
        <div class='form_label'>Descrizione dell'evento-role (consentito il BBCODE)</div>
        <div class='form_field'>
            <textarea type="textbox" class="form_textarea" name="descrizione" ></textarea>
        </div>
        <div class='form_label'>Data inizio indicativa</div>
        <div class='form_field'>
            <input type="datetime-local" required name="start">
        </div>
        <div class='form_label'>Data fine indicativa</div>
        <div class='form_field'>
            <input type="datetime-local"  name="end" >
        </div>
        <!-- bottoni -->
        <div class='form_submit'>
                <input type="hidden" name="personaggio" value="<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>">
            <input type="hidden" name="op" value="save_new">
                <input type="submit" class="button" value="<?php echo gdrcd_filter('out','inserisci');?>" />
        </div>
    </form>
<?php
}
?>