{foreach $body_rows as $esito}
    <div class='tr'>
        <div class='td'>{{$esito.titolo}}</div>
        <div class='td'>{{$esito.data}}</div>
        <div class='td'>{{$esito.dice}}</div>
        <div class='td'>{{$esito.abi_name}}</div>
        <div class='td'>
            <form class='form chat_form_ajax ajax_form' action='chat/chat_ajax.php' data-callback='invioSuccess'>
                <input type='hidden' name='action' value='send_esito'>
                <input type='hidden' name='id' value='{{$esito.id}}'>
                <button type='submit'><i class='fas fa-dice'></i></button>
            </form>
        </div>
    </div>
{/foreach}