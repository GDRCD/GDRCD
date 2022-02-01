{foreach $body_rows as $row}
    <div class='tr'>
        <div class='td'>{{$row.nome}}</div>
        <div class='td'><input type="number" value="{{$row.percentuale}}"></div>
        <div class='td'><input type="number" value="{{$row.quantita_min}}"></div>
        <div class='td'><input type="number" value="{{$row.quantita_max}}"></div>
        {if $row.gathering_ability}
            <div class='td'><input type="number" value="{{$row.livello_abi}}"></div>
        {/if}
        <div class='td commands'>
            {if $row.gathering_view_permission}
                <a href='/main.php?page={$path}&op=edit_chat_item&id={$row.id}' title='Modifica'><i class='fas fa-edit'></i></a>
                <a class='ajax_link' data-id='{$row.id}' data-action='delete_chat_item' href='#' title='Elimina'><i class='far fa-trash'></i></a>
            {/if}
        </div>
    </div>
{/foreach}