<?php

include('includes/required.php');

/* Eseguo la connessione al database */
$handleDBConnection = gdrcd_connect();

if ($PARAMETERS['mode']['chatsave'] != 'ON') {
    echo $MESSAGE['chat']['error']['permissions'];
    exit;
}

$typeOrder = ($PARAMETERS['mode']['chat_from_bottom'] == 'ON') ? 'DESC' : 'ASC';
$formato = in_array($_POST['formato'] ?? null, ['html', 'txt', 'pdf']) ? $_POST['formato'] : 'html';
//recupero il tempo di salvataggio delle chat
$tempo_salvataggio= gdrcd_configuration_get('salva_chat.tempo_salvataggio');
//recuper il parametro per controllare che il personaggio sia presente in giocata
$solo_autore= gdrcd_configuration_get('salva_chat.solo_autore');

// L'intervallo scelto nel modale limita solo la UI: una richiesta manomessa non deve
// poter superare il tetto configurato dall'admin, quindi il clamp avviene comunque qui.
$ore_richieste = ((int) ($_POST['ore'] ?? 0)) > 0 ? (int) $_POST['ore'] : (int) $tempo_salvataggio;
$ore = min($ore_richieste, (int) $tempo_salvataggio);

// Checkbox "includi messaggi di sistema": default true (comportamento storico) quando il
// campo non arriva affatto; il modale invia sempre un hidden '0' abbinato, quindi un
// unselect esplicito è distinguibile da un campo assente.
$includi_sistema = ($_POST['includi_sistema'] ?? '1') === '1';

// Whitelist esplicita, stesso pattern di $formato; il default resta l'ordinamento di sito.
$ordine = in_array($_POST['ordine'] ?? null, ['asc', 'desc'], true) ? strtoupper($_POST['ordine']) : $typeOrder;

// Nome file suggerito dal modale: mai fidarsi as-is, finisce in un header Content-Disposition.
$nomefile_richiesto = trim((string) ($_POST['nomefile'] ?? ''));
$nomefile_sanitizzato = $nomefile_richiesto !== ''
    ? trim(preg_replace('/[^A-Za-z0-9_\-]+/', '_', $nomefile_richiesto), '_')
    : '';


