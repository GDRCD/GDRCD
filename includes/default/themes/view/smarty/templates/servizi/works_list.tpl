{foreach $body_rows as $row}
    <div class='tr'>
        <div class='td'>{{$row.name}}</div>
        <div class='td'><img alt="{{$row.name}}" src="{{$row.immagine}}"></div>
        <div class='td'>{{$row.stipendio}}</div>
        <div class='td commands'>
            {if $row.buyable}
                <form action="servizi/lavori/ajax.php" class="ajax_form" data-callback="updateWorksTable">
                    <input type="hidden" name="action" value="get_work">
                    <input type="hidden" name="id" value="{{$row.id}}">
                    <button type="submit">
                        <i class="far fa-file-signature"></i>
                    </button>
                </form>
            {/if}
            {if $row.dimissions}
                <form action="servizi/lavori/ajax.php" class="ajax_form" data-callback="updateWorksTable">
                    <input type="hidden" name="action" value="remove_work">
                    <input type="hidden" name="id" value="{{$row.id}}">
                    <button type="submit">
                        <i class="fas fa-file-excel"></i>
                    </button>
                </form>
            {/if}
        </div>
    </div>
{/foreach}