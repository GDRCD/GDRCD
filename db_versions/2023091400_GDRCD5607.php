<?php

class GDRCD5607 extends DbMigration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        gdrcd_query("ALTER TABLE `messaggioaraldo` CHANGE `titolo` `titolo` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL; ");
        gdrcd_query("ALTER TABLE `messaggioaraldo` CHANGE `messaggio` `messaggio` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL; ");
        gdrcd_query("ALTER TABLE `messaggi` CHANGE `testo` `testo` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL; ");
        gdrcd_query("ALTER TABLE `messaggi` CHANGE `oggetto` `oggetto` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;  ");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        gdrcd_query("ALTER TABLE `messaggioaraldo` CHANGE `titolo` `titolo` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; ");
        gdrcd_query("ALTER TABLE `messaggioaraldo` CHANGE `messaggio` `messaggio` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; ");
        gdrcd_query("ALTER TABLE `messaggi` CHANGE `testo` `testo` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;  ");
        gdrcd_query("ALTER TABLE `messaggi` CHANGE `oggetto` `oggetto` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;   ");
    }
}
