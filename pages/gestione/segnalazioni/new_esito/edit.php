<?php
if ($_GET['op']=='edit' && ($_SESSION['permessi']>=ESITI_PERM)) {  ?>
    <div class="page_title">
        <h2>Modifica serie di esiti</h2>
    </div>
    <?php
    $id_edit = gdrcd_query("SELECT * FROM blocco_esiti WHERE id = ".$_GET['id']."
     AND (master = '0' || master ='" . gdrcd_filter('in', $_SESSION['login']) . "') ", 'result');
    $id_num = gdrcd_query($id_edit, 'num_rows');

    if($id_num==0) {
        echo '<div class="warning">Non hai i permessi per modificare questa serie di esiti</div>';
    } else { ?>
        <form action="main.php?page=gestione_segnalazioni&segn=esito_index"
              method="post"
              class="form_gestione">
            <?php 	$tit=gdrcd_query($id_edit, 'fetch'); ?>

            <div class='form_label'>
                Titolo
            </div>
            <div class='form_field'>
                <input name="titolo"
                       value="<?php echo $tit['titolo'];?>"/>
            </div>

            <div class='form_label'>
                Stato serie di esiti
            </div>
            <div class='form_field'>
                <select name="stato">
                    <option value="0" <?php if ($tit['closed']==0) {echo 'selected';}?> >Aperta</option>
                    <option value="1" <?php if ($tit['closed']==1) {echo 'selected';}?> >Chiusa</option>
                </select>
            </div>
            <!-- bottoni -->
            <div class='form_submit'>
                <input type="hidden"
                       name="op"
                       value="modify">
                <input type="hidden"
                       name="id"
                       value="<?php echo $tit['id'];?>">
                <input type="submit"
                       value="<?php echo gdrcd_filter('out',$MESSAGE['interface']['forms']['submit']);?>" />
            </div>

        </form>
    <?php } ?>
    <!-- link pié di pagina -->
    <div class="link_back">
        <a href='main.php?page=gestione_segnalazioni&segn=esiti_master'>
            Torna alla lista
        </a>
    </div>
<?php } ?>