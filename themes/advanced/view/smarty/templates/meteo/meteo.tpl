{if isset($moon)}
    <img title="{{$moon.Title}}" src="{{$moon.Img}}" alt="{{$moon.Title}}">
{/if}
{if isset($meteo)}
    <div class="meteo">
        Meteo: <br>
        {{$meteo.temp}}C - {{$meteo.vento}} <br>

        {if $meteo.img}
            <img alt="{{$meteo.meteo}}" src="{{$meteo.img}}" style="width: 20px">
        {/if}
         {{$meteo.meteo}}
    </div>
{/if}