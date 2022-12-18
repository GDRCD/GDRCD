<div class="new_conversation">
    <form method="POST" class="ajax_form" action="conversazioni/ajax.php"
          data-callback="editConvSuccess"
          data-swal="false">
        <div class="single_input">
            <div class="label">Titolo</div>
            <input type="text" name="title" value="{{$data.nome}}" required>
        </div>

        <div class="single_input">
            <div class="label">Immagine</div>
            <input type="text" name="img" value="{{$data.immagine}}" required>
        </div>

        <div class="single_input">
            <div class="label">Seleziona membri</div>
            <select name="members[]" class="chosen-select" multiple data-placeholder="Seleziona membri">
                {{$personaggi}}
            </select>
        </div>

        <div class="single_input">
            <div class="label">Proprietario</div>
            <select name="owner" data-placeholder="Seleziona proprietario">
                {{$personaggi_proprietario}}
            </select>
        </div>

        <div class="single_input submit">
            <button type="submit" title="Invia"><i class="fas fa-paper-plane"></i></button>
            <input type="hidden" name="action" value="edit_conversation">
            <input type="hidden" name="id" value="{{$data.id}}">
        </div>
    </form>
</div>