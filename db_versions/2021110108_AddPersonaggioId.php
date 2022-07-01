<?php

class AddPersonaggioId extends DbMigration
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function up()
    {
        DB::query("ALTER TABLE personaggio
DROP PRIMARY KEY,
ADD COLUMN `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY");
        
        //TODO aggiungere un indice UNIQUE su name?
    }
    
    public function down()
    {
        DB::query("ALTER TABLE personaggio
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`nome`);");
        
        DB::query("ALTER TABLE personaggio
DROP COLUMN `id`");
    
    }
}
