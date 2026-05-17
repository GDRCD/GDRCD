<?php
if($_SESSION['permessi'] < MODERATOR) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
        return; 
    } elseif($PARAMETERS['mode']['skillsystem'] == 'OFF') {
        echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['warning']['unactive']).'</div>';
        return;
    }
    switch ($_REQUEST['op']) {
        case 'save_new':
              gdrcd_stmt("INSERT INTO abilita (nome, descrizione, car, id_razza) 
              VALUES (?, ?, ?, ?)", [
                  $_POST['nome'],
                  $_POST['descrizione'],
                  $_POST['car'],
                  $_POST['id_razza']
               ]);  
                echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['warning']['inserted']).'</div>';
                break;
                case 'save_edit':
                    $id = $_POST['id_record'];
                    gdrcd_stmt("UPDATE abilita SET nome = ?, descrizione = ?, car = ?, id_razza = ? WHERE id_abilita = ?", [
                        $_POST['nome'],
                        $_POST['descrizione'],
                        $_POST['car'],
                        $_POST['id_razza'],
                        $id
                    ]);  
                    echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['warning']['modified']).'</div>';
                    break;
                    case 'save_delete':
                        $id = $_POST['id_record'];
                        gdrcd_stmt("DELETE FROM abilita WHERE id_abilita = ?", [$id]);  
                        echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['warning']['deleted']).'</div>';
                        break;
                        default:
                            die('Operazione non riconosciuta.');
                }



        echo '<div class="link_back">
                <a href="main.php?page=gestione_abilita">Torna indietro</a>
            </div>';
