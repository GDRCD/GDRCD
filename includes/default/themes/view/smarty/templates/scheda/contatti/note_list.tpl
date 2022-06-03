{foreach $body_rows as $row}
    <div class='tr'>
        <div class='td'>
            <a href='{$row.pop_up}'> {$row.titolo}</a>
        </div>
        <div class='td'>
            {$row.nota}[...]
        </div>
        {if $row.contatti_view_permission}
        <div class='td commands'>


            Creato il: {$row.creato_il} | Creato da: {$row.creato_da} |
            {if $row.contatti_update_permission} <a href='{$row.pop_up_modifica}'><i class='fas fa-edit'></i></a>{/if}
            {if $row.contatti_delete_permission}|
            <a class='ajax_link' data-id='{$row.id}' data-action='delete_nota' href='#'  title='Elimina'><i class='far fa-trash'></i></a>
            {/if}
        </div>
        {/if}
    </div>
{/foreach}