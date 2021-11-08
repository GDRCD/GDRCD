<?php /*Form di inserimento/modifica*/


require_once(__DIR__ . '/../../../includes/required.php');

$quest = Quest::getInstance();

if ($quest->manageTramePermission()) {

    $op = Filters::out($_POST['op']);

    switch ($op) {
        case 'insert_trama':
            $resp = $quest->insertTrama($_POST);
            break;
    } ?>

    <!-- Form di inserimento/modifica -->
    <div class="panels_box form_container">
        <form action="main.php?page=gestione_trame&op=insert_trama" method="post" class="form">

            <?php if (isset($resp)) { ?>
                <div class="warning"><?= $resp['mex']; ?></div>
            <?php } ?>

            <div class="form_title">
                Inserisci trama
            </div>

            <div class="single_input">
                <div class='label'>
                    Titolo
                </div>
                <input name="titolo"/>
            </div>

            <div class="single_input">
                <div class='label'>
                    Descrizione
                </div>
                <textarea name="descrizione"></textarea>
            </div>

            <div class="single_input">
                <div class='form_label'>
                    Stato trama
                </div>
                <div class='form_field'>
                    <select name="stato">
                        <?= $quest->trameStatusList(); ?>
                    </select>
                </div>
            </div>

            <!-- bottoni -->
            <div class='single_input'>
                <input type="submit" value="<?= Filters::out($MESSAGE['interface']['forms']['submit']); ?>"/>
                <input type="hidden" name="op" value="insert_trama">
            </div>

        </form>
    </div>
<?php } else { ?>
    <div class="warning">Permesso negato.</div>
<?php }//if ?>

<!-- Link di ritorno alla visualizzazione di base -->
<div class="link_back">
    <a href="main.php?page=gestione_trame">
        Torna alla lista delle trame
    </a>
</div>