<?php

class GDRCD57_Add_Id_Personaggio_Destinatario_Autore_Master_Columns_Drop_Autore_Pg_Master_Columns_In_Esiti_Table extends DbMigration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        // Aggiunge le colonne dopo 'titolo'
        gdrcd_query("ALTER TABLE `esiti` ADD COLUMN `id_personaggio_destinatario` INT UNSIGNED NOT NULL AFTER `titolo`");
        gdrcd_query("ALTER TABLE `esiti` ADD COLUMN `id_personaggio_autore` INT UNSIGNED NOT NULL AFTER `id_personaggio_destinatario`");
        gdrcd_query("ALTER TABLE `esiti` ADD COLUMN `id_personaggio_master` INT UNSIGNED DEFAULT NULL AFTER `id_personaggio_autore`");
        // Rimuove le colonne autore, pg e master
        gdrcd_query("ALTER TABLE `esiti` DROP COLUMN `autore`");
        gdrcd_query("ALTER TABLE `esiti` DROP COLUMN `pg`");
        gdrcd_query("ALTER TABLE `esiti` DROP COLUMN `master`");
        // Aggiunge indici
        gdrcd_query("ALTER TABLE `esiti` ADD INDEX (`id_personaggio_destinatario`)");
        gdrcd_query("ALTER TABLE `esiti` ADD INDEX (`id_personaggio_autore`)");
        gdrcd_query("ALTER TABLE `esiti` ADD INDEX (`id_personaggio_master`)");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        // Rimuove gli indici
        gdrcd_query("ALTER TABLE `esiti` DROP INDEX `id_personaggio_destinatario`");
        gdrcd_query("ALTER TABLE `esiti` DROP INDEX `id_personaggio_autore`");
        gdrcd_query("ALTER TABLE `esiti` DROP INDEX `id_personaggio_master`");
        // Rimuove le colonne id_personaggio_*
        gdrcd_query("ALTER TABLE `esiti` DROP COLUMN `id_personaggio_destinatario`");
        gdrcd_query("ALTER TABLE `esiti` DROP COLUMN `id_personaggio_autore`");
        gdrcd_query("ALTER TABLE `esiti` DROP COLUMN `id_personaggio_master`");
        // Ripristina le colonne autore, pg e master (VARCHAR(255) NOT NULL)
        gdrcd_query("ALTER TABLE `esiti` ADD COLUMN `autore` VARCHAR(255) NOT NULL AFTER `titolo`");
        gdrcd_query("ALTER TABLE `esiti` ADD COLUMN `pg` VARCHAR(255) NOT NULL AFTER `autore`");
        gdrcd_query("ALTER TABLE `esiti` ADD COLUMN `master` VARCHAR(255) NOT NULL AFTER `pg`");
    }
}
