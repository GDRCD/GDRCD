{foreach $body_rows as $stat}
    <div class="tr">
        <div class="sub-tr">
            <div class="td open_text" title="Clicca per aprire/chiudere la descrizione.">
                <span>{{$stat.nome}}</span>
            </div>
            <div class="td">
                {{$stat.valore}}
            </div>
            <div class="td stats_command">
                {if $stat.upgrade_permission}
                    <form class="form ajax_form" action="scheda/stats/ajax.php" data-callback="updateStats">
                        <button type="submit">[+]</button>
                        <input type="hidden" name="action" value="upgrade_stat"/>
                        <input type="hidden" name="stat" value="{{$stat.id}}"/>
                        <input type="hidden" name="pg" value="{{$stat.id_pg}}"/>
                    </form>
                {/if}
                {if $stat.downgrade_permission}
                    <form class="form ajax_form" action="scheda/stats/ajax.php" data-callback="updateStats">
                        <button type="submit">[-]</button>
                        <input type="hidden" name="action" value="downgrade_stat"/>
                        <input type="hidden" name="stat" value="{{$stat.id}}"/>
                        <input type="hidden" name="pg" value="{{$stat.id_pg}}"/>
                    </form>
                {/if}

            </div>
        </div>

        <div class="sub-tr hidden_text">
            <div class="td">
                <div class="table-subtitle">Descrizione</div>
                <div class="table-text">{{$stat.descrizione}}</div>
            </div>
        </div>

    </div>
{/foreach}