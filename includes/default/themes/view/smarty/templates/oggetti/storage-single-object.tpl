<div class="single_object_info">
    <div class="object_image">
        <img src="{{$immagine}}" alt="{{$nome}}">
    </div>
    <div class="object_info">
        <div class="object_name">{{$nome}}</div>
        <div class="object_sub_info">
            <div class="info_box info_left"></div>
            <div class="info_box info_center"></div>
            <div class="info_box info_right commands">

                {if $get_permission}
                    <form class="ajax_form" action="servizi/gruppi/ajax.php" data-callback="updateObjectView">
                        <input type="hidden" name="id" value="{{$id}}">
                        <input type="hidden" name="action" value="get_storage_item">
                        <button type="submit" ><i class="fas fa-hand-receiving" title="Prendi oggetto"></i></button>
                    </form>
                {/if}
            </div>
        </div>
    </div>


</div>