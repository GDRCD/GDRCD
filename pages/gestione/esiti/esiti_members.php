<?php

require_once(__DIR__ . '/../../../includes/required.php');

$esiti = Esiti::getInstance();
$id_record = Filters::int($_GET['id_record']);

if ($esiti->esitoMembersPermission($id_record)) {


    $op = Filters::out($_POST['op']);

    switch ($op) {
        case 'add_member':
            $resp = $esiti->addMember($_POST);
            break;
        case 'delete_member':
            $resp = $esiti->deleteMember($_POST);
            break;
    }
    ?>


    <?php if (isset($resp)) { ?>
        <div class="warning"><?= $resp['mex']; ?></div>
    <?php } ?>

    <div class="fake-table esiti_list esiti_members">
        <div class="tr header">
            <div class="td">Membro</div>
            <div class="td">Controlli</div>
        </div>
        <?= $esiti->membersList($id_record); ?>
    </div>

    <div class="form_container">
        <form method="POST" class="form">

            <div class="form_title">Aggiungi un membro.</div>

            <div class="single_input">
                <div class="label">Personaggio</div>
                <select name="personaggio" required>
                    <option value=""></option>
                    <?= Functions::getPgList(); ?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="op" value="add_member">
                <input type="hidden" name="id" value="<?= $id_record; ?>">
                <input type="submit" value="Aggiungi">
            </div>
        </form>
    </div>


<?php } else { ?>
    <div class="warning">Permesso negato.</div>
<?php } ?>


<div class="link_back"><a href="/main.php?page=gestione_esiti">Indietro</a></div>
