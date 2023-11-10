<div class="event_tooltip_container">
    <div class="title">
       {{$event_data.titolo}}
    </div>
    <hr>
    <div class="description">
        {{$event_data.descrizione}}
    </div>
    <hr>
    <div class="date">
        <div class="start cell">
            <div class="label">
                Inizio
            </div>
            <div class="value">
                {{$event_data.start_format}}
            </div>
        </div>
        <div class="end cell">
            <div class="label">
                Fine
            </div>
            <div class="value">
                {{$event_data.end_format}}
            </div>
        </div>
    </div>
</div>