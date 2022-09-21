{foreach $body_rows as $reg}
    <div class="tr">
        <div class="titolo td">
            {{$reg.titolo}}
        </div>
        <div class="td">
            {{$reg.chat}}
        </div>
        <div class="td">
            {{$reg.inizio}}
        </div>
        <div class="td">
            {{$reg.fine}}
        </div>
        <div class="td">
            {if $reg.bloccata}
                <span title="Bloccata"> <i class="fas fa-lock"></i>  </span>
            {/if}
            {if $reg.controllata}
                <span title="Controllata"> <i class="fas fa-eye"></i>  </span>
            {/if}
        </div>
        <div class="td commands">

            {if $reg.view_permission}
                <a href="main.php?page=scheda/index&op=registrazioni_view&id={{$reg.id}}&id_pg={{$reg.id_pg}}">
                    <i class="fas fa-eye"></i>
                </a>
            {/if}

            {if $reg.update_permission}
                <a href='main.php?page=scheda/index&op=registrazioni_edit&id={{$reg.id}}&id_pg={{$reg.id_pg}}'><i
                            class='fas fa-edit'></i></a>
                <form class="form ajax_form"
                      action="scheda/registrazioni/ajax.php"
                      data-callback="updateRegistrazioniTable">
                    <input type="hidden" name="action" value="registrazioni_delete">
                    <input type="hidden" name="id" value="{{$reg.id}}">
                    <button type="submit">
                        <i class='far fa-trash'></i>
                    </button>
                </form>
            {/if}
        </div>

    </div>
{/foreach}