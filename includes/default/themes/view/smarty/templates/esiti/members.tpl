{foreach $body_rows as $membro}
    <div class='tr'>
        <div class='td'>{{$membro.name}}</div>
        <div class='td'>

            <form action='gestione/esiti/esiti_ajax.php' class='delete_member_form ajax_form'
                  data-callback='refreshMembers'>
                <input type='hidden' name='action' value='delete_member'>
                <input type='hidden' name='id' value='{{$membro.id_row}}'>
                <input type='hidden' name='id_esito' value='{{$membro.id}}'>
                <button type='submit' title='Elimina membro'><i class='fas fa-user-minus'></i></button>
            </form>

        </div>
    </div>
{/foreach}