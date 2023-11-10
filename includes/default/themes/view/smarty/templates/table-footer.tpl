<div class="tr footer">
    {if !empty($footer_text)}
        <span>{$footer_text}</span>
    {/if}
    {if !empty($footer_pagination)}
        <div class="tr pagination">
            {foreach $footer_pagination.pages as $pagination}
                {if $pagination.page == $footer_pagination.current}
                    <span class="current">{$pagination.page}</span>
                {else}
                    <a href="{$pagination.url}">{$pagination.page}</a>
                {/if}
            {/foreach}
        </div>
    {/if}
    {foreach $links as $link}
        <a href="{{$link.href}}">{{$link.text}}</a>
        {if $link.separator}|{/if}
    {/foreach}
</div>