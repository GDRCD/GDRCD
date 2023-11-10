<div class="chat_top">
    {if $chat_icone }
        <span class='chat_icons'>
        {if $chat_avatar}
            <img src='{$img_chat}' class='chat_avatar'>
        {/if}
            {foreach $data_imgs as $data_img}
                <img class='chat_ico' src='{$link}{$data_img.Img}' title='{$data_img.Title}'>
            {/foreach}
    </span>
    {/if}

    <span class='chat_time'>{$ora}</span>
    <span class='chat_name'><a href='#'>{$mittente_nome}</a></span>
    <span class='chat_tag'> [{$tag}]</span>
</div>
<div class='chat_msg' style='color:{$colore_descr}; font-size:{$size};'>{$testo}</div>
