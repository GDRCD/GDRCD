{foreach $data as $type}
    <div class='subtitle'>{$type.title}</div>
    <ul>
        {foreach $type.options as $option}
            <li>
                <input type='radio' name='online_status[online_{{$option.id}}]' value='{{$option.id}}'>{{$option.name}}</input>
            </li>
        {/foreach}
    </ul>
{/foreach}