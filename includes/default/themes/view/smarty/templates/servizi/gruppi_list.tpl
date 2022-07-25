{foreach $body_rows as $row}
    <div class='tr'>
        <div class='td'>{{$row.name}}</div>
        <div class='td'>{{$row.member_number}}</div>
        <div class='td'>{{$row.tipo}}</div>
        <div class='td commands'>

            {if $row.storage_permission}
                <a href="main.php?page=servizi/gruppi/index&op=storage&group={{$row.id}}">
                    <i class="fas fa-box-alt"></i>
                </a>
            {/if}

            <a href="main.php?page=servizi/gruppi/index&op=read&group={{$row.id}}">
                <i class='fas fa-eye'></i>
            </a>

        </div>
    </div>
{/foreach}