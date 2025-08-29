<?php
if ($_SESSION['permessi'] < SUPERUSER){
    echo '<div class="error">'.gdrcd_filter('out',$MESSAGE['error']['not_allowed']).'</div>';
} else {
            $config_result = gdrcd_query("SELECT * FROM configurazioni ORDER BY categoria, ordinamento, parametro", 'result');
            echo <<<HTML
<div class="form_container">
HTML;
            // Raggruppa le configurazioni per categoria
            $configs_by_category = array();
            while($config = gdrcd_query($config_result, 'fetch')){
                $categoria = gdrcd_filter('out', $config['categoria']);
                if(empty($categoria)) $categoria = 'Generale';
                $configs_by_category[$categoria][] = $config;
            }
            // Genera le sezioni espandibili per categoria
            foreach($configs_by_category as $categoria => $configs){
                echo <<<HTML
    <details class="config-section">
        <summary class="config-section-summary">{$categoria}</summary>
        <div class="config-section-content">
HTML;    
                foreach($configs as $config){
                    $id = gdrcd_filter('out', $config['id']);
                    $tipo = gdrcd_filter('out', $config['tipo']);
                    $ordinamento = gdrcd_filter('out', $config['ordinamento']);
                    $parametro = gdrcd_filter('out', $config['parametro']);
                    $valore = gdrcd_filter('out', $config['valore']);
                    $descrizione = gdrcd_filter('out', $config['descrizione']);
                    $input_type = gdrcd_filter('out', $config['input']);
                    $opzioni = gdrcd_filter('out', $config['opzioni']);
                    echo <<<HTML
        <form action="main.php?page=gestione_config" method="post" class="config-form">
            <h4 class="config-form-title">{$parametro}</h4>
            <div class="config-form-field">
HTML;                
                    // Genera il campo input appropriato
                    if($input_type == 'select' && !empty($opzioni)){
                        $options_array = explode(',', $opzioni);
                        echo <<<HTML
                <select name="valore" class="config-form-select">
HTML;
                        foreach($options_array as $option){
                            $option = trim($option);
                            $selected = ($option == $valore) ? 'selected' : '';
                            echo <<<HTML
                    <option value="{$option}" {$selected}>{$option}</option>
HTML;
                        }
                        echo <<<HTML
                </select>
HTML;
                    } elseif($input_type == 'textarea'){
                        echo <<<HTML
                <textarea name="valore" rows="3">{$valore}</textarea>
HTML;
                    } elseif($input_type == 'checkbox'){
                        $checked = ($valore == '1' || $valore == 'true') ? 'checked' : '';
                        echo <<<HTML
                <input type="checkbox" name="valore" value="1" {$checked} style="margin-right: 5px;">
HTML;
                    } elseif($input_type == 'token'){
                        echo <<<HTML
                <div class="token-generator">
                    <input type="text" name="valore" value="{$valore}" id="token_field_{$id}" class="config-form-input">
                    <button type="button" onclick="generateRandomToken('{$id}')" class="token-generate-btn">Genera Token</button>
                </div>
HTML;
                    } else {
                        echo <<<HTML
                <input type="text" name="valore" value="{$valore}">
HTML;
                    }
                
                    echo <<<HTML
            </div>
HTML;
                    
                    if(!empty($descrizione)){
                        echo <<<HTML
            <div class="config-form-description">
                {$descrizione}
            </div>
HTML;
                    }
                    
                    echo <<<HTML
            <div class="config-form-submit">
                <input type="hidden" name="id" value="{$id}" />
                <input type="hidden" name="op" value="save_config" />
                <input type="submit" value="Salva"  />
            </div>
        </form>
HTML;
                }
                echo <<<HTML
        </div>
    </details>
HTML;
            }
            echo <<<HTML
</div>
HTML;
    echo <<<HTML
<div class="link_back">
    <a href="main.php?page=gestione">Torna indietro</a>
</div>

<script>
function generateRandomToken(configId) {
    // Genera un token alfanumerico di 16 caratteri
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let token = '';
    for (let i = 0; i < 16; i++) {
        token += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    
    // Inserisce il token nel campo corrispondente
    const tokenField = document.getElementById('token_field_' + configId);
    if (tokenField) {
        tokenField.value = token;
    }
}
</script>
HTML;
}