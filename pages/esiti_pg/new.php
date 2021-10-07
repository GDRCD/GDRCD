<?php
$blocco = gdrcd_query("SELECT id, pg, titolo FROM blocco_esiti WHERE pg = '".gdrcd_filter('in',$_SESSION['login'])."'
        AND id= ".gdrcd_filter('num',$_GET['blocco'])." LIMIT 1 ");

if (gdrcd_filter('num',$blocco['id'])>0) {
    ?>

    <div class="page_title">
        <h2>Serie di esiti: <?php echo $blocco['titolo'];?></h2>
    </div>

    <div class="form_info">
        <?php echo $MESSAGE['interface']['esiti']['newesitopg'];?>
    </div>
    <form action="main.php?page=servizi_esitinew"
          method="post"
          class="form_gestione">

        <div class='form_label'>
            Titolo
        </div>
        <div class='form_field'>
            <input name="titolo"
                   value=""/>
        </div>
        <div class='form_label'>
            Contenuto ON
        </div>
        <div class='form_field'>
            <textarea name="contenuto"></textarea>
        </div>

        <?php if (TIRI_ESITO) { ?>
            <div class='form_label'>
                Tira dei dadi
            </div>
            <div class='form_field'>
                Numero di dadi: <input name="dice_num" value="" /><br>
                Numero di facce dei dadi: <input name="dice_face" value="" /><br>
            </div>
        <?php } ?>
        <div class='form_label'>
            Note OFF
        </div>
        <div class='form_field'>
            <input name="note" value="" />
        </div>
        <div class="form_info" >
            Utilizzare solo per brevi chiarimenti
        </div>
        <!-- bottoni -->
        <div class='form_submit'>
            <input type="hidden"
                   name="op"
                   value="add">
            <input type="hidden"
                   name="id"
                   value="<?php echo $_GET['blocco'];?>">
            <input type="submit"
                   value="<?php echo gdrcd_filter('out',$MESSAGE['interface']['forms']['submit']);?>" />
        </div>

    </form>
<?php } else {
    echo '<div class="warning">Non hai i permessi per visualizzare questa sezione</div>';
    }

?>