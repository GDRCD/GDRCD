{foreach $body_rows as $row}
    <div class='tr'>
        <div class='td'>{{$row.name}}</div>
        <div class='td'><img src="{{$row.logo}}" alt="{{$row.name}}"/></div>
        <div class='td'>{{$row.stipendio}}</div>
        <div class='td'>{if $row.poteri} <i class="fas fa-star"></i> {/if}</div>
    </div>
{/foreach}