<?php

class GDRCD57_Add_Id_Personaggio_Column_Drop_Personaggio_Column_In_Clgpersonaggioruolo_Table extends DbMigration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        // Aggiunge la colonna id_personaggio dopo personaggio
        gdrcd_query("ALTER TABLE `clgpersonaggioruolo` ADD COLUMN `id_personaggio` INT UNSIGNED AFTER `personaggio`");
        // Imposta la nuova chiave primaria composta
        gdrcd_query("ALTER TABLE `clgpersonaggioruolo` ADD PRIMARY KEY (`id_personaggio`, `id_ruolo`)");
        // Rimuove la colonna personaggio
        gdrcd_query("ALTER TABLE `clgpersonaggioruolo` DROP COLUMN `personaggio`");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        // Ripristina la colonna personaggio (VARCHAR(20) NOT NULL, adattare se diverso)
        gdrcd_query("ALTER TABLE `clgpersonaggioruolo` ADD COLUMN `personaggio` VARCHAR(20) NOT NULL AFTER `id_personaggio`");
        // Ripristina la chiave primaria su personaggio e id_ruolo
        gdrcd_query("ALTER TABLE `clgpersonaggioruolo` DROP PRIMARY KEY");
        // Rimuove la colonna id_personaggio
        gdrcd_query("ALTER TABLE `clgpersonaggioruolo` DROP COLUMN `id_personaggio`");
    }
}
