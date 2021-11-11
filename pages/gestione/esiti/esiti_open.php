<?php

require_once(__DIR__ . '/../../../includes/required.php');

$esiti = Esiti::getInstance();
$id_record = Filters::int($_GET['id_record']);

if ($esiti->esitiManageAll()) {

    $resp = $esiti->esitoOpen($id_record); ?>

    <div class="warning"><?= $resp['mex']; ?></div>
<?php } else { ?>
    <div class="warning">Permesso negato.</div>
<?php } ?>

<div class="link_back"><a href="/main.php?page=gestione_esiti">Indietro</a></div>

<?php Functions::redirect('/main.php?page=gestione_esiti', 3); ?>

