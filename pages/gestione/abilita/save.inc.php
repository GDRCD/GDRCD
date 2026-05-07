<?php
if($_SESSION['permessi'] < MODERATOR) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
        return; 
    } elseif($PARAMETERS['mode']['skillsystem'] == 'OFF') {
        echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['warning']['unactive']).'</div>';
        return;
    }
    switch ($_REQUEST['op']) {
        case 'save_new':.
        
              gdrcd_stmt("INSERT INTO abilita (nome, descrizione, car, id_razza) 
              VALUES (?, ?, ?, ?)", [
                  $_POST['nome'],
                  $_POST['descrizione'],
                  $_POST['car'],
                  $_POST['id_razza']
               ]);  
            
            

        }