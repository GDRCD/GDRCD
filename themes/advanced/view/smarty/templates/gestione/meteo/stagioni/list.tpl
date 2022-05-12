{foreach $body_rows as $row}
    <div class='tr'>
        <div class='td'>{{$row.name}}</div>
        <div class='td'>{{$row.min}}</div>
        <div class='td'>{$row.max}</div>
        <div class='td'>{{$row.date_start}}</div>
        <div class='td'>{{$row.sunrise}}</div>
        <div class='td'>{{$row.sunset}}</div>
        <div class='td commands'>
            {if $row.meteo_season_permission}
                <a href='/main.php?page=gestione_meteo_stagioni&op=edit&id={$row.id}' title='Modifica'>
                    <i class="fas fa-edit"></i>
                </a>
                <a href='/main.php?page=gestione_meteo_stagioni&op=conditions&id={$row.id}' title='Modifica'>
                    <i class="fas fa-sun-cloud"></i>
                </a>
                <a class='ajax_link' data-id='{$row.id}' data-action='op_delete' href='#' title='Elimina'>
                    <i class="fas fa-times-circle"></i>
                </a>
            {/if}

        </div>
    </div>
{/foreach}