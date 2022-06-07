{foreach $body_rows as $row}
    <div class='tr'>

        <div class='td'><a href='/main.php?page=scheda&pg={$row.contatto}' title='Modifica'>{{$row.contatto}}</a></div>
        {if $row.contatti_categories_enabled}
            {if $row.contatti_categories_public}
                {if $row.contatti_categories_staff}
                    <div class='td'>{{$row.categoria}}</div>
                {/if}
            {/if}
        {/if}
        <div class='td commands'>

            <a href='/main.php?page={$path}&op=view&id={$row.id}&id_pg={$row.id_pg}&pg={$row.pg}' title='Visualizza'><i class="far fa-info"></i></a>

            {if $row.contatti_update_permission} <a href='{$row.pop_up_modifica}'><i class='fas fa-edit'></i></a>{/if}


            {if $row.contatti_delete_permission}
                |  <a class='ajax_link' data-id='{$row.id}' data-action='delete_contatto' href='#' title='Elimina'><i class='far fa-trash'></i></a>
            {/if}

        </div>
    </div>
{/foreach}