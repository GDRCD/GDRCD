<?php
require_once(__DIR__ . '/../../../includes/required.php');

$contatti_note=ContactsNotes::GetInstance();
$id=Filters::in($_REQUEST['id']);

$nota=$contatti_note->getNota('titolo, nota, pubblica', $id);
?>

<div class="form_container">
    <form method="POST" class="form">
        <div class="single_input">
            <div class='label'>
                Pubblica
            </div>
            <select name="pubblica">
                <option value="si" <?php echo ($nota['pubblica']=='si' ? 'selected' : '')?> >Si</option>
                <option value="no" <?php echo ($nota['pubblica']=='no' ? 'selected' : '')?>>No</option>
            </select>
        </div>
        <div class="single_input">
            <div class='label'>
                Titolo nota
            </div>
            <input type="text" name="titolo" required value="<?php echo Filters::out($nota['titolo'])?>">
        </div>
        <div class="single_input">
            <div class='label'>
                Nota:
            </div>
            <textarea name="nota" required><?php echo Filters::out($nota['nota'])?></textarea>
        </div>

        <div class="fake-table">
            <div class="header">
                <!-- bottoni -->
                <div class="single_input">
                    <div class='form_submit'>
                        <input type="hidden" name="action" value="new_nota">

                        <input type="hidden" id="id" name="id_contatto" value="<?=$id?>">

                        <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>"/>

                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<script src="pages/scheda/contatti/JS/nota_contatti.js"></script>
