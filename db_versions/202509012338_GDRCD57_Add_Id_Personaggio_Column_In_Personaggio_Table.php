<?php

class GDRCD57_Add_Id_Personaggio_Column_In_Personaggio_Table extends DbMigration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        gdrcd_query("ALTER TABLE `personaggio` ADD COLUMN `id_personaggio` INT UNSIGNED NOT NULL AUTO_INCREMENT FIRST, ADD UNIQUE KEY(`id_personaggio`)");
        gdrcd_query("ALTER TABLE `personaggio` DROP PRIMARY KEY");
        gdrcd_query("ALTER TABLE `personaggio` ADD PRIMARY KEY (`id_personaggio`)");
        gdrcd_query("ALTER TABLE `personaggio` ADD UNIQUE KEY(`nome`)");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        gdrcd_query("ALTER TABLE `personaggio` DROP PRIMARY KEY");
        gdrcd_query("ALTER TABLE `personaggio` DROP COLUMN `id`");
        gdrcd_query("ALTER TABLE `personaggio` DROP INDEX `nome`");
        gdrcd_query("ALTER TABLE `personaggio` ADD PRIMARY KEY (`nome`)");
    }
}
