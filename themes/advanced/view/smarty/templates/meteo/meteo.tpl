{if isset($moon)}
    <img title="{{$moon.Title}}" src="{{$moon.Img}}">
{/if}
{if isset($meteo)}
    <div class="meteo">
        Temperatura: {{$meteo.meteo}} <br>
        Vento: {{$meteo.vento}}
    </div>
{/if}