<?php

class GDRCD57_Add_Id_Personaggio_Column_Drop_Nome_Column_In_Clgpersonaggiomostrine_Table extends DbMigration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        // Aggiunge la colonna id_personaggio dopo nome
        gdrcd_query("ALTER TABLE `clgpersonaggiomostrine` ADD COLUMN `id_personaggio` INT UNSIGNED AFTER `nome`");
        // Imposta la nuova chiave primaria composta
        gdrcd_query("ALTER TABLE `clgpersonaggiomostrine` DROP PRIMARY KEY, ADD PRIMARY KEY (`id_personaggio`, `id_mostrina`)");
        // Rimuove la colonna nome
        gdrcd_query("ALTER TABLE `clgpersonaggiomostrine` DROP COLUMN `nome`");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        // Ripristina la colonna nome (VARCHAR(20) NOT NULL, adattare se diverso)
        gdrcd_query("ALTER TABLE `clgpersonaggiomostrine` ADD COLUMN `nome` VARCHAR(20) NOT NULL AFTER `id_personaggio`");
        // Ripristina la chiave primaria su nome e id_mostrina
        gdrcd_query("ALTER TABLE `clgpersonaggiomostrine` DROP PRIMARY KEY, ADD PRIMARY KEY (`nome`, `id_mostrina`)");
        // Rimuove la colonna id_personaggio
        gdrcd_query("ALTER TABLE `clgpersonaggiomostrine` DROP COLUMN `id_personaggio`");
    }
}
