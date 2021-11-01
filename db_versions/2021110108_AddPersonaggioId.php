<?php

class AddPersonaggioId extends DbMigration
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function up()
    {
        gdrcd_query("ALTER TABLE personaggio
ADD COLUMN `id` int(11) NOT NULL AUTO_INCREMENT");
    
        gdrcd_query("ALTER TABLE personaggio
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`id`);");
        
        //TODO aggiungere un indice UNIQUE su name?
    }
    
    public function down()
    {
        gdrcd_query("ALTER TABLE personaggio
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`nome`);");
        
        gdrcd_query("ALTER TABLE personaggio
DROP COLUMN `id`");
    
    }
}