if($solo_autore == 'si'){
    $check_pg=gdrcd_stmt_one("SELECT count(*) as conta 
    FROM chat 
    WHERE stanza = ".$_SESSION['luogo']." 
    AND DATE_SUB(NOW(), INTERVAL $tempo_salvataggio HOUR) < ora and id_personaggio_mittente = '".$_SESSION['id_personaggio']."' AND tipo !='S'");
    if(!$check_pg['conta']){
        echo $MESSAGE['chat']['error']['solo_autore'];
        exit; 
    }
}


if ($PARAMETERS['mode']['chatsavepvt'] == 'ON') {
    $query =  "SELECT c.id, 
                        c.imgs, 
                        c.id_personaggio_mittente, 
                        c.id_personaggio_destinatario, 
                        c.tipo, c.ora,
                        c.testo, 
                        pm.url_img_chat, 
                        pm.nome AS nome_mittente,
                        pd.nome AS nome_destinatario,
                        m.ora_prenotazione,
                        m.privata
                    FROM chat c
                    INNER JOIN mappa m ON m.id = c.stanza
                    LEFT JOIN personaggio pm 
                    ON pm.id_personaggio = c.id_personaggio_mittente
                     LEFT JOIN personaggio pd 
                                ON pd.id_personaggio = c.id_personaggio_destinatario
                    WHERE c.stanza = ? AND DATE_SUB(NOW(), INTERVAL ? HOUR) < c.ora "
                    . ($includi_sistema ? '' : "AND c.tipo != 'S' ")
                    . "ORDER BY c.id " . $ordine;

} else {
    $query = "	SELECT  c.id, 
                        c.imgs, 
                        c.id_personaggio_mittente, 
                        c.id_personaggio_destinatario, 
                        c.tipo, c.ora,
                        c.testo, 
                        pm.url_img_chat, 
                        pm.nome AS nome_mittente,
                        pd.nome AS nome_destinatario,
                        m.ora_prenotazione,
                        m.privata
                    FROM chat c
                    INNER JOIN mappa m ON m.id = c.stanza
                    LEFT JOIN personaggio pm ON pm.id_personaggio = c.id_personaggio_mittente
                     LEFT JOIN personaggio pd 
                                ON pd.id_personaggio = c.id_personaggio_destinatario
                    WHERE c.stanza = ?
                    AND m.privata = 0 AND DATE_SUB(NOW(), INTERVAL ? HOUR) < c.ora
                    AND c.ora > IFNULL(m.ora_prenotazione, '0000-00-00 00:00:00') "
                    . ($includi_sistema ? '' : "AND c.tipo != 'S' ")
                    . "ORDER BY c.id " . $ordine;
}

$do_query = gdrcd_stmt_all($query, [$_SESSION['luogo'], $ore]);

$stanza_info = gdrcd_stmt_one("SELECT nome FROM mappa WHERE id = ?", [$_SESSION['luogo']]);
$nome_stanza = $stanza_info['nome'] ?? '';

/* Inlinea i fogli di stile del sito, cosi' l'export HTML resta leggibile anche offline */
$docroot = __DIR__;
$inline_css = '';

if ($formato === 'html') {
    $css_files = [
        $docroot . '/themes/homepage/' . $PARAMETERS['themes']['homepage'] . '/homepage.css',
        $docroot . '/themes/' . $PARAMETERS['themes']['current_theme'] . '/main.css',
        $docroot . '/themes/' . $PARAMETERS['themes']['current_theme'] . '/chat.css',
    ];

    foreach ($css_files as $css_file) {
        if (is_file($css_file)) {
            $inline_css .= file_get_contents($css_file) . "\n";
        }
    }
}

/*Inizio a preparare il testo da inserire poi nel file da salvare.*/
$add_chat = '';

if ($formato === 'html') {
    $favicon = base64_encode(file_get_contents($docroot . '/public/images/favicon.ico'));

    $add_chat = '

        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
            "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
            <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <meta http-equiv="X-UA-Compatible" content="IE=edge" />
            <link rel="shortcut icon" href="data:image/x-icon;base64,' . $favicon . '" type="image/x-icon" />
            <style type="text/css">' . $inline_css . '</style>
            </head>
            <body class="main_body" style="overflow:auto; text-align:justify;"> ';
}


$i = 0;
$start_time = null;
/* Eseguo la query  */

foreach($do_query as $row){
    if ($start_time === null) {
        $start_time = $row['ora'];
    }

    $messaggio = gdrcd_chat_message_handler($row, $formato);

    if ($messaggio === null) {
        continue;
    }

    $add_chat .= $messaggio . "\n";
}

if ($formato === 'html') {
    $add_chat .= '
            </body>
            </html>
            ';
}

if ($formato === 'html' || $formato === 'pdf') {
    // Rende offline le icone razza/genere e ogni altra immagine effettivamente locale
    $add_chat = gdrcd_chat_html_embed_local_images($add_chat, $docroot);
}

if ($formato === 'pdf') {
    // Rendering separato dal live/HTML export: qui $add_chat è già il markup minimale prodotto
    // da gdrcd_chat_html_to_pdf_markup(). Avatar/immagini di chat con URL esterno spariscono
    // silenziosamente dal PDF.
    $add_chat = \Dskripchenko\PhpPdf\Document::fromHtml($add_chat)->toBytes();
}

    if ($start_time === null) {
        $start_time = date('Y-m-d H:i:s', strtotime('-' . $ore . ' hours'));
    }

    /* Scrivo tutto in un file di testo */
    $start = gdrcd_format_datetime_cat($start_time);
    $nome_stanza_sanitizzato = preg_replace('/[^A-Za-z0-9_\-]+/', '_', $nome_stanza);
    $nome_base = $nomefile_sanitizzato !== '' ? $nomefile_sanitizzato : $nome_stanza_sanitizzato . "-" . $start;
    $file = $nome_base . "." . $formato;

    $byteLength = strlen($add_chat);

    /* Do le informazioni di download */
    header("Content-Disposition: attachment; filename=" . urlencode($file));
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");
    header("Content-Description: File Transfer");
    header("Content-Length: " . strlen($add_chat));

    $chunkSize = 4096;

    for ($bufferIndex = 0; $bufferIndex <= $byteLength; $bufferIndex += $chunkSize) {
        echo substr($add_chat, $bufferIndex, $chunkSize);
    }
    

