{foreach $body_rows as $row}
    <form method="POST" class="form form-chat">
    <div class='tr'>
        <div class='td'>{{$row.nome}}</div>
        <div class='td'><input type="number" name="percentuale" value="{{$row.percentuale}}"></div>
        <div class='td'><input type="number" name="quantita_min" value="{{$row.quantita_min}}"></div>
        <div class='td'><input type="number" name="quantita_max" value="{{$row.quantita_max}}"></div>
        {if $row.gathering_ability}
            <div class='td'><input type="number" name="livello_abi" value="{{$row.livello_abi}}"></div>
        {/if}
        <div class='td commands'>
            {if $row.gathering_view_permission}
                <!-- bottoni -->
                <div class="single_input">
                    <div class='form_submit'>
                        <input type="number" name="id" value="{{$row.id}}" hidden>
                        <input type="hidden" name="action" value="edit_chat_item">
                        <input type="submit" value="modifica"/>
                    </div>
                </div>
                </form>




                <a class='ajax_link' data-id='{$row.id}' data-action='delete_chat_item' href='#' title='Elimina'><i class='far fa-trash'></i></a>
            {/if}
        </div>
    </div>

{/foreach}