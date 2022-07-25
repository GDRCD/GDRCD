<div class='single_object' title='{$nome}'>
    <div class='img'><img src='{$immagine}'></div>

    {if empty($id)}
        <div class='name'>{$nome}</div>
    {else}
        <div class='name' data-id="{$id}">{$nome}</div>
    {/if}
</div>