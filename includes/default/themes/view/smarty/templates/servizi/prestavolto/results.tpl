{foreach $body_rows as $pg}
    <div class='tr'>
        <div class='td'>{{$pg.name_pv}}</div>
        <div class='td'>{{$pg.surname_pv}}</div>
        <div class='td'>{{$pg.name}}</div>
        <div class='td'>{{$pg.surname}}</div>
    </div>
{/foreach}