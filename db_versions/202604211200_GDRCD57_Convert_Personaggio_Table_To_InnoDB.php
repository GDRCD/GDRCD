<?php

class GDRCD57_Convert_Personaggio_Table_To_InnoDB extends DbMigration
{
    public function up() {
        // Conversione della tabella personaggio da MyISAM a InnoDB
        gdrcd_query("ALTER TABLE `personaggio` ENGINE=InnoDB");
    }

    public function down() {
        // Ripristino della tabella personaggio a MyISAM
        gdrcd_query("ALTER TABLE `personaggio` ENGINE=MyISAM");
    }
}
