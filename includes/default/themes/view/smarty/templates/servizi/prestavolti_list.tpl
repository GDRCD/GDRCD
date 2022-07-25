{foreach $body_rows as $row}
    <div class='tr'>
        <div class='td'>{{$row.personaggio}}</div>
        <div class='td'>{{$row.nome}}</div>
        <div class='td'>{{$row.cognome}}</div>
        <div class='td'>{{$row.fonte}}</div>

    </div>
{/foreach}