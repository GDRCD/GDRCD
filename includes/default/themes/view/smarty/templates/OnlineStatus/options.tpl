<div class="container_set_online_status">
    <div class="container_status_title">Informazioni sullo stato Online</div>
    <form class="ajax_form online_status_form" action="online_status/ajax.php">
        {foreach $data as $type}
            <div class="status_type_container">
                <div class='subtitle'>{$type.title}</div>
                <ul>
                    {foreach $type.options as $option}
                        <li>
                            <input type='radio' name='online_status[online_{{$type.id}}]'
                                   {if $option.selected}checked{/if}
                                   value='{{$option.id}}'>{{$option.name}}</input>
                        </li>
                    {/foreach}
                </ul>
            </div>
        {/foreach}
        <div class="submit_input">
            <input type="submit" value="Seleziona">
            <input type="hidden" name="action" value="choose_status">
        </div>
    </form>
</div>