{foreach $data as $esito}
    <div class='single_answer {{$esito.mine}}'>
        <div class='text'>{{$esito.contenuto}}</div>
        {if $esito.dice_enabled}
            <div class='dice'>
        <span> Sono richiesti {$esito.dice_num} dadi da {$esito.dice_face} su
                    {$esito.abi_name} in chat:
            <a href='/main.php?dir={$esito.chat_id}'>{$esito.chat_name}</a> .
        </span>
            </div>
            {foreach $esito.lanciati as $lancio}
                <div class='dice_result'> {{$lancio.pg}} : <span>{{$lancio.res_num}}</span>
                    <div class='internal_text'> {{$lancio.res_text}}</div>
                </div>
            {/foreach}
        {/if}

        <div class='sub_text'>
            <span class='author'>{{$esito.autore}}</span> -
            <span class='date'> {{$esito.date}}</span>
        </div>

    </div>
{/foreach}