<?php
// Gestione operazioni Token di Iscrizione

// Controllo permessi amministratore
if($_SESSION['permessi'] < SUPERUSER)  {
    echo '<div class="error">Accesso negato. Permessi insufficienti.</div>';
    return;
}

// Gestione operazioni
if(isset($_REQUEST['op'])) {
    switch($_REQUEST['op']) {
        case 'genera_token':
            $giorni_scadenza = intval($_REQUEST['giorni_scadenza']);
            if($giorni_scadenza < 1 || $giorni_scadenza > 365) {
                $giorni_scadenza = 30;
            }
            
            // Genera token alfanumerico univoco
            do {
                $token = '';
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                for($i = 0; $i < 16; $i++) {
                    $token .= $chars[rand(0, strlen($chars) - 1)];
                }
                $check_stmt = gdrcd_stmt("SELECT id FROM token_iscrizione WHERE valore = ?", ['s', $token]);
                $token_exists = gdrcd_query($check_stmt, 'num_rows') > 0;
               
            } while($token_exists);
            
            $insert_stmt = gdrcd_stmt(
                "INSERT INTO token_iscrizione (valore, creato_il, scadenza, utilizzato, utilizzato_da, data_utilizzo) VALUES (?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL ? DAY), 0, NULL, NULL)",
                ['si', $token, $giorni_scadenza]
            );
             
            echo '<div class="success">Token generato con successo: ' . gdrcd_filter('out', $token) . '</div>';
            break;
            
        case 'elimina_token':
            $token_id = intval($_REQUEST['token_id']);
            $delete_stmt = gdrcd_stmt(
                "DELETE FROM token_iscrizione WHERE id = ?",
                ['i', $token_id]
            );
            
            echo '<div class="success">Token eliminato con successo.</div>';
            break;
    }
}

echo <<<HTML
<!-- Link a piè di pagina -->
<div class="link_back">
    <a href="main.php?page=gestione_token">Torna indietro</a>
</div>
HTML;
?>