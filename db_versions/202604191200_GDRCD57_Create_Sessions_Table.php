<?php

class GDRCD57_Create_Sessions_Table extends DbMigration
{
    public function up() {
        // Creazione tabella sessions
        gdrcd_query("CREATE TABLE `sessions` (
            `id_sessione` VARCHAR(255) NOT NULL,
            `id_personaggio` INT UNSIGNED NOT NULL,
            `status` ENUM('active', 'refreshed', 'revoked') NOT NULL DEFAULT 'active',
            `data_creazione` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `data_refresh` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `data_refreshed_at` TIMESTAMP NULL DEFAULT NULL,
            `data_scadenza` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `data_ultimavisita` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `data_login` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `data_logout` TIMESTAMP NULL DEFAULT NULL,
            `ip` VARBINARY(16) NOT NULL,
            `client` JSON NOT NULL,
            `id_sessione_next` VARCHAR(255) NULL DEFAULT NULL,
            PRIMARY KEY (`id_sessione`),
            INDEX `idx_personaggio_status` (`id_personaggio`, `status`),
            INDEX `idx_data_scadenza` (`data_scadenza`),
            INDEX `idx_ip` (`ip`)
        ) ENGINE=InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci");
    }

    public function down() {
        // Rimozione tabella sessions
        gdrcd_query("DROP TABLE IF EXISTS `sessions`");
    }
}
