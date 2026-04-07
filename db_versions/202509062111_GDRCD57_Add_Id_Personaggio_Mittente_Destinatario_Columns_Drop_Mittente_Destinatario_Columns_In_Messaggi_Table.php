<?php

class GDRCD57_Add_Id_Personaggio_Mittente_Destinatario_Columns_Drop_Mittente_Destinatario_Columns_In_Messaggi_Table extends DbMigration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        // Aggiunge le colonne id_personaggio_mittente e id_personaggio_destinatario dopo id
        gdrcd_query("ALTER TABLE `messaggi` ADD COLUMN `id_personaggio_mittente` INT UNSIGNED AFTER `id`");
        gdrcd_query("ALTER TABLE `messaggi` ADD COLUMN `id_personaggio_destinatario` INT UNSIGNED AFTER `id_personaggio_mittente`");
        // Rimuove le colonne mittente e destinatario
        gdrcd_query("ALTER TABLE `messaggi` DROP COLUMN `mittente`");
        gdrcd_query("ALTER TABLE `messaggi` DROP COLUMN `destinatario`");
        // Aggiunge indici
        gdrcd_query("ALTER TABLE `messaggi` ADD INDEX (`id_personaggio_mittente`)");
        gdrcd_query("ALTER TABLE `messaggi` ADD INDEX (`id_personaggio_destinatario`)");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        // Rimuove gli indici
        gdrcd_query("ALTER TABLE `messaggi` DROP INDEX `id_personaggio_mittente`");
        gdrcd_query("ALTER TABLE `messaggi` DROP INDEX `id_personaggio_destinatario`");
        // Rimuove le colonne id_personaggio_mittente e id_personaggio_destinatario
        gdrcd_query("ALTER TABLE `messaggi` DROP COLUMN `id_personaggio_mittente`");
        gdrcd_query("ALTER TABLE `messaggi` DROP COLUMN `id_personaggio_destinatario`");
        // Ripristina le colonne mittente e destinatario (VARCHAR(40) NOT NULL, adattare se diverso)
        gdrcd_query("ALTER TABLE `messaggi` ADD COLUMN `mittente` VARCHAR(40) NOT NULL AFTER `id`");
        gdrcd_query("ALTER TABLE `messaggi` ADD COLUMN `destinatario` VARCHAR(40) NOT NULL AFTER `mittente`");
    }
}
