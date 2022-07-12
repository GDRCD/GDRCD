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

    $loaded_record=gdrcd_query("SELECT * FROM eventi_personaggio WHERE personaggio='".gdrcd_filter('out',$_POST['pg'])."' AND id=".gdrcd_filter('num',$_POST['id'])." LIMIT 1 ");
?>

<form action="main.php?page=scheda_calendario&pg=<?=gdrcd_filter('out',$_POST['pg'])?>" method="post" class="form_gestione">
        <div class='form_label'>Tipo di evento</div>
        <div class='form_field'>
            <?=(gdrcd_list('eventi_tipo', gdrcd_filter('out',$loaded_record['title'])))?>
        </div>
        <div class='form_label'>
            <?php echo gdrcd_filter('out','Colore - indicazioni in guida'); ?>
        </div>
        <div class='form_field'>
            <?=(gdrcd_list('eventi_colori', gdrcd_filter('out',$loaded_record['colore'])))?>
        </div>
        <div class='form_label'>Titolo</div>
        <div class='form_field'>
            <input name="titolo" required value="<?= gdrcd_filter('out',$loaded_record['titolo']); ?>"/>
        </div>
        <div class='form_label'>Descrizione dell'evento-role (consentito il BBCODE)</div>
        <div class='form_field'>
            <textarea type="textbox" class="form_textarea" name="descrizione" ><?php echo gdrcd_filter('out',$loaded_record['descrizione']); ?></textarea>
        </div>
        <div class='form_label'>Data inizio indicativa</div>
        <div class='form_field'>
            <input type="datetime-local" required name="start" value="<?=gdrcd_filter('out',$loaded_record['start']); ?>">
        </div>
        <div class='form_label'>Data fine indicativa</div>
        <div class='form_field'>
            <input type="datetime-local"  name="end"  value="<?=gdrcd_filter('out',$loaded_record['end']); ?>" >
        </div>
        <!-- bottoni -->
        <div class='form_submit'>
                <input type="hidden" name="op" value="save_edit">
                <input hidden name="id" value="<?= gdrcd_filter('num',$_POST['id']); ?>">
            <input hidden name="pg" value="<?=gdrcd_filter('out',$_POST['pg']); ?>">
                <input type="submit" class="button" value="<?php echo gdrcd_filter('out','inserisci');?>" />
        </div>
    </form>
<?php
}
?>