<div class="conversation_header">
    <div class="img">
        <img src="{{$conversation.data.immagine}}" alt="immagine profilo">
    </div>
    <div class="title" title="{{$conversation.data.nome}}">
        {{$conversation.data.nome}}
    </div>
</div>
<div class="conversation_body">

    {foreach $conversation.messages as $message}
        <div class="single_message {if $message.is_me}mine_message{else}other_message{/if}">
            <div class="body">
                <div class="author">
                    <div class="img">
                        <img src="{{$message.author.url_img_chat}}" alt="immagine profilo">
                    </div>
                    <div class="name">
                        <a href="/main.php?page=scheda/index&id_pg={{$message.author.id}}">
                            {{$message.author.nome}}
                        </a>
                    </div>
                    {if $message.data.forum_post_id}
                        <div> - </div>
                        <div class="share">
                            Allegato:
                            <a href="/main.php?page=forum/index&op=post&post_id={{$message.data.forum_post_id}}&pagination=1">
                                {{$message.data.forum_post_name}}
                            </a>
                        </div>
                    {/if}
                </div>
                <div class="message">
                    <div class="text">
                        {{$message.data.testo}}
                    </div>
                    <div class="date">
                        {{$message.data.creato_il}}
                    </div>
                </div>
            </div>
        </div>
    {/foreach}
</div>
<div class="conversation_footer">
    <form method="POST" class="ajax_form" action="conversazioni/ajax.php" data-callback="invioMessaggioSuccess" data-swal="false" >
        <div class="single_input testo">
            <input type="text" name="testo" placeholder="Scrivi un messaggio qui" required>
        </div>
        <div class="single_input submit">
            <button type="submit"  title="Invia"><i class="fas fa-paper-plane"></i> </button>
            <input type="hidden" name="action" value="send_message">
            <input type="hidden" name="id" value="{{$conversation.data.id}}">
        </div>
    </form>
</div>