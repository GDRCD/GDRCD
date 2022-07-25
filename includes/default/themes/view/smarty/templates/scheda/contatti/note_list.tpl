{foreach $body_rows as $row}
    <div class='tr'>
        <div class='td'>
            <a href='main.php?page=scheda/index&op=contatti_nota_details&id={{$row.id}}&id_pg={{$row.id_pg}}'> {$row.titolo}</a>
        </div>
        <div class='td'>
            {$row.nota}...
        </div>
        {if $row.contatti_view_permission}
            <div class='td commands'>
                {if $row.contatti_update_permission}
                    <a href='{$row.pop_up_modifica}'><i class='fas fa-edit'></i></a>
                {/if}

                {if $row.contatti_delete_permission}|
                    <a class='ajax_link' data-id='{$row.id}' data-action='delete_nota' href='#' title='Elimina'><i
                                class='far fa-trash'></i></a>
                {/if}

            </div>
        {/if}
    </div>
{/foreach}