<?php
Router::loadRequired();

$contatti = Contacts::getInstance();
$id_pg = Filters::int($_GET['id_pg']);
$pg = Filters::out($_REQUEST['pg']);
$id_contatto= Filters::int($_REQUEST['id']);

?>

<div class="gestione_incipit">
    Aggiunta di una nuova nota
</div>

<div class="form_container">
    <form class="form ajax_form"
          action="scheda/contatti/contatti_ajax.php"
          data-reset="false"
          data-callback="goBackNoteContatto">
        <?php

        if(!$contatti->contatcPublic()){
            ?>
            <div class="single_input">
                <div class='label'>
                    Pubblica
                </div>
                <select name="pubblica">
                    <option value="si" >Si</option>
                    <option value="no" >No</option>
                </select>
            </div>
            <?php
        }  else{ ?>
        <input type="hidden" value="si" name="pubblica">
        <?php
        }
        ?>
        <div class="single_input">
            <div class='label'>
                Titolo nota
            </div>
           <input type="text" name="titolo" required>
        </div>
        <div class="single_input">
            <div class='label'>
                Nota:
            </div>
             <textarea name="nota" required></textarea>
        </div>

        <div class="fake-table">
            <div class="header">
                <!-- bottoni -->
                <div class="single_input">
                    <div class='form_submit'>
                        <input type="hidden" name="action" value="new_nota">
                        <input type="hidden" id="id_pg" name="id_pg" value="<?=$id_pg?>">
                        <input type="hidden" id="pg" name="pg" value="<?=$pg?>">
                        <input type="hidden" id="id_contatto" name="id_contatto" value="<?=$id_contatto?>">
                        <input type="hidden" id="url" value="/main.php?page=scheda_contatti&id_pg=<?=$id_pg?>&pg=<?=$pg?>&op=view&id=<?=$id_contatto?>">

                        <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>"/> |
                        <a href="/main.php?page=scheda_contatti&id_pg=<?=$id_pg?>&pg=<?=$pg?>&op=view&id=<?=$id_contatto?>">Torna indietro</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="<?= Router::getPagesLink('scheda/contatti/JS/nota_contatti.js'); ?>"></script>
