<?php

class GDRCD56_fix1 extends DbMigration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        gdrcd_query("CREATE TABLE IF NOT EXISTS diario (
            id int NOT NULL AUTO_INCREMENT,
            personaggio varchar(255) DEFAULT NULL,
            data date NOT NULL,
            data_inserimento datetime NOT NULL,
            data_modifica datetime DEFAULT NULL,
            visibile varchar(255) NOT NULL,
            titolo varchar(255) NOT NULL DEFAULT '',
            testo text NOT NULL,
            PRIMARY KEY (id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        gdrcd_query("ALTER TABLE personaggio ADD storia text DEFAULT NULL;");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        gdrcd_query("DROP TABLE IF EXISTS diario");
        gdrcd_query("ALTER TABLE personaggio DROP COLUMN storia");
    }
}
