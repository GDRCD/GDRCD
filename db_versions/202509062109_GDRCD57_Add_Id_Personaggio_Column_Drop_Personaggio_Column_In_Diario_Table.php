<?php

class GDRCD57_Add_Id_Personaggio_Column_Drop_Personaggio_Column_In_Diario_Table extends DbMigration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        // Aggiunge la colonna id_personaggio dopo personaggio
        gdrcd_query("ALTER TABLE `diario` ADD COLUMN `id_personaggio` INT UNSIGNED AFTER `personaggio`");
        // Rimuove la colonna personaggio
        gdrcd_query("ALTER TABLE `diario` DROP COLUMN `personaggio`");
        // Aggiunge un indice su id_personaggio
        gdrcd_query("ALTER TABLE `diario` ADD INDEX (`id_personaggio`)");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        // Rimuove l'indice su id_personaggio
        gdrcd_query("ALTER TABLE `diario` DROP INDEX `id_personaggio`");
        // Rimuove la colonna id_personaggio
        gdrcd_query("ALTER TABLE `diario` DROP COLUMN `id_personaggio`");
        // Ripristina la colonna personaggio (VARCHAR(255) NOT NULL, adattare se diverso)
        gdrcd_query("ALTER TABLE `diario` ADD COLUMN `personaggio` VARCHAR(255) NOT NULL AFTER `id`");
    }
}
