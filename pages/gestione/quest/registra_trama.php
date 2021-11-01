<?php /*Form di inserimento/modifica*/
if ((gdrcd_filter('get',$_POST['op']=='edit_trama')) ||
    (gdrcd_filter('get',$_REQUEST['op'])=='new_trama')){
    /*Preseleziono l'operazione di inserimento*/
    $operation='insert';
    /*Se è stata richiesta una modifica*/
    if ($_POST['op']=='edit_trama'){
        /*Carico il record da modificare*/
        $loaded_record=gdrcd_query("SELECT * FROM quest_trama WHERE id=".gdrcd_filter('num',$_POST['id_record'])." LIMIT 1 ");
        /*Cambio l'operazione in modifica*/
        $operation='edit';

    }	?>

    <!-- Form di inserimento/modifica -->
    <div class="panels_box">
        <form action="main.php?page=gestione_quest"
              method="post"
              class="form_gestione">

            <div class="page_title">
                <?php if ($_POST['op']=='edit_trama') { ?>
                <h2>Modifica trama
                    <?php } else { ?>
                    <h2>Inserisci nuova trama
                        <?php } ?></h2>

            </div>

            <div class='form_label' >
                Titolo
            </div>
            <div class='form_field'>
                <input name="titolo"
                       value="<?php echo $loaded_record['titolo'];?>"  />
            </div>
            <div class='form_label'>
                Descrizione
            </div>
            <div class='form_field'>
                <textarea name="descrizione" ><?php echo $loaded_record['descrizione'];?></textarea>
            </div>

            <div class='form_label'>
                Stato della trama
            </div>
            <div class='form_field'>
                <select name="stato">
                    <option value="0" <?php if ($loaded_record['stato']==0) { echo 'selected';}?> >In corso</option>
                    <option value="1" <?php if ($loaded_record['stato']==1) { echo 'selected';}?> >Conclusa</option>
                </select>
            </div>

    </div>
    <!-- bottoni -->
    <div class='form_submit'>
        <?php /* Se l'operazione è una modifica stampo i tasti modifica*/
        if ($operation == "edit"){?>
            <input type="hidden" name="id_record" value="<?php echo gdrcd_filter('num',$loaded_record['id']);?>">
            <input type="submit"
                   value="<?php echo gdrcd_filter('out',$MESSAGE['interface']['forms']['modify']);?>" />
            <input type="hidden"
                   name="op"
                   value="doedit_trama">

        <?php	} /* Altrimenti il tasto inserisci */
        else { ?>
            <input type="hidden"
                   name="op"
                   value="insert_trama">
            <input type="submit"
                   value="<?php echo gdrcd_filter('out',$MESSAGE['interface']['forms']['submit']);?>" />
        <?php	} ?>

    </div>

    </form>
    </div>
    <!-- Link di ritorno alla visualizzazione di base -->
    <div class="link_back">
        <a href="main.php?page=gestione_quest&op=lista_trame">
            Torna alla lista delle trame
        </a>
    </div>
<?php }//if ?>
