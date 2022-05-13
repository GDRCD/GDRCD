{foreach $body_rows as $row}
    <div class='tr'>
        <div class='td'>{{$row.name}}</div>
        <div class='td'>{{$row.percentage}} %</div>
    </div>
{/foreach}