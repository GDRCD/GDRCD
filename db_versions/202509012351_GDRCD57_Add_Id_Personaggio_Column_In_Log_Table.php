<?php

class GDRCD57_Add_Id_Personaggio_Column_In_Log_Table extends DbMigration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        gdrcd_query("ALTER TABLE `log` ADD COLUMN `id_personaggio` INT UNSIGNED DEFAULT NULL AFTER `id`");
        gdrcd_query("ALTER TABLE `log` ADD INDEX (`id_personaggio`)");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        // Remove id_personaggio column and its index
        gdrcd_query("ALTER TABLE `log` DROP INDEX `id_personaggio`");
        gdrcd_query("ALTER TABLE `log` DROP COLUMN `id_personaggio`");
    }
}
