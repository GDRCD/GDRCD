<?php

$id_pg = Filters::int($_GET['id_pg']);

if (Log::getInstance()->permissionViewLogs()) {
    ?>


    <div class="fake-table log_table">
        <?= Log::getInstance()->logTable($id_pg, PX, 10, 'Log Esperienza'); ?>
    </div>

    <hr>
    <div class="fake-table log_table">
        <?= Log::getInstance()->logTable($id_pg, LOGGEDIN, 10, 'Log Login'); ?>
    </div>

    <hr>
    <div class="fake-table log_table">
        <?= Log::getInstance()->logTable($id_pg, ACCOUNTMULTIPLO, 10, 'Log Account multiplo'); ?>
    </div>

    <hr>
    <div class="fake-table log_table">
        <?= Log::getInstance()->logTable($id_pg, ERRORELOGIN, 10, 'Log Errore login'); ?>
    </div>

    <hr>
    <div class="fake-table log_table">
        <?= Log::getInstance()->logTable($id_pg, NUOVOLAVORO, 10, 'Log Lavoro'); ?>
    </div>

    <hr>
    <div class="fake-table log_table">
        <?= Log::getInstance()->logTable($id_pg, DIMISSIONE, 10, 'Log Dimissioni'); ?>
    </div>

    <hr>
    <div class="fake-table log_table">
        <?= Log::getInstance()->logTable($id_pg, CHANGEDROLE, 10, 'Log Cambio ruolo'); ?>
    </div>

    <hr>
    <div class="fake-table log_table">
        <?= Log::getInstance()->logTable($id_pg, CHANGEDPASS, 10, 'Log Cambio Password'); ?>
    </div>

    <hr>

    <div class="fake-table log_table">
        <?= Log::getInstance()->logTable($id_pg, CHANGEDNAME, 10, 'Log Cambio Nome'); ?>
    </div>

<?php } ?>