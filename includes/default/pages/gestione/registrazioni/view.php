<?php

$id = Filters::int($_GET['id']);

if ( RegistrazioneGiocate::getInstance()->activeRegistrazioni() && RegistrazioneGiocate::getInstance()->permissionViewSingleRecord() ) { ?>

    <div class="gestione_registrazione chat_box">
        <?= RegistrazioneGiocate::getInstance()->characterRecord($id); ?>
    </div>

    <div class="link_back">
        <a href="main.php?page=gestione/registrazioni/index">
            Torna indietro
        </a>
    </div>

<?php } else { ?>

    <div class="warning"> Permesso negato.</div>

<?php } ?>