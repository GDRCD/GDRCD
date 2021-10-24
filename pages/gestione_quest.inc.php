<div class="pagina_gestione">
    <?php
    /*HELP: */
    /*Controllo permessi utente*/
    if($_SESSION['permessi'] < QUEST_PERM && QUEST_ENABLED) {
        echo '<div class="error">'.gdrcd_filter('out', $MESSAGE['error']['not_allowed']).'</div>';
    } else {
    ?>
    <!-- Titolo della pagina -->
    <div class="page_title">
        <h2>Gestione quest</h2>
    </div>
    <!-- Corpo della pagina -->
    <div class="page_body">
        <?php

        /** Richieste POST */
        switch(gdrcd_filter_get($_POST['op'])) {
            case 'edit_quest':
                include ('gestione/quest/registra_quest.php');
                break;
            case 'doedit_quest':
                include ('gestione/quest/doedit_quest.php');
                break;
            case 'insert_quest':
                include ('gestione/quest/insert_quest.php');
                break;
            case 'doedit_trama':
                include ('gestione/quest/doedit_trama.php');
                break;
            case 'insert_trama':
                include ('gestione/quest/insert_trama.php');
                break;
            case 'edit_trama':
                include ('gestione/quest/registra_trama.php');
                break;
            case 'delete_quest':
                include ('gestione/quest/delete_quest.php');
                break;
            case 'delete_trama':
                include ('gestione/quest/delete_trama.php');
                break;
        }
        /** Richieste GET */
        switch(gdrcd_filter_get($_GET['op'])) {
            case 'new_quest':
                include ('gestione/quest/registra_quest.php');
                break;
            case 'new_trama':
                include ('gestione/quest/registra_trama.php');
                break;
            case 'lista_trame':
                include ('gestione/quest/lista_trame.php');
                break;
            default: //Form di manutenzione
                include ('gestione/quest/index.php');
                break;
        }

        echo '</div>'; //<!-- page_body -->
        } //else
        ?>
    </div><!-- pagina -->
