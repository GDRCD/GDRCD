{foreach $body_rows as $row}
    <div class='tr'>
        <div class='td'>
            <a href='{$row.pop_up}'> {$row.titolo}</a>
        </div>
        <div class='td'>
            {$row.nota}[...]
        </div>
        <div class='td commands'>

            {if $row.contatti_view_permission}


                    Creato il: {$row.creato_il} | Creato da: {$row.creato_da} |
                    <a href='/main.php?page=scheda_contatti&op=edit_nota&id={$row.id}&id_pg={$row.id_pg}&pg={$row.pg}' title='Modifica'><i class='fas fa-edit'></i></a> |

                    <a class='ajax_link' data-id='{$row.id}' data-action='delete_nota' href='#' title='Elimina'><i class='far fa-trash'></i></a>

            </div>

            {/if}



</div>

{/foreach}