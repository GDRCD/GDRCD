<?php

class GDRCD57_Create_Sessions_Protection_Token_Table extends DbMigration
{
    public function up() {
        // Creazione tabella sessions_protection_token
        gdrcd_query("CREATE TABLE `sessions_protection_token` (
            `id_personaggio` INT UNSIGNED NOT NULL,
            `token` VARCHAR(60) NOT NULL,
            `data_creazione` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `data_scadenza` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `data_utilizzo` TIMESTAMP NULL DEFAULT NULL,
            `id_sessione` VARCHAR(255) NULL DEFAULT NULL,
            PRIMARY KEY (`id_personaggio`, `token`),
            INDEX `idx_data_creazione` (`data_creazione`)
        ) ENGINE=InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci");
    }

    public function down() {
        // Rimozione tabella sessions_protection_token
        gdrcd_query("DROP TABLE IF EXISTS `sessions_protection_token`");
    }
}
