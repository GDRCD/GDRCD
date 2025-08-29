<?php
if ($_SESSION['permessi'] < SUPERUSER){
    echo '<div class="error">'.gdrcd_filter('out',$MESSAGE['error']['not_allowed']).'</div>';
} else {
    switch ($_REQUEST['op']) {
        case 'save_config':
            $id = gdrcd_filter('in', $_POST['id']);
            $valore = gdrcd_filter('in', $_POST['valore']);
            
            // Validazione input
            if(empty($id) || !is_numeric($id)){
                $error_message = 'ID configurazione non valido';
                echo <<<HTML
<div class="error">{$error_message}</div>
HTML;
                break;
            }
            
            // Aggiorna la configurazione
            $query = "UPDATE configurazioni SET valore = '" . gdrcd_filter('in', $valore) . "' WHERE id = " . intval($id);
            $result = gdrcd_query($query, 'result');
            
            if($result){
                $success_message = 'Configurazione aggiornata con successo';
                echo <<<HTML
<div class="success">{$success_message}</div>
HTML;
            } else {
                $error_message = 'Errore durante l\'aggiornamento della configurazione';
                echo <<<HTML
<div class="error">{$error_message}</div>
HTML;
            }
            break;
            
        default:
            die('Operazione non riconosciuta.');
    }
}

echo <<<HTML
<!-- Link a piè di pagina -->
<div class="link_back">
    <a href="main.php?page=gestione_config">Torna indietro</a>
</div>
HTML;
?>