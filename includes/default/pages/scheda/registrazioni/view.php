<?php

$id = Filters::int($_GET['id']);
$id_pg = Filters::int($_GET['id_pg']);

if ( RegistrazioneGiocate::getInstance()->activeRegistrazioni() && RegistrazioneGiocate::getInstance()->permissionViewSingleRecord($id) ) { ?>

    <div class="registrations_list chat_box">
        <?= RegistrazioneGiocate::getInstance()->characterRecord($id); ?>
    </div>

    <script src="<?= Router::getPagesLink('scheda/registrazioni/view.js'); ?>"></script>

    <div class="link_back">
        <a href="main.php?page=scheda/index&op=registrazioni&id_pg=<?= $id_pg; ?>">
            Torna indietro
        </a>
    </div>

<?php } else { ?>

    <div class="warning"> Permesso negato.</div>

<?php } ?>