
<?php

require_once(__DIR__ . '/../../../includes/required.php');

$scheda_con = SchedaContatti::getInstance();
$contatti_note=ContactsNotes::GetInstance();
$id_pg = Filters::int($_GET['id_pg']);
$op = Filters::out($_GET['op']);

if ($scheda_con->isAccessible($id_pg)) { ?>
   <div class="page_body">
        <?php require_once(__DIR__ . '/' . $contatti_note->loadManagementContactNotePage(Filters::out($op))); ?>
    </div>

    <?php
}
?>