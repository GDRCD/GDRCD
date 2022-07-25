<div class="tr header">
    {if $table_title}
        <div class="tr header-title">{$table_title}</div>
    {/if}
    {foreach $cells as $cell}
        <div class="td">{$cell}</div>
    {/foreach}
</div>