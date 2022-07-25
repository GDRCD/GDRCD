<div class='object_img'>
    <img src='/themes/advanced/imgs/items/{{$immagine}}'>
</div>
<div class='object_data'>
    <div class='object_name'>{{$nome}}</div>
    <div class='object_descr'>{{$descrizione}}</div>
    <div class='object_info'>Tipo : {{$tipo}}</div>
    <div class='object_info'>Cariche : {{$cariche}}</div>

    <div class="object_commands">
        {if $equip_permission}
            <form method="POST" class="form ajax_form" action="scheda/oggetti/ajax.php" data-callback="updateObjects">
                <div class="single_input">
                    <input type="hidden" name="object" value="{{$id}}">
                    <input type="hidden" name="action" value="equip">
                    <button type="submit">{{$indossato}}</button>
                </div>
            </form>
        {/if}

        {if $remove_permission}
            <form method="POST" class="form ajax_form" action="scheda/oggetti/ajax.php" data-callback="updateObjects">
                <div class="single_input">
                    <input type="hidden" name="object" value="{{$id}}">
                    <input type="hidden" name="action" value="remove">
                    <button type="submit">Getta</button>
                </div>
            </form>
        {/if}


    </div>


</div>