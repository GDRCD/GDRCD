<?php

class GDRCD57 extends DbMigration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        // Colonna dice per associare un tipo di dado al lancio di una specifica abilità
        gdrcd_query("ALTER TABLE abilita ADD COLUMN dice TINYINT(3) UNSIGNED NULL DEFAULT NULL AFTER car");

        // Indice di ottimizzazione per le chat
        gdrcd_query("CREATE INDEX idx_chat_stanza_id_ora ON chat (stanza, id, ora)");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        gdrcd_query("DROP INDEX idx_chat_stanza_id_ora ON chat");
        gdrcd_query("ALTER TABLE abilita DROP COLUMN dice");
    }
}
