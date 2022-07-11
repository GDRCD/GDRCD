<?php

class GDRCDCalendar extends DbMigration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        gdrcd_query("
            CREATE TABLE IF NOT EXISTS eventi (
                id INT(11) NOT NULL AUTO_INCREMENT,
                title  INT(11) NULL DEFAULT NULL,
                start DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                end DATETIME NULL DEFAULT NULL DEFAULT CURRENT_TIMESTAMP,
                titolo VARCHAR(250) DEFAULT NULL,
                descrizione TEXT DEFAULT NULL,
                colore  INT(11) NULL DEFAULT NULL,
                PRIMARY KEY (id) USING BTREE
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;"
        );

        gdrcd_query("
            CREATE TABLE IF NOT EXISTS eventi_colori (
                id INT(11) NOT NULL AUTO_INCREMENT,
                backgroundColor VARCHAR(50)DEFAULT NULL,
                borderColor VARCHAR(50) DEFAULT NULL,
                textColor VARCHAR(50) DEFAULT NULL,
                colore VARCHAR(50) DEFAULT NULL,
                PRIMARY KEY (id) USING BTREE
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;"
        );

        gdrcd_query("
            CREATE TABLE IF NOT EXISTS eventi_tipo (
                id INT(11) NOT NULL AUTO_INCREMENT,
                title VARCHAR(50) DEFAULT NULL,
                permessi VARCHAR(50) DEFAULT NULL,
                PRIMARY KEY (id) USING BTREE
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;"
        );

        gdrcd_query("
            INSERT INTO eventi_tipo (id, title, permessi) VALUES (1, 'QUEST', 'GAMEMASTER');
            INSERT INTO eventi_tipo (id, title, permessi) VALUES (2, 'ROLE', NULL);
            INSERT INTO eventi_tipo (id, title, permessi) VALUES (3, 'GRUPPO', NULL);
            INSERT INTO eventi_tipo (id, title, permessi) VALUES (4, 'CORP', 'GUILDMODERATOR');
            INSERT INTO eventi_tipo (id, title, permessi) VALUES (5, 'ALTRO', NULL);"
        );

        gdrcd_query("
            INSERT INTO eventi_colori (id, backgroundColor, borderColor, textColor, colore) VALUES (1, 'purple', 'purple', 'white', 'Viola');
            INSERT INTO eventi_colori (id, backgroundColor, borderColor, textColor, colore) VALUES (2, 'lightblue', 'lightblue', 'black', 'Azzurro');
            INSERT INTO eventi_colori (id, backgroundColor, borderColor, textColor, colore) VALUES (3, 'green', 'green', 'white', 'Verde');
            INSERT INTO eventi_colori (id, backgroundColor, borderColor, textColor, colore) VALUES (4, 'pink', 'pink', 'black', 'Rosa');
            INSERT INTO eventi_colori (id, backgroundColor, borderColor, textColor, colore) VALUES (5, 'gold', 'gold', 'black', 'Giallo');
            INSERT INTO eventi_colori (id, backgroundColor, borderColor, textColor, colore) VALUES (6, 'grey', 'grey', 'black', 'Grigio');
            INSERT INTO eventi_colori (id, backgroundColor, borderColor, textColor, colore) VALUES (7, 'orange', 'orange', 'black', 'Arancione');
            INSERT INTO eventi_colori (id, backgroundColor, borderColor, textColor, colore) VALUES (8, 'red', 'red', 'white', 'Rosso');
            INSERT INTO eventi_colori (id, backgroundColor, borderColor, textColor, colore) VALUES (9, 'black', 'black', 'white', 'Nero');
            INSERT INTO eventi_colori (id, backgroundColor, borderColor, textColor, colore) VALUES (10, 'white', 'white', 'black', 'Bianco');
        ");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        gdrcd_query("DROP TABLE IF EXISTS eventi");

    }
}
