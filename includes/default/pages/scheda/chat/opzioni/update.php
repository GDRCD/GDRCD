<?php

Router::loadRequired();

$pg_chat_options = SchedaChatOpzioni::getInstance();
$id_pg = Filters::int($_GET['id_pg']);

?>
<div class="scheda_chat_options_container form_container">

    <form
        class="form ajax_form"
        action="scheda/chat/opzioni/ajax.php"
        data-reset="false">

        <?= $pg_chat_options->optionsList($id_pg); ?>

        <div class="single_input">
            <input type="hidden" name="action" value="save_options">
            <input type="hidden" name="pg" value="<?= $id_pg; ?>">
            <input type="submit" value="Salva">
        </div>

    </form>
</div>
