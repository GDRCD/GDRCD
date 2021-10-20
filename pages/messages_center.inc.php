<?php
include_once('../header.inc.php');
/*Header comune*/
?>

<div class="pagina_messages_center">
    <div class="page_title">
        <h2><?php echo gdrcd_filter('out', $PARAMETERS['names']['private_message']['plur']); ?></h2>
    </div>
    <div class="page_body">
        <?php
        /*
         * Richieste POST
         */
        switch(gdrcd_filter_get($_POST['op'])) {
            case 'erase': //Eliminazione di un messaggio
                include('messages/erase.inc.php');
                break;
            case 'erase_checked': //Controllo eliminazione di un messaggio
                include('messages/erase_checked.inc.php');
                break;
            case 'eraseall': //Eliminazione di tutti i messaggi
                include('messages/eraseall.inc.php');
                break;
            case 'send_message': //Inserimento nuovo messaggio nel db
                include('messages/send_message.inc.php');
                break;
            case 'attach': //Form di composizione di un messaggio
            case 'send':
            case 'reply':
                include('messages/reply.inc.php');
                break;
            default:
                break;
        }
        /*
         * Richieste GET
         */
        switch(gdrcd_filter_get($_GET['op'])) {
            case 'read': //Visualizzazione completa di un messaggio
                include('messages/read.inc.php');
                break;
            case 'create': //Form creazione nuovo messaggio
                include ('messages/create.inc.php');
                break;
            case 'inviati':
            default: //visualizzazione di base
                include('messages/index.inc.php');
                break;
        }
    ?>
    </div><!-- page_body -->
</div><!-- Pagina -->
