{foreach $tickets as $tickets_data}
    <div class="fake-table tickets_list_table">
        <div class="tr header">
            <div class="tr header-title">
                {{$tickets_data.type_data.titolo}}
            </div>
            <div class="td">
                Titolo
            </div>
            <div class="td">
                Sezione
            </div>
            <div class="td">
                Assegnato a
            </div>
            <div class="td">
                Data Apertura
            </div>
        </div>
        {foreach $tickets_data.tickets as $single_ticket}
            <div class="tr single_ticket {if !$single_ticket.archived}inactive{/if}">
                <div class="td" title="{{$single_ticket.title}}">
                    <a href="/main.php?page=tickets/index&op=read&ticket_id={{$single_ticket.id}}">
                        {if $single_ticket.to_read && $single_ticket.archived}
                            <span class="to_read" title="Nuove risposte!"><i class="far fa-exclamation-circle"></i></span>
                        {/if}
                        {{$single_ticket.title}}
                    </a>
                </div>
                <div class="td" title="{{$single_ticket.sezione}}">
                    {{$single_ticket.sezione}}
                </div>
                <div class="td assigned_to" title="{{$single_ticket.author}}">
                    <a href="main.php?page=scheda/index&pg={{$single_ticket.assegnato_a}}&id_pg={{$single_ticket.assegnato_id}}">
                       {{$single_ticket.assegnato_a}}
                    </a>
                </div>
                <div class="td" title="{{$single_ticket.date}}">
                    {{$single_ticket.date}}
                </div>
            </div>
        {/foreach}
    </div>
{/foreach}