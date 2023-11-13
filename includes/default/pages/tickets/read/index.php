<?php

Router::loadRequired();

$ticket_id = Filters::int($_GET['ticket_id']);

if ( Tickets::getInstance()->permissionReadTicket($ticket_id) ) {
    ?>
    <div class="general_title">News : "<?= Tickets::getInstance()->renderTicketTitle($ticket_id); ?>"</div>

    <div class="tickets_container">
        <?= Tickets::getInstance()->ticketRead($ticket_id); ?>
    </div>

    <div class="link_back">
        <a href="main.php?page=news/index">
            Torna indietro
        </a>
    </div>

<?php } else { ?>

    <div class="warning error"> Permesso negato.</div>

<?php } ?>