<?php

$pg = Functions::getInstance()->getMyId();
$bank = Banca::getInstance();

if ( $bank->permissionManageBank($pg) ) {

    $bank_data = $bank->extractBankData($pg);

    ?>

    <div class="general_title">Banca</div>

    <div class="general_incipit bank_totals">
        <div class="title">In banca: <span class="bank_count"><?= Filters::int($bank_data['banca']); ?></span> </div>
        <div class="title">In tasca: <span class="money_count"><?= Filters::int($bank_data['soldi']); ?></span> </div>
    </div>


    <div class="form_container">
        <div class="general_title">Deposito</div>

        <form method="POST" class="ajax_form chat_form_ajax" action="servizi/banca/ajax.php" data-callback="updateBank">

            <div class="single_input">
                <div class="label">Denaro da depositare</div>
                <input type="number" name="money" required>
            </div>

            <div class="input_container invia">
                <input type="submit" value="Deposita">
                <input type="hidden" name="action" value="deposit" required>
            </div>

        </form>

        <div class="general_title">Prelievo</div>

        <form method="POST" class="ajax_form chat_form_ajax" action="servizi/banca/ajax.php" data-callback="updateBank">

            <div class="single_input">
                <div class="label">Denaro da prelevare</div>
                <input type="number" name="money" required>
            </div>

            <div class="input_container invia">
                <input type="submit" value="Preleva">
                <input type="hidden" name="action" value="withdraw" required>
            </div>

        </form>

        <div class="general_title">Bonifico</div>

        <form method="POST" class="ajax_form chat_form_ajax" action="servizi/banca/ajax.php" data-callback="updateBank">

            <div class="single_input">
                <div class="label">Denaro da inviare</div>
                <input type="number" name="money" required>
            </div>

            <div class="single_input">
                <div class="label">Personaggio a cui inviare</div>
                <select name="pg" required>
                    <?= Personaggio::getInstance()->listPgs(); ?>
                </select>
            </div>

            <div class="single_input">
                <div class="label">Causale</div>
                <input type="text" name="causal" required>
            </div>

            <div class="input_container invia">
                <input type="submit" value="Invia bonifico">
                <input type="hidden" name="action" value="transfer" required>
            </div>

        </form>
    </div>

    <script src="<?= Router::getPagesLink('servizi/banca/index.js'); ?>"></script>

<?php } else { ?>

    <div class="warning">Permesso negato</div>

<?php } ?>
