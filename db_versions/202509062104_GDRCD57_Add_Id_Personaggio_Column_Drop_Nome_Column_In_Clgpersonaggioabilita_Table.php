<?php

class GDRCD57_Add_Id_Personaggio_Column_Drop_Nome_Column_In_Clgpersonaggioabilita_Table extends DbMigration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        // Aggiunge la colonna id_personaggio dopo nome
        gdrcd_query("ALTER TABLE `clgpersonaggioabilita` ADD COLUMN `id_personaggio` INT UNSIGNED AFTER `nome`");
        // Imposta la nuova chiave primaria composta
        gdrcd_query("ALTER TABLE `clgpersonaggioabilita` ADD PRIMARY KEY (`id_personaggio`, `id_abilita`)");
        // Rimuove la colonna nome
        gdrcd_query("ALTER TABLE `clgpersonaggioabilita` DROP COLUMN `nome`");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        // Ripristina la colonna nome (VARCHAR(20) NOT NULL, adattare se diverso)
        gdrcd_query("ALTER TABLE `clgpersonaggioabilita` ADD COLUMN `nome` VARCHAR(20) NOT NULL AFTER `id_personaggio`");
        // Ripristina la chiave primaria su nome e id_abilita
        gdrcd_query("ALTER TABLE `clgpersonaggioabilita` DROP PRIMARY KEY");
        // Rimuove la colonna id_personaggio
        gdrcd_query("ALTER TABLE `clgpersonaggioabilita` DROP COLUMN `id_personaggio`");
    }
}
