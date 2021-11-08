<?php /*Form di inserimento/modifica*/


require_once(__DIR__ . '/../../../includes/required.php');

$quest = Quest::getInstance();

if ($quest->manageTramePermission()) {

    $op = Filters::out($_POST['op']);

    switch ($op) {
        case 'edit_trama':
            $resp = $quest->editTrama($_POST);
            break;
    }

    $id_trama = Filters::int($_GET['id_record']);

    if ($quest->tramaExist($id_trama)) {
        $trama_data = $quest->getTrama($id_trama);

        ?>

        <!-- Form di inserimento/modifica -->
        <div class="panels_box form_container">
            <form action="main.php?page=gestione_trame&op=edit_trama&id_record=<?=$id_trama;?>" method="post" class="form">

                <?php if (isset($resp)) { ?>
                    <div class="warning"><?= $resp['mex']; ?></div>
                <?php } ?>

                <div class="form_title">
                    Modifica trama
                </div>

                <div class="single_input">
                    <div class='label'>
                        Titolo
                    </div>
                    <input name="titolo" value="<?= Filters::out($trama_data['titolo']); ?>"/>
                </div>

                <div class="single_input">
                    <div class='label'>
                        Descrizione
                    </div>
                    <textarea name="descrizione"><?= Filters::out($trama_data['descrizione']); ?></textarea>
                </div>

                <div class="single_input">
                    <div class='form_label'>
                        Stato trama
                    </div>
                    <div class='form_field'>
                        <select name="stato">
                            <?= $quest->trameStatusList(Filters::out($trama_data['stato'])); ?>
                        </select>
                    </div>
                </div>

                <!-- bottoni -->
                <div class='single_input'>
                    <input type="submit" value="<?= Filters::out($MESSAGE['interface']['forms']['submit']); ?>"/>
                    <input type="hidden" name="op" value="edit_trama">
                    <input type="hidden" name="trama" value="<?= Filters::int($id_trama); ?>">
                </div>

            </form>
        </div>
    <?php } else { ?>
        <div class="warning">Trama inesistente</div>
    <?php }
} else { ?>
    <div class="warning">Permesso negato.</div>//if ?>
<?php } ?>

<!-- Link di ritorno alla visualizzazione di base -->
<div class="link_back">
    <a href="main.php?page=gestione_trame">
        Torna alla lista delle trame
    </a>
</div>