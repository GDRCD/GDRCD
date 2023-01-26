<div class='single_input'>
    <div class='label'> Titolo</div>
    <input type='text' name='title'>
</div>

<div class='single_input'>
    <div class='label'> Descrizione</div>
    <textarea name='description'></textarea>
</div>

{if $conversations_active}
    <div class="single_input">
        <div class='label'> Convidi in:</div>
        <select name="conversation">
            {{$conversations}}
        </select>
    </div>

    <div class='single_input'>
        <div class='label'> Testo messaggio</div>
        <textarea name='conversation_text'></textarea>
    </div>

{/if}