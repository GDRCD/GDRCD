{foreach $data as $section}
    <div class='single_section'>
        <div class='form_title'>{$section.name}</div>
        <div class='box_input'>
            {foreach $section.constants as $constant}
                <div class='single_input w-33'>
                    <div class='label'>{{$constant.label}}</div>
                    {{$constant.input}}
                </div>
            {/foreach}
        </div>
    </div>
{/foreach}