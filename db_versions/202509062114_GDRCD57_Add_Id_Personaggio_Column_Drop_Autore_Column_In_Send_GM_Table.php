<?php

class GDRCD57_Add_Id_Personaggio_Column_Drop_Autore_Column_In_Send_GM_Table extends DbMigration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        // Aggiunge la colonna id_personaggio dopo data
        gdrcd_query("ALTER TABLE `send_GM` ADD COLUMN `id_personaggio` INT UNSIGNED AFTER `data`");
        // Rimuove la colonna autore
        gdrcd_query("ALTER TABLE `send_GM` DROP COLUMN `autore`");
        // Aggiunge un indice su id_personaggio
        gdrcd_query("ALTER TABLE `send_GM` ADD INDEX (`id_personaggio`)");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        // Rimuove l'indice su id_personaggio
        gdrcd_query("ALTER TABLE `send_GM` DROP INDEX `id_personaggio`");
        // Rimuove la colonna id_personaggio
        gdrcd_query("ALTER TABLE `send_GM` DROP COLUMN `id_personaggio`");
        // Ripristina la colonna autore (TEXT NOT NULL)
        gdrcd_query("ALTER TABLE `send_GM` ADD COLUMN `autore` TEXT NOT NULL AFTER `data`");
    }
}
