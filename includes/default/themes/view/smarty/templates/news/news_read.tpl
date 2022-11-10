<div class="single_news">
    <div class="bottom_box">
        <div class="box post_left">
            <div class="name">
                {{$news.author}}
                {if $news.manage_permission}
                    (<a href="/main.php?page=scheda/index&id_pg={$news.real_author_id}">
                        {$news.real_author_name}
                    </a>)
                {/if}
            </div>
            <div class="date">
                {$news.date}
            </div>
        </div>
        <div class="box post_right">
            {$news.text}
        </div>
    </div>
</div>
