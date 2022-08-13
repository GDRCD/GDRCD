<?php

$id_pg = Filters::int($_GET['id_pg']);

if (RegistrazioneGiocate::getInstance()->activeRegistrazioni() && RegistrazioneGiocate::getInstance()->permissionViewRecords($id_pg) ) {
    ?>

    <div class="fake-table scheda_registrazioni_table">
        <?=RegistrazioneGiocate::getInstance()->characterRecords($id_pg);?>
    </div>

    <script src="<?= Router::getPagesLink('scheda/registrazioni/index.js'); ?>"></script>

    <div class="link_back">
        <a href="main.php?page=scheda/index&id_pg=<?=$id_pg;?>">
            Torna indietro
        </a>
    </div>


<?php } ?>
