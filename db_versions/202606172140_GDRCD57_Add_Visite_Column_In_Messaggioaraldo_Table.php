<?php

class GDRCD57_Add_Visite_Column_In_Messaggioaraldo_Table extends DbMigration
{
    public function up() {
        // Aggiunge il contatore visite ai thread del forum
        gdrcd_query("ALTER TABLE `messaggioaraldo` ADD COLUMN `visite` bigint NOT NULL DEFAULT '0'");
    }

    public function down() {
        // Rimuove la colonna creata in up()
        gdrcd_query("ALTER TABLE `messaggioaraldo` DROP COLUMN `visite`");
    }
}
