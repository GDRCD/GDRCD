<?php

require_once(__DIR__ . '/../../../includes/required.php');

$esiti = Esiti::getInstance();
$id_record = Filters::int($_GET['id_record']);

if ($esiti->esitoMembersPermission($id_record)) {


    $op = Filters::out($_POST['op']);

    switch ($op) {
        case 'change_master':
            $resp = $esiti->setMaster($_POST);
            break;
    }

    $data = $esiti->getEsito($id_record);
    $master = Filters::int($data['master']);

    ?>


    <?php if (isset($resp)) { ?>
        <div class="warning"><?= $resp['mex']; ?></div>
    <?php } ?>

    <?php if ($master > 0) {
        $master_name = Personaggio::nameFromId($master); ?>
        <div class="gestione_incipit">
            Master attuale : <?=$master_name;?>
        </div>
    <?php } ?>

    <div class="form_container">
        <form method="POST" class="form">

            <div class="form_title">Seleziona il nuovo master.</div>

            <div class="single_input">
                <div class="label">Masters</div>
                <select name="personaggio" required>
                    <option value=""></option>
                    <?= $esiti->esitiManagersList(); ?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="op" value="change_master">
                <input type="hidden" name="id" value="<?= $id_record; ?>">
                <input type="submit" value="Aggiungi">
            </div>
        </form>
    </div>


<?php } else { ?>
    <div class="warning">Permesso negato.</div>
<?php } ?>


<div class="link_back"><a href="/main.php?page=gestione_esiti">Indietro</a></div>
