<?php
Router::loadRequired();

$contatti = Contatti::getInstance();
$id_pg = Filters::int($_GET['id_pg']);
$pg = Filters::in($_GET['pg']);

if ($contatti->contactEnables()) { ?>

    <div class="fake-table contatti_list">
        <?= $contatti->ContactList($id_pg); ?>
    </div>

    <div class="fake-table">
        <div class="footer">

            <?php if (Personaggio::isMyPg($id_pg)) { ?>
                <a href="/main.php?page=scheda_contatti&op=contact_new&id_pg=<?= $id_pg ?>&pg=<?= $pg ?>">Nuovo contatto</a> |
            <?php } ?>

            <a href="/main.php?page=scheda&id_pg=<?= $id_pg ?>&pg=<?= $pg ?>">Torna indietro</a>
        </div>
    </div>

    <script src="<?= Router::getPagesLink('scheda/contatti/JS/contact_delete.js'); ?>"></script>

<?php } ?>