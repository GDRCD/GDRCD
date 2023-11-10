{foreach $forums as $forum_data}
    <div class="fake-table forum_list_table">
        <div class="tr header">
            <div class="tr header-title">
                {{$forum_data.type_data.nome}}
            </div>
            <div class="td">
                Nome
            </div>
            <div class="td">
                Nuovi
            </div>
        </div>
        {foreach $forum_data.forums as $forum}
            <div class="tr">
                <div class="td" title="{{$forum.nome}}">
                    <a href="/main.php?page=forum/index&op=posts&forum_id={{$forum.id}}&pagination=1">
                        {{$forum.nome}}
                    </a>
                </div>
                <div class="td" title="{{$forum.nome}}">
                    {{$forum.nuovi}}
                </div>
            </div>
        {/foreach}
    </div>
{/foreach}