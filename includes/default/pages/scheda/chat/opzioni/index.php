<?php

Router::loadRequired();

$pg_chat_options = SchedaChatOpzioni::getInstance();
$id_pg = Filters::int($_GET['id_pg']);
$op = Filters::out($_GET['op']);

if ($pg_chat_options->available($id_pg)) {

    if ($pg_chat_options->isAccessible($id_pg)) { ?>


        <div class="pagina_scheda pagina_scheda_chat_opzioni">

            <div class="general_title">Opzioni Chat</div>

            <?php require_once(__DIR__ . '/../../menu.inc.php'); ?>

            <div class="page_body">
                <?php require_once(__DIR__ . '/' . $pg_chat_options->indexSchedaChatOpzioni(Filters::out($op))); ?>
            </div>

        </div>

    <?php }
} ?>
