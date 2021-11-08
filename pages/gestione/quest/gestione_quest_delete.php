<?php

/*Form di inserimento/modifica*/
require_once(__DIR__ . '/../../../includes/required.php');

$quest = Quest::getInstance();


if ($quest->manageQuestPermission()) {

    $op = Filters::out($_POST['op']);

    switch ($op) {
        case 'delete_quest':
            $resp = $quest->deleteQuest($_POST);
            break;
    }

    $quest_id = Filters::int($_GET['id_record']);

    if (!isset($resp)) {
        if ($quest->questExist($quest_id)) {
            $loaded_record = $quest->getQuest($quest_id);
            $titolo = Filters::out($loaded_record['titolo']); ?>

            <div class="form_container">
                <form method="post" class="form">
                    <div class="single_input">
                        <div class="label">Confermi l'eliminazione della quest "<?= $titolo; ?>"?</div>
                    </div>

                    <div class="single_input">
                        <button type="submit"> Conferma</button>
                        <input type="hidden" name="op" value="delete_quest">
                        <input type="hidden" name="quest" value="<?= $quest_id; ?>">
                    </div>

                </form>
            </div>

        <?php } else { ?>
            <div class="warning">
                Quest inesistente.
            </div>
        <?php }
    } else {
        if ($resp['response']) { ?>
            <div class="warning"> Quest eliminata con successo.</div>
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
    <a href="main.php?page=gestione_quest">
        Torna a gestione quest
    </a>
</div>