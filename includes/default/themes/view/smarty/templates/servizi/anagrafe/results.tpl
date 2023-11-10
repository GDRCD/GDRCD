{foreach $body_rows as $pg}
    <div class='tr'>
        <div class='td'><img src="{{$pg.img}}" alt="{{$pg.name}}" title="{{$pg.name}}"></div>
        <div class='td'>{{$pg.name}}</div>
        <div class='td'><img src="{$pg.race_icon}" alt="{{$pg.race}}" title="{{$pg.race}}"></div>
        <div class='td'><img src="{$pg.gender_icon}" alt="{{$pg.gender}}" title="{{$pg.gender}}"></div>
    </div>
{/foreach}