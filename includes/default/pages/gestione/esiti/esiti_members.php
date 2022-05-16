<?php

Router::loadRequired();

$esiti = Esiti::getInstance();
$id_record = Filters::int($_GET['id_record']);

if ($esiti->esitoMembersPermission($id_record)) {

    ?>


    <?php if (isset($resp)) { ?>
        <div class="warning"><?= $resp['mex']; ?></div>
    <?php } ?>

    <div class="fake-table esiti_list esiti_members">
        <?= $esiti->membersList($id_record); ?>
    </div>

    <div class="form_container members_add_form">
        <form class="form ajax_form" action="gestione/esiti/esiti_ajax.php" data-callback="refreshMembers">

            <div class="form_title">Aggiungi un membro.</div>

            <div class="single_input">
                <div class="label">Personaggio</div>
                <select name="personaggio" required>
                    <option value=""></option>
                    <?= Functions::getPgList(); ?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="add_member">
                <input type="hidden" name="id" value="<?= $id_record; ?>">
                <input type="submit" value="Aggiungi">
            </div>
        </form>
    </div>

    <script src="<?=Router::getPagesLink('gestione/esiti/JS/esiti_members.js');?>"></script>

<?php } else { ?>
    <div class="warning">Permesso negato.</div>
<?php } ?>


<div class="link_back"><a href="/main.php?page=gestione/esiti/esiti_index">Indietro</a></div>
