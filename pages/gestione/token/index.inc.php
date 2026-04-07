<?php
// Gestione Token di Iscrizione

// Controllo permessi amministratore
if($_SESSION['permessi'] < SUPERUSER)  {
    echo '<div class="error">Accesso negato. Permessi insufficienti.</div>';
    return;
}



// Recupera statistiche
$stats_stmt = gdrcd_stmt("SELECT 
    COUNT(*) as totali,
    SUM(CASE WHEN utilizzato = 0 AND scadenza >= CURDATE() THEN 1 ELSE 0 END) as attivi,
    SUM(CASE WHEN utilizzato = 1 THEN 1 ELSE 0 END) as utilizzati,
    SUM(CASE WHEN utilizzato = 0 AND scadenza < CURDATE() THEN 1 ELSE 0 END) as scaduti
    FROM token_iscrizione");
$stats = gdrcd_query($stats_stmt, 'assoc');
gdrcd_query($stats_stmt, 'free');

// Filtro per visualizzazione
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'tutti';
$where_clause = '';
switch($filtro) {
    case 'attivi':
        $where_clause = 'WHERE utilizzato = 0 AND scadenza >= CURDATE()';
        break;
    case 'utilizzati':
        $where_clause = 'WHERE utilizzato = 1';
        break;
    case 'scaduti':
        $where_clause = 'WHERE utilizzato = 0 AND scadenza < CURDATE()';
        break;
    default:
        $where_clause = '';
} 
// Recupera token
$tokens_stmt = gdrcd_stmt("SELECT 
    token_iscrizione.*,
    personaggio.nome as nome_personaggio
    FROM token_iscrizione
    LEFT JOIN personaggio ON token_iscrizione.id_personaggio = personaggio.id_personaggio
      $where_clause ORDER BY creato_il DESC");      

echo <<<HTML
<div class="gestione-token-container">
    <h2>Gestione Token di Iscrizione</h2>
    
    <!-- Statistiche -->
    <div class="token-stats">
        <div class="stat-item">
            <span class="stat-label">Totali:</span>
            <span class="stat-value">{$stats['totali']}</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Attivi:</span>
            <span class="stat-value stat-active">{$stats['attivi']}</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Utilizzati:</span>
            <span class="stat-value stat-used">{$stats['utilizzati']}</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Scaduti:</span>
            <span class="stat-value stat-expired">{$stats['scaduti']}</span>
        </div>
    </div>
    
    <!-- Form generazione token -->
    <div class="token-generator">
        <h3>Genera Nuovo Token</h3>
        <form method="post" action="main.php?page=gestione_token" class="generate-form">
            <div class="form-group">
                <label for="giorni_scadenza">Giorni di validità:</label>
                <input type="number" name="giorni_scadenza" id="giorni_scadenza" value="30" min="1" max="365" required>
            </div>
            <input type="hidden" name="op" value="genera_token">
            <input type="submit" value="Genera Token" class="btn-generate">
        </form>
    </div>
    
    <!-- Filtri -->
    <div class="token-filters">
        <a href="main.php?page=gestione_token&filtro=tutti" class="filter-link">Tutti</a>
        <a href="main.php?page=gestione_token&filtro=attivi" class="filter-link">Attivi</a>
        <a href="main.php?page=gestione_token&filtro=utilizzati" class="filter-link">Utilizzati</a>
        <a href="main.php?page=gestione_token&filtro=scaduti" class="filter-link">Scaduti</a>
    </div>
    
    <!-- Tabella token -->
    <div class="fake-table token-table">
        <div class="tr header">
            <div class="td">Token</div>
            <div class="td">Creato il</div>
            <div class="td">Scadenza</div>
            <div class="td">Stato</div>
            <div class="td">Utilizzato da</div>
            <div class="td">Data utilizzo</div>
            <div class="td">Azioni</div>
        </div>
HTML;

if(gdrcd_query($tokens_stmt, 'num_rows') > 0) {
    while($token = gdrcd_query($tokens_stmt, 'assoc')) {
        $id = intval($token['id']);
        $valore = gdrcd_filter('out', $token['valore']);
        $creato_il = gdrcd_filter('out', $token['creato_il']);
        $scadenza = gdrcd_filter('out', $token['scadenza']);
        $utilizzato = $token['utilizzato'];
        $utilizzato_da = gdrcd_filter('out', $token['nome_personaggio']);
        $data_utilizzo = gdrcd_filter('out', $token['data_utilizzo']);
        
        // Determina stato
        $stato_class = '';
        $stato_text = '';
        if($utilizzato == 1) {
            $stato_class = 'status-used';
            $stato_text = 'Utilizzato';
        } elseif(strtotime($scadenza) < time()) {
            $stato_class = 'status-expired';
            $stato_text = 'Scaduto';
        } else {
            $stato_class = 'status-active';
            $stato_text = 'Attivo';
        }
        
        echo <<<HTML
        <div class="tr">
            <div class="td token-value">{$valore}</div>
            <div class="td">{$creato_il}</div>
            <div class="td">{$scadenza}</div>
            <div class="td"><span class="status {$stato_class}">{$stato_text}</span></div>
            <div class="td">{$utilizzato_da}</div>
            <div class="td">{$data_utilizzo}</div>
            <div class="td">
HTML;
        
        if($utilizzato == 0) {
            echo <<<HTML
                <form method="post" action="main.php?page=gestione_token" style="display: inline;" onsubmit="return confirm('Sei sicuro di voler eliminare questo token?');">
                    <input type="hidden" name="token_id" value="{$id}">
                    <input type="hidden" name="op" value="elimina_token">
                    <input type="submit" value="Elimina" class="btn-delete">
                </form>
HTML;
        }
        
        echo <<<HTML
            </div>
        </div>
HTML;
    }
} else {
    echo <<<HTML
        <div class="tr">
            <div class="td" colspan="7">Nessun token trovato.</div>
        </div>
HTML;
}

echo <<<HTML
    </div>
</div>
HTML;

// Libera le risorse
gdrcd_query($tokens_stmt, 'free');

echo <<<HTML
<div class="link_back">
    <a href="main.php?page=gestione">Torna indietro</a>
</div>
HTML;
?>
