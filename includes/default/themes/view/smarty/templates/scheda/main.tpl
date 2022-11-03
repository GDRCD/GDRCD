<div class="first_box">
    <div class="left_avatar">
        <div class="scheda_name">{{$character_data.nome}} {{$character_data.cognome}}</div>
        <img src="{{$character_data.url_img}}" alt="">
    </div>
    <div class="right_data">
        <div class="general_title">Dati personaggio</div>

        <div class="single_info">
            <div class="label">Occupazione</div>
            <div class="value">{{$groups_icons}}</div>
        </div>

        <div class="single_info">
            <div class="label">Razza</div>
            <div class="value">{{$race_icon}}</div>
        </div>

        <div class="single_info">
            <div class="label">Esperienza</div>
            <div class="value">{{intval($character_data.esperienza)}}</div>
        </div>

        <div class="single_info">
            <div class="label">Punti ferita</div>
            <div class="value">{{$character_data.salute}}/{{$character_data.salute_max}}</div>
        </div>

        <div class="single_info">
            <div class="label">Data registrazione</div>
            <div class="value">{{$registration_day}}</div>
        </div>

        <div class="single_info">
            <div class="label">Ultimo login</div>
            <div class="value">{{$last_login}}</div>
        </div>

        {if $status_online_enabled}
            <div class="general_title">Status Online</div>
            {foreach $status_online as $index => $value }
                <div class="single_info">
                    <div class="label">{{$index}}</div>
                    <div class="value">{{$value}}</div>
                </div>
            {/foreach}
        {/if}

        <div class="message_link">
            <a href="main.php?page=messages_center&op=create&reply_dest={{$character_data.id}}">
                Invia un messaggio a {{$character_data.nome}}
            </a>
        </div>
    </div>
</div>