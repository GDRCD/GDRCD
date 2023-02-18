<?php

class GDRCD5606 extends DbMigration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        gdrcd_query("ALTER TABLE mappa_click ADD COLUMN principale tinyint(1) NOT NULL DEFAULT 0 AFTER mobile");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        gdrcd_query("ALTER TABLE mappa_click DROP COLUMN principale");
    }
}
