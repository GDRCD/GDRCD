<div class="menu_box">
    {foreach $links as $link}
        <a href='main.php?page={{$link.page}}'>
            <div class='single_menu'>{{$link.name}}</div>
        </a>
    {/foreach}
</div>