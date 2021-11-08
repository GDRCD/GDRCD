<?php

require_once(__DIR__ . '/../includes/required.php');

$quest = Quest::getInstance();
$pg = Filters::out($_GET['pg']);

if (Personaggio::isMyPg($pg) || $quest->viewExpPermission()) {

    switch ($_POST['op']) {
        case 'assegna_px':
            $resp = $quest->assignSingleExp($_POST);
            break;
    }

    ?>

    <?php if (!isset($resp)) { ?>
        <div class="pagina_scheda pagina_scheda_esperienza">


            <div class="general_title">Esperienza</div>

            <div class="box_exp">
                <div class="fake-table exp_log_table">
                    <div class="tr header">
                        <div class="td causale">Causale</div>
                        <div class="td autore">Autore</div>
                        <div class="td data">Data</div>
                    </div>
                    <?= $quest->renderPgExpLog($pg); ?>
                </div>
            </div>

            <?php if ($quest->manageExpPermission()) { ?>
                <div class="general_title">Assegnazione singola</div>

                <div class="form_container">
                    <form method="POST" class="form">
                        <div class="single_input">
                            <div class="label">Causale</div>
                            <input type="text" name="causale" required>
                        </div>
                        <div class="single_input">
                            <div class="label">PX</div>
                            <input type="number" name="px" required>
                        </div>
                        <div class="single_input">
                            <input type="submit" value="Assegna">
                            <input type="hidden" name="op" value="assegna_px" required>
                            <input type="hidden" name="pg" value="<?= $pg; ?>" required>
                        </div>
                    </form>
                </div>
            <?php } ?>

        </div>

    <?php } else { ?>

        <div class="warning"><?=Filters::out($resp['mex']);?></div>

        <div class="link_back">
            <a href="/main.php?page=scheda_px&pg=<?=$pg;?>">Indietro</a>
        </div>

    <?php }
} else { ?>
    <div class="warning">Permesso negato</div>
<?php } ?>