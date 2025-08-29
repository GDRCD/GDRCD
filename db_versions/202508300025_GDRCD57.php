<?php

class GDRCD57 extends DbMigration
{
    public function up() {
        // Creazione tabella configurazioni
        gdrcd_query("CREATE TABLE `configurazioni` (
            `id` INT(10) NOT NULL AUTO_INCREMENT,
            `tipo` VARCHAR(50) NULL DEFAULT NULL,
            `categoria` VARCHAR(50) NULL DEFAULT NULL,
            `ordinamento` INT(10) NULL DEFAULT NULL,
            `opzioni` VARCHAR(50) NULL DEFAULT NULL,
            `default` VARCHAR(50) NULL DEFAULT NULL,
            `parametro` VARCHAR(50) NULL DEFAULT NULL,
            `valore` TEXT NULL DEFAULT NULL,
            `descrizione` TEXT NULL DEFAULT NULL,
            `input` VARCHAR(50) NULL DEFAULT NULL,
            PRIMARY KEY (`id`) USING BTREE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4_general_ci");

        // Inserimento dati iniziali configurazioni
        gdrcd_query("INSERT INTO `configurazioni` (`id`, `tipo`, `categoria`, `ordinamento`, `opzioni`, `default`, `parametro`, `valore`, `descrizione`, `input`) VALUES (1, 'string', 'Registrazione', 1, 'Aperto,Chiuso,Su invito', 'Aperto', 'Stato Registrazione', 'Aperto', 'Imposta lo stato delle iscrizioni della land. Se aperto permette l\\iscrizione, se chiuso inibisce le iscrizioni. Su invito permette di generare un token da fornire per l\\iscrizione', 'select')");
        
        gdrcd_query("INSERT INTO `configurazioni` (`id`, `tipo`, `categoria`, `ordinamento`, `opzioni`, `default`, `parametro`, `valore`, `descrizione`, `input`) VALUES (2, 'string', 'Registrazione', 2, NULL, NULL, 'Messaggio Registrazione', NULL, 'Messaggio che appare nel caso delle iscrizioni chiuse al posto del form di iscrizione', 'textarea')");

        // Creazione tabella token_iscrizione
        gdrcd_query("CREATE TABLE `token_iscrizione` (
            `id` INT(10) NOT NULL AUTO_INCREMENT,
            `valore` VARCHAR(50) NULL DEFAULT NULL,
            `creato_il` DATE NULL DEFAULT NULL,
            `scadenza` DATE NULL DEFAULT NULL,
            `utilizzato` INT(10) NULL DEFAULT NULL,
            `utilizzato_da` VARCHAR(50) NULL DEFAULT NULL,
            `data_utilizzo` DATE NULL DEFAULT NULL,
            PRIMARY KEY (`id`) USING BTREE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4_general_ci");
    }

    public function down() {
        // Rimozione tabelle create in up()
        gdrcd_query("DROP TABLE IF EXISTS `token_iscrizione`");
        gdrcd_query("DROP TABLE IF EXISTS `configurazioni`");
    }
}