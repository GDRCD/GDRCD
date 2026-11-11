<?php

class Insert_AvatarPresentiEstesi_In_Configurazioni_Table  extends DbMigration
{
    public function up() {
        // Inserimento parametro su tabella configurazioni
       
        gdrcd_query("INSERT INTO `configurazioni` (`id`, `tipo`, `categoria`, `ordinamento`, `opzioni`, `default`, `parametro`, `valore`, `descrizione`, `input`) VALUES (3, 'string', 'presenti_estesi', 1, 'si,no', 'no', 'avatar', 'no', 'Attiva/disattiva il mini avatar nella lista dei presenti estesi', 'select')");
   

    }

    public function down() {
        // Rimozione tabelle create in up()
        gdrcd_query("DROP TABLE IF EXISTS `configurazioni`");
    }
}
