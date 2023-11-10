
<div class="general_title">Storico modifiche</div>

<div class="forum_post">
    <div class="single_forum_post {if $post.deleted}deleted{/if}">
        <div class="bottom_box">
            <div class="box post_left">
                <div class="name">
                    <a href="/main.php?page=scheda/index&id_pg={$post.author_id}">
                        {$post.author_name}
                    </a>
                </div>
                <div class="avatar">
                    <img src="{$post.author_avatar}">
                </div>
                <div class="date">
                    {$post.date}
                </div>
            </div>
            <div class="box post_right">
                {$post.text}
            </div>
        </div>
    </div>


    {foreach $history as $single_history}
        <div class="single_forum_post">
            <div class="bottom_box">
                <div class="box post_left">
                    <div class="name">
                        <a href="/main.php?page=scheda/index&id_pg={$single_history.author_id}">
                           Modificato da: {$single_history.author_name}
                        </a>
                    </div>
                    <div class="avatar">
                        <img src="{$single_history.author_avatar}">
                    </div>
                    <div class="date">
                        {$single_history.date}
                    </div>
                </div>
                <div class="box post_right">
                    {$single_history.text}
                </div>
            </div>
        </div>


    {/foreach}

</div>