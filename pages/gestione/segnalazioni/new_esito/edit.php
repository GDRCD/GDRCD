<?php
if ($_GET['op']=='edit' && ($_SESSION['permessi']>=ESITI_PERM)) {  ?>
    <div class="page_title">
        <h2>Modifica serie di esiti</h2>
    </div>
    <?php
    $tit = gdrcd_stmt_one("SELECT * FROM blocco_esiti WHERE id = ? AND (id_personaggio_master IS NULL || id_personaggio_master =?) ", 
    [$_GET['id'], $_SESSION['id_personaggio']]);
    

    if(empty($tit)) {
        echo '<div class="warning">Non hai i permessi per modificare questa serie di esiti</div>';
        return;
    } ?>
        <form action="main.php?page=gestione_segnalazioni&segn=esito_index"
              method="post"
              class="form_gestione">
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
