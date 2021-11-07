<?php

class GDRCD56 extends DbMigration
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
        gdrcd_query("ALTER TABLE backmessaggi CHANGE `spedito` `spedito` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;");

        gdrcd_query("ALTER TABLE backmessaggi ADD COLUMN tipo int(2) NOT NULL default '0'");

        gdrcd_query("ALTER TABLE backmessaggi ADD COLUMN oggetto text NULL DEFAULT NULL");

        gdrcd_query("CREATE TABLE IF NOT EXISTS blocco_esiti(
            id     int(11)                         NOT NULL AUTO_INCREMENT,
            titolo text CHARACTER SET utf8         NOT NULL,
            data   datetime                        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            autore varchar(255) CHARACTER SET utf8 NOT NULL,
            pg     varchar(255) CHARACTER SET utf8 NOT NULL,
            id_min int(11)                                  DEFAULT NULL,
            master varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '0',
            closed int(11)                         NOT NULL DEFAULT '0',
            PRIMARY KEY (id)
        ) ENGINE = MyISAM DEFAULT CHARSET = utf8;");

        gdrcd_query("ALTER TABLE chat CHANGE `ora` `ora` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;");

        gdrcd_query("CREATE TABLE IF NOT EXISTS esiti (
            id int(11) NOT NULL AUTO_INCREMENT,
            sent int(11) NOT NULL DEFAULT '0',
            titolo varchar(255) CHARACTER SET utf8 NOT NULL,
            data datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            chat int(11) NOT NULL DEFAULT '0',
             autore varchar(255) CHARACTER SET utf8 NOT NULL,
            contenuto mediumtext CHARACTER SET utf8,
            noteoff text CHARACTER SET utf8,
            master varchar(255) CHARACTER SET utf8 DEFAULT NULL,
            pg varchar(255) CHARACTER SET utf8 DEFAULT NULL,
            id_blocco int(11) NOT NULL,
            id_ab int(11) DEFAULT '0',
            CD_1 text CHARACTER SET utf8,
            CD_2 text CHARACTER SET utf8,
            CD_3 text CHARACTER SET utf8,
            CD_4 text CHARACTER SET utf8,
            letto_master int(11) NOT NULL DEFAULT '0',
            letto_pg int(11) NOT NULL DEFAULT '0',
            dice_face int(4) NOT NULL DEFAULT '0',
            dice_num int(4) NOT NULL DEFAULT '0',
            dice_results varchar(1000) DEFAULT NULL,
            PRIMARY KEY (id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        gdrcd_query("ALTER TABLE log CHANGE `data_evento` `data_evento` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");

        gdrcd_query("ALTER TABLE messaggi CHANGE `spedito` `spedito` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");

        gdrcd_query("ALTER TABLE messaggi ADD COLUMN tipo int(2) NOT NULL default '0'");

        gdrcd_query("ALTER TABLE messaggi ADD COLUMN oggetto text NULL DEFAULT NULL");

        gdrcd_query("ALTER TABLE messaggioaraldo CHANGE `data_messaggio` `data_messaggio` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");

        gdrcd_query("ALTER TABLE messaggioaraldo CHANGE `data_ultimo_messaggio` `data_ultimo_messaggio` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");

        gdrcd_query("ALTER TABLE oggetto CHANGE `data_inserimento` `data_inserimento` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");

        gdrcd_query("ALTER TABLE personaggio CHANGE COLUMN ora_uscita ora_uscita datetime NOT NULL default '2009-07-01 00:00:00'");

        gdrcd_query("ALTER TABLE personaggio DROP COLUMN ultimo_messaggio");

        gdrcd_query("CREATE TABLE IF NOT EXISTS segnalazione_role (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            stanza int(11) NOT NULL,
            conclusa int(11) NOT NULL DEFAULT '0',
            partecipanti text CHARACTER SET utf8,
            mittente varchar(20) CHARACTER SET utf8 NOT NULL,
            data_inizio datetime DEFAULT NULL,
            data_fine datetime DEFAULT NULL,
            tags text CHARACTER SET utf8,
            quest text CHARACTER SET utf8,
            PRIMARY KEY (id)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");

        gdrcd_query("CREATE TABLE IF NOT EXISTS send_GM (
            id int(11) NOT NULL AUTO_INCREMENT,
            data datetime NOT NULL,
            autore text CHARACTER SET utf8 NOT NULL,
            role_reg int(11) NOT NULL,
            note text CHARACTER SET utf8,
            PRIMARY KEY (id)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        gdrcd_query("ALTER TABLE backmessaggi ALTER spedito SET default '2021-01-01 00:00:00'");

        gdrcd_query("ALTER TABLE backmessaggi DROP COLUMN tipo");

        gdrcd_query("ALTER TABLE backmessaggi DROP COLUMN oggetto");

        gdrcd_query("DROP TABLE IF EXISTS blocco_esiti");

        gdrcd_query("ALTER TABLE chat ALTER COLUMN ora SET DEFAULT '2021-01-01 00:00:00'");

        gdrcd_query("DROP TABLE IF EXISTS esiti");

        gdrcd_query("ALTER TABLE log ALTER COLUMN data_evento SET DEFAULT '2021-01-01 00:00:00'");

        gdrcd_query("ALTER TABLE messaggi ALTER COLUMN spedito SET DEFAULT '2021-01-01 00:00:00'");

        gdrcd_query("ALTER TABLE messaggi DROP COLUMN tipo");

        gdrcd_query("ALTER TABLE messaggi DROP COLUMN oggetto");

        gdrcd_query("ALTER TABLE messaggioaraldo ALTER COLUMN data_messaggio SET DEFAULT '2021-01-01 00:00:00'");

        gdrcd_query("ALTER TABLE messaggioaraldo ALTER COLUMN data_ultimo_messaggio SET DEFAULT '2021-01-01 00:00:00'");

        gdrcd_query("ALTER TABLE oggetto ALTER COLUMN data_inserimento SET DEFAULT '2021-01-01 00:00:00'");

        gdrcd_query("ALTER TABLE personaggio CHANGE COLUMN ora_uscita ora_uscita datetime default '2021-01-01 00:00:00'");

        gdrcd_query("ALTER TABLE personaggio ADD COLUMN ultimo_messaggio bigint(20) NOT NULL default '0'");

        gdrcd_query("DROP TABLE IF EXISTS segnalazione_role");

        gdrcd_query("DROP TABLE IF EXISTS send_GM");
    }
}
