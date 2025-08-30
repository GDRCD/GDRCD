<?php
if (($_SESSION['permessi'] < MODERATOR) || ($PARAMETERS['mode']['spymessages'] != 'ON')){
        
        echo '<div class="error">'.gdrcd_filter('out',$MESSAGE['error']['not_allowed']).'</div>';
    } else {
        $result=gdrcd_query("SELECT nome FROM personaggio ORDER BY nome", 'result');
        $log_by_user_label = gdrcd_filter('out',$MESSAGE['interface']['administration']['log']['chat']['log_by_user']);
        $submit_value = gdrcd_filter('out',$MESSAGE['interface']['forms']['submit']);
        
        echo <<<HTML
    <div class="form_container">
        <form action="main.php?page=log_chat" method="post">
            <div class='label'>
                {$log_by_user_label}
            </div>
            <div class='form_field'>
                <select name="pg">
HTML;
        
        while($row=gdrcd_query($result, 'fetch')){
            $nome_filtered = gdrcd_filter('out',$row['nome']);
            echo <<<HTML
                        <option value="{$nome_filtered}">
                            {$nome_filtered}
                        </option>
HTML;
        }
        
        echo <<<HTML
                </select>
            </div>
            <!-- bottoni -->
            <div class='form_submit'>
                <input type="hidden" value="view_user" name="op" />
<input type="submit"  value="{$submit_value}" />
</div>
</form>
</div>
HTML;
        
        $result=gdrcd_query("SELECT nome, id FROM mappa WHERE chat=1 ORDER BY nome", 'result');
        $log_by_room_label = gdrcd_filter('out',$MESSAGE['interface']['administration']['log']['chat']['log_by_room']);
        $begin_label = gdrcd_filter('out',$MESSAGE['interface']['administration']['log']['chat']['begin']);
        $end_label = gdrcd_filter('out',$MESSAGE['interface']['administration']['log']['chat']['end']);
        $submit_value = gdrcd_filter('out',$MESSAGE['interface']['forms']['submit']);
        $current_date = date("Y-m-d");
        
        echo <<<HTML
<br><br>
    <div class="form_container">
        <form action="main.php?page=log_chat"  method="post">
            <div class='form_label'>
                {$log_by_room_label}
            </div>
            <div class='form_field'>
                <select name="luogo">
HTML;
        
        while($row=gdrcd_query($result, 'fetch')){
            $id_filtered = gdrcd_filter('out',$row['id']);
            $nome_filtered = gdrcd_filter('out',$row['nome']);
            echo <<<HTML
                        <option value="{$id_filtered}">
                            {$nome_filtered}
                        </option>
HTML;
        }
        
        echo <<<HTML
                </select>
            </div>
            <div class='form_label'>
                {$begin_label}
            </div>
            <div class='form_field'>
                <input type="datetime-local" name="data_a" value="{$current_date}T00:00">
            </div>
            <div class='form_label'>
                {$end_label}
            </div>
            <div class='form_field'>
                <input type="datetime-local" name="data_b" value="{$current_date}T23:59">
            </div>
            <!-- bottoni -->
            <div class='form_submit'>
                <input type="hidden" value="view_date" name="op" />
                <input type="submit"  value="{$submit_value}" />
            </div>

        </form>
        </div>

        <div class="link_back">
            <a href="main.php?page=opzioni">Torna indietro</a>
        </div>
HTML;

}