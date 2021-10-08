<?php /*HELP: */
//Se non e' stato specificato il nome del pg
if(isset($_REQUEST['pg']) === false) {
    echo gdrcd_filter('out', $MESSAGE['error']['unknonw_character_sheet']);
    exit();
}

$query="SELECT data, titolo, testo,  visibile, id FROM diario WHERE id='".gdrcd_filter('url',$_POST['id'])."'  ";
$result = gdrcd_query($query, 'query');
?>
<div class="form_gioco">
    <form action="main.php?page=scheda_diario&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>" method="post">
    <div class='form_label'>
        <?php echo gdrcd_filter('out',$MESSAGE['interface']['sheet']['diary']['title']); ?>
    </div>
    <div class='form_field'>
        <input type="text" name="titolo" class="form_input" required value="<?php echo gdrcd_filter('out', $result['titolo']); ?>"/>
    </div>
    <div class='form_label'>
        <?php echo gdrcd_filter('out',$MESSAGE['interface']['sheet']['diary']['date']); ?>
    </div>
    <div class='form_field'>
        <input type="date" name="data" class="form_input" value="<?php echo gdrcd_filter('out', $result['data']); ?>"/>
    </div>
    <div class='form_label'>
        <?php echo gdrcd_filter('out',$MESSAGE['interface']['sheet']['diary']['visible']); ?>
    </div>
    <div class='form_field'>
      <select name="visibile">
     <?php
     $selection = array('si', 'no');
     foreach ($selection as $selection) {
       $selected = (gdrcd_filter('out', $result['visibile']) == $selection) ? "selected" : "";
       echo '<option '.$selected.' value="'.$selection.'">'.$selection.'</option>';
      }
       ?>
      </select>
    </div>
    <div class='form_label'>
        <?php echo gdrcd_filter('out',$MESSAGE['interface']['sheet']['diary']['text']); ?>
    </div>
    <div class='form_field'>
        <textarea type="textbox" name="testo" class="form_textarea"><?php echo gdrcd_filter('out', $result['testo']); ?></textarea>
    </div>
    <!--- registrazione giocate ---->
    <div class="form_submit">
        <input type="submit"  name="submit"  value="Salva" />
        <input hidden name="op" value="save_edit">
        <input hidden name="id" value="<?php echo gdrcd_filter('out', $result['id']); ?>">
      </form>
    </div>
</div>
<!-- Link a piè di pagina -->
<div class="link_back">
    <a href="main.php?page=scheda_diario&pg=<?php echo gdrcd_filter('url', $_REQUEST['pg']); ?>"><?php echo gdrcd_filter('out',
            $MESSAGE['interface']['sheet']['diary']['back']); ?></a>
</div>
