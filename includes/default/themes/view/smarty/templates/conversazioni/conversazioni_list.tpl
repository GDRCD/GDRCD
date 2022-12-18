{foreach $conversations as $conversation}
    <div class="single_conversation">
        <div class="box {if $conversation.new_messages}new{/if}">
            <div class="logo">
                <img src="{$conversation.immagine}" alt="{$conversation.nome}"/>
            </div>
            <div class="text">
                <div class="title" title="{$conversation.nome}">{$conversation.nome}</div>
                <p>{$conversation.descrizione}</p>
            </div>
        </div>
        <div class="commands">
            <div class="command" title="Leggi conversazione">
                <a href="/main.php?page=conversazioni/index&conversation={$conversation.id}">
                    <i class="fas fa-eye"></i>
                </a>
            </div>
            <div class="command" title="Vedi membri">
                <a href="/main.php?page=conversazioni/index&conversation={$conversation.id}&op=members">
                    <i class="fas fa-users"></i>
                </a>
            </div>
            {if $conversation.update_permission}
                <div class="command" title="Modifica conversazione">
                    <a href="/main.php?page=conversazioni/index&conversation={$conversation.id}&op=edit">
                        <i class="fa fa-pencil"></i>
                    </a>
                </div>
            {/if}
            {if $conversation.delete_permission}
                <div class="command" title="Elimina conversazione">
                    <a class='ajax_link' data-id='{{$conversation.id}}' data-action='delete_conversation' href='#'>
                        <i class="fa fa-trash"></i>
                    </a>
                </div>
            {/if}
        </div>
    </div>
{/foreach}
