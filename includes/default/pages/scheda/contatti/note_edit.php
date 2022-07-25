<?php
Router::loadRequired();

$contatti_note = ContattiNote::GetInstance();
$id = Filters::in($_REQUEST['id']);

$nota = $contatti_note->getNota($id, 'titolo, nota, pubblica');
?>

<div class="form_container">
    <form class="form ajax_form"
          action="scheda/contatti/contact_ajax.php"
          data-reset="false"
          data-callback="closeNoteContatto">
        <?php

        if (!$contatti_note->contactPublic()) {
            ?>
            <div class="single_input">
                <div class='label'>
                    Pubblica
                </div>
                <select name="pubblica">
                    <option value="si" <?= ($nota['pubblica'] == 'si' ? 'selected' : '') ?> >Si</option>
                    <option value="no" <?= ($nota['pubblica'] == 'no' ? 'selected' : '') ?>>No</option>
                </select>
            </div>
            <?php
        } else {
            ?>
            <input type="hidden" value="si" name="pubblica">
            <?php
        }
        ?>
        <div class="single_input">
            <div class='label'>
                Titolo nota
            </div>
            <input type="text" name="titolo" required value="<?= Filters::out($nota['titolo']) ?>">
        </div>
        <div class="single_input">
            <div class='label'>
                Nota:
            </div>
            <textarea name="nota" required><?= Filters::out($nota['nota']) ?></textarea>
        </div>

        <div class='single_input'>
            <input type="submit" value="<?= gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>"/>
            <input type="hidden" name="action" value="note_edit">
            <input type="hidden" name="id" value="<?= Filters::int($id); ?>">

        </div>

    </form>
</div>
<script src="<?= Router::getPagesLink('scheda/contatti/JS/note_edit.js'); ?>"></script>

