{foreach $body_row as $row}

    {if $row.position}
        <div class="general_title">

            {if ($row.position_id > 0)}
                <a href="main.php?dir={{$row.position_id}}">{{$row.position}}</a>
            {else}
                {{$row.position}}
            {/if}

        </div>
    {/if}
    <div class="single_presente">

        <div class="presente_image">
            <img src="{{$row.mini_avatar}}" alt="">
        </div>
        <div class="presente_info">
            <div class="presente_name">
                <a href="main.php?page=scheda&pg={{$row.nome}}&id_pg={{$row.id}}">
                    {{$row.nome}}
                </a>
                <img class="presenti_ico" src="{{$row.gender_icon}}" alt="{{$row.gender_name}}"
                     title="{{$row.gender_name}}"/>
                <img class="presenti_ico" src="{{$row.availability_icon}}" alt="{{$row.availability_name}}"
                     title="{{$row.availability_name}}"/>
            </div>

            <div class="presente_commands">
                <!-- # TODO Cambiare nome a id una volta fatti i messaggi -->
                <a href="/main.php?page=messages_center&op=create&destinatario={{$row.nome}}" title="Invia messaggio a {{$row.nome}}">
                    <i class="fas fa-paper-plane"></i>
                </a>
            </div>
        </div>

    </div>
{/foreach}