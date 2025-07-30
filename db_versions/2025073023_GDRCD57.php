<?php

class GDRCD57 extends DbMigration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        gdrcd_query("ALTER TABLE abilita ADD COLUMN dice TINYINT(3) UNSIGNED NULL DEFAULT NULL AFTER car");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        gdrcd_query("ALTER TABLE abilita DROP COLUMN dice");
    }
}
