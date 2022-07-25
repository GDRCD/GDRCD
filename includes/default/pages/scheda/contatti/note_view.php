<?php
Router::loadRequired();
$contatti_note = ContattiNote::GetInstance();

$id_pg = Filters::int($_GET['id_pg']);//id della scheda pg
$id = Filters::int($_REQUEST['id']);//id del contatto

?>
<div class="fake-table note_list">
    <?= $contatti_note->NoteList($id); ?>
</div>

<script src="<?= Router::getPagesLink('scheda/contatti/JS/note_view.js'); ?>"></script>
