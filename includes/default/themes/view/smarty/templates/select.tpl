
<option value=""></option>
{foreach $options as $option}
    <option value="{{$option.{{$value_cell}}}}" {if $option.{{$value_cell}} == $selected_value || $option.{{$name_cell}} == $selected_value} selected {/if} >{{$option.{{$name_cell}}}}</option>
{/foreach}