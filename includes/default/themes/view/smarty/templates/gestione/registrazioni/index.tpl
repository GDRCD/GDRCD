{foreach $body_rows as $reg}
    <div class="tr">
        <div class="titolo td">
            <a href="main.php?page=scheda/index&id_pg={{$reg.id_autore}}">
                {{$reg.autore}}
            </a>
        </div>
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
        <div class="td commands">

            {if $reg.view_permission}
                <a href="main.php?page=gestione/registrazioni/view&id={{$reg.id}}">
                    <i class="fas fa-eye"></i>
                </a>
            {/if}

            {if $reg.update_permission}
                <a href='main.php?page=gestione/registrazioni/edit&id={{$reg.id}}'><i
                            class='fas fa-edit'></i>
                </a>
                <form class="form ajax_form"
                      action="gestione/registrazioni/ajax.php"
                      data-callback="updateRegistrazioniTable">
                    <input type="hidden" name="action" value="registrazioni_bloccata">
                    <input type="hidden" name="id" value="{{$reg.id}}">
                    <input type="hidden" name="type" value="{{$reg.type}}">
                    <button type="submit">
                        {if $reg.bloccata}
                            <i class="fas fa-lock" title="Sblocca"></i>
                        {else}
                            <i class="fas fa-unlock" title="Blocca"></i>
                        {/if}
                    </button>
                </form>

                <form class="form ajax_form"
                      action="gestione/registrazioni/ajax.php"
                      data-callback="updateRegistrazioniTable">
                    <input type="hidden" name="action" value="registrazioni_controllata">
                    <input type="hidden" name="id" value="{{$reg.id}}">
                    <input type="hidden" name="type" value="{{$reg.type}}">
                    <button type="submit">
                        {if $reg.controllata}
                            <i class="fas fa-check" title="Segma come da controllare"></i>
                        {else}
                            <i class="fas fa-times" title="Segna come controllata"></i>
                        {/if}
                    </button>
                </form>

                <form class="form ajax_form"
                      action="gestione/registrazioni/ajax.php"
                      data-callback="updateRegistrazioniTable">
                    <input type="hidden" name="action" value="registrazioni_delete">
                    <input type="hidden" name="id" value="{{$reg.id}}">
                    <input type="hidden" name="type" value="{{$reg.type}}">
                    <button type="submit">
                        <i class='far fa-trash'></i>
                    </button>
                </form>
            {/if}
        </div>

    </div>
{/foreach}