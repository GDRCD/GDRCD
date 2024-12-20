{foreach $body_rows as $pg}
    <div class='tr'>

        
            
            
        <div class='td'>{{$pg.name_pv}}</div>
        <div class='td'>{{$pg.surname_pv}}</div>
        <div class='td'>{{$pg.fonte}}</div>
        <div class='td'>{{$pg.name}}</div>
        <div class='td'>{{$pg.surname}}</div>
        {if $pg.condivisione_attiva}

            <div class='td'>{{$pg.condivisibile}}</div>
            {if $pg.condivisione_img_attiva}
                    <div class='td'><img src="{{$pg.condivisibile_immagine}}"></div>
            {/if}
            <div class='td'>{{$pg.condivisibile_descrizione}}</div>
        {/if}

    </div>
{/foreach}