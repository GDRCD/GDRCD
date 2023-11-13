<div class="single_ticket">
    <div class="bottom_box">
        <div class="box post_left">
            <div class="avatar">
                <img src="{{$ticket.author_pic}}" alt="{{$ticket.author_name}}">
            </div>
            <div class="name">
                {{$ticket.author_name}}
            </div>
            <div class="date">
                {$ticket.date}
            </div>
        </div>
        <div class="box post_right">
            {$ticket.text}
        </div>
    </div>
</div>
