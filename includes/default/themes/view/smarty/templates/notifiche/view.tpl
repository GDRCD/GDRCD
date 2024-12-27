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
