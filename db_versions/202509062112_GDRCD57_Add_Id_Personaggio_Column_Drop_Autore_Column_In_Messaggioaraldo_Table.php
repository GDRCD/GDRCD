<?php

class GDRCD57_Add_Id_Personaggio_Column_Drop_Autore_Column_In_Messaggioaraldo_Table extends DbMigration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        // Aggiunge la colonna id_personaggio dopo id_araldo
        gdrcd_query("ALTER TABLE `messaggioaraldo` ADD COLUMN `id_personaggio` INT UNSIGNED AFTER `id_araldo`");
        // Rimuove la colonna autore
        gdrcd_query("ALTER TABLE `messaggioaraldo` DROP COLUMN `autore`");
        // Aggiunge un indice su id_personaggio
        gdrcd_query("ALTER TABLE `messaggioaraldo` ADD INDEX (`id_personaggio`)");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        // Rimuove l'indice su id_personaggio
        gdrcd_query("ALTER TABLE `messaggioaraldo` DROP INDEX `id_personaggio`");
        // Rimuove la colonna id_personaggio
        gdrcd_query("ALTER TABLE `messaggioaraldo` DROP COLUMN `id_personaggio`");
        // Ripristina la colonna autore (VARCHAR(20) DEFAULT NULL)
        gdrcd_query("ALTER TABLE `messaggioaraldo` ADD COLUMN `autore` VARCHAR(20) DEFAULT NULL AFTER `messaggio`");
    }
}
