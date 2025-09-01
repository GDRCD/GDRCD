<?php

class GDRCD57_Create_Token_Iscrizione_Table extends DbMigration
{
    public function up() {
       
        // Creazione tabella token_iscrizione
        gdrcd_query("CREATE TABLE `token_iscrizione` (
            `id` INT(10) NOT NULL AUTO_INCREMENT,
            `valore` VARCHAR(50) NULL DEFAULT NULL,
            `creato_il` DATE NULL DEFAULT NULL,
            `scadenza` DATE NULL DEFAULT NULL,
            `utilizzato` INT(10) NULL DEFAULT NULL,
            `utilizzato_da` VARCHAR(50) NULL DEFAULT NULL,
            `data_utilizzo` DATE NULL DEFAULT NULL,
            PRIMARY KEY (`id`) USING BTREE,
            UNIQUE INDEX `valore` (`valore`) USING BTREE
        ) ENGINE=InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci");
    }

    public function down() {
        // Rimozione tabelle create in up()
        gdrcd_query("DROP TABLE IF EXISTS `token_iscrizione`");
    }
}