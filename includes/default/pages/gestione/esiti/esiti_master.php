<?php

Router::loadRequired();

$esiti = Esiti::getInstance();
$id_record = Filters::int($_GET['id_record']);

if ( $esiti->esitoMembersPermission($id_record) ) {

    $data = $esiti->getEsito($id_record);
    $master = Filters::int($data['master']);

    ?>


    <?php if ( isset($resp) ) { ?>
        <div class="warning"><?= $resp['mex']; ?></div>
    <?php } ?>

    <?php if ( $master > 0 ) {
        $master_name = Personaggio::nameFromId($master); ?>
        <div class="gestione_incipit">
            Master attuale : <?= $master_name; ?>
        </div>
    <?php } ?>

    <div class="form_container change_master_form">
        <form method="POST" class="form ajax_form" action="gestione/esiti/esiti_ajax.php">

            <div class="form_title">Seleziona il nuovo master.</div>

            <div class="single_input">
                <div class="label">Masters</div>
                <select name="personaggio" required>
                    <option value=""></option>
                    <?= $esiti->esitiManagersList(); ?>
                </select>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="change_master">
                <input type="hidden" name="id" value="<?= $id_record; ?>">
                <input type="submit" value="Aggiungi">
            </div>
        </form>
    </div>

    <script src="<?= Router::getPagesLink('gestione/esiti/JS/esiti_master.js'); ?>"></script>


<?php } else { ?>
    <div class="warning">Permesso negato.</div>
<?php } ?>


<div class="link_back"><a href="/main.php?page=gestione/esiti/esiti_index">Indietro</a></div>
