
<option value="">{$label}</option>
{foreach $options as $option}
    <option value="{{$option.{{$value_cell}}}}"
            {if $option.{{$value_cell}}|in_array:$selected_value || $option.{{$name_cell}}|in_array:$selected_value}selected{/if}>
        {{$option.{{$name_cell}}}}
    </option>
{/foreach}