<div class="menu_box">
    {foreach $categories as $category}
        <div class='single_section'>
            <div class='section_title'> {{$category.name}}</div>
            <div class='box_input'>
                {foreach $category.links as $link}
                    <a href='main.php?page={{$link.page}}'>
                        <div class='single_menu'>{{$link.name}}</div>
                    </a>
                {/foreach}
            </div>
        </div>
    {/foreach}

</div>

