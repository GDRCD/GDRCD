<?php

/*Form di inserimento/modifica*/
require_once(__DIR__ . '/../../../includes/required.php');

$quest = Quest::getInstance();


if ($quest->manageTramePermission()) {

    $op = Filters::out($_POST['op']);

    switch ($op) {
        case 'delete_trama':
            $resp = $quest->deleteTrama($_POST);
            break;
    }

    $trama_id = Filters::int($_GET['id_record']);

    if (!isset($resp)){
        if ($quest->tramaExist($trama_id)) {
            $loaded_record = $quest->getTrama($trama_id);
            $titolo = Filters::out($loaded_record['titolo']); ?>

            <div class="form_container">
                <form method="post" class="form">
                    <div class="single_input">
                        <div class="label">Confermi l'eliminazione della trama "<?= $titolo; ?>"?</div>
                    </div>

                    <div class="single_input">
                        <button type="submit"> Conferma</button>
                        <input type="hidden" name="op" value="delete_trama">
                        <input type="hidden" name="trama" value="<?= $trama_id; ?>">
                    </div>

                </form>
            </div>

        <?php } else { ?>
            <div class="warning">
                Trama inesistente.
            </div>
        <?php }
    } else {
        if ($resp['response']) { ?>
            <div class="warning"> Trama eliminata con successo.</div>
        <?php } else { ?>
            <div class="warning"><?= Filters::out($resp['mex']); ?></div>
        <?php }
    }
} else { ?>
    <div class="warning">
        Permessi negati.
    </div>
<?php } ?>

<!-- Link di ritorno alla visualizzazione di base -->
<div class="link_back">
    <a href="main.php?page=gestione_trame">
        Torna alla lista delle trame
    </a>
</div>