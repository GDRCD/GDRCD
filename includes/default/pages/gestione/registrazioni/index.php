<?php

if (RegistrazioneGiocate::getInstance()->activeRegistrazioni() && RegistrazioneGiocate::getInstance()->permissionViewRecords() ) {
    ?>

    <div class="fake-table manage_registrazioni_table table_new">
        <?=RegistrazioneGiocate::getInstance()->allRecords('new');?>
    </div>

    <div class="fake-table manage_registrazioni_table table_blocked">
        <?=RegistrazioneGiocate::getInstance()->allRecords('blocked');?>
    </div>

    <div class="fake-table manage_registrazioni_table table_controlled">
        <?=RegistrazioneGiocate::getInstance()->allRecords('controlled');?>
    </div>

    <script src="<?= Router::getPagesLink('gestione/registrazioni/index.js'); ?>"></script>

    <div class="link_back">
        <a href="main.php?page=gestione">
            Torna indietro
        </a>
    </div>


<?php } ?>