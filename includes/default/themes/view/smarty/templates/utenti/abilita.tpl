{foreach $body_rows as $ability}
    <div class="tr">
        <div class="sub-tr">
            <div class="abilita_scheda_nome td" title="Clicca per aprire/chiudere la descrizione.">
                <span>{{$ability.name}}</span>
            </div>
            <div class="abilita_scheda_car td">
                {{$ability.stat}}
            </div>
        </div>
        <div class="sub-tr">
            <div class="abilita_scheda_text">
                <div class="default_tex">
                    <div class="table-subtitle">Descrizione</div>
                    <div class="table-text">{{$ability.description}}</div>

                    {if $ability.extra}

                        <div class="table-subtitle">Extra</div>
                        {foreach $ability.extra as $extra_data}
                            <div class="extra_level">
                                <div class="general_subtitle">Grado {{$extra_data.level}}</div>
                                <div class="table-text">{{$extra_data.description}}</div>
                            </div>
                        {/foreach}
                    {/if}


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