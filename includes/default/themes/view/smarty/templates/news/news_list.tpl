{foreach $news as $news_data}
    <div class="fake-table news_list_table">
        <div class="tr header">
            <div class="tr header-title">
                {{$news_data.type_data.nome}}
            </div>
            <div class="td">
                Titolo
            </div>
            <div class="td">
                Autore
            </div>
            <div class="td">
                Data
            </div>
        </div>
        {foreach $news_data.news as $single_news}
            <div class="tr single_news {if !$single_news.active}inactive{/if}">
                <div class="td" title="{{$single_news.title}}">
                    <a href="/main.php?page=news/index&op=read&news_id={{$single_news.id}}">
                        {if $single_news.to_read && $single_news.active}
                            <span class="to_read" title="Nuove risposte!"><i class="far fa-exclamation-circle"></i></span>
                        {/if}
                        {{$single_news.title}}
                    </a>
                </div>
                <div class="td" title="{{$single_news.author}}">
                    {{$single_news.author}}
                    {if $manage_permission}
                    ( <a href="main.php?page=scheda/index&pg={{$single_news.real_author_name}}&id_pg={{$single_news.real_author_id}}">
                        {$single_news.real_author_name}
                    </a>)
                        {/if}
                </div>
                <div class="td" title="{{$single_news.date}}">
                    {{$single_news.date}}
                </div>
            </div>
        {/foreach}
    </div>
{/foreach}