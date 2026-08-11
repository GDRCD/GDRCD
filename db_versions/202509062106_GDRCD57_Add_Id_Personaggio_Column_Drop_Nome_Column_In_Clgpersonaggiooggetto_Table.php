<?php

class GDRCD57_Add_Id_Personaggio_Column_Drop_Nome_Column_In_Clgpersonaggiooggetto_Table extends DbMigration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        // Aggiunge la colonna id_personaggio dopo nome
        gdrcd_query("ALTER TABLE `clgpersonaggiooggetto` ADD COLUMN `id_personaggio` INT UNSIGNED AFTER `nome`");
        // Imposta la nuova chiave primaria composta
        gdrcd_query("ALTER TABLE `clgpersonaggiooggetto` DROP PRIMARY KEY, ADD PRIMARY KEY (`id_personaggio`, `id_oggetto`)");
        // Rimuove la colonna nome
        gdrcd_query("ALTER TABLE `clgpersonaggiooggetto` DROP COLUMN `nome`");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        // Ripristina la colonna nome (VARCHAR(20) NOT NULL, adattare se diverso)
        gdrcd_query("ALTER TABLE `clgpersonaggiooggetto` ADD COLUMN `nome` VARCHAR(20) NOT NULL AFTER `id_personaggio`");
        // Ripristina la chiave primaria su nome e id_oggetto
        gdrcd_query("ALTER TABLE `clgpersonaggiooggetto` DROP PRIMARY KEY, ADD PRIMARY KEY (`nome`, `id_oggetto`)");
        // Rimuove la colonna id_personaggio
        gdrcd_query("ALTER TABLE `clgpersonaggiooggetto` DROP COLUMN `id_personaggio`");
    }
}
