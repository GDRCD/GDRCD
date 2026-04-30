<?php

class GDRCD57_Add_Id_Personaggio_Column_Drop_Mittente_Column_In_Segnalazione_Role_Table extends DbMigration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        // Aggiunge la colonna id_personaggio dopo stanza
        gdrcd_query("ALTER TABLE `segnalazione_role` ADD COLUMN `id_personaggio` INT UNSIGNED AFTER `stanza`");
        // Rimuove la colonna mittente
        gdrcd_query("ALTER TABLE `segnalazione_role` DROP COLUMN `mittente`");
        // Aggiunge un indice su id_personaggio
        gdrcd_query("ALTER TABLE `segnalazione_role` ADD INDEX (`id_personaggio`)");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        // Rimuove l'indice su id_personaggio
        gdrcd_query("ALTER TABLE `segnalazione_role` DROP INDEX `id_personaggio`");
        // Rimuove la colonna id_personaggio
        gdrcd_query("ALTER TABLE `segnalazione_role` DROP COLUMN `id_personaggio`");
        // Ripristina la colonna mittente (VARCHAR(20) NOT NULL)
        gdrcd_query("ALTER TABLE `segnalazione_role` ADD COLUMN `mittente` VARCHAR(20) NOT NULL AFTER `stanza`");
    }
}
