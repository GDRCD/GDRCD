<?php

require_once(__DIR__ . '/../../../includes/required.php');

$esiti = Esiti::getInstance();
$abi = Abilita::getInstance();
$chat = new Chat();
$id_record = Filters::int($_GET['id_record']);

$op = Filters::out($_POST['op']);

switch ($op) {
    case 'answer':
        $resp = $esiti->newAnswer($_POST);
        break;
}

if ($esiti->esitoViewPermission($id_record) && $esiti->esitoExist($id_record)) {

    $esitoData = $esiti->getEsito($id_record, 'titolo');
    $titolo = Filters::out($esitoData['titolo']);

    if (isset($resp)) { ?>
        <div class="warning"><?= $resp['mex']; ?></div>
        <div class="link_back"><a href="/main.php?page=gestione_esiti">Indietro</a></div>
        <?php
        Functions::redirect('/main.php?page=gestione_esiti&op=read&id_record=' . $id_record, 2);
    } ?>

    <div class="answer_box">
        <div class="titolo_box"><?= $titolo; ?></div>
        <div class="answer_list">
            <?= $esiti->renderEsitoAnswers($id_record); ?>
            <div style="height: 1px;clear: both"></div>
        </div>
        <div class="give_answer form_container">
            <form method="POST" class="form">

                <div class="single_input">
                    <div class="label">Risposta</div>
                    <textarea name="contenuto" required></textarea>
                </div>

                <?php if ($esiti->esitiTiriEnabled()) { ?>
                    <div class="single_input w-33">
                        <div class="label">Numero dadi</div>
                        <input type="number" name="dadi_num">
                    </div>
                    <div class="single_input w-33">
                        <div class="label">Numero facce dado</div>
                        <input type="number" name="dadi_face">
                    </div>
                    <div class="single_input w-33">
                        <div class="label">Abilità</div>
                        <select name="abilita">
                            <?=$abi->ListaAbilita();?>
                        </select>
                    </div>
                    <div class="single_input w-33">
                        <div class="label">Chat</div>
                        <select name="chat">
                            <?=$chat->chatList();?>
                        </select>
                    </div>
                <?php } ?>

                <div class="single_input">
                    <input type="submit" value="invia">
                    <input type="hidden" name="op" value="answer">
                    <input type="hidden" name="id_record" value="<?= $id_record; ?>">
                </div>
            </form>

        </div>
    </div>


<?php } else { ?>
    <div class="warning">Permesso negato.</div>
<?php } ?>

<div class="link_back">
    <a href="/main.php?page=gestione_esiti">Indietro</a>
</div>