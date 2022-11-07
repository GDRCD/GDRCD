{foreach $body_rows as $row}
    <div class='tr'>
        <div class='td title'>
            {if $row.important}
                <span class="important" title="Importante"><i class="fas fa-stars"></i></span>
            {/if}
            {if $row.closed}
                <span class="closed" title="Chiuso"><i class="fas fa-lock"></i></span>
            {/if}
            <a href="/main.php?page=forum/index&op=post&post_id={{$row.id}}&pagination=1">
                {{$row.title}}
            </a>
        </div>
        <div class='td'>
            <a href="/main.php?page=scheda/index&id_pg={{$row.author_id}}">
                {{$row.author}}
            </a>
        </div>
        <div class='td'>{$row.date}</div>
        <div class='td'>
            {{$row.date_last}}
            {if $row.to_read}
                <span class="to_read" title="Nuove risposte!"><i class="far fa-exclamation-circle"></i></span>
            {/if}
        </div>
    </div>
{/foreach}