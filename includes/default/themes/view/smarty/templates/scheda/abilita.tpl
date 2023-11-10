{foreach $body_rows as $ability}
    <div class="tr">
        <div class="sub-tr">
            <div class="abilita_scheda_nome td" title="Clicca per aprire/chiudere la descrizione.">
                <span>{{$ability.nome}}</span>
            </div>
            <div class="abilita_scheda_car td">
                {{$ability.stat}}
            </div>
            <div class="abilita_scheda_tank td">
                {{$ability.grado}}
            </div>
            <div class="abilita_scheda_sub td">
                {if $ability.upgrade_permission}
                    <form class="form ajax_form" action="scheda/abilita/ajax.php" data-callback="updateAbilities">
                        <button type="submit">[+]</button>
                        <input type="hidden" name="action" value="upgrade_ability"/>
                        <input type="hidden" name="abilita" value="{{$ability.id}}"/>
                        <input type="hidden" name="pg" value="{{$ability.id_pg}}"/>
                    </form>
                {/if}
                {if $ability.downgrade_permission}
                    <form class="form ajax_form" action="scheda/abilita/ajax.php" data-callback="updateAbilities">
                        <button type="submit">[-]</button>
                        <input type="hidden" name="action" value="downgrade_ability"/>
                        <input type="hidden" name="abilita" value="{{$ability.id}}"/>
                        <input type="hidden" name="pg" value="{{$ability.id_pg}}"/>
                    </form>
                {/if}
            </div>
        </div>
        <div class="sub-tr">
            <div class="abilita_scheda_text">
                <div class="default_tex">
                    <div class="table-subtitle">Descrizione</div>
                    <div class="table-text">{{$ability.extra_data.text}}</div>
                </div>
                {if $extra_active}
                    <div class="actual_extra_text">
                        <div class="table-subtitle">Descrizione Livello attuale</div>
                        <div class="table-text">{{$ability.extra_data.lvl_extra_text}}</div>
                    </div>
                    <div class="next_extra_text">
                        <div class="table-subtitle">Descrizione Livello successivo</div>
                        <div class="table-text">{{$ability.extra_data.next_lvl_extra_text}}</div>
                    </div>
                    <div class="next_extra_text">
                        <div class="table-subtitle">Costo Livello successivo</div>
                        <div class="table-text"
                             style="text-align: center">{{$ability.extra_data.price}}</div>
                    </div>
                {/if}
                {if $requirement_active}
                    <div class="next_req_text">
                        <div class="table-subtitle">Requisiti Livello successivo</div>
                        <div class="table-text">{{$ability.extra_data.requirement}}</div>
                    </div>
                {/if}

            </div>
        </div>

    </div>
{/foreach}