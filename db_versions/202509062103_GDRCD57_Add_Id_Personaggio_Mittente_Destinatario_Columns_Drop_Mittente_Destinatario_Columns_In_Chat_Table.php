<?php

class GDRCD57_Add_Id_Personaggio_Mittente_Destinatario_Columns_Drop_Mittente_Destinatario_Columns_In_Chat_Table extends DbMigration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        // Aggiunge le colonne id_personaggio_mittente (NOT NULL) e id_personaggio_destinatario (DEFAULT NULL) dopo id
        gdrcd_query("ALTER TABLE `chat` ADD COLUMN `id_personaggio_mittente` INT UNSIGNED NOT NULL AFTER `id`");
        gdrcd_query("ALTER TABLE `chat` ADD COLUMN `id_personaggio_destinatario` INT UNSIGNED DEFAULT NULL AFTER `id_personaggio_mittente`");
        gdrcd_query("ALTER TABLE `chat` ADD COLUMN `tag_posizione` VARCHAR(50) DEFAULT NULL AFTER `id_personaggio_destinatario`");
        // Rimuove le colonne mittente e destinatario
        gdrcd_query("ALTER TABLE `chat` DROP COLUMN `mittente`");
        gdrcd_query("ALTER TABLE `chat` DROP COLUMN `destinatario`");
        // Aggiunge indici
        gdrcd_query("ALTER TABLE `chat` ADD INDEX (`id_personaggio_mittente`)");
        gdrcd_query("ALTER TABLE `chat` ADD INDEX (`id_personaggio_destinatario`)");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        // Rimuove gli indici
        gdrcd_query("ALTER TABLE `chat` DROP INDEX `id_personaggio_mittente`");
        gdrcd_query("ALTER TABLE `chat` DROP INDEX `id_personaggio_destinatario`");
        // Rimuove le colonne id_personaggio_mittente e id_personaggio_destinatario
        gdrcd_query("ALTER TABLE `chat` DROP COLUMN `id_personaggio_mittente`");
        gdrcd_query("ALTER TABLE `chat` DROP COLUMN `id_personaggio_destinatario`");
        gdrcd_query("ALTER TABLE `chat` DROP COLUMN `tag_posizione`");
        // Ripristina le colonne mittente e destinatario (VARCHAR(30) NOT NULL, adattare se diverso)
        gdrcd_query("ALTER TABLE `chat` ADD COLUMN `mittente` VARCHAR(30) NOT NULL AFTER `id`");
        gdrcd_query("ALTER TABLE `chat` ADD COLUMN `destinatario` VARCHAR(30) DEFAULT NULL AFTER `mittente`");
    }
}
