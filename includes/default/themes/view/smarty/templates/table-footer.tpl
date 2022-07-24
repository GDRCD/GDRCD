<div class="tr footer">
    {if !empty($footer_text)}
        <span>{$footer_text}</span>
    {/if}
    {foreach $links as $link}
            <a href="{{$link.href}}">{{$link.text}}</a> {if $link.separator}|{/if}
    {/foreach}
</div>