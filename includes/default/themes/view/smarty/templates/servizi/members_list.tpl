{foreach $body_rows as $row}
    <div class='tr'>
        <div class='td'>{{$row.personaggio}}</div>
        <div class='td'>{{$row.name}}</div>
        <div class='td'><img src="{{$row.logo}}" alt="{{$row.name}}"/></div>
    </div>
{/foreach}