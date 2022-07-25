{foreach $body_rows as $log}
    <div class="tr">
        <div class="causale td" title="{{$log.title}}">
            {{$log.testo}}...
        </div>
        <div class="td">
            {{$log.destinatario}}
        </div>
        <div class="td">
            {{$log.creato_il}}
        </div>
        <div class="td">
            {{$log.autore}}
        </div>
    </div>
{/foreach}