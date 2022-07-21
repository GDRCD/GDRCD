{foreach $body_rows as $row}
    <div class="single_object" data-id="{{$row.id}}">
        <img src="{{$row.immagine}}" alt="{{$row.nome}}" title="{{$row.nome}}">
    </div>

{/foreach}
