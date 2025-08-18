<?php

class GDRCD57 extends DbMigration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        // Indice di ottimizzazione per le chat
        gdrcd_query("CREATE INDEX idx_chat_stanza_id_ora ON chat (stanza, id, ora)");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        gdrcd_query("DROP INDEX idx_chat_stanza_id_ora ON chat");
    }
}
