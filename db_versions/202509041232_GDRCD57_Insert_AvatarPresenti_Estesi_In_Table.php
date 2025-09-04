<?php

class GDRCD57_Insert_AvatarPresenti_Estesi_In_Table extends DbMigration
{
    public function up() {
        // Inserimento parametro su tabella configurazioni
       
        gdrcd_query("INSERT INTO `configurazioni` (`tipo`, `categoria`, `ordinamento`, `opzioni`, `default`, `parametro`, `valore`, `descrizione`, `input`) VALUES ('string', 'presenti', 1, NULL, 'si,no', 'avatar', 'no', 'Attiva/disattiva il mini avatar nella lista dei presenti estesi', 'select')");
   

    }

    public function down() {
        // Rimozione tabelle create in up()
        gdrcd_query("DROP TABLE IF EXISTS `configurazioni`");
    }
}
