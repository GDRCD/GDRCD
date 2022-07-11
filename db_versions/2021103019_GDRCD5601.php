<?php

class GDRCD5601 extends DbMigration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        gdrcd_query("CREATE TABLE IF NOT EXISTS diario (
            id int NOT NULL AUTO_INCREMENT,
            personaggio varchar(255) DEFAULT NULL,
            data datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            data_inserimento datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            data_modifica datetime DEFAULT NULL DEFAULT CURRENT_TIMESTAMP,
            visibile varchar(255) NOT NULL DEFAULT 1,
            titolo varchar(255) NOT NULL DEFAULT '',
            testo text DEFAULT NULL,
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
