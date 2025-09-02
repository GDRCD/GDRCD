<?php

class GDRCD57_Create_Configurazioni_Table extends DbMigration
{
    public function up() {
        // Creazione tabella configurazioni
        gdrcd_query("CREATE TABLE `configurazioni` (
            `id` INT(10) NOT NULL AUTO_INCREMENT,
            `tipo` VARCHAR(50) NULL DEFAULT NULL,
            `categoria` VARCHAR(50) NULL DEFAULT NULL,
            `ordinamento` INT(10) NULL DEFAULT NULL,
            `opzioni` VARCHAR(50) NULL DEFAULT NULL,
            `default` VARCHAR(255) NULL DEFAULT NULL,
            `parametro` VARCHAR(50) NULL DEFAULT NULL,
            `valore` TEXT NULL DEFAULT NULL,
            `descrizione` TEXT NULL DEFAULT NULL,
            `input` VARCHAR(50) NULL DEFAULT NULL,
            PRIMARY KEY (`id`) USING BTREE,
            UNIQUE KEY `unique_categoria_parametro` (`categoria`, `parametro`)
        ) ENGINE=InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci");

        // Inserimento dati iniziali configurazioni
        gdrcd_query("INSERT INTO `configurazioni` (`id`, `tipo`, `categoria`, `ordinamento`, `opzioni`, `default`, `parametro`, `valore`, `descrizione`, `input`) VALUES (1, 'string', 'registrazione', 1, 'Aperto,Chiuso,Su invito', 'Aperto', 'stato_registrazione', 'Aperto', 'Imposta lo stato delle iscrizioni della land. Se aperto permette l\\iscrizione, se chiuso inibisce le iscrizioni. Su invito permette di generare un token da fornire per l\\iscrizione', 'select')");
        
        gdrcd_query("INSERT INTO `configurazioni` (`id`, `tipo`, `categoria`, `ordinamento`, `opzioni`, `default`, `parametro`, `valore`, `descrizione`, `input`) VALUES (2, 'string', 'registrazione', 2, NULL, 'Le registrazioni sono attualmente chiuse. Riprova più tardi.', 'messaggio_registrazione', NULL, 'Messaggio che appare nel caso delle iscrizioni chiuse al posto del form di iscrizione', 'textarea')");

    }

    public function down() {
        // Rimozione tabelle create in up()
        gdrcd_query("DROP TABLE IF EXISTS `configurazioni`");
    }
}