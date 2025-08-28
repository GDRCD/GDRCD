<?php

class GDRCD57 extends DbMigration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        // Cambia il charset della tabella chat ad utf8mb4
        gdrcd_query("ALTER TABLE `chat` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        gdrcd_query("ALTER TABLE `chat` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");

        // Indice di ottimizzazione per le chat
        gdrcd_query("CREATE INDEX idx_chat_stanza_id_ora ON chat (stanza, id, ora)");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        gdrcd_query("DROP INDEX idx_chat_stanza_id_ora ON chat");

        // Ripristina il charset precedente
        gdrcd_query("ALTER TABLE `chat` CONVERT TO CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci");
        gdrcd_query("ALTER TABLE `chat` DEFAULT CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci");
    }
}
