<?php

/**
 * CONFIGURAZIONI PERSONALI DI GDRCD
 * Questo file contiene le configurazioni personali del gioco.
 *
 * Per poter usare le configurazioni personali, è necessario copiare questo file nella cartella
 * principale di GDRCD e rinominarlo in configs.inc.php.
 *
 * Questo file viene letto da tutti gli script di GDRCD e sovrascrive le impostazioni di default definite in
 * configs/config.core.inc.php
 */

/* PARAMETRI DI CONNESSIONE */
/**
 * HELP:
 * Sostituire le diciture inserite tra le virgolette con i parametri di connessione al Database del proprio dominio.
 * Essi sono forniti al momento della registrazione. Se non si e' in possesso di tali parametri consultare le FAQ
 * della homepage dell'host che fornisce il dominio. Se non le si trovano li contattare lo staff dell'host.
 */
$PARAMETERS['database']['username'] = 'gdrcd';            //nome utente del database
$PARAMETERS['database']['password'] = 'gdrcd';            //password del database
$PARAMETERS['database']['database_name'] = 'gdrcd';    //nome del database
$PARAMETERS['database']['url'] = 'localhost';        //indirizzo ip del database


/* INFORMAZIONI SUL SITO */
/**
 * HELP: I parametri di questa voce compaiono come informazioni sulla homepage.
 */
$PARAMETERS['info']['site_name'] = 'GDRCD 5.6.0.6'; //nome del gioco
$PARAMETERS['info']['site_url'] = 'http://gdrcd.test/'; //indirizzo URL del gioco
$PARAMETERS['info']['webmaster_name'] = 'Webmaster'; //nome e cognome del responsabile del sito
$PARAMETERS['info']['webmaster_email'] = 'webmaster@gdrhost.it'; //email ufficiale del webmaster (è visibile in homepage)
$PARAMETERS['info']['homepage_name'] = 'Homepage'; //nome con il quale si indica la prima pagina visualizzata
$PARAMETERS['info']['dbadmin_name'] = 'Admin DB'; //nome del responsabile del database


/* SCELTA DELLA LINGUA */
/**
 * HELP: Per definire un diverso vocabolario creare una copia del file /vocabulary/IT-it.vocabulary.php nella cartella vocabulary.
 * Il nome del file deve essere [nome].vocabulary.php, dove la stringa [nome] può essere scelta e deve essere il valore specificato
 * in $PARAMETER['languages']['set'].
 */
$PARAMETERS['languages']['set'] = 'IT-it'; //lingua italiana

/* SCELTA DEL TEMA */
// HOMEPAGE
$PARAMETERS['themes']['homepage'] = 'advanced'; //tema in uso
// MAINPAGE
$PARAMETERS['themes']['current_theme'] = 'advanced'; //tema in uso

/**
 * Inserendo i nomi dei temi in questo elenco è possibile rendere disponibili agli utenti temi alternativi rispetto a quello di default
 * Il primo elemento di ogni riga deve corrispondere al nome della cartella in cui è contenuto il tema in themes/<nome tema>
 * Il secondo elemento è il nome che verrà presentato agli utenti durante la scelta
 *
 * Se non si vuole dare gli utenti la possibilità di scegliere temi alternativi, è sufficiente non impostare alcun tema in questa variabile
 *
 * NOTA: le pagine esterne del sito, cioè quelle visualizzate prima del login saranno sempre visualizzate con il tema di default
 */
$PARAMETERS['themes']['available'] = array(
    'advanced' => 'Tema "Advanced" GDRCD',
    //'il_mio_tema_preferito' => 'Il mio tema troppo figo'
);