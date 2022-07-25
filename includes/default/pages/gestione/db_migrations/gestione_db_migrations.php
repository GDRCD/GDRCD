<?php

if ( !Permissions::permission("MANAGE_DB_MIGRATIONS") ) {
    die('Permesso negato.');
}

?>
<div class="gestione_pagina gestione_db_migrations">
    <div class="gestione_incipit">
        <div class="title">Gestione Versioni del Database</div>
        Questa sezione è utilizzata per la gestione delle versioni del database.
    </div>

    <?php

    if ( DbMigrationEngine::dbNeedsUpdate() and !empty($_POST['do_update']) ) {
        try {
            $target = empty($_POST['target']) ? null : (int)$_POST['target'];
            DbMigrationEngine::updateDbSchema($target);
            echo '<div class="warning">' . gdrcd_filter('out', $MESSAGE['homepage']['installer']['done']) . '</div>';
        } catch ( Exception $e ) {
            echo '<div class="warning">' . gdrcd_filter('out', $e->getMessage()) . '</div>';
        }
    }

    ?>

    <div class="gestione_form_container">
        <div class="warning"><?php
            if ( DbMigrationEngine::dbNeedsInstallation() ) {
                echo 'Necessario installare il DB!';//Impossibile in realtà
            } else if ( DbMigrationEngine::dbNeedsUpdate() ) {
                echo 'Necessario installare un aggiornamento al Database GDRCD!';
            } else {
                echo 'Il Database GDRCD risulta aggiornato all\'ultima versione';
            }
            ?></div>

        <form method="post">
            <div class='form_label'>Porta il database alla seguente versione</div>
            <div class="form_info">ATTENZIONE: questa funzione modifica solo la struttura del database, non
                può ripristinare dati che sono stati cancellati
            </div>
            <div class='form_field'>
                <select name="target" id="target">
                    <?php
                    $migrations = DbMigrationEngine::getAllAvailableMigrations();
                    $last = DbMigrationEngine::getLastAppliedMigration();
                    foreach ( $migrations as $m ) {
                        echo '<option value="' . $m->getMigrationId() . '" '
                            . ($m->getMigrationId() == $last['migration_id'] ? 'selected="selected"' : '')
                            . '>' . gdrcd_filter('out', get_class($m)) . '</option>';
                    }
                    ?>
                </select>
            </div>

            <input type="submit" name="do_update" value="Esegui"/>
        </form>

    </div>
</div>
