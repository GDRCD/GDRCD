<?php
if($_SESSION['permessi'] >= LOG_PERM) {
    $pg = $_REQUEST['pg'];
    $query = "SELECT * FROM segnalazione_role WHERE id = ? 
     AND conclusa = 1" ;
     $check=gdrcd_stmt_one($query, array($_POST['id'])); 
} else {
    $pg = $_SESSION['id_personaggio'];
    $query="SELECT * FROM segnalazione_role WHERE id = ? 
    AND id_personaggio = ? AND conclusa = 1";    
    $check=gdrcd_stmt_one($query, array($_POST['id'], $pg)); 
}
 

$typeOrder = ($PARAMETERS['mode']['chat_from_bottom'] == 'ON') ? 'DESC' : 'ASC'; 

if (empty($check)) {
    echo 'Non hai accesso a questo log chat';
    return;
}
    ?>

    <div class="page_title">
        <h2>Log chat</h2>
    </div>
    <div class="log_roles">
        <?php
        //
        $r_nam = gdrcd_stmt_one(" SELECT nome FROM mappa WHERE id = ? ", [$check['stanza']]);
        
        $query = gdrcd_stmt_all("SELECT 
                                c.id,
                                c.imgs,
                                c.id_personaggio_mittente,
                                pm.nome AS nome_mittente,
                                c.id_personaggio_destinatario,
                                pd.nome AS nome_destinatario,
                                c.tipo,
                                c.ora,
                                c.testo,
                                c.tag_posizione,
                                pm.url_img_chat AS url_img_chat
                            FROM chat c
                            LEFT JOIN personaggio pm 
                                ON pm.id_personaggio = c.id_personaggio_mittente
                            LEFT JOIN personaggio pd 
                                ON pd.id_personaggio = c.id_personaggio_destinatario
							WHERE stanza = ? AND ora >= ? 
							AND ora <= ? 
							ORDER BY ora " . $typeOrder, [$check['stanza'], $check['data_inizio'], $check['data_fine']]);
                            
//Recupero dei partecipanti -> pg che hanno giocato in quella chat alla stessa ora.
        /* Se esistono record */
        if ($query) {

            echo '<div style="text-align:center;">' . gdrcd_format_date($check['data_inizio']) . '</div>';
            //Titolo del log
            echo '<div class="titolo_box">' . $r_nam['nome'] . '</div>';
            /* Eseguo la query e le formattazioni */
            foreach ($query as $row) {
                echo gdrcd_chat_message_handler($row);  
            }
        } else {
            echo 'Nessun record';
        } ?>
    </div>

    <div class="link_back">
        <a href="main.php?page=gestione_segnalazioni&segn=roles_gm">
            <?php echo gdrcd_filter('out',
                $MESSAGE['interface']['sheet']['link']['back_roles']); ?>
        </a>
    </div>
 