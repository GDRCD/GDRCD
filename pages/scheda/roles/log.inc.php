<?php


if($_SESSION['permessi'] >= LOG_PERM) {
    $pg = $_REQUEST['pg'];
    $query = "SELECT * FROM segnalazione_role WHERE id = " . gdrcd_filter('num', $_POST['id']) . " 
     AND conclusa = 1" ;
} else {
    $pg = $_SESSION['id_personaggio'];
    $query="SELECT * FROM segnalazione_role WHERE id = " . gdrcd_filter('num', $_POST['id']) . " 
    AND id_personaggio = '" .gdrcd_filter('in', $pg ). "' AND conclusa = 1";    
}

$check=gdrcd_query($query, 'result'); 
$num_check = gdrcd_query($check, 'num_rows');
$check_f= gdrcd_query($check, 'fetch');

$typeOrder = ($PARAMETERS['mode']['chat_from_bottom'] == 'ON') ? 'DESC' : 'ASC'; 

if ($num_check == 0) {
    echo 'Non hai accesso a questo log chat';
} else {
    ?>

    <div class="page_title">
        <h2>Log chat</h2>
    </div>
    <div class="log_roles">
        <?php
        //
        $name = gdrcd_query(" SELECT nome FROM mappa WHERE id = " . $check_f['stanza'] . "", 'result');
        $r_nam = gdrcd_query($name, 'fetch');

        $query = gdrcd_query("SELECT 
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
							WHERE stanza = " . $check_f['stanza'] . " AND ora >= '" . gdrcd_filter('in', $check_f['data_inizio']) . "' 
							AND ora <= '" . gdrcd_filter('in', $check_f['data_fine']) . "' 
							ORDER BY ora " . $typeOrder, 'result');
                            

        $num = gdrcd_query($query, 'num_rows');

        //Recupero dei partecipanti -> pg che hanno giocato in quella chat alla stessa ora.
        /* Se esistono record */
        if ($num > 0) {

            echo '<div style="text-align:center;">' . gdrcd_format_date($check_f['data_inizio']) . '</div>';
            //Titolo del log
            echo '<div class="titolo_box">' . $r_nam['nome'] . '</div>';
            /* Eseguo la query e le formattazioni */
            while ($row = gdrcd_query($query, 'fetch')) {
                 
                echo gdrcd_chat_message_handler($row);
            }
        } else {
            echo 'Nessun record';
        } ?>
    </div>

    <div class="link_back">
        <a href="main.php?page=scheda_roles&pg=<?php echo gdrcd_filter('in', $_REQUEST['pg']); ?>">
            <?php echo gdrcd_filter('out',
                $MESSAGE['interface']['sheet']['link']['back_roles']); ?>
        </a>
    </div>
<?php }