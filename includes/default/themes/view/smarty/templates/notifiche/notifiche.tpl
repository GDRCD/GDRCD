<div class="container_notifications">
    <div class="container_notifications_top_bar">
        <div class="container_notifications_title">
            Notifiche
        </div>
        <div class="container_notifications_read_all" title="Segna tutte come lette">
            <form class="ajax_form online_status_form" action="notifiche/ajax.php">
                <button type="submit">
                    <i class="fas fa-bell"></i>

                </button>
                <input type="hidden" name="action" value="read_all">
            </form>
        </div>
    </div>
    {foreach $notifications as $row}
        <div class="single_notification">
            <div class="notification_title">
                {$row.titolo}
            </div>
            <div class="notification_text">
                {$row.testo}
            </div>
            <div class="notification_date">
                {$row.creato_il}
            </div>
        </div>
    {/foreach}

    <div class="notification_button">
        <a href="/main.php?page=notifiche/index">Visualizza tutte le notifiche</a>
    </div>
</div>