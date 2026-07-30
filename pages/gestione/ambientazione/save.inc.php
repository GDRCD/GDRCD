<?php
if($_SESSION['permessi'] < MODERATOR) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
        return; 
    } 

      switch ($_REQUEST['op']) {
          case 'save_new':
               gdrcd_stmt("INSERT INTO ambientazione (capitolo, titolo, testo) 
               VALUES (?, ?, ?)",[gdrcd_filter('num', $_POST['articolo']),gdrcd_filter('in', $_POST['titolo']),gdrcd_filter('in', $_POST['testo'])]);
                echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['warning']['inserted']).'</div>';
                break;
            case 'save_edit':
                gdrcd_stmt("UPDATE ambientazione SET capitolo = ?, titolo = ?, testo = ? WHERE id = ?",[gdrcd_filter('num', $_POST['articolo']),gdrcd_filter('in', $_POST['titolo']),gdrcd_filter('in', $_POST['testo']),gdrcd_filter('num', $_POST['id'])]);
                echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['warning']['modified']).'</div>';
                break;
            case 'save_delete':
                gdrcd_stmt("DELETE FROM ambientazione WHERE id = ?",[gdrcd_filter('num', $_POST['id'])]);
                echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['warning']['deleted']).'</div>';
                break;
            default:
                die('Operazione non riconosciuta.');

                
          }

          
           echo '<div class="link_back">
                <a href="main.php?page=gestione_ambientazione">Torna indietro</a>
            </div>';
