<?php

class ObjectsKey extends DbMigration
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function up()
    {
        gdrcd_query("ALTER TABLE clgpersonaggiooggetto
ADD COLUMN `id` INT NOT NULL AUTO_INCREMENT");
        
        gdrcd_query("ALTER TABLE clgpersonaggiooggetto
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`id`);");
    }
    
    public function down()
    {
        gdrcd_query("ALTER TABLE clgpersonaggiooggetto
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`nome`,`id_oggetto`);");
        
        gdrcd_query("ALTER TABLE clgpersonaggiooggetto
DROP COLUMN `id`");
    }
}
