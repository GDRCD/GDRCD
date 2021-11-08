<?php

class ObjectsKey extends DbMigration
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function up()
    {
        DB::query("ALTER TABLE clgpersonaggiooggetto
    DROP PRIMARY KEY,
ADD COLUMN `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY");
    }
    
    public function down()
    {
        DB::query("ALTER TABLE clgpersonaggiooggetto
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`nome`,`id_oggetto`);");
        
        DB::query("ALTER TABLE clgpersonaggiooggetto
DROP COLUMN `id`");
    }
}
