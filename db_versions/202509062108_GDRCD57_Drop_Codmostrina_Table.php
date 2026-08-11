<?php

class GDRCD57_Drop_Codmostrina_Table extends DbMigration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        gdrcd_query("DROP TABLE IF EXISTS `codmostrina`");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        gdrcd_query("CREATE TABLE codmostrina (
            id_mostrina int(4) NOT NULL auto_increment,
            nome varchar(20) NOT NULL,
            img_url char(50) NOT NULL default 'grigia.gif',
            descrizione char(255) NOT NULL default 'nessuna',
            PRIMARY KEY  (id_mostrina)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    }
}
