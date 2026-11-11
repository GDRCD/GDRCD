<?php

class GDRCD57_Add_Id_Personaggio_Column_Drop_Nome_Column_In_Araldo_Letto_Table extends DbMigration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        // Aggiunge la colonna id_personaggio dopo id
        gdrcd_query("ALTER TABLE `araldo_letto` ADD COLUMN `id_personaggio` INT UNSIGNED AFTER `id`");
        // Rimuove la colonna nome
        gdrcd_query("ALTER TABLE `araldo_letto` DROP COLUMN `nome`");
        // Aggiunge un indice su id_personaggio
        gdrcd_query("ALTER TABLE `araldo_letto` ADD INDEX (`id_personaggio`)");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        // Rimuove l'indice su id_personaggio
        gdrcd_query("ALTER TABLE `araldo_letto` DROP INDEX `id_personaggio`");
        // Rimuove la colonna id_personaggio
        gdrcd_query("ALTER TABLE `araldo_letto` DROP COLUMN `id_personaggio`");
        // Ripristina la colonna nome (VARCHAR(50) NOT NULL, adattare se diverso)
        gdrcd_query("ALTER TABLE `araldo_letto` ADD COLUMN `nome` VARCHAR(50) NOT NULL AFTER `id`");
    }
}
