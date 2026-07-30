<?php

class GDRCD57_Add_Id_Column_Drop_Column_In_Ambientazione_Table extends DbMigration
{
    public function up() {
        // Aggiunge la colonna id 
        gdrcd_query("ALTER TABLE `ambientazione` ADD COLUMN `id` INT NOT NULL AUTO_INCREMENT , ADD PRIMARY KEY (`id`);");
        
    }

    public function down() {
        // Rimuove la colonna id
        gdrcd_query("ALTER TABLE `ambientazione` DROP COLUMN `id`");
        // Rimuove la chiave primaria
        gdrcd_query("ALTER TABLE `ambientazione` DROP PRIMARY KEY");
        
    }
}
