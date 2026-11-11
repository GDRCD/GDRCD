<?php

class GDRCD57_Add_Id_Personaggio_Column_Drop_Utilizzato_Da_Column_In_Token_Iscrizione_Table extends DbMigration
{
    public function up() {
        // Aggiunge la colonna id_personaggio dopo utilizzato
        gdrcd_query("ALTER TABLE `token_iscrizione` ADD COLUMN `id_personaggio` INT UNSIGNED NULL DEFAULT NULL AFTER `utilizzato`");
        // Rimuove la colonna utilizzato_da
        gdrcd_query("ALTER TABLE `token_iscrizione` DROP COLUMN `utilizzato_da`");
        // Aggiunge un indice su id_personaggio
        gdrcd_query("ALTER TABLE `token_iscrizione` ADD INDEX (`id_personaggio`)");
    }

    public function down() {
        // Rimuove l'indice su id_personaggio
        gdrcd_query("ALTER TABLE `token_iscrizione` DROP INDEX `id_personaggio`");
        // Rimuove la colonna id_personaggio
        gdrcd_query("ALTER TABLE `token_iscrizione` DROP COLUMN `id_personaggio`");
        // Ripristina la colonna utilizzato_da (VARCHAR(50) NULL DEFAULT NULL)
        gdrcd_query("ALTER TABLE `token_iscrizione` ADD COLUMN `utilizzato_da` VARCHAR(50) NULL DEFAULT NULL AFTER `utilizzato`");
    }
}
