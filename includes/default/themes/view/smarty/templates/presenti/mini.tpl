{foreach $body_row as $row}
    <div class="single_presente">
        <a href="main.php?page=scheda/index&pg={{$row.nome}}&id_pg={{$row.id}}">
            {{$row.nome}}
        </a>
        <img class="presenti_ico" src="{{$row.gender_icon}}" alt="{{$row.gender_name}}" title="{{$row.gender_name}}" />
        <img class="presenti_ico" src="{{$row.availability_icon}}" alt="{{$row.availability_name}}" title="{{$row.availability_name}}" />
    </div>
{/foreach}