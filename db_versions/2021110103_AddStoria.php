<?php

class AddStoria extends DbMigration
{
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * @inheritDoc
     */
    public function up()
    {
        DB::query("ALTER TABLE personaggio
ADD COLUMN storia text");
    }
    
    /**
     * @inheritDoc
     */
    public function down()
    {
        DB::query("ALTER TABLE personaggio
DROP COLUMN storia");
    }
}
