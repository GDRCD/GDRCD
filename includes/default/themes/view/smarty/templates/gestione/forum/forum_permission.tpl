{foreach $body_rows as $permission}
    <div class="tr">
        <div class="td">
            {{$permission.user_name}}
        </div>
        <div class="td">
            {{$permission.forum_name}}
        </div>
        <div class="td">
            <form class="form ajax_form" action="gestione/forum/permessi/gestione_forum_permessi_ajax.php"
                  data-callback="updateForumPermission">
                <div class="single_input">
                    <input type="hidden" name="action" value="op_delete"> <!-- OP NEEDED -->
                    <input type="hidden" name="personaggio" value="{{$permission.user_id}}">
                    <input type="hidden" name="forum" value="{{$permission.forum_id}}">
                    <button type='submit'><i class='far fa-trash'></i></button>
                </div>
            </form>
        </div>

    </div>
{/foreach}