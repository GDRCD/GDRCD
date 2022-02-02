<form method="POST" class="form form-new_chat">
    <div class='tr'>
        <div class='td'><select name="item">{{$body_rows}}</select></div>
        <div class='td'><input type="number" name="percentuale" ></div>
        <div class='td'><input type="number" name="quantita_min" ></div>
        <div class='td'><input type="number" name="quantita_max"></div>
        {if $gathering_ability}
            <div class='td'><input type="number" name="livello_abi"></div>
        {/if}
        <div class='td commands'>
            {if $gathering_view_permission}
                <!-- bottoni -->
                <div class="single_input">
                    <div class='form_submit'>
                        <input type="number" name="id_chat" value="{{$id_chat}}" hidden >
                        <input type="hidden" name="action" value="new_chat_item">
                        <input type="submit" value="Aggiungi"/>
                    </div>
                </div>

            {/if}
        </div>
    </div>
</form>
