<?php
if ($_GET['op']=='first') { ?>
    <div class="page_title">
        <h2>Nuova serie di esiti</h2>
    </div>
    <div class="form_info">
        <?php echo $MESSAGE['interface']['esitiserie']['intro'];?>
    </div>
    <form action="main.php?page=gestione_segnalazioni&segn=esito_index"
          method="post"
          class="form_gestione">

        <div class='form_label'>
            Nome PG coinvolto
        </div>
        <div class='form_field'>
            <input name="pg" value=""/>
        </div>

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

        <div class="form_info" >
            Indicazioni sulle azioni ON da compiere/compiute.
        </div>

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
                   value="insert">
            <input type="submit"
                   value="<?php echo gdrcd_filter('out',$MESSAGE['interface']['forms']['submit']);?>" />
        </div>

    </form>
    <!-- link pié di pagina -->
    <div class="link_back">
        <a href='main.php?page=gestione_segnalazioni&segn=esiti_master'>
            Torna alla lista
        </a>
    </div>
<?php }

?>