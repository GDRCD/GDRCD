{foreach $body_rows as $row}
    <div class='tr'>
        <div class='td'>{{$row.nome}}</div>
        <div class='td'>{{$row.descrizione}}</div>
        <div class='td commands'>
            {if $row.gathering_view_permission}
                <a href='/main.php?page={$path}&op=edit_cat&id={$row.id}' title='Modifica'><i class='fas fa-edit'></i></a>
                <a class='ajax_link' data-id='{$row.id}' data-action='delete_cat' href='#' title='Elimina'><i class='far fa-trash'></i></a>
            {/if}
        </div>
    </div>
{/foreach}