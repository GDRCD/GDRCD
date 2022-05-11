{foreach $body_rows as $row}
    <div class='tr'>

        <div class='td'><a href='/main.php?page=scheda&pg={$row.contatto}' title='Modifica'>{{$row.contatto}}</a></div>
        <div class='td'>{{$row.categoria}}</div>
        <div class='td commands'>
            <a href='/main.php?page={$path}&op=view&id={$row.id}' title='Visualizza'><i class='fas fa-file'></i></a>
            {if $row.contatti_view_permission}
                <a href='/main.php?page={$path}&op=edit&id={$row.id}' title='Modifica'><i class='fas fa-edit'></i></a>
                <a class='ajax_link' data-id='{$row.id}' data-action='delete_contatto' href='#' title='Elimina'><i class='far fa-trash'></i></a>
            {/if}
        </div>
    </div>
{/foreach}