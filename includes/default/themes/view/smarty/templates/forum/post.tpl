{foreach $posts as $post}
    <div class="single_forum_post {if $post.deleted}deleted{/if}">
        <div class="forum_post_commands">
            {if $post.edit_permission}
                <div class="single_command edit_post" title="Modifica">
                    <a href="/main.php?page=forum/index&op=post_edit&post_id={{$post.id}}">
                        <i class="fas fa-edit"></i>
                    </a>
                </div>
            {/if}

            {if $post.delete_permission && !$post.deleted}
                <form method="POST" class="form ajax_form" action="forum/post/ajax.php"
                      data-callback="updatePosts">
                    <div class="single_input single_command">
                        <input type="hidden" name="post_id" value="{{$post.id}}">
                        <input type="hidden" name="pagination" value="{{$pagination}}">
                        <input type="hidden" name="action" value="delete_post">
                        <button type="submit"><i class="fas fa-trash"></i></button>
                    </div>
                </form>
            {/if}

            {if $post.admin_permission && $post.deleted}
                <form method="POST" class="form ajax_form" action="forum/post/ajax.php"
                      data-callback="updatePosts">
                    <div class="single_input single_command">
                        <input type="hidden" name="post_id" value="{{$post.id}}">
                        <input type="hidden" name="pagination" value="{{$pagination}}">
                        <input type="hidden" name="action" value="restore_post">
                        <button type="submit">
                            <i class="fas fa-trash-restore"></i>
                        </button>
                    </div>
                </form>
            {/if}

            {if $post.admin_permission && $post.padre}

                <!-- CHIUDI -->
                <form method="POST" class="form ajax_form" action="forum/post/ajax.php"
                      data-callback="updatePosts">
                    <div class="single_input single_command">
                        <input type="hidden" name="post_id" value="{{$post.id}}">
                        <input type="hidden" name="pagination" value="{{$pagination}}">
                        <input type="hidden" name="action" value="lock_post">
                        {if $post['closed']}
                            <button type="submit" title="Chiuso">
                                <span class="lock_closed">
                                    <i class="fas fa-lock"></i>
                                </span>
                            </button>
                        {else}
                            <button type="submit" title="Chiuso">
                                <span class="lock_open">
                                    <i class="fas fa-lock-open"></i>
                                </span>
                            </button>
                        {/if}
                    </div>
                </form>

                <!-- IMPORTANTE -->
                <form method="POST" class="form ajax_form" action="forum/post/ajax.php"
                      data-callback="updatePosts">
                    <div class="single_input single_command">
                        <input type="hidden" name="post_id" value="{{$post.id}}">
                        <input type="hidden" name="pagination" value="{{$pagination}}">
                        <input type="hidden" name="action" value="important_post">
                        {if $post['important']}
                            <button type="submit" title="Importante">
                                <span class="important">
                                    <i class="fas fa-stars"></i>
                                </span>
                            </button>
                        {else}
                            <button type="submit" title="Non Importante">
                                <span class="not_important">
                                    <i class="far fa-stars"></i>
                                </span>
                            </button>
                        {/if}
                    </div>
                </form>
            {/if}
        </div>
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
{/foreach}