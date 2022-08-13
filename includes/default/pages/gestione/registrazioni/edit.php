<?php

$class = RegistrazioneGiocate::getInstance();

$id_pg = Filters::int($_GET['id_pg']);
$id = Filters::int($_GET['id']);

$record_data = $class->getRecord($id);
$owner = Filters::int($record_data['autore']);

if ( $class->activeRegistrazioni() && $class->permissionUpdateRecords($owner) ) { ?>

    <div class="form_container">
        <form class="form ajax_form"
              action="gestione/registrazioni/ajax.php" data-reset="false">

            <div class="single_input">
                <div class="label">Chat</div>
                <select name="chat">
                    <?= Chat::getInstance()->listChats(Filters::int($record_data['chat'])); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Titolo</div>
                <input type="text" name="titolo" value="<?=Filters::out($record_data['titolo']);?>" required>
            </div>

            <div class="single_input">
                <div class="label">Nota</div>
                <textarea name="nota"><?=Filters::out($record_data['titolo']);?></textarea>
            </div>

            <div class="single_input">
                <div class="label">Inizio</div>
                <input type="datetime-local" name="inizio" value="<?=Filters::out($record_data['inizio']);?>" required>
            </div>

            <div class="single_input">
                <div class="label">Fine</div>
                <input type="datetime-local" name="fine" value="<?=Filters::out($record_data['fine']);?>" required>
            </div>

            <div class="single_input">
                <input type="submit" value="Registra">
                <input type="hidden" name="action" value="registrazione_edit" required>
                <input type="hidden" name="id" value="<?=$id;?>" required>
            </div>

        </form>
    </div>

    <div class="link_back">
        <a href="main.php?page=gestione/registrazioni/index">
            Torna indietro
        </a>
    </div>

<?php } ?>
