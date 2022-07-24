<?php

$id_pg = Filters::int($_GET['id_pg']);
$scheda_transactions = SchedaTransazioni::getInstance();

if (Personaggio::isMyPg($id_pg) || $scheda_transactions->permissionViewTransactions($id_pg)) { ?>


    <div class="fake-table log_table">
        <?=Log::getInstance()->logTable($id_pg, BONIFICO, 10, 'Log Transazioni');?>
    </div>

<?php }

if (Personaggio::isMyPg($id_pg) || $scheda_transactions->viewExpPermission()) { ?>
    <div class="scheda_exp_box">
        <div class="fake-table log_table scheda_exp_table">
            <?=Log::getInstance()->logTable($id_pg, PX, 10, 'Esperienza');?>
        </div>
    </div>

    <?php if ($scheda_transactions->manageExpPermission()) { ?>

        <div class="form_container">
            <form class="form ajax_form"
                  action="scheda/transazioni/ajax.php"
                  data-callback="updateExpTransactions">

                <div class="single_input">
                    <div class="label">Causale</div>
                    <input type="text" name="causale" required>
                </div>

                <div class="single_input">
                    <div class="label">PX</div>
                    <input type="number" name="px" required>
                </div>

                <div class="single_input">
                    <input type="submit" value="Assegna">
                    <input type="hidden" name="action" value="add_exp" required>
                    <input type="hidden" name="pg" value="<?= $id_pg; ?>" required>
                </div>

            </form>
        </div>

        <script src="<?= Router::getPagesLink('scheda/transazioni/transazioni.js'); ?>"></script>
    <?php }
} ?>

<div class="form_info">
    Mantenere il mouse sulle causali per poterne leggere il testo per intero.
</div>

