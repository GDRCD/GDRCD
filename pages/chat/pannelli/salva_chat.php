<body>
<style>
    h1 {
        margin: 1em;
    }
    
    .form_field {
        margin: 1em;
    }

    .form_field h2 {
        margin: 0.5em 0;
    }

    .form_submit {
        margin: 1em;
    }
</style>

<h1><?php echo gdrcd_filter('out', $MESSAGE['chat']['salvachat']['title']); ?></h1>

<?php

if ($PARAMETERS['mode']['chatsave'] != 'ON') {
    echo '<div class="warning" style="width: auto;">'
        . gdrcd_filter('out', $MESSAGE['chat']['error']['permissions'])
        . '</div>';
    echo '</body>';
    exit;
}

$tempo_salvataggio = (int) gdrcd_configuration_get('salva_chat.tempo_salvataggio');

$stanza_info = gdrcd_stmt_one('SELECT nome FROM mappa WHERE id = ?', [$_SESSION['luogo']]);
$nome_stanza_sanitizzato = preg_replace('/[^A-Za-z0-9_\-]+/', '_', $stanza_info['nome'] ?? '');

$ordine_default = ($PARAMETERS['mode']['chat_from_bottom'] == 'ON') ? 'desc' : 'asc';

?>

<form action="chat_save.proc.php" method="post" id="salvaChatForm">

    <div class="form_field">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['chat']['salvachat']['label']['formato']); ?></h2>
        <label>
            <input type="radio" name="formato" value="html" checked>
            <?php echo gdrcd_filter('out', $MESSAGE['chat']['salvachat']['label']['formato_html']); ?>
        </label>
        <label>
            <input type="radio" name="formato" value="txt">
            <?php echo gdrcd_filter('out', $MESSAGE['chat']['salvachat']['label']['formato_txt']); ?>
        </label>
        <label>
            <input type="radio" name="formato" value="pdf">
            <?php echo gdrcd_filter('out', $MESSAGE['chat']['salvachat']['label']['formato_pdf']); ?>
        </label>
    </div>

    <div class="form_field">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['chat']['salvachat']['label']['nomefile']); ?></h2>
        <input type="text" name="nomefile" id="salvaChatNomefile" style="width:90%;">
    </div>

    <div class="form_field">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['chat']['salvachat']['label']['ore']); ?></h2>
        <input
            type="number"
            name="ore"
            min="1"
            max="<?php echo gdrcd_filter_num($tempo_salvataggio); ?>"
            value="<?php echo gdrcd_filter_num($tempo_salvataggio); ?>"
        >
    </div>

    <div class="form_field">
        <label>
            <input type="hidden" name="includi_sistema" value="0">
            <input type="checkbox" name="includi_sistema" value="1" checked>
            <?php echo gdrcd_filter('out', $MESSAGE['chat']['salvachat']['label']['includi_sistema']); ?>
        </label>
    </div>

    <div class="form_field">
        <h2><?php echo gdrcd_filter('out', $MESSAGE['chat']['salvachat']['label']['ordine']); ?></h2>
        <label>
            <input type="radio" name="ordine" value="asc" <?php echo $ordine_default === 'asc' ? 'checked' : ''; ?>>
            <?php echo gdrcd_filter('out', $MESSAGE['chat']['salvachat']['label']['ordine_asc']); ?>
        </label>
        <label>
            <input type="radio" name="ordine" value="desc" <?php echo $ordine_default === 'desc' ? 'checked' : ''; ?>>
            <?php echo gdrcd_filter('out', $MESSAGE['chat']['salvachat']['label']['ordine_desc']); ?>
        </label>
    </div>

    <div class="form_submit">
        <input type="submit" value="<?php echo gdrcd_filter('out', $MESSAGE['chat']['salvachat']['submit']); ?>">
    </div>

</form>

<script>
    
    // Precompila il nome file con un suggerimento leggibile (stanza + data/ora corrente)
    (function () {
        var nomeStanza = <?php echo json_encode($nome_stanza_sanitizzato); ?>;
        var pad = function (n) { return (n < 10 ? '0' : '') + n; };
        var now = new Date();
        var suggerito = nomeStanza + '-'
            + now.getFullYear() + pad(now.getMonth() + 1) + pad(now.getDate())
            + '_' + pad(now.getHours()) + pad(now.getMinutes());

        document.getElementById('salvaChatNomefile').value = suggerito;
    })();
</script>
</body>
