{foreach $body_rows as $row}
    <div class='tr'>

        <div class='td'>{{$row.nome}}</div>
        <div class='td'>{{$row.categoria}}%</div>
        <div class='td commands'>
            {if $row.contatti_view_permission}
                <a href='/main.php?page={$path}&op=edit_contatto&id={$row.id}' title='Modifica'><i class='fas fa-edit'></i></a>
                <a class='ajax_link' data-id='{$row.id}' data-action='delete_contatto' href='#' title='Elimina'><i class='far fa-trash'></i></a>
            {/if}
        </div>
    </div>
{/foreach}