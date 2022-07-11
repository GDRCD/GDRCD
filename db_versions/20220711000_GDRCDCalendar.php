<?php

class GDRCDCalendar extends DbMigration
{
    /**
     * @inheritDoc
     */
    public function up()
    {

        gdrcd_query("CREATE TABLE IF NOT EXISTS eventi (
            id INT(11) NOT NULL AUTO_INCREMENT,
            title VARCHAR(255)DEFAULT NULL,
            start DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            end DATETIME NULL DEFAULT NULL DEFAULT CURRENT_TIMESTAMP,
            titolo VARCHAR(250) DEFAULT NULL,
            descrizione TEXT DEFAULT NULL,
            backgroundColor VARCHAR(50) DEFAULT NULL,
            borderColor VARCHAR(50) DEFAULT NULL,
            textColor VARCHAR(50) DEFAULT NULL,
            PRIMARY KEY (id) USING BTREE
        )ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        gdrcd_query("DROP TABLE IF EXISTS eventi");

    }
}
