<?php

class DefaultLengths extends DbMigration
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
        DB::query("ALTER TABLE messaggioaraldo
CHANGE COLUMN autore autore varchar(255) DEFAULT NULL");
    
        DB::query("ALTER TABLE personaggio
CHANGE COLUMN autore_esilio autore_esilio varchar(255) DEFAULT NULL");
    
        DB::query("ALTER TABLE abilita
CHANGE COLUMN nome nome varchar(255) NOT NULL");
    
        DB::query("ALTER TABLE ambientazione
CHANGE COLUMN titolo titolo varchar(255) NOT NULL");
    
        DB::query("ALTER TABLE araldo
CHANGE COLUMN nome nome varchar(255) DEFAULT NULL");
    
        DB::query("ALTER TABLE araldo_letto
CHANGE COLUMN nome nome varchar(255) DEFAULT NULL");
    
        DB::query("ALTER TABLE backmessaggi
CHANGE COLUMN mittente mittente varchar(255) NOT NULL DEFAULT ''");
    
        DB::query("ALTER TABLE backmessaggi
CHANGE COLUMN destinatario destinatario varchar(255) NOT NULL DEFAULT ''");
    
        DB::query("ALTER TABLE blacklist
CHANGE COLUMN ip ip varchar(255) NOT NULL DEFAULT ''");
        DB::query("ALTER TABLE blacklist
CHANGE COLUMN nota nota varchar(255) DEFAULT NULL");
        DB::query("ALTER TABLE blacklist
CHANGE COLUMN host host varchar(255) NOT NULL DEFAULT '-'");
    
        DB::query("ALTER TABLE chat
CHANGE COLUMN imgs imgs varchar(255) NOT NULL DEFAULT ''");
        DB::query("ALTER TABLE chat
CHANGE COLUMN mittente mittente varchar(255) NOT NULL DEFAULT ''");
        DB::query("ALTER TABLE chat
CHANGE COLUMN destinatario destinatario varchar(255) DEFAULT NULL");
        DB::query("ALTER TABLE chat
CHANGE COLUMN tipo tipo varchar(255) DEFAULT NULL");
    
        DB::query("ALTER TABLE clgpersonaggioabilita
CHANGE COLUMN nome nome varchar(255) NOT NULL");
        
        DB::query("ALTER TABLE clgpersonaggiooggetto
CHANGE COLUMN nome nome varchar(255) NOT NULL DEFAULT ''");
    
        DB::query("ALTER TABLE clgpersonaggioruolo
CHANGE COLUMN personaggio personaggio varchar(255) NOT NULL");
    
        DB::query("ALTER TABLE codmostrina
CHANGE COLUMN nome nome varchar(255) NOT NULL");
        DB::query("ALTER TABLE codmostrina
CHANGE COLUMN img_url img_url varchar(255) NOT NULL DEFAULT 'grigia.gif'");
        DB::query("ALTER TABLE codmostrina
CHANGE COLUMN descrizione descrizione varchar(255) NOT NULL DEFAULT 'nessuna'");
    
        DB::query("ALTER TABLE codtipogilda
CHANGE COLUMN descrizione descrizione varchar(255) NOT NULL");
    
        DB::query("ALTER TABLE codtipooggetto
CHANGE COLUMN descrizione descrizione text NOT NULL");
    
        DB::query("ALTER TABLE diario
CHANGE COLUMN personaggio personaggio varchar(255) DEFAULT NULL");
        DB::query("ALTER TABLE diario
CHANGE COLUMN visibile visibile varchar(255) NOT NULL");
        DB::query("ALTER TABLE diario
CHANGE COLUMN titolo titolo varchar(255) NOT NULL DEFAULT ''");
    
        DB::query("ALTER TABLE esiti
CHANGE COLUMN dice_results dice_results varchar(255) DEFAULT NULL");
    
        DB::query("ALTER TABLE gilda
CHANGE COLUMN nome nome varchar(255) NOT NULL DEFAULT ''");
        DB::query("ALTER TABLE gilda
CHANGE COLUMN tipo tipo varchar(255) NOT NULL DEFAULT '0'");
        DB::query("ALTER TABLE gilda
CHANGE COLUMN immagine immagine varchar(255) DEFAULT NULL");
        DB::query("ALTER TABLE gilda
CHANGE COLUMN url_sito url_sito varchar(255) DEFAULT NULL");
    
        DB::query("ALTER TABLE log
CHANGE COLUMN nome_interessato nome_interessato varchar(255) NOT NULL DEFAULT ''");
        DB::query("ALTER TABLE log
CHANGE COLUMN autore autore varchar(255) NOT NULL DEFAULT ''");
        DB::query("ALTER TABLE log
CHANGE COLUMN codice_evento codice_evento varchar(255) NOT NULL DEFAULT ''");
        DB::query("ALTER TABLE log
CHANGE COLUMN descrizione_evento descrizione_evento varchar(255) NOT NULL DEFAULT ''");
    
        DB::query("ALTER TABLE mappa
CHANGE COLUMN nome nome varchar(255) DEFAULT NULL");
        DB::query("ALTER TABLE mappa
CHANGE COLUMN stato stato varchar(255) NOT NULL DEFAULT ''");
        DB::query("ALTER TABLE mappa
CHANGE COLUMN immagine immagine varchar(255) DEFAULT 'standard_luogo.png'");
        DB::query("ALTER TABLE mappa
CHANGE COLUMN stanza_apparente stanza_apparente varchar(255) DEFAULT NULL");
        DB::query("ALTER TABLE mappa
CHANGE COLUMN link_immagine link_immagine varchar(255) NOT NULL");
        DB::query("ALTER TABLE mappa
CHANGE COLUMN link_immagine_hover link_immagine_hover varchar(255) NOT NULL");
        DB::query("ALTER TABLE mappa
CHANGE COLUMN proprietario proprietario varchar(255) DEFAULT NULL");
    
        DB::query("ALTER TABLE mappa_click
CHANGE COLUMN nome nome varchar(255) DEFAULT NULL");
        DB::query("ALTER TABLE mappa_click
CHANGE COLUMN immagine immagine varchar(255) NOT NULL DEFAULT 'standard_mappa.png'");
        DB::query("ALTER TABLE mappa_click
CHANGE COLUMN meteo meteo varchar(255) NOT NULL DEFAULT '20Â°c - sereno'");
    
        DB::query("ALTER TABLE messaggi
CHANGE COLUMN mittente mittente varchar(255) NOT NULL");
        DB::query("ALTER TABLE messaggi
CHANGE COLUMN destinatario destinatario varchar(255) NOT NULL DEFAULT 'Nessuno'");
    
        DB::query("ALTER TABLE oggetto
CHANGE COLUMN nome nome varchar(255) NOT NULL DEFAULT 'Sconosciuto'");
        DB::query("ALTER TABLE oggetto
CHANGE COLUMN creatore creatore varchar(255) NOT NULL DEFAULT 'System Op'");
        DB::query("ALTER TABLE oggetto
CHANGE COLUMN cariche cariche varchar(255) NOT NULL DEFAULT '0'");
        DB::query("ALTER TABLE oggetto
CHANGE COLUMN urlimg urlimg varchar(255) DEFAULT NULL");
    
        DB::query("ALTER TABLE personaggio
CHANGE COLUMN nome nome varchar(255) NOT NULL DEFAULT ''");
        DB::query("ALTER TABLE personaggio
CHANGE COLUMN cognome cognome varchar(255) NOT NULL DEFAULT '-'");
        DB::query("ALTER TABLE personaggio
CHANGE COLUMN pass pass varchar(255) NOT NULL DEFAULT ''");
        DB::query("ALTER TABLE personaggio
CHANGE COLUMN email email varchar(255) DEFAULT NULL");
        DB::query("ALTER TABLE personaggio
CHANGE COLUMN sesso sesso varchar(255) DEFAULT 'm'");
        DB::query("ALTER TABLE personaggio
CHANGE COLUMN online_status online_status varchar(255) DEFAULT NULL");
        DB::query("ALTER TABLE personaggio
CHANGE COLUMN last_ip last_ip varchar(255) DEFAULT NULL");
    
        DB::query("ALTER TABLE razza
CHANGE COLUMN nome_razza nome_razza varchar(255) NOT NULL DEFAULT ''");
        DB::query("ALTER TABLE razza
CHANGE COLUMN sing_m sing_m varchar(255) NOT NULL DEFAULT ''");
        DB::query("ALTER TABLE razza
CHANGE COLUMN sing_f sing_f varchar(255) NOT NULL DEFAULT ''");
        DB::query("ALTER TABLE razza
CHANGE COLUMN immagine immagine varchar(255) NOT NULL DEFAULT 'standard_razza.png'");
        DB::query("ALTER TABLE razza
CHANGE COLUMN icon icon varchar(255) NOT NULL DEFAULT 'standard_razza.png'");
        DB::query("ALTER TABLE razza
CHANGE COLUMN url_site url_site varchar(255) DEFAULT NULL");
    
        DB::query("ALTER TABLE regolamento
CHANGE COLUMN titolo titolo varchar(255) NOT NULL");
    
        DB::query("ALTER TABLE ruolo
CHANGE COLUMN nome_ruolo nome_ruolo varchar(255) NOT NULL");
        DB::query("ALTER TABLE ruolo
CHANGE COLUMN immagine immagine varchar(255) NOT NULL");
    
        DB::query("ALTER TABLE segnalazione_role
CHANGE COLUMN mittente mittente varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
        
        DB::query("ALTER TABLE send_GM
CHANGE COLUMN autore autore varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
    }
    
    /**
     * @inheritDoc
     */
    public function down()
    {
        DB::query("ALTER TABLE messaggioaraldo
CHANGE COLUMN autore autore varchar(20) DEFAULT NULL");
    
        DB::query("ALTER TABLE personaggio
CHANGE COLUMN autore_esilio autore_esilio varchar(20) DEFAULT NULL");
    
        DB::query("ALTER TABLE abilita
CHANGE COLUMN nome nome varchar(20) NOT NULL");
    
        DB::query("ALTER TABLE ambientazione
CHANGE COLUMN titolo titolo varchar(30) NOT NULL");
    
        DB::query("ALTER TABLE araldo
CHANGE COLUMN nome nome varchar(50) DEFAULT NULL");
    
        DB::query("ALTER TABLE araldo_letto
CHANGE COLUMN nome nome varchar(50) DEFAULT NULL");
    
        DB::query("ALTER TABLE backmessaggi
CHANGE COLUMN mittente mittente varchar(20) NOT NULL DEFAULT ''");
    
        DB::query("ALTER TABLE backmessaggi
CHANGE COLUMN destinatario destinatario varchar(20) NOT NULL DEFAULT ''");
    
        DB::query("ALTER TABLE blacklist
CHANGE COLUMN ip ip char(15) NOT NULL DEFAULT ''");
        DB::query("ALTER TABLE blacklist
CHANGE COLUMN nota nota char(255) DEFAULT NULL");
        DB::query("ALTER TABLE blacklist
CHANGE COLUMN host host char(255) NOT NULL DEFAULT '-'");
    
        DB::query("ALTER TABLE chat
CHANGE COLUMN imgs imgs varchar(100) NOT NULL DEFAULT ''");
        DB::query("ALTER TABLE chat
CHANGE COLUMN mittente mittente varchar(20) NOT NULL DEFAULT ''");
        DB::query("ALTER TABLE chat
CHANGE COLUMN destinatario destinatario varchar(20) DEFAULT NULL");
        DB::query("ALTER TABLE chat
CHANGE COLUMN tipo tipo char(1) DEFAULT NULL");
    
        DB::query("ALTER TABLE clgpersonaggioabilita
CHANGE COLUMN nome nome varchar(20) NOT NULL");
    
        DB::query("ALTER TABLE clgpersonaggiooggetto
CHANGE COLUMN nome nome varchar(20) NOT NULL DEFAULT ''");
    
        DB::query("ALTER TABLE clgpersonaggioruolo
CHANGE COLUMN personaggio personaggio varchar(20) NOT NULL");
    
        DB::query("ALTER TABLE codmostrina
CHANGE COLUMN nome nome varchar(20) NOT NULL");
        DB::query("ALTER TABLE codmostrina
CHANGE COLUMN img_url img_url char(50) NOT NULL DEFAULT 'grigia.gif'");
        DB::query("ALTER TABLE codmostrina
CHANGE COLUMN descrizione descrizione char(255) NOT NULL DEFAULT 'nessuna'");
    
        DB::query("ALTER TABLE codtipogilda
CHANGE COLUMN descrizione descrizione varchar(50) NOT NULL");
    
        DB::query("ALTER TABLE codtipooggetto
CHANGE COLUMN descrizione descrizione char(20) NOT NULL");
    
        DB::query("ALTER TABLE diario
CHANGE COLUMN personaggio personaggio varchar(20) DEFAULT NULL");
    
        DB::query("ALTER TABLE diario
CHANGE COLUMN personaggio personaggio varchar(20) DEFAULT NULL");
        DB::query("ALTER TABLE diario
CHANGE COLUMN visibile visibile char(5) NOT NULL");
        DB::query("ALTER TABLE diario
CHANGE COLUMN titolo titolo varchar(50) NOT NULL DEFAULT ''");
    
        DB::query("ALTER TABLE esiti
CHANGE COLUMN dice_results dice_results varchar(1000) DEFAULT NULL");
    
        DB::query("ALTER TABLE gilda
CHANGE COLUMN nome nome char(50) NOT NULL DEFAULT ''");
        DB::query("ALTER TABLE gilda
CHANGE COLUMN tipo tipo varchar(1) NOT NULL DEFAULT '0'");
        DB::query("ALTER TABLE gilda
CHANGE COLUMN immagine immagine char(255) DEFAULT NULL");
        DB::query("ALTER TABLE gilda
CHANGE COLUMN url_sito url_sito char(255) DEFAULT NULL");
    
        DB::query("ALTER TABLE log
CHANGE COLUMN nome_interessato nome_interessato char(20) NOT NULL DEFAULT ''");
        DB::query("ALTER TABLE log
CHANGE COLUMN autore autore char(255) NOT NULL DEFAULT ''");
        DB::query("ALTER TABLE log
CHANGE COLUMN codice_evento codice_evento char(20) NOT NULL DEFAULT ''");
        DB::query("ALTER TABLE log
CHANGE COLUMN descrizione_evento descrizione_evento char(100) NOT NULL DEFAULT ''");
    
        DB::query("ALTER TABLE mappa
CHANGE COLUMN nome nome varchar(50) DEFAULT NULL");
        DB::query("ALTER TABLE mappa
CHANGE COLUMN stato stato varchar(50) NOT NULL DEFAULT ''");
        DB::query("ALTER TABLE mappa
CHANGE COLUMN immagine immagine varchar(50) DEFAULT 'standard_luogo.png'");
        DB::query("ALTER TABLE mappa
CHANGE COLUMN stanza_apparente stanza_apparente varchar(50) DEFAULT NULL");
        DB::query("ALTER TABLE mappa
CHANGE COLUMN link_immagine link_immagine varchar(256) NOT NULL");
        DB::query("ALTER TABLE mappa
CHANGE COLUMN link_immagine_hover link_immagine_hover varchar(256) NOT NULL");
        DB::query("ALTER TABLE mappa
CHANGE COLUMN proprietario proprietario char(20) DEFAULT NULL");
    
        DB::query("ALTER TABLE mappa_click
CHANGE COLUMN nome nome varchar(50) DEFAULT NULL");
        DB::query("ALTER TABLE mappa_click
CHANGE COLUMN immagine immagine varchar(50) NOT NULL DEFAULT 'standard_mappa.png'");
        DB::query("ALTER TABLE mappa_click
CHANGE COLUMN meteo meteo varchar(40) NOT NULL DEFAULT '20Â°c - sereno'");
    
        DB::query("ALTER TABLE messaggi
CHANGE COLUMN mittente mittente varchar(40) NOT NULL");
        DB::query("ALTER TABLE messaggi
CHANGE COLUMN destinatario destinatario varchar(20) NOT NULL DEFAULT 'Nessuno'");
    
        DB::query("ALTER TABLE oggetto
CHANGE COLUMN nome nome varchar(50) NOT NULL DEFAULT 'Sconosciuto'");
        DB::query("ALTER TABLE oggetto
CHANGE COLUMN creatore creatore varchar(20) NOT NULL DEFAULT 'System Op'");
        DB::query("ALTER TABLE oggetto
CHANGE COLUMN cariche cariche varchar(10) NOT NULL DEFAULT '0'");
        DB::query("ALTER TABLE oggetto
CHANGE COLUMN urlimg urlimg varchar(50) DEFAULT NULL");
    
        DB::query("ALTER TABLE personaggio
CHANGE COLUMN nome nome varchar(20) NOT NULL DEFAULT ''");
        DB::query("ALTER TABLE personaggio
CHANGE COLUMN cognome cognome varchar(50) NOT NULL DEFAULT '-'");
        DB::query("ALTER TABLE personaggio
CHANGE COLUMN pass pass varchar(100) NOT NULL DEFAULT ''");
        DB::query("ALTER TABLE personaggio
CHANGE COLUMN email email varchar(50) DEFAULT NULL");
        DB::query("ALTER TABLE personaggio
CHANGE COLUMN sesso sesso char(1) DEFAULT 'm'");
        DB::query("ALTER TABLE personaggio
CHANGE COLUMN online_status online_status varchar(100) DEFAULT NULL");
        DB::query("ALTER TABLE personaggio
CHANGE COLUMN last_ip last_ip varchar(16) DEFAULT NULL");
    
        DB::query("ALTER TABLE razza
CHANGE COLUMN nome_razza nome_razza char(50) NOT NULL DEFAULT ''");
        DB::query("ALTER TABLE razza
CHANGE COLUMN sing_m sing_m char(50) NOT NULL DEFAULT ''");
        DB::query("ALTER TABLE razza
CHANGE COLUMN sing_f sing_f char(50) NOT NULL DEFAULT ''");
        DB::query("ALTER TABLE razza
CHANGE COLUMN immagine immagine char(50) NOT NULL DEFAULT 'standard_razza.png'");
        DB::query("ALTER TABLE razza
CHANGE COLUMN icon icon varchar(50) NOT NULL DEFAULT 'standard_razza.png'");
        DB::query("ALTER TABLE razza
CHANGE COLUMN url_site url_site char(255) DEFAULT NULL");
    
        DB::query("ALTER TABLE regolamento
CHANGE COLUMN titolo titolo varchar(30) NOT NULL");
    
        DB::query("ALTER TABLE ruolo
CHANGE COLUMN nome_ruolo nome_ruolo char(50) NOT NULL");
        DB::query("ALTER TABLE ruolo
CHANGE COLUMN immagine immagine varchar(256) NOT NULL");
    
        DB::query("ALTER TABLE segnalazione_role
CHANGE COLUMN mittente mittente varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
    
        DB::query("ALTER TABLE send_GM
CHANGE COLUMN autore autore text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
    }
}
