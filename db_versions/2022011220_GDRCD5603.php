<?php

class GDRCD5603 extends DbMigration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        gdrcd_query("ALTER TABLE messaggioaraldo CHANGE messaggio messaggio longtext DEFAULT NULL");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        gdrcd_query("ALTER TABLE messaggioaraldo CHANGE messaggio messaggio text DEFAULT NULL");
    }
}
