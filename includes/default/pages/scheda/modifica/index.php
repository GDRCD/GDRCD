<?php


$id_pg = Filters::in($_GET['id_pg']);

if (Scheda::getInstance()->permissionUpdateCharacter($id_pg)) {

    $pg_data = Personaggio::getPgData($id_pg);
    ?>


    <div class="general_title">Dati personaggio</div>

    <form method="POST" class="ajax_form chat_form_ajax" action="scheda/modifica/ajax.php" data-reset="false">

        <div class="single_input">
            <div class="label">Cognome</div>
            <input type="text" name="cognome" value="<?= Filters::out($pg_data['cognome']); ?>" required>
        </div>

        <div class="single_input">
            <div class="label">Url Avatar</div>
            <input type="text" name="url_img" value="<?= Filters::out($pg_data['url_img']); ?>" required>
        </div>

        <div class="single_input">
            <div class="label">Url Mini avatar</div>
            <input type="text" name="url_img_chat" value="<?= Filters::out($pg_data['url_img_chat']); ?>">
        </div>

        <div class="single_input">
            <div class="label">Messaggio presenti</div>
            <input type="text" name="online_status" value="<?= Filters::out($pg_data['online_state']); ?>">
        </div>

        <div class="single_input">
            <div class="label">Descrizione</div>
            <textarea name="descrizione"><?= Filters::out($pg_data['descrizione']); ?></textarea>
        </div>

        <div class="single_input">
            <div class="label">Storia</div>
            <textarea name="storia"><?= Filters::out($pg_data['storia']); ?></textarea>
        </div>

        <div class="single_input">
            <div class="label">Disabilita tutti i suoni nel gioco</div>
            <input type="checkbox" name="blocca_media" <?= Filters::bool($pg_data['blocca_media']) ? 'checked' : ''; ?>>
        </div>

        <div class="single_input">
            <div class="label">Url Canzone scheda</div>
            <input type="text" name="url_media" value="<?= Filters::out($pg_data['url_media']); ?>">
        </div>

        <div class="single_input">
            <input type="hidden" name="action" value="update_character_data">
            <input type="hidden" name="pg" value="<?= $id_pg; ?>">
            <input type="submit" value="Modifica">
        </div>
    </form>


    <?php if (Scheda::getInstance()->permissionStatusCharacter()) { ?>

        <div class="general_title">Status personaggio</div>

        <form method="POST" class="ajax_form chat_form_ajax" action="scheda/modifica/ajax.php" data-reset="false">
            <div class="single_input">
                <div class="label">Note fato</div>
                <textarea name="stato"><?= Filters::out($pg_data['stato']); ?></textarea>
            </div>


            <div class="single_input">
                <div class="label">Salute</div>
                <input type="text" name="salute" value="<?= Filters::out($pg_data['salute']); ?>" required>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="update_character_status">
                <input type="hidden" name="pg" value="<?= $id_pg; ?>">
                <input type="submit" value="Modifica">
            </div>
        </form>

    <?php } ?>

    <?php if (Scheda::getInstance()->permissionBanCharacter()) { ?>

        <div class="general_title">Ban personaggio</div>

        <form method="POST" class="ajax_form chat_form_ajax" action="scheda/modifica/ajax.php" data-reset="false">

            <div class="single_input">
                <div class="label">Banna fino al</div>
                <input type="date" name="esilio" required>
            </div>


            <div class="single_input">
                <div class="label">Motivo</div>
                <input type="text" name="motivo_esilio" required>
            </div>

            <div class="single_input">
                <input type="hidden" name="action" value="ban_character">
                <input type="hidden" name="pg" value="<?= $id_pg; ?>">
                <input type="submit" value="Modifica">
            </div>
        </form>

    <?php } ?>

<?php } ?>