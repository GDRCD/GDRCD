<?php

require_once(__DIR__ . '/../../core/required.php');

$esiti = Esiti::getInstance();
$id_record = Filters::int($_GET['id_record']);

$op = Filters::out($_POST['op']);

switch ($op) {
    case 'new':
        $resp = $esiti->newEsitoPlayer($_POST);
        break;
}


if ($esiti->esitiFromPlayerEnabled()) {
    ?>

    <div class="gestione_incipit">
        Creazione nuova conversazione esito.
    </div>

    <div class="form_container">

        <?php if (isset($resp)) { ?>
            <div class="warning"><?= $resp['mex']; ?></div>
            <div class="link_back"><a href="/main.php?page=servizi_esiti">Indietro</a></div>
            <?php
            Functions::redirect('/main.php?page=servizi_esiti', 2);
        } ?>

        <form method="POST" class="form">


            <div class="single_input">
                <div class='label'>
                    Titolo
                </div>
                <input name="titolo" required/>
            </div>

            <div class="single_input">
                <div class='label'>
                    Richiesta
                </div>
                <textarea name="contenuto" required></textarea>
            </div>

            <!-- bottoni -->
            <div class="single_input">
                <div class='form_submit'>
                    <input type="hidden" name="op" value="new">
                    <input type="submit"
                           value="<?php echo gdrcd_filter('out', $MESSAGE['interface']['forms']['submit']); ?>"/>
                </div>
            </div>

        </form>

    </div>
<?php } else { ?>
    <div class="warning">Permesso negato.</div>
<?php } ?>

<div class="link_back"><a href="/main.php?page=servizi_esiti">Indietro</a></div>