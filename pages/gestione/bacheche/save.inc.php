<?php
if($_SESSION['permessi'] < MODERATOR) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
        return; 
    } 

      switch ($_REQUEST['op']) {
          case 'save_new':
               gdrcd_stmt("INSERT INTO araldo (nome, tipo, proprietari) 
               VALUES (?, ?, ?)",[gdrcd_filter('in', $_POST['nome']),gdrcd_filter('num', $_POST['tipo']),gdrcd_filter('num', $_POST['owner'])]);
                echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['warning']['inserted']).'</div>';
                break;
            case 'save_edit':
                gdrcd_stmt("UPDATE araldo SET nome = ?, tipo = ?, proprietari = ? WHERE id_araldo = ?",[gdrcd_filter('in', $_POST['nome']),gdrcd_filter('num', $_POST['tipo']),gdrcd_filter('num', $_POST['owner']),gdrcd_filter('num', $_POST['id'])]);
                echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['warning']['modified']).'</div>';
                break;
            case 'save_delete':
                gdrcd_stmt("DELETE FROM araldo WHERE id_araldo = ?",[gdrcd_filter('num', $_POST['id'])]);
                echo '<div class="warning">'.gdrcd_filter('out', $MESSAGE['warning']['deleted']).'</div>';
                break;
            default:
                die('Operazione non riconosciuta.');

                
          }
          echo '<div class="link_back">
                <a href="main.php?page=gestione_bacheche">Torna indietro</a>
            </div>';