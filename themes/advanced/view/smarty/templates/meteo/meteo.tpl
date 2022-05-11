{if isset($moon)}
    <img title="{{$moon.Title}}" src="{{$moon.Img}}">
{/if}
{if isset($meteo)}
    <div class="meteo">
        Meteo: <br>
        {{$meteo.temp}}C <br> {{$meteo.vento}} <br> {{$meteo.meteo}}
    </div>
{/if}