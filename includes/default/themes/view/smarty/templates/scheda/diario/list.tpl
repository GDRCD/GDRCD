{foreach $body_rows as $diary}
    <div class="tr">
        <div class="td">
            {{$diary.date}}
        </div>
        <div class="td">
            <a href="main.php?page=scheda/index&op=diario_view&id={{$diary.id}}&id_pg={{$diary.id_pg}}">
                {{$diary.titolo}}
            </a>
        </div>
        {if $diary.update_permission}
            <div class="td">
                {{$diary.visibile}}
            </div>
            <div class="td commands">
                <a href="main.php?page=scheda/index&op=diario_edit&id={{$diary.id}}&id_pg={{$diary.id_pg}}">
                    <i class='fas fa-edit'></i>
                </a>
                <form class="form ajax_form"
                      action="scheda/diario/ajax.php"
                      data-callback="updateDiaryList">
                    <input type="hidden" name="action" value="delete_diary" >
                    <input type="hidden" name="id" value="{{$diary.id}}" >
                    <button type="submit">
                        <i class='far fa-trash'></i>
                    </button>
                </form>
            </div>
        {/if}
    </div>
{/foreach}