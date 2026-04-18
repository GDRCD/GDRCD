<?php

class Insert_ChatSave_In_Configurazioni_Table  extends DbMigration
{
    public function up() {
        // Inserimento parametri su tabella configurazioni
       
        gdrcd_query("INSERT INTO `configurazioni` (`tipo`, `categoria`, `ordinamento`, `opzioni`, `default`, `parametro`, `valore`, `descrizione`, `input`) VALUES ('string', 'salva_chat', 1, 'si,no', 'no', 'controllo_salvataggio', 'si', 'Controlla che chi salva la chat abbia mandato un\'azione in chat', 'select')");
        gdrcd_query("INSERT INTO `configurazioni` (`tipo`, `categoria`, `ordinamento`, `opzioni`, `default`, `parametro`, `valore`, `descrizione`, `input`) VALUES ('string', 'salva_chat', 2, '', '2', 'tempo_salvataggio', '2', 'Imposta il numero massimo di ore scaricabili con il salva chat', 'input')");
    }

    public function down() {
        // Rimozione tabelle create in up()
        gdrcd_query("DROP TABLE IF EXISTS `configurazioni`");
    }
}