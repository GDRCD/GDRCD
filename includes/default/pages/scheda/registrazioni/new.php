<?php

$id_pg = Filters::int($_GET['id_pg']);

if ( RegistrazioneGiocate::getInstance()->activeRegistrazioni() ) { ?>

    <div class="form_container">
        <form class="form ajax_form"
              action="scheda/registrazioni/ajax.php">

            <div class="single_input">
                <div class="label">Chat</div>
                <select name="chat">
                    <?= Chat::getInstance()->listChats(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Titolo</div>
                <input type="text" name="titolo" required>
            </div>

            <div class="single_input">
                <div class="label">Nota</div>
                <textarea name="nota"></textarea>
            </div>

            <div class="single_input">
                <div class="label">Inizio</div>
                <input type="datetime-local" name="inizio" required>
            </div>

            <div class="single_input">
                <div class="label">Fine</div>
                <input type="datetime-local" name="fine" required>
            </div>

            <div class="single_input">
                <input type="submit" value="Registra">
                <input type="hidden" name="action" value="registrazione_new" required>
            </div>

        </form>
    </div>

    <div class="link_back">
        <a href="main.php?page=scheda/index&op=registrazioni&id_pg=<?=$id_pg;?>">
            Torna indietro
        </a>
    </div>

<?php } ?>
